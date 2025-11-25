<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==========================================
// 🔹 CONFIGURACIÓN INICIAL
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

echo "<h1>🧪 TEST - Envío Información Evento</h1>";
echo "<p>Enviando a: <strong>patricio.hp.iqq@gmail.com</strong></p>";
echo "<hr>";

// ==========================================
// 🔹 CONEXIÓN A BD
// ==========================================
$servername = "localhost";
$username   = "seidev";
$password   = "Tz9!qE7#Xr2@Lm5$Vp8^";
$database   = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

echo "✅ Conexión a BD exitosa<br>";

// ==========================================
// 🔹 OBTENER PRIMER REGISTRO
// ==========================================
$sql = "SELECT * FROM registros ORDER BY id LIMIT 1";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die("❌ No hay registros en la base de datos");
}

$row = $result->fetch_assoc();
echo "✅ Registro obtenido (ID: {$row['id']})<br>";

// ==========================================
// 🔹 FUNCIÓN PARA CONVERTIR ACENTOS
// ==========================================
function ent($txt) {
    return htmlentities($txt, ENT_QUOTES, 'UTF-8');
}

// Convertir datos del primer registro
$nombre   = ent($row['nombre']);
$apellido = ent($row['apellido']);

echo "<p><strong>Datos del registro:</strong><br>";
echo "Nombre: " . html_entity_decode($nombre) . " " . html_entity_decode($apellido) . "</p>";

// Email de prueba FORZADO
$email_prueba = "patricio.hp.iqq@gmail.com";

echo "<p><strong>Email de destino:</strong> $email_prueba</p>";
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

    echo "✅ Configuración SMTP lista<br>";

    $mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioce&aacute;nico Central 2025');
    $mail->addAddress($email_prueba, html_entity_decode("$nombre $apellido"));
    $mail->addReplyTo('contacto@bioceanicocentral.cl', 'Nodo Bioceánico Central 2025');
    $mail->addCustomHeader('X-Campaign', 'Test-Informacion-Evento-2025');
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

    <!-- BANNER DE PRUEBA -->
    <div style='max-width:650px;margin:20px auto;padding:15px;background:#fff3cd;border:2px solid #ffc107;border-radius:8px;text-align:center;'>
        <strong>⚠️ ESTE ES UN CORREO DE PRUEBA</strong><br>
        <small>Registro ID: {$row['id']} | Email original: " . ent($row['email']) . "</small>
    </div>
</div>";

    echo "✅ Contenido HTML generado<br>";
    echo "<hr>";
    echo "<p><strong>📤 Enviando correo...</strong></p>";

    $mail->send();

    echo "<div style='background:#d4edda;padding:20px;border-radius:8px;border-left:4px solid #28a745;margin:20px 0;'>";
    echo "<h2 style='color:#155724;margin:0;'>✅ ¡CORREO ENVIADO EXITOSAMENTE!</h2>";
    echo "<p style='color:#155724;margin:10px 0 0;'>El correo ha sido enviado a: <strong>$email_prueba</strong></p>";
    echo "</div>";

    echo "<h3>📋 Resumen:</h3>";
    echo "<ul>";
    echo "<li><strong>De:</strong> contacto@bioceanicocentral.cl</li>";
    echo "<li><strong>Para:</strong> $email_prueba</li>";
    echo "<li><strong>Asunto:</strong> Ultimas Informaciones - Nodo Bioceánico Central Arica 2025</li>";
    echo "<li><strong>Datos usados:</strong> " . html_entity_decode("$nombre $apellido") . " (Registro ID: {$row['id']})</li>";
    echo "</ul>";

    echo "<p style='background:#e7f3ff;padding:15px;border-radius:8px;border-left:4px solid #004aad;'>";
    echo "💡 <strong>Revisa tu bandeja de entrada</strong> (y también spam/correo no deseado) para verificar que el correo se vea correctamente.";
    echo "</p>";

} catch (Exception $e) {
    echo "<div style='background:#f8d7da;padding:20px;border-radius:8px;border-left:4px solid #dc3545;margin:20px 0;'>";
    echo "<h2 style='color:#721c24;margin:0;'>❌ ERROR AL ENVIAR</h2>";
    echo "<p style='color:#721c24;margin:10px 0 0;'><strong>Error:</strong> {$mail->ErrorInfo}</p>";
    echo "<p style='color:#721c24;margin:10px 0 0;'><strong>Excepción:</strong> {$e->getMessage()}</p>";
    echo "</div>";
}

$conn->close();
?>
