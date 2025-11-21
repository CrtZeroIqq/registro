<?php
// ==========================================
// 🔹 API PARA OBTENER ESTADÍSTICAS
// ==========================================
header('Content-Type: application/json');
date_default_timezone_set('America/Santiago');

$servername = "localhost";
$username   = "seidev";
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión']));
}

// Obtener estadísticas generales
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN email_abierto = 1 THEN 1 ELSE 0 END) as abiertos,
    SUM(CASE WHEN email_abierto = 0 OR email_abierto IS NULL THEN 1 ELSE 0 END) as sin_abrir
FROM registros";

$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();

$tasa_apertura = $stats['total'] > 0 ? 
    round(($stats['abiertos'] / $stats['total']) * 100, 1) : 0;

// Obtener registros detallados
$sql_registros = "SELECT 
    codigo_registro as codigo,
    nombre,
    apellido,
    email,
    COALESCE(email_abierto, 0) as abierto,
    DATE_FORMAT(fecha_apertura, '%d/%m/%Y %H:%i') as fecha_apertura
FROM registros
ORDER BY fecha_apertura DESC, apellido ASC";

$result_registros = $conn->query($sql_registros);

$registros = [];
while ($row = $result_registros->fetch_assoc()) {
    $registros[] = $row;
}

$conn->close();

// Respuesta JSON
echo json_encode([
    'total' => (int)$stats['total'],
    'abiertos' => (int)$stats['abiertos'],
    'sin_abrir' => (int)$stats['sin_abrir'],
    'tasa_apertura' => $tasa_apertura,
    'registros' => $registros
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>