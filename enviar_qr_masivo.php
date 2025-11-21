<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==========================================
// 🔹 CONFIGURACIÓN INICIAL
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ==========================================
// 🔹 CONEXIÓN A BD
// ==========================================
$servername = "localhost";
$username   = "seidev";
$password   = "Tz9!qE7#Xr2@Lm5$Vp8^";
$database   = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Error al conectar BD']));
}

// ==========================================
// 🔹 FUNCIÓN PARA CONVERTIR ACENTOS
// ==========================================
function ent($txt) {
    return htmlentities($txt, ENT_QUOTES, 'UTF-8');
}

// ==========================================
// 🔹 MODO PRUEBA O PRODUCCIÓN
// ==========================================

// TRUE = solo IDs 6,8,9
// FALSE = enviar a toda la BD
$modo_prueba = true;

if ($modo_prueba) {
    $sql = "SELECT * FROM registros WHERE id IN (6,8,9)";
} else {
    $sql = "SELECT * FROM registros";
}

$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    die(json_encode(['status' => 'error', 'message' => 'No hay registros']));
}

// ==========================================
// 🔹 INICIO BUCLE DE ENVÍOS
// ==========================================
$enviados = 0;
$errores  = 0;

while ($row = $result->fetch_assoc()) {

    // Convertir cada campo a entidades HTML
    $nombre   = ent($row['nombre']);
    $apellido = ent($row['apellido']);
    $email    = ent($row['email']);
    $telefono = ent($row['telefono']);
    $pais     = ent($row['pais']);
    $empresa  = ent($row['empresa']);
    $cargo    = ent($row['cargo']);
    $sector   = ent($row['sector']);
    $codigo   = ent($row['codigo_registro']);

    // URL del QR
    $qr_url = "https://www.bioceanicocentral.cl/registro/qrcodes/$codigo.png";

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

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioce&aacute;nico 2025');
        $mail->addAddress(html_entity_decode($email), html_entity_decode("$nombre $apellido"));
        $mail->isHTML(true);
        $mail->Subject = "Tu QR de acceso – Nodo Bioce&aacute;nico 2025";

        // ==========================================
        // 🔹 HTML BONITO CON ENTIDADES
        // ==========================================
        $mail->Body = "
<div style='margin:0;padding:0;background:#e9eef5;font-family:Arial, sans-serif;'>
    <div style='max-width:650px;margin:30px auto;background:white;border-radius:12px;
        overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.12);'>

        <div style='background:#004aad;padding:30px 25px;text-align:center;color:white;'>
            <img src='https://www.bioceanicocentral.cl/wp-content/uploads/2025/11/logonuevo.png'
                alt='Nodo Bioce&aacute;nico'
                style='width:120px;max-width:35%;margin-bottom:12px;'>

            <h2 style='margin:10px 0 0;font-size:26px;font-weight:700;'>
                Nodo Bioce&aacute;nico 2025
            </h2>

            <p style='margin:5px 0 0;font-size:14px;opacity:0.9;'>
                Arica, Chile &mdash; 26, 27 y 28 de Noviembre
            </p>
        </div>

        <div style='padding:35px;color:#333;line-height:1.6;font-size:15px;'>
            <p>Hola <strong>$nombre $apellido</strong>,</p>

            <p>
                Te reenviamos tu <strong>c&oacute;digo QR de acceso oficial</strong> al evento 
                <strong>Nodo Bioce&aacute;nico 2025</strong>.<br>
                Presenta este c&oacute;digo en el registro para agilizar tu acreditaci&oacute;n.
            </p>

            <div style='text-align:center;margin:35px 0;'>
                <div style='display:inline-block;background:white;padding:15px;border-radius:12px;
                    border:3px solid #004aad;
                    box-shadow:0 4px 12px rgba(0,0,0,0.15);'>
                    <img src='$qr_url' alt='QR' style='width:220px;border-radius:6px;'>
                </div>

                <p style='margin-top:15px;font-size:18px;color:#004aad;'>
                    C&oacute;digo: <strong>$codigo</strong>
                </p>
            </div>

            <h3 style='color:#004aad;margin-bottom:10px;'>Resumen de tu Registro</h3>

            <table style='width:100%;border-collapse:collapse;font-size:15px;'>
                <tr><td style='padding:6px 0;color:#555;'><strong>Email:</strong></td><td>$email</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>Tel&eacute;fono:</strong></td><td>$telefono</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>Pa&iacute;s:</strong></td><td>$pais</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>Empresa:</strong></td><td>$empresa</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>Cargo:</strong></td><td>$cargo</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>Sector:</strong></td><td>$sector</td></tr>
            </table>

            <div style='text-align:center;margin-top:35px;'>
                <a href='https://www.bioceanicocentral.cl' 
                   style='background:#004aad;color:white;padding:14px 32px;font-size:16px;
                   font-weight:bold;text-decoration:none;border-radius:8px;display:inline-block;
                   box-shadow:0 4px 10px rgba(0,0,0,0.15);'>
                   Ver detalles del Evento
                </a>
            </div>

            <p style='margin-top:35px;font-size:13px;color:#777;text-align:center;'>
                Si tienes dudas, puedes responder directamente a este correo.
            </p>
        </div>

        <div style='background:#f0f3f8;padding:15px;text-align:center;font-size:12px;color:#555;'>
            &copy; 2025 Nodo Bioce&aacute;nico &mdash; Todos los derechos reservados.
        </div>
    </div>
</div>";

        $mail->send();
        $enviados++;

    } catch (Exception $e) {
        $errores++;
    }
}

// ==========================================
// 🔹 RESULTADO FINAL
// ==========================================
echo json_encode([
    'status'   => 'ok',
    'enviados' => $enviados,
    'errores'  => $errores,
    'modo'     => $modo_prueba ? 'prueba' : 'produccion'
]);

$conn->close();
?>
