<?php
// ==========================================
// 🔹 TRACKING DE APERTURA DE EMAILS
// ==========================================
date_default_timezone_set('America/Santiago');

// Obtener código del email
$codigo = isset($_GET['code']) ? $_GET['code'] : '';

if (!empty($codigo)) {
    // Archivo de log
    $log_file = __DIR__ . '/email_tracking.log';
    
    // Datos del tracking
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Registrar apertura
    $log_entry = json_encode([
        'timestamp' => $timestamp,
        'codigo' => $codigo,
        'ip' => $ip,
        'user_agent' => $user_agent
    ]) . "\n";
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    
    // También registrar en BD (opcional)
    try {
        $conn = new mysqli("localhost", "seidev", 'Tz9!qE7#Xr2@Lm5$Vp8^', "registro_evento");
        
        if (!$conn->connect_error) {
            $stmt = $conn->prepare("UPDATE registros SET email_abierto = 1, fecha_apertura = NOW() WHERE codigo_registro = ?");
            $stmt->bind_param("s", $codigo);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    } catch (Exception $e) {
        // Silenciar errores para no romper el pixel
    }
}

// Devolver pixel transparente 1x1
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Pixel PNG 1x1 transparente en base64
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
exit;
?>