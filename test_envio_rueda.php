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
    $mail->Subject = "[PRUEBA] 🤝 Rueda de Negocios Arica - 28 de Noviembre | Instrucciones de Participaci&oacute;n";

    // ==========================================
    // 🔹 PLANTILLA DE EMAIL - RUEDA DE NEGOCIOS
    // ==========================================
    $mail->Body = "
<div style='margin:0;padding:0;background:#e9eef5;font-family:Arial, sans-serif;'>
    <div style='max-width:700px;margin:30px auto;background:white;border-radius:12px;
        overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.12);'>

        <div style='background:linear-gradient(135deg, #004aad 0%, #0066cc 100%);padding:35px 25px;text-align:center;color:white;'>
            <img src='https://www.bioceanicocentral.cl/wp-content/uploads/2025/11/logonuevo.png'
                alt='Nodo Bioce&aacute;nico'
                style='width:120px;max-width:35%;margin-bottom:15px;'>

            <h2 style='margin:10px 0 0;font-size:28px;font-weight:700;'>
                🤝 Rueda de Negocios Arica
            </h2>

            <p style='margin:8px 0 0;font-size:16px;opacity:0.95;'>
                28 de Noviembre 2025 | 11:00 - 12:30 hrs
            </p>
        </div>

        <div style='padding:40px 35px;color:#333;line-height:1.7;font-size:15px;'>
            <p style='font-size:16px;'>Estimado/a <strong>$nombre $apellido</strong>,</p>

            <p>
                Le confirmamos su participaci&oacute;n en la <strong style='color:#004aad;'>Rueda de Negocios - Nodo Bioce&aacute;nico Central</strong>
                que se realizar&aacute; el <strong>28 de Noviembre de 2025</strong>, de <strong>11:00 a 12:30 hrs</strong>.
            </p>

            <div style='background:#f0f7ff;border-left:4px solid #004aad;padding:20px;margin:25px 0;border-radius:6px;'>
                <h3 style='margin:0 0 15px 0;font-size:18px;color:#004aad;'>
                    📋 C&oacute;mo Funciona el Sistema
                </h3>
                <p style='margin:0 0 15px 0;font-weight:bold;color:#333;'>Dos Modalidades de Participaci&oacute;n:</p>
            </div>

            <div style='background:#fff;border:2px solid #10b981;padding:20px;margin:20px 0;border-radius:8px;'>
                <h4 style='margin:0 0 12px 0;color:#10b981;font-size:17px;'>
                    📌 MODALIDAD A: Recibo Solicitudes
                </h4>
                <ul style='margin:0;padding-left:20px;color:#555;line-height:1.8;'>
                    <li>Usted marca su disponibilidad en el sistema</li>
                    <li>Selecciona <strong>1 mesa fija</strong> y hasta <strong>2 bloques horarios</strong> (15 min cada uno)</li>
                    <li>Otras empresas le solicitan reuniones</li>
                    <li>Usted decide con qui&eacute;n reunirse (aprobar/rechazar)</li>
                </ul>
            </div>

            <div style='background:#fff;border:2px solid #3b82f6;padding:20px;margin:20px 0;border-radius:8px;'>
                <h4 style='margin:0 0 12px 0;color:#3b82f6;font-size:17px;'>
                    🔍 MODALIDAD B: Solicito Reuniones
                </h4>
                <ul style='margin:0;padding-left:20px;color:#555;line-height:1.8;'>
                    <li>Usted explora las empresas disponibles</li>
                    <li>Ve sus mesas y horarios</li>
                    <li>Solicita reuniones con las empresas que le interesen</li>
                    <li>Espera confirmaci&oacute;n de la otra empresa</li>
                </ul>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px 0;font-size:18px;'>
                ⏰ Bloques Horarios Disponibles
            </h3>

            <table style='width:100%;border-collapse:collapse;font-size:15px;margin-bottom:20px;background:#f9fafb;'>
                <tr style='background:#004aad;color:white;'>
                    <th style='padding:12px;text-align:left;border:1px solid #ddd;'>Bloque</th>
                    <th style='padding:12px;text-align:left;border:1px solid #ddd;'>Horario</th>
                </tr>
                <tr>
                    <td style='padding:10px;border:1px solid #ddd;'><strong>Bloque 1</strong></td>
                    <td style='padding:10px;border:1px solid #ddd;'>11:00 - 11:15</td>
                </tr>
                <tr style='background:#f3f4f6;'>
                    <td style='padding:10px;border:1px solid #ddd;'><strong>Bloque 2</strong></td>
                    <td style='padding:10px;border:1px solid #ddd;'>11:20 - 11:35</td>
                </tr>
                <tr>
                    <td style='padding:10px;border:1px solid #ddd;'><strong>Bloque 3</strong></td>
                    <td style='padding:10px;border:1px solid #ddd;'>11:40 - 11:55</td>
                </tr>
                <tr style='background:#f3f4f6;'>
                    <td style='padding:10px;border:1px solid #ddd;'><strong>Bloque 4</strong></td>
                    <td style='padding:10px;border:1px solid #ddd;'>12:00 - 12:15</td>
                </tr>
            </table>

            <h3 style='color:#004aad;margin:30px 0 15px 0;font-size:18px;'>
                🎯 Capacidad
            </h3>

            <div style='background:#fef3c7;border-left:4px solid #f59e0b;padding:15px;margin:15px 0;border-radius:6px;'>
                <ul style='margin:0;padding-left:20px;color:#555;'>
                    <li><strong>15 mesas f&iacute;sicas</strong> en el evento</li>
                    <li>M&aacute;ximo <strong>60 reuniones</strong> en total</li>
                    <li>Sistema de reserva por <strong>orden de llegada</strong></li>
                </ul>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px 0;font-size:18px;'>
                🚀 Pr&oacute;ximos Pasos
            </h3>

            <div style='background:#ecfdf5;border:2px solid #10b981;padding:20px;margin:20px 0;border-radius:8px;'>
                <p style='margin:0 0 10px 0;font-weight:bold;color:#065f46;'>Si est&aacute; en MODALIDAD A (Recibo solicitudes):</p>
                <ol style='margin:5px 0 0 0;padding-left:20px;color:#555;line-height:1.8;'>
                    <li>Ingrese con sus credenciales</li>
                    <li>Complete su perfil empresarial</li>
                    <li>Seleccione su mesa y bloques horarios (m&aacute;x. 2)</li>
                    <li>Revise y apruebe/rechace solicitudes de reuni&oacute;n</li>
                    <li>El d&iacute;a del evento, dir&iacute;jase a su mesa asignada</li>
                </ol>
            </div>

            <div style='background:#eff6ff;border:2px solid #3b82f6;padding:20px;margin:20px 0;border-radius:8px;'>
                <p style='margin:0 0 10px 0;font-weight:bold;color:#1e40af;'>Si est&aacute; en MODALIDAD B (Solicito reuniones):</p>
                <ol style='margin:5px 0 0 0;padding-left:20px;color:#555;line-height:1.8;'>
                    <li>Ingrese con sus credenciales</li>
                    <li>Complete su perfil empresarial</li>
                    <li>Explore empresas disponibles</li>
                    <li>Solicite reuniones en los horarios que le convengan</li>
                    <li>Espere confirmaci&oacute;n por email</li>
                    <li>El d&iacute;a del evento, dir&iacute;jase a la mesa indicada</li>
                </ol>
            </div>

            <div style='background:#fef2f2;border-left:4px solid #ef4444;padding:20px;margin:25px 0;border-radius:6px;'>
                <h4 style='margin:0 0 10px 0;color:#991b1b;font-size:16px;'>⚠️ Importante</h4>
                <ul style='margin:0;padding-left:20px;color:#555;line-height:1.8;'>
                    <li>✓ <strong>Capacidad limitada:</strong> Solo 60 espacios disponibles</li>
                    <li>✓ <strong>Registro prioritario:</strong> Configure su disponibilidad cuanto antes</li>
                    <li>✓ <strong>Confirmaci&oacute;n autom&aacute;tica:</strong> Recibir&aacute; emails con cada actualizaci&oacute;n</li>
                    <li>✓ <strong>Mesa asignada:</strong> Sabr&aacute; exactamente d&oacute;nde acudir el d&iacute;a del evento</li>
                    <li>✓ <strong>Su rol:</strong> Verifique en el sistema si est&aacute; en Modalidad A o B</li>
                </ul>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px 0;font-size:18px;text-align:center;'>
                🔗 Acceso al Sistema
            </h3>

            <table style='width:100%;border-collapse:collapse;font-size:15px;margin:20px 0;background:#f9fafb;'>
                <tr>
                    <td style='padding:12px;border:1px solid #ddd;font-weight:bold;width:30%;background:#f3f4f6;'>Portal:</td>
                    <td style='padding:12px;border:1px solid #ddd;'>
                        <a href='https://www.bioceanicocentral.cl/rueda-negocios-arica/views/registro.php'
                           style='color:#004aad;text-decoration:underline;word-break:break-all;'>
                           www.bioceanicocentral.cl/rueda-negocios-arica/views/registro.php
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style='padding:12px;border:1px solid #ddd;font-weight:bold;background:#f3f4f6;'>Usuario:</td>
                    <td style='padding:12px;border:1px solid #ddd;'><strong>$email</strong></td>
                </tr>
                <tr>
                    <td style='padding:12px;border:1px solid #ddd;font-weight:bold;background:#f3f4f6;'>Contrase&ntilde;a:</td>
                    <td style='padding:12px;border:1px solid #ddd;'>La que registr&oacute; al crear su cuenta</td>
                </tr>
            </table>

            <p style='text-align:center;font-size:14px;color:#666;margin:15px 0;'>
                ¿Olvid&oacute; su contrase&ntilde;a? Cont&aacute;ctenos a
                <a href='mailto:contacto@bioceanicocentral.cl' style='color:#004aad;'>contacto@bioceanicocentral.cl</a>
            </p>

            <div style='text-align:center;margin:35px 0;'>
                <a href='https://www.bioceanicocentral.cl/rueda-negocios-arica/views/registro.php'
                   style='background:#004aad;color:white;padding:16px 40px;font-size:17px;
                   font-weight:bold;text-decoration:none;border-radius:8px;display:inline-block;
                   box-shadow:0 4px 12px rgba(0,74,173,0.3);'>
                   🚀 Acceder al Sistema Ahora
                </a>
            </div>

            <p style='text-align:center;font-size:16px;color:#004aad;font-weight:bold;margin:30px 0 15px 0;'>
                ¡Prepare sus mejores propuestas y haga crecer su red de negocios!
            </p>
        </div>

        <div style='background:#f0f3f8;padding:25px;text-align:center;font-size:13px;color:#555;'>
            <p style='margin:0 0 8px 0;font-weight:bold;font-size:14px;'>Equipo Organizador</p>
            <p style='margin:0 0 5px 0;'>Rueda de Negocios - Nodo Bioce&aacute;nico Central</p>
            <p style='margin:5px 0;'>
                📧 <a href='mailto:contacto@bioceanicocentral.cl' style='color:#004aad;text-decoration:none;'>contacto@bioceanicocentral.cl</a>
            </p>
            <p style='margin:10px 0 0 0;color:#888;font-size:12px;'>&copy; 2025 Nodo Bioce&aacute;nico Central - Todos los derechos reservados</p>
        </div>
    </div>

    <img src='https://www.bioceanicocentral.cl/registro/track.php?code=$codigo'
         width='1' height='1' style='display:none;' alt=''>
</div>";

    // Texto alternativo para clientes sin HTML
    $mail->AltBody = "
🤝 RUEDA DE NEGOCIOS ARICA
28 de Noviembre 2025 | 11:00 - 12:30 hrs
Nodo Bioceánico Central

Estimado/a $nombre $apellido,

Le confirmamos su participación en la Rueda de Negocios - Nodo Bioceánico Central que se realizará el 28 de Noviembre de 2025, de 11:00 a 12:30 hrs.

=== CÓMO FUNCIONA EL SISTEMA ===

Dos Modalidades de Participación:

MODALIDAD A: Recibo Solicitudes
- Usted marca su disponibilidad en el sistema
- Selecciona 1 mesa fija y hasta 2 bloques horarios (15 min cada uno)
- Otras empresas le solicitan reuniones
- Usted decide con quién reunirse (aprobar/rechazar)

MODALIDAD B: Solicito Reuniones
- Usted explora las empresas disponibles
- Ve sus mesas y horarios
- Solicita reuniones con las empresas que le interesen
- Espera confirmación de la otra empresa

=== BLOQUES HORARIOS ===
Bloque 1: 11:00 - 11:15
Bloque 2: 11:20 - 11:35
Bloque 3: 11:40 - 11:55
Bloque 4: 12:00 - 12:15

=== CAPACIDAD ===
- 15 mesas físicas en el evento
- Máximo 60 reuniones en total
- Sistema de reserva por orden de llegada

=== ACCESO AL SISTEMA ===
Portal: https://www.bioceanicocentral.cl/rueda-negocios-arica/views/registro.php
Usuario: $email
Contraseña: La que registró al crear su cuenta

¿Olvidó su contraseña? Contáctenos a contacto@bioceanicocentral.cl

¡Prepare sus mejores propuestas y haga crecer su red de negocios!

---
Equipo Organizador
Rueda de Negocios - Nodo Bioceánico Central
📧 contacto@bioceanicocentral.cl
";

    // ENVIAR
    echo "<h3>🚀 Enviando correo...</h3>";
    echo "<pre>";
    $mail->send();
    echo "</pre>";

    echo "<div style='background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:20px;border-radius:8px;margin:20px 0;'>";
    echo "<h3 style='margin-top:0;'>✅ ¡Correo enviado exitosamente!</h3>";
    echo "<p><strong>Destinatario:</strong> $email_prueba</p>";
    echo "<p><strong>Asunto:</strong> [PRUEBA] 🤝 Rueda de Negocios Arica - 28 de Noviembre | Instrucciones de Participación</p>";
    echo "<p>Revisa tu bandeja de entrada para verificar cómo se ve el correo.</p>";
    echo "</div>";

    echo "<h3>📊 Información adicional:</h3>";
    echo "<ul>";
    echo "<li>El correo incluye el prefijo <code>[PRUEBA]</code> en el asunto</li>";
    echo "<li>Se usaron datos reales de: " . html_entity_decode("$nombre $apellido") . "</li>";
    echo "<li>El email incluye <strong>instrucciones completas</strong> para usar el sistema de rueda de negocios</li>";
    echo "<li>Contiene información sobre las dos modalidades (A y B)</li>";
    echo "<li>Incluye el link al sistema: bioceanicocentral.cl/rueda-negocios-arica/views/registro.php</li>";
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
