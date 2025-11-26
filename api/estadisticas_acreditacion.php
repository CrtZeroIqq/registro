<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

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

// Obtener parámetro de día (opcional)
$dia_filtro = $_GET['dia'] ?? null;

// ==========================================
// ESTADÍSTICAS GENERALES
// ==========================================

// Total de registros disponibles
$total_registros = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM registros");
if ($result && $row = $result->fetch_assoc()) {
    $total_registros += $row['total'];
}

$result = $conn->query("SELECT COUNT(*) as total FROM registros_inacap");
if ($result && $row = $result->fetch_assoc()) {
    $total_registros += $row['total'];
}

// Total de acreditaciones únicas (personas que se han acreditado al menos una vez)
$result = $conn->query("SELECT COUNT(DISTINCT codigo) as total FROM asistencias");
$acreditados_unicos = 0;
if ($result && $row = $result->fetch_assoc()) {
    $acreditados_unicos = $row['total'];
}

// Total de acreditaciones (escaneos totales)
$result = $conn->query("SELECT COUNT(*) as total FROM asistencias");
$total_acreditaciones = 0;
if ($result && $row = $result->fetch_assoc()) {
    $total_acreditaciones = $row['total'];
}

// ==========================================
// ESTADÍSTICAS POR DÍA
// ==========================================
$stats_por_dia = [];

$sql = "SELECT
            dia_evento,
            COUNT(*) as total_acreditaciones,
            COUNT(DISTINCT codigo) as personas_unicas,
            SUM(CASE WHEN tipo_asistente = 'general' THEN 1 ELSE 0 END) as general,
            SUM(CASE WHEN tipo_asistente = 'inacap' THEN 1 ELSE 0 END) as inacap
        FROM asistencias
        GROUP BY dia_evento
        ORDER BY dia_evento";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $fecha_obj = DateTime::createFromFormat('Y-m-d', $row['dia_evento']);
        $stats_por_dia[] = [
            'fecha' => $row['dia_evento'],
            'fecha_formatted' => $fecha_obj->format('d/m/Y'),
            'total_acreditaciones' => (int)$row['total_acreditaciones'],
            'personas_unicas' => (int)$row['personas_unicas'],
            'general' => (int)$row['general'],
            'inacap' => (int)$row['inacap']
        ];
    }
}

// ==========================================
// ACREDITACIONES POR HORA (DÍA ACTUAL O FILTRADO)
// ==========================================
$acreditaciones_por_hora = [];

$sql_hora = "SELECT
                HOUR(fecha_hora) as hora,
                COUNT(*) as cantidad
             FROM asistencias";

if ($dia_filtro) {
    $sql_hora .= " WHERE dia_evento = ?";
}

$sql_hora .= " GROUP BY HOUR(fecha_hora) ORDER BY hora";

if ($dia_filtro) {
    $stmt = $conn->prepare($sql_hora);
    $stmt->bind_param('s', $dia_filtro);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql_hora);
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $acreditaciones_por_hora[] = [
            'hora' => (int)$row['hora'],
            'cantidad' => (int)$row['cantidad']
        ];
    }
}

// ==========================================
// ÚLTIMAS ACREDITACIONES
// ==========================================
$ultimas_acreditaciones = [];

$sql_ultimas = "SELECT
                    codigo,
                    nombre_completo,
                    institucion,
                    email,
                    tipo_asistente,
                    dia_evento,
                    fecha_hora
                FROM asistencias
                ORDER BY fecha_hora DESC
                LIMIT 20";

$result = $conn->query($sql_ultimas);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $fecha_obj = DateTime::createFromFormat('Y-m-d', $row['dia_evento']);
        $ultimas_acreditaciones[] = [
            'codigo' => $row['codigo'],
            'nombre' => $row['nombre_completo'],
            'institucion' => $row['institucion'],
            'email' => $row['email'],
            'tipo' => $row['tipo_asistente'],
            'dia_evento' => $fecha_obj->format('d/m/Y'),
            'fecha_hora' => $row['fecha_hora']
        ];
    }
}

// ==========================================
// TOP INSTITUCIONES
// ==========================================
$top_instituciones = [];

$sql_inst = "SELECT
                institucion,
                COUNT(*) as cantidad
             FROM asistencias
             WHERE institucion IS NOT NULL AND institucion != ''
             GROUP BY institucion
             ORDER BY cantidad DESC
             LIMIT 10";

$result = $conn->query($sql_inst);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $top_instituciones[] = [
            'institucion' => $row['institucion'],
            'cantidad' => (int)$row['cantidad']
        ];
    }
}

// ==========================================
// RESPUESTA FINAL
// ==========================================
$response = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'resumen' => [
        'total_registros' => $total_registros,
        'acreditados_unicos' => $acreditados_unicos,
        'total_acreditaciones' => $total_acreditaciones,
        'porcentaje_acreditacion' => $total_registros > 0 ? round(($acreditados_unicos / $total_registros) * 100, 1) : 0
    ],
    'por_dia' => $stats_por_dia,
    'por_hora' => $acreditaciones_por_hora,
    'ultimas' => $ultimas_acreditaciones,
    'top_instituciones' => $top_instituciones
];

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$conn->close();
?>
