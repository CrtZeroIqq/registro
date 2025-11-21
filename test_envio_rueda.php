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
                Durante su registro al evento <strong style='color:#004aad;'>Nodo Bioce&aacute;nico Central 2025</strong>,
                usted manifest&oacute; inter&eacute;s en participar de nuestra <strong>Rueda de Negocios</strong>.
                ¡Tenemos excelentes noticias para usted!
            </p>

            <div style='background:#f0f7ff;border-left:4px solid #004aad;padding:20px;margin:25px 0;border-radius:6px;'>
                <h3 style='margin:0 0 10px 0;font-size:18px;color:#004aad;'>
                    💼 ¿Qu&eacute; es la Rueda de Negocios?
                </h3>
                <p style='margin:0;color:#555;'>
                    Es un <strong>espacio exclusivo de networking B2B</strong> donde podr&aacute; tener <strong>reuniones 1-a-1 de 15 minutos</strong>
                    con empresas de su inter&eacute;s. Un formato &aacute;gil y eficiente para generar conexiones comerciales concretas.
                </p>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px 0;font-size:18px;'>
                ✨ Beneficios de Participar
            </h3>

            <div style='display:table;width:100%;margin-bottom:20px;'>
                <div style='display:table-row;'>
                    <div style='display:table-cell;padding:10px 15px;background:#f9fafb;border-radius:8px;margin-bottom:10px;'>
                        <div style='display:flex;align-items:start;'>
                            <span style='font-size:24px;margin-right:12px;'>🎯</span>
                            <div>
                                <strong style='color:#004aad;display:block;margin-bottom:3px;'>Conexiones Directas</strong>
                                <span style='color:#666;font-size:14px;'>Reuniones confirmadas con empresas afines a tu sector</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style='display:table;width:100%;margin-bottom:20px;'>
                <div style='display:table-row;'>
                    <div style='display:table-cell;padding:10px 15px;background:#f9fafb;border-radius:8px;margin-bottom:10px;'>
                        <div style='display:flex;align-items:start;'>
                            <span style='font-size:24px;margin-right:12px;'>⏱️</span>
                            <div>
                                <strong style='color:#004aad;display:block;margin-bottom:3px;'>Optimizaci&oacute;n de Tiempo</strong>
                                <span style='color:#666;font-size:14px;'>Hasta 4 reuniones programadas en 90 minutos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style='display:table;width:100%;margin-bottom:20px;'>
                <div style='display:table-row;'>
                    <div style='display:table-cell;padding:10px 15px;background:#f9fafb;border-radius:8px;margin-bottom:10px;'>
                        <div style='display:flex;align-items:start;'>
                            <span style='font-size:24px;margin-right:12px;'>📊</span>
                            <div>
                                <strong style='color:#004aad;display:block;margin-bottom:3px;'>Oportunidades Comerciales</strong>
                                <span style='color:#666;font-size:14px;'>Genera nuevos negocios, alianzas y acuerdos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px 0;font-size:18px;'>
                🔄 ¿C&oacute;mo Funciona?
            </h3>

            <p style='margin-bottom:15px;color:#555;'>Hay <strong>dos modalidades</strong> seg&uacute;n tu objetivo comercial:</p>

            <div style='background:#fff;border:2px solid #10b981;padding:20px;margin:20px 0;border-radius:8px;'>
                <h4 style='margin:0 0 12px 0;color:#10b981;font-size:17px;'>
                    📌 Empresas que Buscan Servicios/Proveedores
                </h4>
                <p style='margin:0 0 10px 0;font-size:14px;color:#555;'>
                    <strong>¿Necesitas contratar servicios o proveedores?</strong>
                </p>
                <ul style='margin:0;padding-left:20px;color:#555;line-height:1.8;font-size:14px;'>
                    <li>Reservas una <strong>mesa fija</strong> en el evento</li>
                    <li>Otras empresas te solicitan reuniones</li>
                    <li>T&uacute; decides con qui&eacute;n reunirte</li>
                </ul>
            </div>

            <div style='background:#fff;border:2px solid #3b82f6;padding:20px;margin:20px 0;border-radius:8px;'>
                <h4 style='margin:0 0 12px 0;color:#3b82f6;font-size:17px;'>
                    🔍 Empresas que Ofrecen Servicios/Productos
                </h4>
                <p style='margin:0 0 10px 0;font-size:14px;color:#555;'>
                    <strong>¿Quieres conseguir clientes o socios comerciales?</strong>
                </p>
                <ul style='margin:0;padding-left:20px;color:#555;line-height:1.8;font-size:14px;'>
                    <li>Exploras el directorio de empresas participantes</li>
                    <li>Solicitas reuniones con las que te interesan</li>
                    <li>Esperas su confirmaci&oacute;n autom&aacute;tica</li>
                </ul>
            </div>

            <div style='background:#fef3c7;border-left:4px solid #f59e0b;padding:20px;margin:25px 0;border-radius:6px;'>
                <h4 style='margin:0 0 10px 0;color:#f59e0b;font-size:16px;'>
                    ⏰ Informaci&oacute;n del Evento
                </h4>
                <ul style='margin:0;padding-left:20px;color:#555;line-height:1.8;'>
                    <li><strong>Fecha:</strong> 28 de Noviembre 2025</li>
                    <li><strong>Horario:</strong> 11:00 - 12:30 hrs</li>
                    <li><strong>Bloques:</strong> 4 sesiones de 15 minutos cada una</li>
                    <li><strong>Capacidad:</strong> 60 reuniones m&aacute;ximo (15 mesas)</li>
                </ul>
            </div>

            <div style='background:#fef2f2;border-left:4px solid #ef4444;padding:20px;margin:25px 0;border-radius:6px;'>
                <h4 style='margin:0 0 10px 0;color:#991b1b;font-size:16px;'>⚠️ ¡Cupos Limitados!</h4>
                <p style='margin:0;color:#555;font-size:14px;'>
                    Solo hay <strong>60 espacios disponibles</strong>. Complete su registro <strong>cuanto antes</strong>
                    para asegurar su participaci&oacute;n. Las mesas se asignan por orden de llegada.
                </p>
            </div>

            <h3 style='color:#004aad;margin:30px 0 15px 0;font-size:18px;text-align:center;'>
                🚀 Complete su Registro Ahora
            </h3>

            <p style='text-align:center;color:#555;margin-bottom:25px;'>
                Es r&aacute;pido y simple: complete sus datos, elija su modalidad y defina qu&eacute; busca o qu&eacute; ofrece.
            </p>

            <div style='text-align:center;margin:40px 0;'>
                <a href='https://www.bioceanicocentral.cl/rueda-negocios-arica/views/registro.php'
                   style='background:#004aad;color:white;padding:18px 45px;font-size:18px;
                   font-weight:bold;text-decoration:none;border-radius:8px;display:inline-block;
                   box-shadow:0 4px 12px rgba(0,74,173,0.3);'>
                   ✅ COMPLETAR MI REGISTRO
                </a>
            </div>

            <div style='background:#e0f2fe;border:2px solid #0284c7;padding:20px;margin:30px 0;border-radius:8px;'>
                <h4 style='margin:0 0 12px 0;color:#0284c7;font-size:16px;'>
                    📝 ¿Qu&eacute; sucede despu&eacute;s de registrarse?
                </h4>
                <ol style='margin:0;padding-left:20px;color:#555;line-height:1.8;font-size:14px;'>
                    <li>Acceder&aacute; a la plataforma para ver empresas participantes</li>
                    <li>Podr&aacute; solicitar o recibir solicitudes de reuni&oacute;n</li>
                    <li>Recibir&aacute; confirmaciones autom&aacute;ticas por email</li>
                    <li>El d&iacute;a del evento sabr&aacute; exactamente d&oacute;nde y cu&aacute;ndo son sus reuniones</li>
                </ol>
            </div>

            <p style='text-align:center;font-size:16px;color:#004aad;font-weight:bold;margin:30px 0 15px 0;'>
                ¡No pierda esta oportunidad de hacer crecer su red de negocios!
            </p>

            <p style='text-align:center;font-size:13px;color:#666;margin-top:20px;'>
                ¿Tiene dudas? Cont&aacute;ctenos a
                <a href='mailto:contacto@bioceanicocentral.cl' style='color:#004aad;text-decoration:none;font-weight:600;'>contacto@bioceanicocentral.cl</a>
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
