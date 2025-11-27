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

// Capturar datos
$data = json_decode(file_get_contents('php://input'), true);
$codigo = trim($data['codigo'] ?? '');
$dia_evento = trim($data['dia_evento'] ?? '');
$evento = trim($data['evento'] ?? 'Nodo Bioceánico 2025');

if (empty($codigo)) {
    echo json_encode(['status' => 'error', 'message' => 'Código QR vacío']);
    exit;
}

if (empty($dia_evento)) {
    echo json_encode(['status' => 'error', 'message' => 'Día del evento no especificado']);
    exit;
}

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dia_evento)) {
    echo json_encode(['status' => 'error', 'message' => 'Formato de fecha inválido']);
    exit;
}

// Detectar tipo de código (REG o INACAP)
$tipo_asistente = '';
$nombre = '';
$institucion = '';
$tipo_participante = '';
$carrera = '';

if (strpos($codigo, 'REG') === 0) {
    // Es un registro general
    $tipo_asistente = 'general';
    
    // Buscar en tabla registros
    $stmt = $conn->prepare("SELECT nombre, apellido, empresa FROM registros WHERE codigo_registro = ? LIMIT 1");
    $stmt->bind_param('s', $codigo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre = $row['nombre'] . ' ' . $row['apellido'];
        $institucion = $row['empresa'];
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Código no encontrado en registros generales']);
        exit;
    }
    
} elseif (strpos($codigo, 'INACAP') === 0) {
    // Es un registro INACAP
    $tipo_asistente = 'inacap';
    
    // Buscar en tabla registros_inacap
    $stmt = $conn->prepare("SELECT nombre, tipo_participante, carrera FROM registros_inacap WHERE codigo = ? LIMIT 1");
    $stmt->bind_param('s', $codigo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre = $row['nombre'];
        $tipo_participante = $row['tipo_participante'];
        $carrera = $row['carrera'];
        $institucion = 'INACAP';
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Código no encontrado en registros INACAP']);
        exit;
    }
    
} else {
    echo json_encode(['status' => 'error', 'message' => 'Formato de código QR no reconocido']);
    exit;
}

// Verificar si ya se registró asistencia para este día y evento
$stmt = $conn->prepare("SELECT id FROM asistencias WHERE codigo = ? AND dia_evento = ? AND evento = ? LIMIT 1");
$stmt->bind_param('sss', $codigo, $dia_evento, $evento);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Ya existe asistencia para este día y evento
    echo json_encode([
        'status' => 'error',
        'message' => '⚠️ Este código ya registró asistencia para este evento en este día'
    ]);
    exit;
}

// Registrar asistencia
$stmt = $conn->prepare("INSERT INTO asistencias (codigo, dia_evento, evento, tipo_asistente, nombre_completo, institucion) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssss', $codigo, $dia_evento, $evento, $tipo_asistente, $nombre, $institucion);

if ($stmt->execute()) {
    // Formatear fecha para mostrar
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $dia_evento);
    $dia_evento_formatted = $fecha_obj->format('d/m/Y');

    $response = [
        'status' => 'ok',
        'message' => 'Asistencia registrada exitosamente',
        'codigo' => $codigo,
        'nombre' => $nombre,
        'tipo_asistente' => $tipo_asistente,
        'institucion' => $institucion,
        'dia_evento_formatted' => $dia_evento_formatted,
        'evento' => $evento
    ];

    // Agregar campos específicos según el tipo
    if ($tipo_asistente === 'inacap') {
        $response['tipo_participante'] = $tipo_participante;
        $response['carrera'] = $carrera;
    }

    echo json_encode($response);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al guardar asistencia: ' . $stmt->error
    ]);
}

$conn->close();
?>