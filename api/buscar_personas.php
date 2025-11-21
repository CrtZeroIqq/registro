<?php
header('Content-Type: application/json');

$servername = 'localhost';
$username   = 'seidev';
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = 'registro_evento';

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$conn->set_charset('utf8mb4');

$nombre   = trim($_GET['nombre']   ?? '');
$empresa  = trim($_GET['empresa']  ?? '');
$pais     = trim($_GET['pais']     ?? '');

$query = "SELECT nombre, apellido, empresa, pais, codigo_registro 
          FROM registros WHERE 1=1";

$params = [];
$types  = "";

if ($nombre !== "") {
    $query .= " AND (nombre LIKE ? OR apellido LIKE ?)";
    $params[] = "%$nombre%";
    $params[] = "%$nombre%";
    $types .= "ss";
}

if ($empresa !== "") {
    $query .= " AND empresa LIKE ?";
    $params[] = "%$empresa%";
    $types .= "s";
}

if ($pais !== "") {
    $query .= " AND pais LIKE ?";
    $params[] = "%$pais%";
    $types .= "s";
}

$query .= " ORDER BY nombre LIMIT 50";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = [
        "codigo"      => $row['codigo_registro'],
        "nombre"      => $row['nombre'] . " " . $row['apellido'],
        "empresa"     => $row['empresa'],
        "pais"        => $row['pais']
    ];
}

echo json_encode($data);
