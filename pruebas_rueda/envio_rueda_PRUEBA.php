<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==========================================
// 🔹 SCRIPT DE PRUEBA - ENVÍO RUEDA DE NEGOCIOS
// ==========================================
// Este script envía UN SOLO correo de prueba al email configurado
// NO consulta la base de datos, usa datos de ejemplo

date_default_timezone_set('America/Santiago');
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// ==========================================
// ⚙️ CONFIGURACIÓN DE PRUEBA - MODIFICA AQUÍ
// ==========================================
$EMAIL_PRUEBA = 'fzavala@seidgc.cl';  // 👈 CAMBIA ESTE EMAIL
$NOMBRE_PRUEBA = 'Felipe';
$APELLIDO_PRUEBA = 'Zavala';
$EMPRESA_PRUEBA = 'SEIDGC Ltda.';
$CARGO_PRUEBA = 'Director';

// ==========================================
// 🔹 FUNCIÓN PARA CONVERTIR ACENTOS
// ==========================================
function ent($txt) {
    return htmlentities($txt, ENT_QUOTES, 'UTF-8');
}

// ==========================================
// 🔹 PREPARAR DATOS
// ==========================================
$nombre   = ent($NOMBRE_PRUEBA);
$apellido = ent($APELLIDO_PRUEBA);
$email    = ent($EMAIL_PRUEBA);
$empresa  = ent($EMPRESA_PRUEBA);
$cargo    = ent($CARGO_PRUEBA);

$email_limpio = html_entity_decode($email);

echo "<h1>🧪 Script de Prueba - Envío Rueda de Negocios</h1>";
echo "<p><strong>Email destino:</strong> $email_limpio</p>";
echo "<p><strong>Nombre:</strong> $nombre $apellido</p>";
echo "<p><strong>Empresa:</strong> $empresa</p>";
echo "<hr>";

// ==========================================
// 🔹 CONFIGURAR MAILER
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
    $mail->addCustomHeader('X-Campaign', 'Rueda-Negocios-2025-PRUEBA');
    $mail->addCustomHeader('X-Priority', '3');

    $mail->isHTML(true);
    $mail->Subject = "[PRUEBA] Recordatorio: Reg&iacute;strate en la Rueda de Negocios";

    $mail->Body = "
<!-- BANNER DE PRUEBA -->
<div style='background:#ff9800;color:white;text-align:center;padding:15px;font-weight:bold;font-size:16px;'>
    ⚠️ CORREO DE PRUEBA - NO ES ENVÍO REAL
</div>

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
</div>

<!-- BANNER DE PRUEBA INFERIOR -->
<div style='background:#ff9800;color:white;text-align:center;padding:15px;font-weight:bold;font-size:16px;'>
    ⚠️ ESTE ES UN CORREO DE PRUEBA
</div>";

    // ENVIAR
    $mail->send();

    echo "<div style='background:#d4edda;border:2px solid #28a745;padding:20px;margin:20px 0;border-radius:8px;'>";
    echo "<h2 style='color:#155724;margin:0 0 10px;'>✅ Correo de Prueba Enviado Exitosamente</h2>";
    echo "<p style='color:#155724;margin:0;'><strong>Destinatario:</strong> $email_limpio</p>";
    echo "<p style='color:#155724;margin:5px 0 0;'><strong>Nombre:</strong> " . html_entity_decode("$nombre $apellido") . "</p>";
    echo "<p style='color:#155724;margin:5px 0 0;'><strong>Empresa:</strong> " . html_entity_decode($empresa) . "</p>";
    echo "</div>";

    echo "<p>Revisa la bandeja de entrada (y spam) del correo: <strong>$email_limpio</strong></p>";

    echo json_encode([
        'status' => 'ok',
        'message' => 'Correo de prueba enviado exitosamente',
        'destinatario' => $email_limpio
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo "<div style='background:#f8d7da;border:2px solid #dc3545;padding:20px;margin:20px 0;border-radius:8px;'>";
    echo "<h2 style='color:#721c24;margin:0 0 10px;'>❌ Error al Enviar Correo de Prueba</h2>";
    echo "<p style='color:#721c24;margin:0;'><strong>Error:</strong> " . $mail->ErrorInfo . "</p>";
    echo "</div>";

    echo json_encode([
        'status' => 'error',
        'message' => 'Error al enviar correo de prueba: ' . $mail->ErrorInfo
    ], JSON_UNESCAPED_UNICODE);
}
?>
