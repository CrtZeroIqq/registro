<?php
// ==========================================
// 🔹 SCRIPT PARA VERIFICAR EMPRESAS INTERESADAS EN RUEDA DE NEGOCIOS
// ==========================================

header('Content-Type: application/json; charset=utf-8');

$servername = "localhost";
$username   = "seidev";
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Error BD: ' . $conn->connect_error]));
}

$conn->set_charset("utf8mb4");

// Consultar empresas que dijeron SÍ a la rueda
$sql = "SELECT id, nombre, apellido, email, empresa, cargo, rueda
        FROM registros
        WHERE (LOWER(rueda) = 'si' OR LOWER(rueda) = 'sí')
        ORDER BY id";

$result = $conn->query($sql);

$empresas = [];
$total = 0;

if ($result && $result->num_rows > 0) {
    $total = $result->num_rows;
    while ($row = $result->fetch_assoc()) {
        $empresas[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'] . ' ' . $row['apellido'],
            'email' => $row['email'],
            'empresa' => $row['empresa'],
            'cargo' => $row['cargo'],
            'rueda' => $row['rueda']
        ];
    }
}

// Contar total de registros en general
$sql_total = "SELECT COUNT(*) as total FROM registros";
$result_total = $conn->query($sql_total);
$total_registros = $result_total->fetch_assoc()['total'];

$conn->close();

echo json_encode([
    'status' => 'ok',
    'total_interesados' => $total,
    'total_registros' => $total_registros,
    'porcentaje' => $total_registros > 0 ? round(($total / $total_registros) * 100, 2) : 0,
    'empresas' => $empresas
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
