<?php
// ==========================================
// 🔹 API - VERIFICAR CUPOS DISPONIBLES
// ==========================================
// Cuenta registros actuales y calcula cupos disponibles
// Límite máximo: 300 registros
// ==========================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('America/Santiago');

// Configuración
$LIMITE_CUPOS = 300;

// ==========================================
// 🔹 CONECTAR A BASE DE DATOS
// ==========================================
$servername = "localhost";
$username = "seidev";
$password = "Tz9!qE7#Xr2@Lm5\$Vp8^";
$database = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

$conn->set_charset("utf8mb4");

// ==========================================
// 🔹 CONTAR REGISTROS ACTUALES
// ==========================================
$result = $conn->query("SELECT COUNT(*) as total FROM registros");

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al consultar registros'
    ]);
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();
$total_registros = (int)$row['total'];
$cupos_disponibles = $LIMITE_CUPOS - $total_registros;
$registro_cerrado = $total_registros >= $LIMITE_CUPOS;

$conn->close();

// ==========================================
// 🔹 RESPUESTA JSON
// ==========================================
echo json_encode([
    'status' => 'ok',
    'limite_cupos' => $LIMITE_CUPOS,
    'total_registros' => $total_registros,
    'cupos_disponibles' => max(0, $cupos_disponibles),
    'registro_cerrado' => $registro_cerrado,
    'porcentaje_ocupado' => round(($total_registros / $LIMITE_CUPOS) * 100, 1),
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);
