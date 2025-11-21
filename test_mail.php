<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
ini_set('max_execution_time', 0);
error_reporting(E_ALL);

// ============================
// ðŸ”¹ Cargar PHPMailer (ya instalado en tu servidor)
// ============================
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ============================
// ðŸ”¹ ConexiÃ³n BD
// ============================
$conn = new mysqli('localhost', 'seidev', 'Tz9!qE7#Xr2@Lm5$Vp8^', 'registro_evento');
if ($conn->connect_error) {
    die("Error BD: " . $conn->connect_error);
}

// ============================
// ðŸ”¹ IDs reales que probaremos
// ============================
$ids = [6, 8, 9];

// Correos de prueba
$correos_test = [
    6 => 'patricio.hp.iqq@gmail.com',
    8 => 'andesdevs1@gmail.com',
    9 => 'phernandez32@santotomas.cl'
];

// ============================
// ðŸ”¹ Obtener registros reales
// ============================
$sql = "SELECT * FROM registros WHERE id IN (6,8,9)";
$res = $conn->query($sql);

if ($res->num_rows === 0) {
    die("No se encontraron registros de prueba.");
}

// ============================
// ðŸ”¹ ConfiguraciÃ³n PHPMailer (TAL CUAL tu script)
// ============================
$mail = new PHPMailer(true);

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

$mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo BioceÃ¡nico 2025');
$mail->isHTML(true);
$mail->Subject = 'ðŸ”„ ReenvÃ­o de tu CÃ³digo QR â€“ Nodo BioceÃ¡nico 2025';

// ============================
// ðŸ”¹ Enviar a los 3 correos de prueba
// ============================
while ($row = $res->fetch_assoc()) {

    $id       = $row['id'];
    $nombre   = $row['nombre'];
    $apellido = $row['apellido'];
    $email    = $correos_test[$id]; // ðŸ”„ se reemplaza solo para prueba
    $telefono = $row['telefono'];
    $pais     = $row['pais'];
    $empresa  = $row['empresa'];
    $cargo    = $row['cargo'];
    $sector   = $row['sector'];
    $codigo   = $row['codigo_registro'];

    // QR ya existente
    $qr_url = "https://www.bioceanicocentral.cl/registro/qrcodes/" . $codigo . ".png";

    // HTML bonito (simple para prueba)
    $body = "
<div style='margin:0;padding:0;background:#e9eef5;font-family:Arial, sans-serif;'>
    <div style='max-width:650px;margin:30px auto;background:white;border-radius:12px;
        overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.12);'>

        <!-- HEADER -->
        <div style='background:#004aad;padding:30px 25px;text-align:center;color:white;'>
            <img src='https://www.bioceanicocentral.cl/wp-content/uploads/2025/11/logonuevo.png'
                alt='Nodo Bioceánico'
                style='width:120px;max-width:35%;margin-bottom:12px;'>

            <h2 style='margin:10px 0 0;font-size:26px;font-weight:700;'>
                Nodo Bioceánico 2025
            </h2>

            <p style='margin:5px 0 0;font-size:14px;opacity:0.9;'>
                Arica, Chile — 26, 27 y 28 de Noviembre
            </p>
        </div>

        <!-- CUERPO -->
        <div style='padding:35px;color:#333;line-height:1.6;font-size:15px;'>
            <p>Hola <strong>$nombre $apellido</strong>,</p>

            <p>
                Te reenviamos tu <strong>código QR de acceso oficial</strong> al evento 
                <strong>Nodo Bioceánico 2025</strong>.  
                Este QR es obligatorio para tu registro y agiliza tu ingreso al recinto.
            </p>

            <!-- QR -->
            <div style='text-align:center;margin:35px 0;'>
                <div style='display:inline-block;background:white;padding:15px;border-radius:12px;
                    border:3px solid #004aad;
                    box-shadow:0 4px 12px rgba(0,0,0,0.15);'>
                    <img src='$qr_url' alt='QR' style='width:220px;border-radius:6px;'>
                </div>

                <p style='margin-top:15px;font-size:18px;color:#004aad;'>
                    Código: <strong>$codigo</strong>
                </p>
            </div>

            <!-- RESUMEN -->
            <h3 style='color:#004aad;margin-bottom:10px;'>Resumen de tu Registro</h3>

            <table style='width:100%;border-collapse:collapse;font-size:15px;'>
                <tr><td style='padding:6px 0;color:#555;'><strong>Email:</strong></td><td>$email</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>Teléfono:</strong></td><td>$telefono</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>País:</strong></td><td>$pais</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>Empresa:</strong></td><td>$empresa</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>Cargo:</strong></td><td>$cargo</td></tr>
                <tr><td style='padding:6px 0;color:#555;'><strong>Sector:</strong></td><td>$sector</td></tr>
            </table>

            <!-- BOTÓN CTA -->
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

        <!-- FOOTER -->
        <div style='background:#f0f3f8;padding:15px;text-align:center;font-size:12px;color:#555;'>
            © 2025 Nodo Bioceánico — Todos los derechos reservados.
        </div>
    </div>
</div>
";


    $mail->clearAddresses();
    $mail->clearAttachments();

    $mail->addAddress($email, "$nombre $apellido");

    // Insertar QR como embedded (compatible 100%)
    $qr_local_path = __DIR__ . "/qrcodes/$codigo.png";
    if (file_exists($qr_local_path)) {
        $mail->AddEmbeddedImage($qr_local_path, 'qr_image', basename($qr_local_path));
    }

    $mail->Body = $body;

    try {
        $mail->send();
        echo "Correo de prueba enviado a: $email (ID real $id)<br>";
    } catch (Exception $e) {
        echo "Error enviando a $email â†’ " . $mail->ErrorInfo . "<br>";
    }
}

$conn->close();
?>

