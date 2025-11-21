<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==========================================
// 🔹 CONFIGURACIÓN INICIAL ULTRA SEGURA
// ==========================================
date_default_timezone_set('America/Santiago');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('max_execution_time', 0);
set_time_limit(0);

// Limpiar TODOS los buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Handler de errores silencioso
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error PHP: $errstr en $errfile:$errline");
    return true;
});

// Handler de excepciones
set_exception_handler(function($e) {
    error_log("Excepción: " . $e->getMessage());
});

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Connection: close');

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ==========================================
// 🔹 CONFIGURACIÓN DE DELAYS
// ==========================================
$DELAY_ENTRE_CORREOS = 3;  // 3 segundos entre cada correo (MÁS SEGURO)
$LOTE_SIZE = 15;           // 15 correos por lote (MÁS CONSERVADOR)
$DELAY_ENTRE_LOTES = 45;   // 45 segundos entre lotes (PAUSA LARGA)

// ==========================================
// 🔹 ARCHIVOS DE ESTADO - ESPECÍFICOS PARA RUEDA
// ==========================================
$archivo_estado = __DIR__ . '/estado_envio_rueda.json';
$archivo_log = __DIR__ . '/envio_rueda_detallado.log';

function actualizar_estado($data) {
    global $archivo_estado;
    try {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($archivo_estado, $json, LOCK_EX);
        // Forzar escritura inmediata
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($archivo_estado, true);
        }
        clearstatcache(true, $archivo_estado);
    } catch (Exception $e) {
        error_log("Error actualizando estado: " . $e->getMessage());
    }
}

function log_detallado($mensaje) {
    global $archivo_log;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($archivo_log, "[$timestamp] $mensaje\n", FILE_APPEND | LOCK_EX);
}

function inicializar_estado($total) {
    actualizar_estado([
        'iniciado' => date('Y-m-d H:i:s'),
        'estado' => 'iniciando',
        'campana' => 'Invitación Rueda de Negocios',
        'total' => $total,
        'enviados' => 0,
        'errores' => 0,
        'progreso_porcentaje' => 0,
        'ultimo_envio' => 'Iniciando proceso...',
        'logs' => [],
        'errores_detalle' => []
    ]);
    log_detallado("=== INICIO DEL PROCESO - RUEDA DE NEGOCIOS ===");
    log_detallado("Total de correos a enviar: $total");
}

// ==========================================
// 🔹 CONEXIÓN A BD
// ==========================================
$servername = "localhost";
$username   = "seidev";
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Error BD: ' . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");

// ==========================================
// 🔹 FUNCIÓN PARA CONVERTIR ACENTOS
// ==========================================
function ent($txt) {
    return htmlentities($txt, ENT_QUOTES, 'UTF-8');
}

// ==========================================
// 🔹 CONSULTA SQL - FILTRAR POR RUEDA = 'Si'
// ==========================================
$sql = "SELECT * FROM registros WHERE rueda = 'Si' ORDER BY id";

log_detallado("Ejecutando consulta SQL: Filtrando por rueda = 'Si'");
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    log_detallado("ERROR: No hay registros con rueda='Si' para enviar");
    die(json_encode(['status' => 'error', 'message' => 'No hay registros con interés en rueda de negocios']));
}

$total_registros = $result->num_rows;
inicializar_estado($total_registros);

log_detallado("Registros encontrados con rueda='Si': $total_registros");

// ==========================================
// 🔹 INICIO BUCLE DE ENVÍOS
// ==========================================
$enviados = 0;
$errores  = 0;
$contador_lote = 0;
$logs = [];
$errores_detalle = [];

while ($row = $result->fetch_assoc()) {

    $contador_lote++;
    $id_registro = $row['id'];

    log_detallado("--- Procesando registro ID: $id_registro ---");

    // Convertir cada campo
    $nombre   = ent($row['nombre']);
    $apellido = ent($row['apellido']);
    $email    = ent($row['email']);
    $telefono = ent($row['telefono'] ?? 'N/A');
    $pais     = ent($row['pais'] ?? 'N/A');
    $empresa  = ent($row['empresa'] ?? 'N/A');
    $cargo    = ent($row['cargo'] ?? 'N/A');
    $sector   = ent($row['sector'] ?? 'N/A');
    $codigo   = ent($row['codigo_registro']);

    $qr_url = "https://www.bioceanicocentral.cl/registro/qrcodes/$codigo.png";

    $email_limpio = html_entity_decode($email);
    log_detallado("Preparando envío a: $email_limpio");

    // ==========================================
    // 🔹 CONFIGURAR MAILER CON TRY-CATCH
    // ==========================================
    $mail = new PHPMailer(true);

    try {
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isSMTP();
        $mail->Host       = '10.24.254.104';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'contacto@bioceanicocentral.cl';
        $mail->Password   = '@@Bio2025';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->Timeout    = 30;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioce&aacute;nico 2025');
        $mail->addAddress($email_limpio, html_entity_decode("$nombre $apellido"));
        $mail->addReplyTo('contacto@bioceanicocentral.cl', 'Nodo Bioceánico 2025');
        $mail->addCustomHeader('X-Campaign', 'Rueda-Negocios-2025');
        $mail->addCustomHeader('X-Priority', '3');

        $mail->isHTML(true);
        $mail->Subject = "Invitaci&oacute;n Especial: Rueda de Negocios – Nodo Bioce&aacute;nico 2025";

        // ==========================================
        // 🔹 PLANTILLA DE EMAIL - RUEDA DE NEGOCIOS
        // ==========================================
        $mail->Body = "
<div style='margin:0;padding:0;background:#e9eef5;font-family:Arial, sans-serif;'>
    <div style='max-width:650px;margin:30px auto;background:white;border-radius:12px;
        overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.12);'>

        <div style='background:linear-gradient(135deg, #004aad 0%, #0066cc 100%);padding:35px 25px;text-align:center;color:white;'>
            <img src='https://www.bioceanicocentral.cl/wp-content/uploads/2025/11/logonuevo.png'
                alt='Nodo Bioce&aacute;nico'
                style='width:120px;max-width:35%;margin-bottom:15px;'>

            <h2 style='margin:10px 0 0;font-size:28px;font-weight:700;'>
                Rueda de Negocios 2025
            </h2>

            <p style='margin:8px 0 0;font-size:15px;opacity:0.95;'>
                Oportunidad exclusiva de networking empresarial
            </p>
        </div>

        <div style='padding:40px 35px;color:#333;line-height:1.7;font-size:15px;'>
            <p style='font-size:16px;'>Estimado/a <strong>$nombre $apellido</strong>,</p>

            <p>
                En su registro para el evento <strong>Nodo Bioce&aacute;nico Central 2025</strong>,
                usted manifest&oacute; inter&eacute;s en participar de nuestra
                <strong style='color:#004aad;'>Rueda de Negocios</strong>.
            </p>

            <div style='background:#f0f7ff;border-left:4px solid #004aad;padding:20px;margin:25px 0;border-radius:6px;'>
                <p style='margin:0 0 10px 0;font-size:16px;font-weight:bold;color:#004aad;'>
                    ¡Su lugar está reservado!
                </p>
                <p style='margin:0;font-size:14px;color:#555;'>
                    Lo invitamos cordialmente a formar parte de esta instancia &uacute;nica de conexi&oacute;n
                    empresarial que reunir&aacute; a l&iacute;deres del comercio internacional, log&iacute;stica
                    y desarrollo econ&oacute;mico de la regi&oacute;n bioce&aacute;nica.
                </p>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px 0;font-size:18px;'>
                ¿Qu&eacute; es la Rueda de Negocios?
            </h3>

            <p>
                Es un espacio dise&ntilde;ado para facilitar <strong>encuentros empresariales directos</strong>,
                donde podr&aacute; establecer contactos estrat&eacute;gicos, explorar oportunidades de
                colaboraci&oacute;n y expandir su red de negocios en el corredor bioce&aacute;nico.
            </p>

            <h3 style='color:#004aad;margin:25px 0 15px 0;font-size:18px;'>
                Beneficios de participar:
            </h3>

            <ul style='color:#555;line-height:1.8;'>
                <li>Reuniones 1 a 1 con empresarios y tomadores de decisi&oacute;n</li>
                <li>Acceso a oportunidades de comercio e inversi&oacute;n internacional</li>
                <li>Networking con l&iacute;deres de Chile, Bolivia, Paraguay, Argentina y Brasil</li>
                <li>Agenda personalizada de reuniones seg&uacute;n su perfil empresarial</li>
            </ul>

            <div style='background:#fff8e1;border:2px solid #ffc107;padding:20px;margin:30px 0;border-radius:8px;'>
                <p style='margin:0 0 10px 0;font-weight:bold;color:#d68000;font-size:16px;'>
                    📋 Pr&oacute;ximos pasos:
                </p>
                <ol style='margin:5px 0;padding-left:20px;color:#555;'>
                    <li>Confirme su asistencia respondiendo este correo antes del <strong>23 de noviembre</strong></li>
                    <li>Indique el tipo de contactos empresariales que le interesan</li>
                    <li>Recibir&aacute; su agenda personalizada de reuniones</li>
                </ol>
            </div>

            <h3 style='color:#004aad;margin:25px 0 12px 0;font-size:17px;'>
                Detalles del Evento
            </h3>

            <table style='width:100%;border-collapse:collapse;font-size:15px;margin-bottom:25px;'>
                <tr>
                    <td style='padding:8px 0;color:#555;width:35%;'><strong>📅 Fechas:</strong></td>
                    <td>26, 27 y 28 de Noviembre 2025</td>
                </tr>
                <tr>
                    <td style='padding:8px 0;color:#555;'><strong>📍 Lugar:</strong></td>
                    <td>Arica, Chile</td>
                </tr>
                <tr>
                    <td style='padding:8px 0;color:#555;'><strong>🏢 Su empresa:</strong></td>
                    <td>$empresa</td>
                </tr>
                <tr>
                    <td style='padding:8px 0;color:#555;'><strong>💼 Cargo:</strong></td>
                    <td>$cargo</td>
                </tr>
                <tr>
                    <td style='padding:8px 0;color:#555;'><strong>🔖 Sector:</strong></td>
                    <td>$sector</td>
                </tr>
            </table>

            <div style='text-align:center;margin:35px 0 25px 0;'>
                <p style='margin-bottom:15px;color:#555;font-size:14px;'>Su c&oacute;digo QR de acceso:</p>
                <div style='display:inline-block;background:white;padding:15px;border-radius:12px;
                    border:3px solid #004aad;box-shadow:0 4px 12px rgba(0,0,0,0.15);'>
                    <img src='$qr_url' alt='QR' style='width:200px;border-radius:6px;'>
                </div>
                <p style='margin-top:12px;font-size:16px;color:#004aad;'>
                    C&oacute;digo: <strong>$codigo</strong>
                </p>
            </div>

            <div style='text-align:center;margin-top:35px;'>
                <a href='https://www.bioceanicocentral.cl'
                   style='background:#004aad;color:white;padding:15px 35px;font-size:16px;
                   font-weight:bold;text-decoration:none;border-radius:8px;display:inline-block;
                   box-shadow:0 4px 10px rgba(0,0,0,0.2);transition:all 0.3s;'>
                   Confirmar mi participaci&oacute;n
                </a>
            </div>

            <p style='margin-top:40px;font-size:13px;color:#777;text-align:center;line-height:1.6;'>
                Para confirmar su asistencia o consultas adicionales, puede responder directamente a este correo
                o contactarnos a <a href='mailto:contacto@bioceanicocentral.cl' style='color:#004aad;'>contacto@bioceanicocentral.cl</a>
            </p>

            <p style='margin-top:25px;font-size:14px;color:#555;text-align:center;font-style:italic;'>
                ¡Esperamos contar con su presencia para hacer de esta rueda de negocios un &eacute;xito!
            </p>
        </div>

        <div style='background:#f0f3f8;padding:20px;text-align:center;font-size:12px;color:#555;'>
            <p style='margin:0 0 5px 0;font-weight:bold;'>Nodo Bioce&aacute;nico Central 2025</p>
            <p style='margin:0;'>Arica, Chile &mdash; Conectando mercados, construyendo futuro</p>
            <p style='margin:10px 0 0 0;'>&copy; 2025 Todos los derechos reservados.</p>
        </div>
    </div>

    <img src='https://www.bioceanicocentral.cl/registro/track.php?code=$codigo'
         width='1' height='1' style='display:none;' alt=''>
</div>";

        // ENVIAR
        $mail->send();
        $enviados++;

        log_detallado("✓ ENVIADO exitosamente a: $email_limpio");

        $log_item = [
            'timestamp' => date('H:i:s'),
            'email' => $email_limpio,
            'nombre' => html_entity_decode("$nombre $apellido"),
            'empresa' => html_entity_decode($empresa),
            'status' => 'enviado'
        ];

        $logs[] = $log_item;

        // Actualizar estado
        $progreso = round(($enviados + $errores) / $total_registros * 100, 1);
        actualizar_estado([
            'iniciado' => date('Y-m-d H:i:s'),
            'estado' => 'enviando',
            'campana' => 'Invitación Rueda de Negocios',
            'total' => $total_registros,
            'enviados' => $enviados,
            'errores' => $errores,
            'progreso_porcentaje' => $progreso,
            'ultimo_envio' => $email_limpio,
            'logs' => array_slice($logs, -10),
            'errores_detalle' => $errores_detalle
        ]);

    } catch (Exception $e) {
        $errores++;

        $error_msg = $mail->ErrorInfo;
        log_detallado("✗ ERROR enviando a: $email_limpio - $error_msg");

        $error_item = [
            'timestamp' => date('H:i:s'),
            'email' => $email_limpio,
            'nombre' => html_entity_decode("$nombre $apellido"),
            'error' => $error_msg
        ];

        $errores_detalle[] = $error_item;

        $log_item = [
            'timestamp' => date('H:i:s'),
            'email' => $email_limpio,
            'nombre' => html_entity_decode("$nombre $apellido"),
            'status' => 'error'
        ];

        $logs[] = $log_item;

        // Actualizar estado con error
        $progreso = round(($enviados + $errores) / $total_registros * 100, 1);
        actualizar_estado([
            'iniciado' => date('Y-m-d H:i:s'),
            'estado' => 'enviando',
            'campana' => 'Invitación Rueda de Negocios',
            'total' => $total_registros,
            'enviados' => $enviados,
            'errores' => $errores,
            'progreso_porcentaje' => $progreso,
            'ultimo_envio' => $email_limpio,
            'logs' => array_slice($logs, -10),
            'errores_detalle' => $errores_detalle
        ]);
    }

    // ==========================================
    // 🔹 DELAYS REALES Y FORZADOS
    // ==========================================

    log_detallado("Esperando $DELAY_ENTRE_CORREOS segundos...");
    sleep($DELAY_ENTRE_CORREOS); // DELAY ENTRE CADA CORREO

    // Pausa entre lotes
    if ($contador_lote >= $LOTE_SIZE && ($enviados + $errores) < $total_registros) {
        log_detallado("=== FIN DE LOTE - Pausa de $DELAY_ENTRE_LOTES segundos ===");
        sleep($DELAY_ENTRE_LOTES); // PAUSA LARGA
        $contador_lote = 0;
        log_detallado("=== REANUDANDO ENVÍOS ===");
    }

    // Liberar memoria
    unset($mail);
}

// ==========================================
// 🔹 FINALIZAR
// ==========================================
log_detallado("=== PROCESO COMPLETADO ===");
log_detallado("Total enviados: $enviados");
log_detallado("Total errores: $errores");

actualizar_estado([
    'iniciado' => date('Y-m-d H:i:s'),
    'estado' => 'completado',
    'campana' => 'Invitación Rueda de Negocios',
    'total' => $total_registros,
    'enviados' => $enviados,
    'errores' => $errores,
    'progreso_porcentaje' => 100,
    'ultimo_envio' => 'Proceso completado',
    'logs' => array_slice($logs, -20),
    'errores_detalle' => $errores_detalle
]);

echo json_encode([
    'status' => 'completado',
    'campana' => 'Rueda de Negocios',
    'enviados' => $enviados,
    'errores' => $errores,
    'total' => $total_registros
], JSON_UNESCAPED_UNICODE);

$conn->close();
log_detallado("=== FIN DEL SCRIPT ===");
exit;
?>
