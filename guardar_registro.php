<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ============================
// 🔹 CONFIGURACIÓN INICIAL
// ============================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Librerías necesarias
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'phpqrcode/qrlib.php';

// ============================
// 🔹 CONEXIÓN A LA BASE DE DATOS
// ============================
$servername = 'localhost';
$username   = 'seidev';
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = 'registro_evento';

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error al conectar con la base de datos']);
    exit;
}

// ============================
// 🔹 CAPTURA DE DATOS (JSON o POST)
// ============================
if (empty($_POST)) {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
} else {
    $data = $_POST;
}

// ============================
// 🔹 ASIGNAR VARIABLES
// ============================
$nombre     = $data['nombre']     ?? '';
$apellido   = $data['apellido']   ?? '';
$email      = $data['email']      ?? '';
$telefono   = $data['telefono']   ?? '';
$pais       = $data['pais']       ?? '';
$documento  = $data['documento']  ?? '';
$empresa    = $data['empresa']    ?? '';
$cargo      = $data['cargo']      ?? '';
$tamano     = $data['tamano']     ?? '';
$sector     = $data['sector']     ?? '';
$website    = $data['website']    ?? '';
$intereses  = is_array($data['intereses'] ?? []) ? implode(', ', $data['intereses']) : ($data['intereses'] ?? '');
$objetivos  = $data['objetivos']  ?? '';
$rueda      = "No"      ?? '';
$fuente     = $data['fuente']     ?? '';

// Validar campos obligatorios
if (empty($nombre) || empty($apellido) || empty($email) || empty($pais)) {
    echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios']);
    exit;
}


$blacklist = [
    'jonathanscott@parinacota.gov.cl',
	'jonathanscottgonzalez3000@gmail.com'
    // Saco e wea Bloqueado
];

$emailLower = strtolower(trim($email));
$is_blocked = false;

foreach ($blacklist as $blocked) {
    $blocked = strtolower(trim($blocked));
    if ($blocked[0] === '@' && str_ends_with($emailLower, $blocked)) {
        $is_blocked = true;
        break;
    } elseif ($emailLower === $blocked) {
        $is_blocked = true;
        break;
    }
}

if ($is_blocked) {
    // Guardar intento en log interno (sin afectar al usuario)
    $conn->query("CREATE TABLE IF NOT EXISTS blocked_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        ip VARCHAR(45),
        user_agent TEXT,
        reason VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $reason = 'blacklist_match';

    $ins = $conn->prepare("INSERT INTO blocked_attempts (email, ip, user_agent, reason) VALUES (?, ?, ?, ?)");
    if ($ins) {
        $ins->bind_param('ssss', $emailLower, $ip, $ua, $reason);
        $ins->execute();
        $ins->close();
    }

    // Mensaje genérico + código HTTP 400 para evitar que el frontend lo tome como éxito
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Error en Registro.']);
$conn->close();
exit;

}


// ============================
// 🔹 GENERAR CÓDIGO DE REGISTRO Y GUARDAR EN BD
// ============================
$codigo = 'REG' . date('YmdHis') . rand(100, 999);

$stmt = $conn->prepare("INSERT INTO registros (
    nombre, apellido, email, telefono, pais, documento, empresa, cargo, tamano, sector, website, intereses, objetivos, rueda, fuente, codigo_registro
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param('ssssssssssssssss',
    $nombre, $apellido, $email, $telefono, $pais, $documento,
    $empresa, $cargo, $tamano, $sector, $website, $intereses,
    $objetivos, $rueda, $fuente, $codigo
);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar registro']);
    exit;
}

// ============================
// 🔹 GENERAR QR CON EL CÓDIGO DE REGISTRO
// ============================
$qrDir = __DIR__ . '/qrcodes/';
if (!is_dir($qrDir)) {
    if (!mkdir($qrDir, 0775, true) && !is_dir($qrDir)) {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo crear el directorio de QR.']);
        exit;
    }
}

$qrFile = $qrDir . $codigo . '.png';
// El QR solo contendrá el código, sin URL
QRcode::png($codigo, $qrFile, QR_ECLEVEL_L, 5);

// ============================
// 🔹 CREAR ARCHIVO .ICS PARA CALENDARIO
// ============================
$icsContent = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Nodo Bioceánico//Registro 2025//ES
BEGIN:VEVENT
UID:" . uniqid() . "@bioceanicocentral.cl
DTSTAMP:" . gmdate('Ymd\THis\Z') . "
DTSTART:20251126T120000Z
DTEND:20251128T230000Z
SUMMARY:Evento Nodo Bioceánico
LOCATION:Arica, Chile
DESCRIPTION=Evento internacional de integración y desarrollo macrozonal andino.
END:VEVENT
END:VCALENDAR";

$icsFile = $qrDir . "evento_" . $codigo . ".ics";
file_put_contents($icsFile, $icsContent);

// ============================
// 🔹 ENVIAR CORREO DE CONFIRMACIÓN
// ============================
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

    $mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioceánico 2025');
    $mail->addAddress($email, "$nombre $apellido");
    $mail->isHTML(true);
$mail->Subject = 'Confirmación de Registro – Nodo Bioceánico 2025';
$mail->Body = "
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: auto;'>
    <h2 style='color: #004aad; text-align: center;'>¡Gracias por registrarte, {$nombre} {$apellido}!</h2>
    <p style='text-align: center;'>Te confirmamos tu participación en el 
    <strong>Nodo Bioceánico 2025</strong>.</p>

    <p style='font-size: 16px; text-align: center;'>
        Tu código de registro es:<br>
        <span style='font-size: 20px; font-weight: bold; color: #007BFF;'>{$codigo}</span>
    </p>

    <div style='text-align: center; margin: 15px 0;'>
        <img src='cid:qr_image' alt='QR de registro' style='width:160px; height:160px;'>
    </div>

    <hr style='margin: 20px 0; border: none; border-top: 1px solid #ddd;'>

    <h3 style='color: #004aad;'>Resumen de tu registro:</h3>
    <ul style='font-size: 15px; line-height: 1.7;'>
        <li>📧 <strong>Email:</strong> {$email}</li>
        <li>📱 <strong>Teléfono:</strong> {$telefono}</li>
        <li>🌎 <strong>País:</strong> {$pais}</li>
        <li>🏢 <strong>Empresa:</strong> {$empresa}</li>
        <li>💼 <strong>Cargo:</strong> {$cargo}</li>
        <li>🏗️ <strong>Sector:</strong> {$sector}</li>
    </ul>

    <p style='text-align: center; font-size: 16px; margin-top: 25px;'>
        📅 <strong>26, 27 y 28 de Noviembre 2025</strong> – Arica, Chile 🇨🇱
    </p>

    <p style='text-align: center; color: #555; font-size: 13px; margin-top: 20px;'>
        © Nodo Bioceánico 2025
    </p>
</div>
";

// Embebe el QR dentro del correo
$mail->AddEmbeddedImage($qrFile, 'qr_image', basename($qrFile));

// 🔹 Adjunta el archivo ICS con el MIME correcto para que Gmail/Outlook lo reconozcan como evento
$mail->addStringAttachment(
    file_get_contents($icsFile),
    'Nodo_Bioceanico_2025.ics',
    'base64',
    'text/calendar; charset=utf-8; method=REQUEST'
);


    $mail->send();

    echo json_encode(['status' => 'ok', 'message' => 'Registro completado, correo enviado y calendario adjunto.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Registro guardado, pero error al enviar correo: ' . $mail->ErrorInfo]);
}

$conn->close();
?>
