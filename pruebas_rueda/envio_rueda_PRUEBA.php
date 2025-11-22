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
    $mail->Subject = "[PRUEBA] Rueda de Negocios B2B - Nodo Bioce&aacute;nico 2025";

    $mail->Body = "
<div style='margin:0;padding:0;background:#f4f7fa;font-family:Arial, sans-serif;'>
    <!-- BANNER DE PRUEBA -->
    <div style='background:#ff9800;color:white;text-align:center;padding:15px;font-weight:bold;font-size:16px;'>
        ⚠️ CORREO DE PRUEBA - NO ES ENVÍO REAL
    </div>

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

            <hr style='margin:30px 0;border:none;border-top:1px solid #e0e0e0;'>

            <div style='text-align:center;margin-top:25px;'>
                <p style='font-size:13px;color:#999;margin-bottom:15px;'>
                    ¿Ya no te interesa participar en la Rueda de Negocios?
                </p>
                <a href='mailto:contacto@bioceanicocentral.cl?subject=No%20me%20interesa%20Rueda%20de%20Negocios&body=Hola,%0D%0A%0D%0AYa%20no%20me%20interesa%20participar%20en%20la%20Rueda%20de%20Negocios.%0D%0A%0D%0AEmail:%20$email%0D%0AEmpresa:%20$empresa'
                   style='background:#6c757d;color:white;padding:12px 30px;font-size:14px;
                   font-weight:600;text-decoration:none;border-radius:6px;display:inline-block;'>
                   Ya no me interesa
                </a>
            </div>
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

    <!-- BANNER DE PRUEBA INFERIOR -->
    <div style='background:#ff9800;color:white;text-align:center;padding:15px;font-weight:bold;font-size:16px;'>
        ⚠️ ESTE ES UN CORREO DE PRUEBA
    </div>
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
