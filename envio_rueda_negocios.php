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
        $mail->Subject = "Recordatorio: Reg&iacute;strate en la Rueda de Negocios - Nodo Bioce&aacute;nico 2025";

        $mail->Body = "
<div style='margin:0;padding:0;background:#f5f5f5;font-family:Arial, Helvetica, sans-serif;'>
    <div style='max-width:600px;margin:20px auto;background:white;border:1px solid #e0e0e0;'>

        <!-- Logo -->
        <div style='text-align:center;padding:30px 20px 20px;'>
            <img src='https://www.bioceanicocentral.cl/wp-content/uploads/2025/11/logonuevo.png'
                alt='Nodo Bioceánico'
                style='width:80px;height:auto;'>
        </div>

        <!-- Título Principal -->
        <div style='text-align:center;padding:10px 20px;'>
            <h1 style='margin:0;font-size:24px;color:#333;font-weight:700;'>
                Rueda de Negocios Arica 2025
            </h1>
            <p style='margin:10px 0 0;font-size:14px;color:#666;'>
                28 de Noviembre - 11:00 - 12:30 hrs
            </p>
        </div>

        <!-- Contenido -->
        <div style='padding:30px 35px;color:#333;line-height:1.6;font-size:15px;'>

            <p style='margin:0 0 20px;'>
                Estimado/a <strong>$nombre $apellido</strong>,
            </p>

            <p style='margin:0 0 20px;'>
                Durante su registro al evento <strong>Nodo Bioceánico Central 2025</strong>, usted manifestó
                interés en participar en nuestra <strong>Rueda de Negocios</strong>. ¡Tenemos excelentes
                noticias para usted!
            </p>

            <!-- Qué es la Rueda -->
            <div style='background:#e8f4f8;padding:20px;margin:25px 0;border-radius:5px;'>
                <h3 style='margin:0 0 12px;font-size:16px;color:#0066cc;font-weight:700;'>
                    ¿Qué es la Rueda de Negocios?
                </h3>
                <p style='margin:0;font-size:14px;color:#333;line-height:1.6;'>
                    Un espacio exclusivo de networking B2B con reuniones 1-a-1 de 15 minutos.
                    Formato ágil y eficiente para generar conexiones comerciales concretas.
                </p>
            </div>

            <!-- Por qué participar -->
            <h3 style='margin:25px 0 15px;font-size:16px;color:#333;font-weight:700;'>
                Por qué participar
            </h3>

            <table style='width:100%;margin:0 0 25px;'>
                <tr>
                    <td style='width:30px;vertical-align:top;padding:5px 0;'>→</td>
                    <td style='padding:5px 0;'>
                        <strong style='font-size:15px;color:#333;'>Conexiones directas</strong><br>
                        <span style='font-size:14px;color:#666;'>Reuniones confirmadas con empresas de tu sector</span>
                    </td>
                </tr>
                <tr>
                    <td style='width:30px;vertical-align:top;padding:5px 0;'>→</td>
                    <td style='padding:5px 0;'>
                        <strong style='font-size:15px;color:#333;'>Optimización de tiempo</strong><br>
                        <span style='font-size:14px;color:#666;'>Hasta 4 reuniones en 90 minutos</span>
                    </td>
                </tr>
                <tr>
                    <td style='width:30px;vertical-align:top;padding:5px 0;'>→</td>
                    <td style='padding:5px 0;'>
                        <strong style='font-size:15px;color:#333;'>Nuevas oportunidades</strong><br>
                        <span style='font-size:14px;color:#666;'>Negocios, alianzas y acuerdos concretos</span>
                    </td>
                </tr>
            </table>

            <!-- Dos formas de participar -->
            <h3 style='margin:25px 0 15px;font-size:16px;color:#333;font-weight:700;'>
                Dos formas de participar
            </h3>

            <table style='width:100%;border-collapse:collapse;margin:0 0 25px;'>
                <tr>
                    <td style='width:50%;padding:0 5px 0 0;vertical-align:top;'>
                        <div style='border:1px solid #0066cc;border-radius:5px;overflow:hidden;'>
                            <div style='background:#0066cc;color:white;padding:10px;text-align:center;font-weight:700;font-size:13px;'>
                                EMPRESAS QUE BUSCAN
                            </div>
                            <div style='background:#f0f8ff;padding:15px;font-size:13px;color:#333;'>
                                Reserva mesa fija • Recibe solicitudes • Aprueba reuniones
                            </div>
                        </div>
                    </td>
                    <td style='width:50%;padding:0 0 0 5px;vertical-align:top;'>
                        <div style='border:1px solid #28a745;border-radius:5px;overflow:hidden;'>
                            <div style='background:#28a745;color:white;padding:10px;text-align:center;font-weight:700;font-size:13px;'>
                                EMPRESAS QUE OFRECEN
                            </div>
                            <div style='background:#f0fff4;padding:15px;font-size:13px;color:#333;'>
                                Explora directorio • Solicita reuniones • Recibe confirmaciones
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Detalles del evento -->
            <h3 style='margin:25px 0 15px;font-size:16px;color:#333;font-weight:700;'>
                Detalles del evento
            </h3>

            <table style='width:100%;border-collapse:collapse;margin:0 0 25px;font-size:14px;'>
                <tr>
                    <td style='padding:8px 0;color:#666;font-weight:600;width:100px;'>Fecha:</td>
                    <td style='padding:8px 0;color:#333;'>28 de Noviembre 2025</td>
                </tr>
                <tr>
                    <td style='padding:8px 0;color:#666;font-weight:600;'>Horario:</td>
                    <td style='padding:8px 0;color:#333;'>11:00 - 12:30 hrs</td>
                </tr>
                <tr>
                    <td style='padding:8px 0;color:#666;font-weight:600;'>Bloques:</td>
                    <td style='padding:8px 0;color:#333;'>4 sesiones de 15 minutos</td>
                </tr>
                <tr>
                    <td style='padding:8px 0;color:#666;font-weight:600;'>Capacidad:</td>
                    <td style='padding:8px 0;color:#333;'>60 reuniones máximo (15 mesas)</td>
                </tr>
            </table>

            <!-- Botón Principal -->
            <div style='text-align:center;margin:35px 0 25px;'>
                <a href='https://www.bioceanicocentral.cl/registro/rueda/views/registro.php'
                   style='background:#0066cc;color:white;padding:14px 50px;font-size:16px;
                   font-weight:700;text-decoration:none;border-radius:5px;display:inline-block;'>
                   REGISTRARSE →
                </a>
            </div>

            <p style='margin:0 0 25px;text-align:center;font-size:13px;color:#999;'>
                Cupos limitados
            </p>

            <!-- Botón Ya no me interesa -->
            <div style='text-align:center;padding:20px 0 10px;border-top:1px solid #e0e0e0;'>
                <a href='mailto:contacto@bioceanicocentral.cl?subject=No%20me%20interesa%20Rueda%20de%20Negocios&body=Hola,%0D%0A%0D%0AYa%20no%20me%20interesa%20participar%20en%20la%20Rueda%20de%20Negocios.%0D%0A%0D%0AEmail:%20$email%0D%0AEmpresa:%20$empresa'
                   style='background:#f5f5f5;color:#666;padding:10px 25px;font-size:13px;
                   text-decoration:none;border-radius:4px;display:inline-block;border:1px solid #ddd;'>
                   Ya no me interesa
                </a>
            </div>

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
