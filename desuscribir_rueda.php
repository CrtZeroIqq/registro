<?php
// ==========================================
// 🔹 DESUSCRIPCIÓN DE RUEDA DE NEGOCIOS
// ==========================================
// Permite a los usuarios indicar que ya no están
// interesados en participar de la Rueda de Negocios
// ==========================================

date_default_timezone_set('America/Santiago');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// 🔹 VALIDAR PARÁMETRO
// ==========================================
if (!isset($_GET['codigo']) || empty($_GET['codigo'])) {
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Error - Rueda de Negocios</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #d32f2f; margin-top: 0; }
            p { color: #666; line-height: 1.6; }
            a { color: #004aad; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>⚠️ Error</h1>
            <p>No se proporcionó un código válido.</p>
            <p><a href='https://www.bioceanicocentral.cl'>← Volver al sitio</a></p>
        </div>
    </body>
    </html>
    ");
}

$codigo = trim($_GET['codigo']);

// ==========================================
// 🔹 CONECTAR A BASE DE DATOS
// ==========================================
$servername = "localhost";
$username = "seidev";
$password = "Tz9!qE7#Xr2@Lm5\$Vp8^";
$database = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// ==========================================
// 🔹 VERIFICAR SI EL CÓDIGO EXISTE
// ==========================================
$stmt = $conn->prepare("SELECT id, nombre, apellido, email, rueda FROM registros WHERE codigo = ?");
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>No encontrado - Rueda de Negocios</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #d32f2f; margin-top: 0; }
            p { color: #666; line-height: 1.6; }
            a { color: #004aad; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>❌ Registro no encontrado</h1>
            <p>No se encontró un registro con el código proporcionado.</p>
            <p>Código: <code>$codigo</code></p>
            <p><a href='https://www.bioceanicocentral.cl'>← Volver al sitio</a></p>
        </div>
    </body>
    </html>
    ");
}

$row = $result->fetch_assoc();
$id = $row['id'];
$nombre = $row['nombre'];
$apellido = $row['apellido'];
$email = $row['email'];
$rueda_actual = $row['rueda'];

$stmt->close();

// ==========================================
// 🔹 VERIFICAR SI YA ESTÁ DESUSCRITO
// ==========================================
if ($rueda_actual === 'No' || $rueda_actual === null) {
    $conn->close();
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Ya desuscrito - Rueda de Negocios</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #666; margin-top: 0; }
            p { color: #666; line-height: 1.6; }
            .info-box { background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #666; }
            a { color: #004aad; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>ℹ️ Ya desuscrito</h1>
            <p>Hola <strong>$nombre $apellido</strong>,</p>
            <p>Tu registro ya está marcado como <strong>no interesado</strong> en la Rueda de Negocios.</p>
            <div class='info-box'>
                <p style='margin: 0;'><strong>Estado actual:</strong> No interesado</p>
            </div>
            <p>Si cambias de opinión, puedes contactarnos a <a href='mailto:contacto@bioceanicocentral.cl'>contacto@bioceanicocentral.cl</a></p>
            <p><a href='https://www.bioceanicocentral.cl'>← Volver al sitio</a></p>
        </div>
    </body>
    </html>
    ");
}

// ==========================================
// 🔹 ACTUALIZAR REGISTRO - CAMBIAR A 'No'
// ==========================================
$stmt_update = $conn->prepare("UPDATE registros SET rueda = 'No' WHERE id = ?");
$stmt_update->bind_param("i", $id);
$success = $stmt_update->execute();
$stmt_update->close();
$conn->close();

if (!$success) {
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Error - Rueda de Negocios</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #d32f2f; margin-top: 0; }
            p { color: #666; line-height: 1.6; }
            a { color: #004aad; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>❌ Error al actualizar</h1>
            <p>Hubo un error al procesar tu solicitud. Por favor intenta nuevamente más tarde.</p>
            <p><a href='https://www.bioceanicocentral.cl'>← Volver al sitio</a></p>
        </div>
    </body>
    </html>
    ");
}

// ==========================================
// 🔹 MOSTRAR CONFIRMACIÓN
// ==========================================
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Desuscrito - Rueda de Negocios</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            padding: 40px 20px;
            background: #f5f5f5;
            margin: 0;
        }
        .container {
            max-width: 550px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 5px solid #004aad;
        }
        h1 {
            color: #1a1a1a;
            margin-top: 0;
            font-size: 24px;
        }
        p {
            color: #666;
            line-height: 1.7;
            margin: 15px 0;
        }
        .success-box {
            background: #e8f5e9;
            padding: 20px;
            border-radius: 4px;
            margin: 25px 0;
            border-left: 4px solid #4caf50;
        }
        .success-box p {
            margin: 0;
            color: #2e7d32;
            font-weight: 500;
        }
        .info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 14px;
        }
        a {
            color: #004aad;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
            font-size: 13px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>✓ Actualización confirmada</h1>

        <p>Hola <strong><?php echo htmlspecialchars($nombre . ' ' . $apellido, ENT_QUOTES, 'UTF-8'); ?></strong>,</p>

        <div class='success-box'>
            <p>✓ Tu preferencia ha sido actualizada correctamente</p>
        </div>

        <p>Hemos registrado que <strong>ya no estás interesado/a</strong> en participar de la Rueda de Negocios del evento Nodo Bioceánico Central 2025.</p>

        <div class='info'>
            <p style='margin: 0 0 8px 0;'><strong>Tu registro principal se mantiene activo</strong></p>
            <p style='margin: 0; color: #666;'>Solo se removió tu interés en la Rueda de Negocios. Sigues registrado/a para los demás eventos del Nodo Bioceánico Central 2025.</p>
        </div>

        <p>Si cambias de opinión en el futuro, puedes contactarnos a:</p>
        <p style='text-align: center; margin: 20px 0;'>
            <a href='mailto:contacto@bioceanicocentral.cl'>contacto@bioceanicocentral.cl</a>
        </p>

        <div class='footer'>
            <p style='margin: 0;'>Código de registro: <code><?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?></code></p>
            <p style='margin: 10px 0 0 0;'><a href='https://www.bioceanicocentral.cl'>← Volver al sitio principal</a></p>
        </div>
    </div>
</body>
</html>
