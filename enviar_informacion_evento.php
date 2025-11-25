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
$archivo_estado = __DIR__ . '/estado_envio_evento.json';
$archivo_log = __DIR__ . '/envio_evento.log';

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
    log_detallado("=== INICIO DEL PROCESO - ENVÍO INFORMACIÓN EVENTO ===");
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
// 🔹 CONSULTA SQL - TODOS LOS REGISTROS
// ==========================================
$sql = "SELECT * FROM registros ORDER BY id";

log_detallado("Ejecutando consulta SQL");
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    log_detallado("ERROR: No hay registros para enviar");
    die(json_encode(['status' => 'error', 'message' => 'No hay registros en la base de datos']));
}

$total_registros = $result->num_rows;
inicializar_estado($total_registros);

log_detallado("Registros encontrados: $total_registros");

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

        $mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioce&aacute;nico Central 2025');
        $mail->addAddress($email_limpio, html_entity_decode("$nombre $apellido"));
        $mail->addReplyTo('contacto@bioceanicocentral.cl', 'Nodo Bioceánico Central 2025');
        $mail->addCustomHeader('X-Campaign', 'Informacion-Evento-2025');
        $mail->addCustomHeader('X-Priority', '3');

        $mail->isHTML(true);
        $mail->Subject = "Ultimas Informaciones - Nodo Bioce&aacute;nico Central Arica 2025";

        $mail->Body = "
<div style='margin:0;padding:0;background:#e9eef5;font-family:Arial, sans-serif;'>
    <div style='max-width:650px;margin:30px auto;background:white;border-radius:12px;
        overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.12);'>

        <!-- HEADER -->
        <div style='background:#004aad;padding:30px 25px;text-align:center;color:white;'>
            <img src='https://www.bioceanicocentral.cl/wp-content/uploads/2025/11/logonuevo.png'
                alt='Nodo Bioce&aacute;nico'
                style='width:120px;max-width:35%;margin-bottom:12px;'>

            <h2 style='margin:10px 0 0;font-size:26px;font-weight:700;'>
                Nodo Bioce&aacute;nico Central 2025
            </h2>

            <p style='margin:5px 0 0;font-size:14px;opacity:0.9;'>
                Arica, Chile &mdash; 26, 27 y 28 de Noviembre
            </p>
        </div>

        <!-- CONTENIDO -->
        <div style='padding:35px;color:#333;line-height:1.6;font-size:15px;'>
            <p>Hola <strong>$nombre $apellido</strong>,</p>

            <p style='margin-bottom:20px;'>
                ¡Estamos a d&iacute;as de vivir uno de los encuentros m&aacute;s importantes para la Macrozona Andina!
                Aqu&iacute; te dejamos la informaci&oacute;n clave para tu asistencia a <strong>Nodo Bioce&aacute;nico Central 2025</strong>:
            </p>

            <!-- MIÉRCOLES 26 -->
            <div style='background:#f8f9fb;padding:20px;border-left:4px solid #004aad;margin-bottom:20px;border-radius:8px;'>
                <p style='margin:0 0 8px;font-size:16px;'>
                    <strong>📌 Mi&eacute;rcoles 26 de noviembre</strong> &ndash; Centro Tur&iacute;stico Integral (EPA)
                </p>
                <p style='margin:0;color:#555;font-size:14px;'>
                    🔒 Actividad exclusiva, con ingreso solo mediante invitaci&oacute;n especial<br>
                    Este es un espacio cerrado y de aforo limitado.
                </p>
            </div>

            <!-- JUEVES 27 -->
            <div style='background:#f8f9fb;padding:20px;border-left:4px solid #004aad;margin-bottom:20px;border-radius:8px;'>
                <p style='margin:0 0 8px;font-size:16px;'>
                    <strong>📌 Jueves 27 de noviembre</strong> &ndash; Hotel Antay
                </p>
                <p style='margin:0;color:#555;font-size:14px;'>
                    🎟️ Ingreso exclusivo con QR de registro<br>
                    🕣 Acreditaci&oacute;n: desde las 08:30 hrs<br>
                    🕕 T&eacute;rmino de jornada: 18:00 hrs<br><br>
                    Prep&aacute;rate para <em>workshops</em>, speakers internacionales y nacionales, espacios de vinculaci&oacute;n y conversatorio.
                </p>
            </div>

            <!-- VIERNES 28 -->
            <div style='background:#f8f9fb;padding:20px;border-left:4px solid #004aad;margin-bottom:25px;border-radius:8px;'>
                <p style='margin:0 0 8px;font-size:16px;'>
                    <strong>📌 Viernes 28 de noviembre</strong> &ndash; Hotel Antay
                </p>
                <p style='margin:0;color:#555;font-size:14px;'>
                    🕣 Acreditaci&oacute;n: 08:30 hrs<br>
                    🕧 Cierre jornada AM: 13:30 hrs<br><br>
                    Prep&aacute;rate para rueda de negocios, speakers internacionales y nacionales, espacios de vinculaci&oacute;n y conversatorio.
                </p>
                <p style='margin:15px 0 0;color:#555;font-size:14px;'>
                    En la jornada de la tarde, te invitamos a ser parte de un momento clave:<br>
                    <strong>✍️ Constituci&oacute;n de mesa y firma de acuerdos del Nodo Bioce&aacute;nico Central 2025</strong>
                    a realizarse en el Hotel Arica, instancia hist&oacute;rica para la integraci&oacute;n y desarrollo log&iacute;stico regional.
                </p>
            </div>

            <!-- BOTÓN DESCARGA PROGRAMA -->
            <div style='text-align:center;margin:35px 0;'>
                <a href='https://www.bioceanicocentral.cl/assets/programa_nodo.pdf'
                   style='background:#004aad;color:white;padding:16px 40px;font-size:16px;
                   font-weight:bold;text-decoration:none;border-radius:8px;display:inline-block;
                   box-shadow:0 4px 12px rgba(0,74,173,0.3);'>
                   📄 Descargar Programa Completo
                </a>
            </div>

            <p style='margin-top:30px;font-size:15px;color:#333;'>
                Gracias por ser parte de este encuentro que est&aacute; <strong>moviendo fronteras y construyendo futuro</strong>.
            </p>

            <p style='margin-top:20px;font-size:15px;color:#333;'>
                Nos vemos pronto 👋<br>
                <strong>Equipo Nodo Bioce&aacute;nico Central 2025</strong>
            </p>

            <p style='margin-top:35px;font-size:13px;color:#777;text-align:center;'>
                Si tienes dudas, puedes responder directamente a este correo.
            </p>
        </div>

        <!-- FOOTER -->
        <div style='background:#f0f3f8;padding:15px;text-align:center;font-size:12px;color:#555;'>
            &copy; 2025 Nodo Bioce&aacute;nico Central &mdash; Todos los derechos reservados.
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
