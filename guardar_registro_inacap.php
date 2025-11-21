<?php
// ==========================
// CONFIGURACIÓN INICIAL
// ==========================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    // ==========================
    // CARGAR LIBRERÍAS
    // ==========================
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    require 'phpqrcode/qrlib.php';

    // ==========================
    // CONEXIÓN BD
    // ==========================
    $conn = new mysqli('localhost', 'seidev', 'Tz9!qE7#Xr2@Lm5$Vp8^', 'registro_evento');
    if ($conn->connect_error) {
        throw new Exception('Error de conexión a BD: ' . $conn->connect_error);
    }

    // ==========================
    // CAPTURA DE DATOS JSON
    // ==========================
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }

    $nombre = trim($data['nombre'] ?? '');
    $rut = trim($data['rut'] ?? '');
    $carrera = trim($data['carrera'] ?? '');
    $email = trim($data['email'] ?? '');
    $tipo_participante = trim($data['tipo_participante'] ?? '');

    if (!$nombre || !$rut || !$carrera || !$email || !$tipo_participante) {
        throw new Exception('Faltan datos obligatorios');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    // ==========================
    // GUARDAR EN BD
    // ==========================
    $codigo = 'INACAP' . date('YmdHis') . rand(100, 999);

    $stmt = $conn->prepare("INSERT INTO registros_inacap (nombre, rut, carrera, email, tipo_participante, codigo) VALUES (?,?,?,?,?,?)");
    if (!$stmt) {
        throw new Exception('Error en preparación SQL: ' . $conn->error);
    }
    $stmt->bind_param("ssssss", $nombre, $rut, $carrera, $email, $tipo_participante, $codigo);
    if (!$stmt->execute()) {
        throw new Exception('Error al guardar en BD: ' . $stmt->error);
    }

    // ==========================
    // GENERAR QR
    // ==========================
    $qrDir = __DIR__ . '/qrcodes/';
    if (!is_dir($qrDir)) mkdir($qrDir, 0775, true);
    $qrFile = $qrDir . $codigo . '.png';
    QRcode::png($codigo, $qrFile, QR_ECLEVEL_L, 5);

    // ==========================
    // CREAR ARCHIVO ICS
    // ==========================
    $icsContent = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Nodo Bioceánico//INACAP//
BEGIN:VEVENT
UID:$codigo@bioceanicocentral.cl
DTSTAMP:" . gmdate('Ymd\THis\Z') . "
DTSTART:20251126T090000Z
DTEND:20251128T180000Z
SUMMARY:Nodo Bioceánico Central 2025
LOCATION:Arica, Chile
DESCRIPTION:Evento internacional de integración y desarrollo macrozonal andino - Seguimiento INACAP.
ORGANIZER;CN=INACAP:mailto:contacto@bioceanicocentral.cl
END:VEVENT
END:VCALENDAR";


    $icsFile = __DIR__ . "/Nodo_Bioceanico_2025.ics";
    file_put_contents($icsFile, $icsContent);

    // ==========================
    // ENVIAR CORREO (MISMA CONFIG QUE FUNCIONA)
    // ==========================
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->isSMTP();
    $mail->Host = '10.24.254.104';
    $mail->SMTPAuth = true;
    $mail->Username = 'contacto@bioceanicocentral.cl';
    $mail->Password = '@@Bio2025';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]
    ];

    $mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioceánico 2025');
    $mail->addAddress($email, $nombre);
    $mail->isHTML(true);
    $mail->Subject = 'Confirmación de Registro INACAP – Nodo Bioceánico 2025';
    $mail->Body = "
    <div style='font-family:Arial,sans-serif;color:#333; text-align:center;'>
        <h2 style='color:#004aad;'>¡Gracias por registrarte, {$nombre}!</h2>
        <p>Tu registro para el evento sido recibido correctamente.</p>
        <p><strong>Código de registro:</strong> {$codigo}</p>
        <p><strong>Carrera:</strong> {$carrera}</p>
        <p><strong>Tipo:</strong> {$tipo_participante}</p>

        <div style='margin:25px auto; text-align:center;'>
            <img src='cid:qr_image' alt='Código QR' style='width:180px; height:180px; display:block; margin:0 auto;' />
        </div>

        <p style='font-size:15px; color:#333; margin-top:10px; line-height:1.5;'>
            Adjuntamos también el archivo para agendar la cumbre en tu calendario.
        </p>

        <div style='margin:25px auto; padding:15px; background:#ffebe0; color:#b22a00; border-radius:10px; max-width:500px; border-top:3px solid #ff5c1a;'>
            <strong>⚠ Importante:</strong><br>
            El código QR adjunto será solicitado al momento de ingresar al evento.
            <br>Por favor, lleva este correo o presenta el QR impreso o en tu teléfono móvil.
        </div>

        <p style='margin-top:20px;color:#555;'>📅 26, 27 y 28 de Noviembre 2025 – Arica, Chile</p>
        <p style='font-size:13px;color:#777;'>© Nodo Bioceánico Central 2025</p>
    </div>";



    if (file_exists($qrFile)) $mail->AddEmbeddedImage($qrFile, 'qr_image', basename($qrFile));
    if (file_exists($icsFile)) $mail->addAttachment($icsFile, 'Nodo_Bioceanico_2025.ics');

    $mail->send();

    echo json_encode([
        'status' => 'ok',
        'message' => '✅ Registro completado. Revisa tu correo electrónico.',
        'codigo' => $codigo
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    error_log("Error en registro INACAP: " . $e->getMessage());
} finally {
    if (isset($conn)) $conn->close();
}
?>
