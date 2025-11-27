<?php
header('Content-Type: application/json');

// Configuración de base de datos
$servername = 'localhost';
$username   = 'seidev';
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = 'registro_evento';

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error al conectar con la base de datos']);
    exit;
}

$conn->set_charset('utf8mb4');

$registros = [];

// Obtener registros generales
$sql_general = "SELECT codigo_registro as codigo, CONCAT(nombre, ' ', apellido) as nombre, empresa, 'General' as pais FROM registros ORDER BY nombre";
$result_general = $conn->query($sql_general);

if ($result_general && $result_general->num_rows > 0) {
    while ($row = $result_general->fetch_assoc()) {
        $registros[] = [
            'codigo' => $row['codigo'],
            'nombre' => $row['nombre'],
            'empresa' => $row['empresa'] ?? '',
            'pais' => $row['pais'] ?? 'N/A',
            'tipo' => 'general'
        ];
    }
}

// Obtener registros INACAP
$sql_inacap = "SELECT codigo, nombre, carrera as empresa, tipo_participante as pais FROM registros_inacap ORDER BY nombre";
$result_inacap = $conn->query($sql_inacap);

if ($result_inacap && $result_inacap->num_rows > 0) {
    while ($row = $result_inacap->fetch_assoc()) {
        $registros[] = [
            'codigo' => $row['codigo'],
            'nombre' => $row['nombre'],
            'empresa' => $row['empresa'] ?? 'INACAP',
            'pais' => $row['pais'] ?? 'Chile',
            'tipo' => 'inacap'
        ];
    }
}

$conn->close();

// Respuesta con timestamp
echo json_encode([
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'total' => count($registros),
    'registros' => $registros
]);
?>
