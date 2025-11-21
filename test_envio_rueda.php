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
    $mail->Subject = "[PRUEBA] 🤝 Invitación: Rueda de Negocios Arica - Completa tu Registro";

    // ==========================================
    // 🔹 PLANTILLA DE EMAIL - RUEDA DE NEGOCIOS
    // ==========================================
    $mail->Body = <<<'EMAILHTML'
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
                                        <p style='margin:12px 0 0 0;font-size:13px;color:#666;'>Cupos limitados &bull; Por orden de llegada</p>
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
Solo hay 60 espacios disponibles. Complete su registro cuanto antes para asegurar su participación. Las mesas se asignan por orden de llegada.

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
    echo "<h3>🚀 Enviando correo...</h3>";
    echo "<pre>";
    $mail->send();
    echo "</pre>";

    echo "<div style='background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:20px;border-radius:8px;margin:20px 0;'>";
    echo "<h3 style='margin-top:0;'>✅ ¡Correo enviado exitosamente!</h3>";
    echo "<p><strong>Destinatario:</strong> $email_prueba</p>";
    echo "<p><strong>Asunto:</strong> [PRUEBA] 🤝 Invitación: Rueda de Negocios Arica - Completa tu Registro</p>";
    echo "<p>Revisa tu bandeja de entrada para verificar cómo se ve el correo.</p>";
    echo "</div>";

    echo "<h3>📊 Información adicional:</h3>";
    echo "<ul>";
    echo "<li>El correo incluye el prefijo <code>[PRUEBA]</code> en el asunto</li>";
    echo "<li>Se usaron datos reales de: " . html_entity_decode("$nombre $apellido") . "</li>";
    echo "<li>El email es una <strong>INVITACIÓN</strong> para completar el registro en la plataforma de rueda de negocios</li>";
    echo "<li>Explica qué es la rueda de negocios y sus beneficios (networking B2B, reuniones 1-1, optimización de tiempo)</li>";
    echo "<li>Presenta las dos modalidades (empresas que buscan servicios vs empresas que ofrecen servicios)</li>";
    echo "<li>Incluye CTA prominente: bioceanicocentral.cl/rueda-negocios-arica/views/registro.php</li>";
    echo "<li>Genera urgencia con información de cupos limitados (60 espacios)</li>";
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
