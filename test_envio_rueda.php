<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==========================================
// 🔹 SCRIPT DE PRUEBA - RUEDA DE NEGOCIOS
// ==========================================
// Este script envía un correo de prueba usando datos
// reales de la base de datos para verificar el diseño
// antes del envío masivo.
// ==========================================

date_default_timezone_set('America/Santiago');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ==========================================
// 🔹 CONFIGURACIÓN
// ==========================================

// Email de destino para la prueba (CAMBIAR AQUÍ)
$email_prueba = 'tu-email@ejemplo.com'; // ⚠️ CAMBIAR ESTO

// Si se pasa por GET, usar ese email
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $email_prueba = $_GET['email'];
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
    die("❌ Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ==========================================
// 🔹 OBTENER UN REGISTRO DE EJEMPLO
// ==========================================
$sql = "SELECT * FROM registros WHERE rueda = 'Si' LIMIT 1";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die("❌ No hay registros con rueda='Si' en la base de datos.");
}

$row = $result->fetch_assoc();

// Función para convertir acentos
function ent($txt) {
    return htmlentities($txt, ENT_QUOTES, 'UTF-8');
}

// Extraer datos
$nombre   = ent($row['nombre']);
$apellido = ent($row['apellido']);
$telefono = ent($row['telefono'] ?? 'N/A');
$pais     = ent($row['pais'] ?? 'N/A');
$empresa  = ent($row['empresa'] ?? 'N/A');
$cargo    = ent($row['cargo'] ?? 'N/A');
$sector   = ent($row['sector'] ?? 'N/A');
$codigo   = ent($row['codigo_registro']);

$qr_url = "https://www.bioceanicocentral.cl/registro/qrcodes/$codigo.png";

echo "<h2>📧 Script de Prueba - Rueda de Negocios</h2>";
echo "<p><strong>Datos del registro de ejemplo:</strong></p>";
echo "<ul>";
echo "<li><strong>Nombre:</strong> " . html_entity_decode("$nombre $apellido") . "</li>";
echo "<li><strong>Empresa:</strong> " . html_entity_decode($empresa) . "</li>";
echo "<li><strong>Cargo:</strong> " . html_entity_decode($cargo) . "</li>";
echo "<li><strong>Código:</strong> " . html_entity_decode($codigo) . "</li>";
echo "</ul>";
echo "<p><strong>Email de destino:</strong> <code>$email_prueba</code></p>";
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
    $mail->SMTPDebug  = 2; // Mostrar debug detallado

    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioce&aacute;nico 2025');
    $mail->addAddress($email_prueba, "Prueba - " . html_entity_decode("$nombre $apellido"));
    $mail->addReplyTo('contacto@bioceanicocentral.cl', 'Nodo Bioceánico 2025');
    $mail->addCustomHeader('X-Campaign', 'Rueda-Negocios-2025-PRUEBA');
    $mail->addCustomHeader('X-Priority', '3');

    $mail->isHTML(true);
    $mail->Subject = "[PRUEBA] Invitaci&oacute;n Especial: Rueda de Negocios – Nodo Bioce&aacute;nico 2025";

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

    // Texto alternativo para clientes sin HTML
    $mail->AltBody = "
Rueda de Negocios 2025 - Nodo Bioceánico Central

Estimado/a $nombre $apellido,

En su registro para el evento Nodo Bioceánico Central 2025, usted manifestó interés en participar de nuestra Rueda de Negocios.

¡Su lugar está reservado!

Detalles del Evento:
- Fechas: 26, 27 y 28 de Noviembre 2025
- Lugar: Arica, Chile
- Su empresa: $empresa
- Cargo: $cargo
- Sector: $sector

Código de acceso: $codigo

Para confirmar su asistencia, responda este correo antes del 23 de noviembre.

Contacto: contacto@bioceanicocentral.cl
";

    // ENVIAR
    echo "<h3>🚀 Enviando correo...</h3>";
    echo "<pre>";
    $mail->send();
    echo "</pre>";

    echo "<div style='background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:20px;border-radius:8px;margin:20px 0;'>";
    echo "<h3 style='margin-top:0;'>✅ ¡Correo enviado exitosamente!</h3>";
    echo "<p><strong>Destinatario:</strong> $email_prueba</p>";
    echo "<p><strong>Asunto:</strong> [PRUEBA] Invitación Especial: Rueda de Negocios</p>";
    echo "<p>Revisa tu bandeja de entrada para verificar cómo se ve el correo.</p>";
    echo "</div>";

    echo "<h3>📊 Información adicional:</h3>";
    echo "<ul>";
    echo "<li>El correo incluye el prefijo <code>[PRUEBA]</code> en el asunto</li>";
    echo "<li>Se usaron datos reales de: " . html_entity_decode("$nombre $apellido") . "</li>";
    echo "<li>El QR code corresponde al código: $codigo</li>";
    echo "<li>El email incluye tracking pixel (se registrará si lo abres)</li>";
    echo "</ul>";

    echo "<div style='background:#fff3cd;border:1px solid #ffeeba;color:#856404;padding:15px;border-radius:8px;margin:20px 0;'>";
    echo "<strong>💡 Tip:</strong> Si todo se ve bien, puedes proceder con el envío masivo usando <code>dashboard_rueda.html</code>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:20px;border-radius:8px;margin:20px 0;'>";
    echo "<h3 style='margin-top:0;'>❌ Error al enviar el correo</h3>";
    echo "<p><strong>Error:</strong> " . $mail->ErrorInfo . "</p>";
    echo "<p><strong>Excepción:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

$conn->close();

echo "<hr>";
echo "<p style='text-align:center;color:#666;font-size:13px;'>";
echo "Para cambiar el email de destino, edita el script o usa: <code>?email=tu@email.com</code>";
echo "</p>";
?>
