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
$DELAY_ENTRE_CORREOS = 3;  // 3 segundos entre cada correo
$LOTE_SIZE = 15;           // 15 correos por lote
$DELAY_ENTRE_LOTES = 45;   // 45 segundos entre lotes

// ==========================================
// 🔹 ARCHIVO DE ESTADO
// ==========================================
$archivo_estado = __DIR__ . '/estado_envio_rueda.json';
$archivo_log = __DIR__ . '/envio_rueda_detallado.log';

function actualizar_estado($data) {
    global $archivo_estado;
    try {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($archivo_estado, $json, LOCK_EX);
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
// 🔹 CONSULTA SQL - SOLO EMPRESAS QUE DIJERON SÍ A LA RUEDA
// ==========================================
// Buscar registros donde rueda = "Si" o "Sí" (case insensitive)
$sql = "SELECT * FROM registros WHERE (LOWER(rueda) = 'si' OR LOWER(rueda) = 'sí') ORDER BY id";

log_detallado("Ejecutando consulta SQL para empresas interesadas en Rueda de Negocios");
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    log_detallado("ERROR: No hay registros de empresas interesadas en Rueda de Negocios");
    die(json_encode(['status' => 'error', 'message' => 'No hay empresas interesadas en Rueda de Negocios']));
}

$total_registros = $result->num_rows;
inicializar_estado($total_registros);

log_detallado("Empresas interesadas en Rueda de Negocios encontradas: $total_registros");

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
    $empresa  = ent($row['empresa']);
    $cargo    = ent($row['cargo']);

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
        $mail->Subject = "Rueda de Negocios B2B - Nodo Bioce&aacute;nico 2025";

        $mail->Body = "
<div style='margin:0;padding:0;background:#f4f7fa;font-family:Arial, sans-serif;'>
    <div style='max-width:650px;margin:30px auto;background:white;border-radius:12px;
        overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.12);'>

        <!-- Header -->
        <div style='background:linear-gradient(135deg, #004aad 0%, #0066cc 100%);padding:35px 25px;text-align:center;color:white;'>
            <img src='https://www.bioceanicocentral.cl/wp-content/uploads/2025/11/logonuevo.png'
                alt='Nodo Bioce&aacute;nico'
                style='width:100px;margin-bottom:15px;'>

            <h2 style='margin:10px 0 5px;font-size:24px;font-weight:700;'>
                Rueda de Negocios B2B
            </h2>
            <p style='margin:5px 0 0;font-size:15px;opacity:0.95;'>
                28 de Noviembre, 2025 | 11:00 - 12:15 hrs
            </p>
        </div>

        <!-- Content -->
        <div style='padding:35px 30px;color:#333;line-height:1.7;font-size:15px;'>
            <p style='margin-bottom:20px;'>Hola <strong>$nombre $apellido</strong>,</p>

            <p style='margin-bottom:20px;'>
                Te contactamos porque <strong>manifestaste interés en participar</strong> en la
                <strong>Rueda de Negocios B2B</strong> del Nodo Bioceánico 2025.
            </p>

            <div style='background:#e8f4f8;border-left:4px solid #004aad;padding:20px;margin:25px 0;border-radius:6px;'>
                <p style='margin:0;font-size:14px;color:#555;'>
                    <strong style='color:#004aad;'>📌 Importante:</strong>
                    Este correo <strong>NO es spam</strong>. Te lo enviamos porque en tu registro
                    indicaste que te interesaba esta actividad.
                </p>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px;font-size:18px;'>
                🤝 ¿Cómo funciona?
            </h3>

            <div style='background:#f8f9fb;padding:20px;border-radius:8px;margin-bottom:25px;'>
                <ul style='margin:0;padding-left:20px;line-height:1.9;'>
                    <li><strong>15 mesas físicas</strong> en el evento</li>
                    <li><strong>4 bloques de 15 minutos</strong> (11:00 - 12:15 hrs)</li>
                    <li>Empresas <strong>demandantes</strong> reservan mesas y horarios</li>
                    <li>Empresas <strong>oferentes</strong> solicitan reuniones en mesas disponibles</li>
                    <li>Reuniones confirmadas de <strong>15 minutos cara a cara</strong></li>
                </ul>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px;font-size:18px;'>
                📋 Modalidades de Participación
            </h3>

            <table style='width:100%;border-collapse:collapse;margin-bottom:25px;'>
                <tr>
                    <td style='background:#004aad;color:white;padding:12px;font-weight:bold;border-radius:6px 6px 0 0;'>
                        🏢 Busco Servicios (Demandante)
                    </td>
                </tr>
                <tr>
                    <td style='background:#f0f4ff;padding:15px;border:1px solid #d0deff;border-radius:0 0 6px 6px;'>
                        • Reservas tu mesa y bloques horarios<br>
                        • Recibes solicitudes de reunión<br>
                        • Aceptas o rechazas reuniones<br>
                        • <strong>Máximo 30 empresas</strong>
                    </td>
                </tr>
            </table>

            <table style='width:100%;border-collapse:collapse;margin-bottom:30px;'>
                <tr>
                    <td style='background:#2ecc71;color:white;padding:12px;font-weight:bold;border-radius:6px 6px 0 0;'>
                        🤝 Ofrezco Servicios (Oferente)
                    </td>
                </tr>
                <tr>
                    <td style='background:#f0fff4;padding:15px;border:1px solid #c8f0d8;border-radius:0 0 6px 6px;'>
                        • Exploras agendas de empresas demandantes<br>
                        • Solicitas reuniones en horarios disponibles<br>
                        • Esperas confirmación<br>
                        • <strong>Sin límite de empresas</strong>
                    </td>
                </tr>
            </table>

            <div style='text-align:center;margin:35px 0;'>
                <a href='https://www.bioceanicocentral.cl/registro/rueda/views/registro.php'
                   style='background:#004aad;color:white;padding:16px 40px;font-size:17px;
                   font-weight:bold;text-decoration:none;border-radius:8px;display:inline-block;
                   box-shadow:0 4px 12px rgba(0,74,173,0.3);'>
                   ✅ Registrarme Ahora
                </a>
            </div>

            <div style='background:#fff9e6;border:1px solid #ffe066;padding:18px;border-radius:8px;margin-top:30px;'>
                <p style='margin:0;font-size:14px;color:#856404;'>
                    <strong>⏰ No esperes más:</strong> Los cupos para empresas demandantes son limitados (máximo 30).
                    ¡Regístrate hoy mismo!
                </p>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px;font-size:18px;'>
                📍 Detalles del Evento
            </h3>

            <table style='width:100%;border-collapse:collapse;font-size:15px;'>
                <tr><td style='padding:8px 0;color:#555;'><strong>📅 Fecha:</strong></td><td>28 de Noviembre, 2025</td></tr>
                <tr><td style='padding:8px 0;color:#555;'><strong>🕐 Horario:</strong></td><td>11:00 - 12:15 hrs (4 bloques de 15 min)</td></tr>
                <tr><td style='padding:8px 0;color:#555;'><strong>📍 Lugar:</strong></td><td>Arica, Chile</td></tr>
                <tr><td style='padding:8px 0;color:#555;'><strong>🏢 Tu Empresa:</strong></td><td>$empresa</td></tr>
            </table>

            <p style='margin-top:30px;font-size:13px;color:#777;text-align:center;'>
                Si tienes dudas, puedes responder directamente a este correo.
            </p>
        </div>

        <!-- Footer -->
        <div style='background:#f0f3f8;padding:20px;text-align:center;font-size:12px;color:#555;'>
            <p style='margin:0 0 10px;'>
                Nodo Bioceánico Central 2025<br>
                Conectando empresas, creando oportunidades
            </p>
            <p style='margin:0;color:#999;font-size:11px;'>
                &copy; 2025 Nodo Bioceánico - Todos los derechos reservados
            </p>
        </div>
    </div>
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
    sleep($DELAY_ENTRE_CORREOS);

    // Pausa entre lotes
    if ($contador_lote >= $LOTE_SIZE && ($enviados + $errores) < $total_registros) {
        log_detallado("=== FIN DE LOTE - Pausa de $DELAY_ENTRE_LOTES segundos ===");
        sleep($DELAY_ENTRE_LOTES);
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
    'enviados' => $enviados,
    'errores' => $errores,
    'total' => $total_registros
], JSON_UNESCAPED_UNICODE);

$conn->close();
log_detallado("=== FIN DEL SCRIPT ===");
exit;
?>
