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
// Función helper - devuelve el texto tal cual (UTF-8 nativo para email)
function ent($txt) {
    return $txt;
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

    log_detallado("Preparando envío a: $email");

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

        $mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioceánico 2025');
        $mail->addAddress($email, "$nombre $apellido");
        $mail->addReplyTo('contacto@bioceanicocentral.cl', 'Nodo Bioceánico 2025');
        $mail->addCustomHeader('X-Campaign', 'Rueda-Negocios-2025');
        $mail->addCustomHeader('X-Priority', '3');

        $mail->isHTML(true);
        $mail->Subject = "🤝 Invitación: Rueda de Negocios Arica - Completa tu Registro";

        // ==========================================
        // 🔹 PLANTILLA DE EMAIL - RUEDA DE NEGOCIOS
        // ==========================================
        $mail->Body = <<<EMAILHTML
<div style='margin:0;padding:0;background:#f5f5f5;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;'>
    <table width="100%" cellpadding="0" cellspacing="0" style='background:#f5f5f5;'>
        <tr>
            <td align="center" style='padding:20px 10px;'>
                <table width="600" cellpadding="0" cellspacing="0" style='background:white;max-width:600px;'>

                    <!-- Header -->
                    <tr>
                        <td style='border-left:5px solid #004aad;padding:30px 35px;background:#ffffff;'>
                            <img src='https://www.bioceanicocentral.cl/wp-content/uploads/2025/11/logonuevo.png'
                                 alt='Nodo Bioceánico' style='height:50px;margin-bottom:18px;'>
                            <h1 style='margin:0 0 6px 0;font-size:23px;font-weight:600;color:#1a1a1a;line-height:1.3;'>
                                Rueda de Negocios Arica 2025
                            </h1>
                            <p style='margin:0;font-size:14px;color:#666;'>28 de Noviembre &bull; 11:00 - 12:30 hrs</p>
                        </td>
                    </tr>

                    <!-- Contenido -->
                    <tr>
                        <td style='padding:25px 35px;line-height:1.6;color:#333;'>
                            <p style='margin:0 0 12px 0;font-size:15px;'>Estimado/a <strong>$nombre $apellido</strong>,</p>
                            <p style='margin:0 0 18px 0;font-size:15px;'>Durante su registro al evento <strong>Nodo Bioceánico Central 2025</strong>, usted manifestó interés en participar de nuestra Rueda de Negocios. ¡Tenemos excelentes noticias para usted!</p>

                            <!-- Qué es -->
                            <table width="100%" cellpadding="0" cellspacing="0" style='margin:20px 0;border-left:3px solid #004aad;background:#f8f9fa;'>
                                <tr>
                                    <td style='padding:16px 18px;'>
                                        <p style='margin:0 0 6px 0;font-size:15px;font-weight:600;color:#004aad;'>¿Qué es la Rueda de Negocios?</p>
                                        <p style='margin:0;font-size:14px;color:#555;line-height:1.5;'>Un espacio exclusivo de networking B2B con reuniones 1-a-1 de 15 minutos. Formato ágil y eficiente para generar conexiones comerciales concretas.</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Beneficios -->
                            <p style='margin:20px 0 10px 0;font-size:15px;font-weight:600;color:#1a1a1a;'>Por qué participar</p>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="40" valign="top" style='padding:6px 0;'><span style='font-size:18px;color:#004aad;'>→</span></td>
                                    <td style='padding:6px 0;font-size:14px;'><strong style='color:#1a1a1a;'>Conexiones directas</strong><br><span style='color:#666;'>Reuniones confirmadas con empresas de tu sector</span></td>
                                </tr>
                                <tr>
                                    <td width="40" valign="top" style='padding:6px 0;'><span style='font-size:18px;color:#004aad;'>→</span></td>
                                    <td style='padding:6px 0;font-size:14px;'><strong style='color:#1a1a1a;'>Optimización de tiempo</strong><br><span style='color:#666;'>Hasta 4 reuniones en 90 minutos</span></td>
                                </tr>
                                <tr>
                                    <td width="40" valign="top" style='padding:6px 0;'><span style='font-size:18px;color:#004aad;'>→</span></td>
                                    <td style='padding:6px 0;font-size:14px;'><strong style='color:#1a1a1a;'>Nuevas oportunidades</strong><br><span style='color:#666;'>Negocios, alianzas y acuerdos concretos</span></td>
                                </tr>
                            </table>

                            <!-- Dos modalidades -->
                            <p style='margin:22px 0 12px 0;font-size:15px;font-weight:600;color:#1a1a1a;'>Dos formas de participar</p>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="48%" valign="top" style='padding:14px;background:#f0f9ff;border-top:2px solid #0284c7;'>
                                        <p style='margin:0 0 8px 0;font-weight:600;font-size:13px;color:#0369a1;'>EMPRESAS QUE BUSCAN</p>
                                        <p style='margin:0;font-size:13px;color:#555;line-height:1.5;'>Reserva mesa fija &bull; Recibe solicitudes &bull; Aprueba reuniones</p>
                                    </td>
                                    <td width="4%"></td>
                                    <td width="48%" valign="top" style='padding:14px;background:#f0fdf4;border-top:2px solid#10b981;'>
                                        <p style='margin:0 0 8px 0;font-weight:600;font-size:13px;color:#047857;'>EMPRESAS QUE OFRECEN</p>
                                        <p style='margin:0;font-size:13px;color:#555;line-height:1.5;'>Explora directorio &bull; Solicita reuniones &bull; Recibe confirmaciones</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info evento -->
                            <table width="100%" cellpadding="0" cellspacing="0" style='margin:22px 0;background:#fafafa;border:1px solid #e5e5e5;'>
                                <tr>
                                    <td style='padding:16px 18px;'>
                                        <p style='margin:0 0 10px 0;font-weight:600;color:#1a1a1a;font-size:14px;'>Detalles del evento</p>
                                        <table width="100%" cellpadding="0" cellspacing="0" style='font-size:13px;color:#666;'>
                                            <tr><td width="90" style='padding:3px 0;'><strong style='color:#1a1a1a;'>Fecha:</strong></td><td style='padding:3px 0;'>28 de Noviembre 2025</td></tr>
                                            <tr><td style='padding:3px 0;'><strong style='color:#1a1a1a;'>Horario:</strong></td><td style='padding:3px 0;'>11:00 - 12:30 hrs</td></tr>
                                            <tr><td style='padding:3px 0;'><strong style='color:#1a1a1a;'>Bloques:</strong></td><td style='padding:3px 0;'>4 sesiones de 15 minutos</td></tr>
                                            <tr><td style='padding:3px 0;'><strong style='color:#1a1a1a;'>Capacidad:</strong></td><td style='padding:3px 0;'>60 reuniones máximo (15 mesas)</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <table width="100%" cellpadding="0" cellspacing="0" style='margin:25px 0;'>
                                <tr>
                                    <td align="center" style='padding:22px 0;background:#f8f9fa;'>
                                        <p style='margin:0 0 12px 0;font-size:15px;color:#1a1a1a;font-weight:600;'>Complete su registro ahora</p>
                                        <a href='https://www.bioceanicocentral.cl/rueda-negocios-arica/views/registro.php' style='display:inline-block;padding:13px 32px;background:#004aad;color:white;text-decoration:none;font-weight:600;font-size:14px;border-radius:4px;'>REGISTRARSE →</a>
                                        <p style='margin:12px 0 0 0;font-size:13px;color:#666;'>Cupos limitados</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Qué pasa después -->
                            <p style='margin:18px 0 8px 0;font-size:14px;font-weight:600;color:#1a1a1a;'>Después del registro:</p>
                            <ol style='margin:0;padding-left:20px;font-size:14px;color:#666;line-height:1.7;'>
                                <li>Accederá a la plataforma de empresas participantes</li>
                                <li>Podrá solicitar o recibir solicitudes de reunión</li>
                                <li>Recibirá confirmaciones automáticas</li>
                                <li>Sabrá exactamente dónde y cuándo son sus reuniones</li>
                            </ol>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='padding:22px 35px;background:#fafafa;border-top:1px solid #e5e5e5;'>
                            <p style='margin:0 0 4px 0;font-size:13px;color:#1a1a1a;font-weight:600;'>Equipo Organizador</p>
                            <p style='margin:0 0 6px 0;font-size:13px;color:#666;'>Rueda de Negocios - Nodo Bioceánico Central</p>
                            <p style='margin:0;font-size:13px;color:#666;'>📧 <a href='mailto:contacto@bioceanicocentral.cl' style='color:#004aad;text-decoration:none;'>contacto@bioceanicocentral.cl</a></p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
    <img src='https://www.bioceanicocentral.cl/registro/track.php?code=$codigo' width='1' height='1' style='display:none;' alt=''>
</div>
EMAILHTML;

        // Texto alternativo para clientes sin HTML
        $mail->AltBody = "
🤝 RUEDA DE NEGOCIOS ARICA
28 de Noviembre 2025 | 11:00 - 12:30 hrs
Nodo Bioceánico Central

Estimado/a $nombre $apellido,

Durante su registro al evento Nodo Bioceánico Central 2025, usted manifestó interés en participar de nuestra Rueda de Negocios. ¡Tenemos excelentes noticias para usted!

=== ¿QUÉ ES LA RUEDA DE NEGOCIOS? ===
Es un espacio exclusivo de networking B2B donde podrá tener reuniones 1-a-1 de 15 minutos con empresas de su interés. Un formato ágil y eficiente para generar conexiones comerciales concretas.

=== BENEFICIOS DE PARTICIPAR ===
🎯 Conexiones Directas: Reuniones confirmadas con empresas afines a tu sector
⏱️ Optimización de Tiempo: Hasta 4 reuniones programadas en 90 minutos
📊 Oportunidades Comerciales: Genera nuevos negocios, alianzas y acuerdos

=== ¿CÓMO FUNCIONA? ===
Hay DOS MODALIDADES según tu objetivo comercial:

📌 EMPRESAS QUE BUSCAN SERVICIOS/PROVEEDORES
¿Necesitas contratar servicios o proveedores?
- Reservas una mesa fija en el evento
- Otras empresas te solicitan reuniones
- Tú decides con quién reunirte

🔍 EMPRESAS QUE OFRECEN SERVICIOS/PRODUCTOS
¿Quieres conseguir clientes o socios comerciales?
- Exploras el directorio de empresas participantes
- Solicitas reuniones con las que te interesan
- Esperas su confirmación automática

=== INFORMACIÓN DEL EVENTO ===
- Fecha: 28 de Noviembre 2025
- Horario: 11:00 - 12:30 hrs
- Bloques: 4 sesiones de 15 minutos cada una
- Capacidad: 60 reuniones máximo (15 mesas)

⚠️ ¡CUPOS LIMITADOS!
Solo hay 60 espacios disponibles. Complete su registro cuanto antes para asegurar su participación.

=== COMPLETE SU REGISTRO AHORA ===
Es rápido y simple: complete sus datos, elija su modalidad y defina qué busca o qué ofrece.

✅ COMPLETAR MI REGISTRO
https://www.bioceanicocentral.cl/rueda-negocios-arica/views/registro.php

=== ¿QUÉ SUCEDE DESPUÉS DE REGISTRARSE? ===
1. Accederá a la plataforma para ver empresas participantes
2. Podrá solicitar o recibir solicitudes de reunión
3. Recibirá confirmaciones automáticas por email
4. El día del evento sabrá exactamente dónde y cuándo son sus reuniones

¡No pierda esta oportunidad de hacer crecer su red de negocios!

¿Tiene dudas? Contáctenos a contacto@bioceanicocentral.cl

---
Equipo Organizador
Rueda de Negocios - Nodo Bioceánico Central
📧 contacto@bioceanicocentral.cl
";

        // ENVIAR
        $mail->send();
        $enviados++;

        log_detallado("✓ ENVIADO exitosamente a: $email");

        $log_item = [
            'timestamp' => date('H:i:s'),
            'email' => $email,
            'nombre' => "$nombre $apellido",
            'empresa' => $empresa,
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
            'ultimo_envio' => $email,
            'logs' => array_slice($logs, -10),
            'errores_detalle' => $errores_detalle
        ]);

    } catch (Exception $e) {
        $errores++;

        $error_msg = $mail->ErrorInfo;
        log_detallado("✗ ERROR enviando a: $email - $error_msg");

        $error_item = [
            'timestamp' => date('H:i:s'),
            'email' => $email,
            'nombre' => "$nombre $apellido",
            'error' => $error_msg
        ];

        $errores_detalle[] = $error_item;

        $log_item = [
            'timestamp' => date('H:i:s'),
            'email' => $email,
            'nombre' => "$nombre $apellido",
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
            'ultimo_envio' => $email,
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
