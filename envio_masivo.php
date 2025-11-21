<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==========================================
// 🔹 CONFIGURACIÓN INICIAL ULTRA SEGURA
// ==========================================
date_default_timezone_set('America/Santiago');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
ini_set('max_execution_time', 0);
set_time_limit(0);

// Limpiar TODOS los buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Handler de errores silencioso
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error PHP: $errstr en $errfile:$errline");
    return true;
});

// Handler de excepciones
set_exception_handler(function($e) {
    error_log("Excepción: " . $e->getMessage());
});

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Connection: close');

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ==========================================
// 🔹 CONFIGURACIÓN DE DELAYS
// ==========================================
$DELAY_ENTRE_CORREOS = 3;  // 3 segundos entre cada correo (MÁS SEGURO)
$LOTE_SIZE = 15;           // 15 correos por lote (MÁS CONSERVADOR)
$DELAY_ENTRE_LOTES = 45;   // 45 segundos entre lotes (PAUSA LARGA)

// ==========================================
// 🔹 ARCHIVO DE ESTADO
// ==========================================
$archivo_estado = __DIR__ . '/estado_envio.json';
$archivo_log = __DIR__ . '/envio_detallado.log';

function actualizar_estado($data) {
    global $archivo_estado;
    try {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($archivo_estado, $json, LOCK_EX);
        // Forzar escritura inmediata
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($archivo_estado, true);
        }
        clearstatcache(true, $archivo_estado);
    } catch (Exception $e) {
        error_log("Error actualizando estado: " . $e->getMessage());
    }
}

function log_detallado($mensaje) {
    global $archivo_log;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($archivo_log, "[$timestamp] $mensaje\n", FILE_APPEND | LOCK_EX);
}

function inicializar_estado($total) {
    actualizar_estado([
        'iniciado' => date('Y-m-d H:i:s'),
        'estado' => 'iniciando',
        'total' => $total,
        'enviados' => 0,
        'errores' => 0,
        'progreso_porcentaje' => 0,
        'ultimo_envio' => 'Iniciando proceso...',
        'logs' => [],
        'errores_detalle' => []
    ]);
    log_detallado("=== INICIO DEL PROCESO ===");
    log_detallado("Total de correos a enviar: $total");
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
    die(json_encode(['status' => 'error', 'message' => 'Error BD: ' . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");

// ==========================================
// 🔹 FUNCIÓN PARA CONVERTIR ACENTOS
// ==========================================
function ent($txt) {
    return htmlentities($txt, ENT_QUOTES, 'UTF-8');
}

// ==========================================
// 🔹 LISTA DE EMAILS YA PROCESADOS
// ==========================================
$emails_ya_procesados = [
    'tejernagasela@gmail.com',
    'tejerinagasela@gmail.com',
    'acondorivaldivia1@gmail.com',
    'yanezjorge729@gmail.com',
    'alejandroel1020@gmail.com',
    'lchc00@gmail.com',
    'biankaelenaflores@gmail.com',
    'lauranicollmamaniz@gmail.com',
    'krisbell.gomez02@inacapmail.cl',
    'sonia_arica@hotmail.com',
    'dnavarrom@umar.cl',
    'pabuawad@deltax.la',
    'dacunac@inacap.cl',
    'lfortiz@deltax.la',
    'luis.rocafull@corfo.cl',
    'fugarte@estructurasmarfil.com',
    'cesar.sandoval@impalaterminals.com',
    'frederick.frantzen@impalaterminals.com',
    'nelson.rojas@corfo.cl',
    'bastianramos.m@gmail.com',
    'marcelo.soto@goreayp.cl',
    'alcaldia@municamarones.cl',
    'paula.bravo@goreayp.cl',
    'yuny.arias@efearicalapaz.cl',
    'ccastro@academicos.uta.cl',
    'paulo.possas@planejamento.gov.br',
    'lperez@puertoarica.cl',
    'jc.gestionycomercio@cftestatalaricayparinacota.cl',
    'ereyes@puertoarica.cl',
    'jrivera@puertoarica.cl',
    'raul.herrera@condensa.cl',
    'marlac@marlac.cl',
    'jorge.manbran@cbb.cl',
    'dmonge@inacap.cl',
    'esoria@imesltda.com',
    'alvaro.valenzuela@ambipar.vom',
    'patricio.arias@wakilabs.cl',
    'gekong@ultraport.cl',
    'victor.mazuelos@impalaterminals.com',
    'jdiaz@jog.com.pe',
    'pveliz@tpa.cl',
    'alejandro.merello@efe.cl',
    'presidente.chilebol@gmail.com',
    'ecam71@gmail.com',
    'ercallapa@gmail.com',
    'guzmaninga9@gmail.com',
    'jecamamani@gmail.com',
    'gm.mamani04@gmail.com',
    'rabarca@puertoarica.cl',
    'alonsoarratia@teus.cl',
    'johnny.vedia.r@gmail.com',
    'gonzalo.gaete@forbislogistics.com',
    'jhanna11.jfbm@gmail.com',
    'jimmyljlb25@hotmail.com',
    'joseantoniotorricoalarcon@gmail.com',
    'raul.ponce@zofri.cl',
    'wsanmiguel@sanmiguelabogados.com.bo',
    'jcqs@conser.com.bo',
    'claudia.moraga.contreras@gmail.com',
    'plepe@gestion.uta.cl',
    'wilson.miranda@ambipar.com',
    'rgamboaa@gmail.com',
    'jorgecacer@gmail.com',
    'm_riquelmed@inacap.cl',
    'vacafarides@gmail.com',
    'ferdymaxxx@gmail.com',
    'patricio.lopez@gorearicayparinacota.gov.cl',
    'rherrera@corfo.cl',
    'lteran@qollqadepot.com',
    'andressdl@qollqadepot.com',
    'agenda.sibra.ro@gmail.com',
    'hgratzl@tpa.cl',
    'dante.choque@zofri.cl',
    'cjobet@tpa.cl',
    'alvaro.alegria@sag.gob.cl',
    'felipe.castillo@probisa.cl',
    'boris.utria@aiib.org',
    'acelis1989@gmail.com',
    'fzavala@seidgc.cl'
];

// Crear condición SQL
$emails_excluir_sql = "'" . implode("','", array_map(function($e) use ($conn) {
    return $conn->real_escape_string($e);
}, $emails_ya_procesados)) . "'";

// Consulta SQL
$sql = "SELECT * FROM registros WHERE email NOT IN ($emails_excluir_sql) ORDER BY id";

log_detallado("Ejecutando consulta SQL");
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    log_detallado("ERROR: No hay registros para enviar");
    die(json_encode(['status' => 'error', 'message' => 'No hay registros pendientes']));
}

$total_registros = $result->num_rows;
inicializar_estado($total_registros);

log_detallado("Registros encontrados: $total_registros");

// ==========================================
// 🔹 INICIO BUCLE DE ENVÍOS
// ==========================================
$enviados = 0;
$errores  = 0;
$contador_lote = 0;
$logs = [];
$errores_detalle = [];

while ($row = $result->fetch_assoc()) {
    
    $contador_lote++;
    $id_registro = $row['id'];
    
    log_detallado("--- Procesando registro ID: $id_registro ---");
    
    // Convertir cada campo
    $nombre   = ent($row['nombre']);
    $apellido = ent($row['apellido']);
    $email    = ent($row['email']);
    $telefono = ent($row['telefono']);
    $pais     = ent($row['pais']);
    $empresa  = ent($row['empresa']);
    $cargo    = ent($row['cargo']);
    $sector   = ent($row['sector']);
    $codigo   = ent($row['codigo_registro']);

    $qr_url = "https://www.bioceanicocentral.cl/registro/qrcodes/$codigo.png";
    
    $email_limpio = html_entity_decode($email);
    log_detallado("Preparando envío a: $email_limpio");

    // ==========================================
    // 🔹 CONFIGURAR MAILER CON TRY-CATCH
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
        $mail->addCustomHeader('X-Campaign', 'Reenvio-QR-2025');
        $mail->addCustomHeader('X-Priority', '3');
        
        $mail->isHTML(true);
        $mail->Subject = "Tu QR de acceso – Nodo Bioce&aacute;nico 2025";

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
    
    <img src='https://www.bioceanicocentral.cl/registro/track.php?code=$codigo' 
         width='1' height='1' style='display:none;' alt=''>
</div>";

        // ENVIAR
        $mail->send();
        $enviados++;
        
        log_detallado("✓ ENVIADO exitosamente a: $email_limpio");
        
        $log_item = [
            'timestamp' => date('H:i:s'),
            'email' => $email_limpio,
            'nombre' => html_entity_decode("$nombre $apellido"),
            'status' => 'enviado'
        ];
        
        $logs[] = $log_item;
        
        // Actualizar estado
        $progreso = round(($enviados + $errores) / $total_registros * 100, 1);
        actualizar_estado([
            'iniciado' => date('Y-m-d H:i:s'),
            'estado' => 'enviando',
            'total' => $total_registros,
            'enviados' => $enviados,
            'errores' => $errores,
            'progreso_porcentaje' => $progreso,
            'ultimo_envio' => $email_limpio,
            'logs' => array_slice($logs, -10),
            'errores_detalle' => $errores_detalle
        ]);

    } catch (Exception $e) {
        $errores++;
        
        $error_msg = $mail->ErrorInfo;
        log_detallado("✗ ERROR enviando a: $email_limpio - $error_msg");
        
        $error_item = [
            'timestamp' => date('H:i:s'),
            'email' => $email_limpio,
            'nombre' => html_entity_decode("$nombre $apellido"),
            'error' => $error_msg
        ];
        
        $errores_detalle[] = $error_item;
        
        $log_item = [
            'timestamp' => date('H:i:s'),
            'email' => $email_limpio,
            'nombre' => html_entity_decode("$nombre $apellido"),
            'status' => 'error'
        ];
        
        $logs[] = $log_item;
        
        // Actualizar estado con error
        $progreso = round(($enviados + $errores) / $total_registros * 100, 1);
        actualizar_estado([
            'iniciado' => date('Y-m-d H:i:s'),
            'estado' => 'enviando',
            'total' => $total_registros,
            'enviados' => $enviados,
            'errores' => $errores,
            'progreso_porcentaje' => $progreso,
            'ultimo_envio' => $email_limpio,
            'logs' => array_slice($logs, -10),
            'errores_detalle' => $errores_detalle
        ]);
    }
    
    // ==========================================
    // 🔹 DELAYS REALES Y FORZADOS
    // ==========================================
    
    log_detallado("Esperando $DELAY_ENTRE_CORREOS segundos...");
    sleep($DELAY_ENTRE_CORREOS); // DELAY ENTRE CADA CORREO
    
    // Pausa entre lotes
    if ($contador_lote >= $LOTE_SIZE && ($enviados + $errores) < $total_registros) {
        log_detallado("=== FIN DE LOTE - Pausa de $DELAY_ENTRE_LOTES segundos ===");
        sleep($DELAY_ENTRE_LOTES); // PAUSA LARGA
        $contador_lote = 0;
        log_detallado("=== REANUDANDO ENVÍOS ===");
    }
    
    // Liberar memoria
    unset($mail);
}

// ==========================================
// 🔹 FINALIZAR
// ==========================================
log_detallado("=== PROCESO COMPLETADO ===");
log_detallado("Total enviados: $enviados");
log_detallado("Total errores: $errores");

actualizar_estado([
    'iniciado' => date('Y-m-d H:i:s'),
    'estado' => 'completado',
    'total' => $total_registros,
    'enviados' => $enviados,
    'errores' => $errores,
    'progreso_porcentaje' => 100,
    'ultimo_envio' => 'Proceso completado',
    'logs' => array_slice($logs, -20),
    'errores_detalle' => $errores_detalle
]);

echo json_encode([
    'status' => 'completado',
    'enviados' => $enviados,
    'errores' => $errores,
    'total' => $total_registros
], JSON_UNESCAPED_UNICODE);

$conn->close();
log_detallado("=== FIN DEL SCRIPT ===");
exit;
?>