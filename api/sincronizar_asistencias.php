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

// Capturar array de asistencias pendientes
$data = json_decode(file_get_contents('php://input'), true);
$asistencias = $data['asistencias'] ?? [];

if (empty($asistencias) || !is_array($asistencias)) {
    echo json_encode(['status' => 'error', 'message' => 'No hay asistencias para sincronizar']);
    exit;
}

$resultados = [];
$exitosos = 0;
$errores = 0;

// Procesar cada asistencia
foreach ($asistencias as $asistencia) {
    $codigo = trim($asistencia['codigo'] ?? '');
    $dia_evento = trim($asistencia['dia_evento'] ?? '');
    $evento = trim($asistencia['evento'] ?? 'Nodo Bioceánico 2025');
    $timestamp = $asistencia['timestamp'] ?? null; // Timestamp de cuando se escaneó offline

    $resultado = [
        'codigo' => $codigo,
        'dia_evento' => $dia_evento,
        'evento' => $evento,
        'timestamp' => $timestamp
    ];

    // Validaciones básicas
    if (empty($codigo)) {
        $resultado['status'] = 'error';
        $resultado['message'] = 'Código QR vacío';
        $resultados[] = $resultado;
        $errores++;
        continue;
    }

    if (empty($dia_evento)) {
        $resultado['status'] = 'error';
        $resultado['message'] = 'Día del evento no especificado';
        $resultados[] = $resultado;
        $errores++;
        continue;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dia_evento)) {
        $resultado['status'] = 'error';
        $resultado['message'] = 'Formato de fecha inválido';
        $resultados[] = $resultado;
        $errores++;
        continue;
    }

    // Detectar tipo de código (REG o INACAP)
    $tipo_asistente = '';
    $nombre = '';
    $institucion = '';
    $email = '';

    if (strpos($codigo, 'REG') === 0) {
        $tipo_asistente = 'general';

        $stmt = $conn->prepare("SELECT nombre, apellido, empresa, email FROM registros WHERE codigo_registro = ? LIMIT 1");
        $stmt->bind_param('s', $codigo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'] . ' ' . $row['apellido'];
            $institucion = $row['empresa'];
            $email = $row['email'];
        } else {
            $resultado['status'] = 'error';
            $resultado['message'] = 'Código no encontrado en registros generales';
            $resultados[] = $resultado;
            $errores++;
            continue;
        }

    } elseif (strpos($codigo, 'INACAP') === 0) {
        $tipo_asistente = 'inacap';

        $stmt = $conn->prepare("SELECT nombre, email, tipo_participante, carrera FROM registros_inacap WHERE codigo = ? LIMIT 1");
        $stmt->bind_param('s', $codigo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
            $email = $row['email'];
            $institucion = 'INACAP';
        } else {
            $resultado['status'] = 'error';
            $resultado['message'] = 'Código no encontrado en registros INACAP';
            $resultados[] = $resultado;
            $errores++;
            continue;
        }

    } else {
        $resultado['status'] = 'error';
        $resultado['message'] = 'Formato de código QR no reconocido';
        $resultados[] = $resultado;
        $errores++;
        continue;
    }

    // Verificar si ya se registró asistencia para este día y evento
    $stmt = $conn->prepare("SELECT id FROM asistencias WHERE codigo = ? AND dia_evento = ? AND evento = ? LIMIT 1");
    $stmt->bind_param('sss', $codigo, $dia_evento, $evento);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Ya existe - esto no es necesariamente un error en modo offline
        $resultado['status'] = 'duplicado';
        $resultado['message'] = 'Ya registrado previamente';
        $resultado['nombre'] = $nombre;
        $resultados[] = $resultado;
        $exitosos++; // Contamos como exitoso porque ya está registrado
        continue;
    }

    // Registrar asistencia
    $stmt = $conn->prepare("INSERT INTO asistencias (codigo, dia_evento, evento, tipo_asistente, nombre_completo, email, institucion) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssssss', $codigo, $dia_evento, $evento, $tipo_asistente, $nombre, $email, $institucion);

    if ($stmt->execute()) {
        $resultado['status'] = 'ok';
        $resultado['message'] = 'Sincronizado exitosamente';
        $resultado['nombre'] = $nombre;
        $resultado['tipo_asistente'] = $tipo_asistente;
        $resultado['institucion'] = $institucion;
        $resultados[] = $resultado;
        $exitosos++;
    } else {
        $resultado['status'] = 'error';
        $resultado['message'] = 'Error al guardar: ' . $stmt->error;
        $resultados[] = $resultado;
        $errores++;
    }
}

$conn->close();

// Respuesta consolidada
echo json_encode([
    'status' => 'ok',
    'message' => "Sincronización completada: {$exitosos} exitosos, {$errores} errores",
    'total' => count($asistencias),
    'exitosos' => $exitosos,
    'errores' => $errores,
    'resultados' => $resultados
]);
?>
