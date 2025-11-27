<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Santiago');

$servername = 'localhost';
$username   = 'seidev';
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = 'registro_evento';

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión']);
    exit;
}

$conn->set_charset('utf8mb4');

// Buscar el campo de fecha disponible en la tabla de registros
$campoFecha = null;

$stmtCampo = $conn->prepare(
    "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'registros' 
     AND COLUMN_NAME IN ('created_at','fecha_registro','fecha')
     LIMIT 1"
);
$stmtCampo->bind_param('s', $database);
$stmtCampo->execute();
$resultCampo = $stmtCampo->get_result();
if ($row = $resultCampo->fetch_assoc()) {
    $campoFecha = $row['COLUMN_NAME'];
}
$stmtCampo->close();

if ($campoFecha) {
    $expresionFecha = "DATE($campoFecha)";
} else {
    // Fallback: extraer la fecha desde el código de registro (formato REGYYYYMMDDhhmmss###)
    $expresionFecha = "DATE(STR_TO_DATE(SUBSTRING(codigo_registro, 4, 8), '%Y%m%d'))";
}

// Agrupar registros por día
$sql = "SELECT $expresionFecha AS dia, COUNT(*) AS total
        FROM registros
        WHERE $expresionFecha IS NOT NULL
        GROUP BY dia
        ORDER BY dia DESC";

$result = $conn->query($sql);
$diario = [];
$totalGeneral = 0;
$hoy = date('Y-m-d');
$ayer = date('Y-m-d', strtotime('-1 day'));
$hoyTotal = 0;
$ayerTotal = 0;
$ultimaSemana = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dia = $row['dia'];
        $totalDia = (int)$row['total'];

        $diario[] = [
            'dia' => $dia,
            'total' => $totalDia
        ];

        $totalGeneral += $totalDia;

        if ($dia === $hoy) {
            $hoyTotal = $totalDia;
        }

        if ($dia === $ayer) {
            $ayerTotal = $totalDia;
        }

        if ($dia >= date('Y-m-d', strtotime('-6 days'))) {
            $ultimaSemana += $totalDia;
        }
    }
}

$conn->close();

$response = [
    'status' => 'ok',
    'actualizado' => date('Y-m-d H:i:s'),
    'total_general' => $totalGeneral,
    'hoy' => $hoyTotal,
    'ayer' => $ayerTotal,
    'ultima_semana' => $ultimaSemana,
    'diario' => $diario
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
