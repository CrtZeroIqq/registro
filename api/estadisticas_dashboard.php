<?php
// ==========================================
// 🔹 API PARA ESTADÍSTICAS DEL DASHBOARD
// ==========================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
date_default_timezone_set('America/Santiago');

$servername = "localhost";
$username   = "seidev";
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

$conn->set_charset("utf8mb4");

// ============================
// 📊 ESTADÍSTICAS GENERALES
// ============================
$sql_general = "SELECT
    COUNT(*) as total_registros,
    COUNT(DISTINCT email) as emails_unicos,
    COUNT(DISTINCT pais) as paises_total,
    SUM(CASE WHEN email_abierto = 1 THEN 1 ELSE 0 END) as emails_abiertos,
    SUM(CASE WHEN email_abierto = 0 OR email_abierto IS NULL THEN 1 ELSE 0 END) as emails_sin_abrir
FROM registros";

$result = $conn->query($sql_general);
$stats_general = $result->fetch_assoc();

// Calcular tasa de apertura
$tasa_apertura = $stats_general['total_registros'] > 0 ?
    round(($stats_general['emails_abiertos'] / $stats_general['total_registros']) * 100, 1) : 0;

$stats_general['tasa_apertura'] = $tasa_apertura;

// ============================
// 📊 ESTADÍSTICAS DE ASISTENCIA
// ============================
$sql_asistencia = "SELECT
    COUNT(DISTINCT codigo_registro) as total_asistentes,
    SUM(CASE WHEN fecha_asistencia LIKE '2025-11-26%' THEN 1 ELSE 0 END) as dia_26,
    SUM(CASE WHEN fecha_asistencia LIKE '2025-11-27%' THEN 1 ELSE 0 END) as dia_27,
    SUM(CASE WHEN fecha_asistencia LIKE '2025-11-28%' THEN 1 ELSE 0 END) as dia_28,
    SUM(CASE WHEN tipo_participante = 'inacap' THEN 1 ELSE 0 END) as inacap,
    SUM(CASE WHEN tipo_participante = 'general' THEN 1 ELSE 0 END) as general
FROM asistencias";

$result = $conn->query($sql_asistencia);
$stats_asistencia = $result->fetch_assoc();

// Tasa de asistencia
$tasa_asistencia = $stats_general['total_registros'] > 0 ?
    round(($stats_asistencia['total_asistentes'] / $stats_general['total_registros']) * 100, 1) : 0;

$stats_asistencia['tasa_asistencia'] = $tasa_asistencia;

// ============================
// 🌎 ESTADÍSTICAS POR PAÍS
// ============================
$sql_paises = "SELECT
    pais,
    COUNT(*) as cantidad,
    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM registros)), 1) as porcentaje
FROM registros
WHERE pais IS NOT NULL AND pais != ''
GROUP BY pais
ORDER BY cantidad DESC
LIMIT 15";

$result = $conn->query($sql_paises);
$stats_paises = [];
while ($row = $result->fetch_assoc()) {
    $stats_paises[] = $row;
}

// ============================
// 🏢 ESTADÍSTICAS POR SECTOR
// ============================
$sql_sectores = "SELECT
    sector,
    COUNT(*) as cantidad,
    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM registros WHERE sector IS NOT NULL AND sector != '')), 1) as porcentaje
FROM registros
WHERE sector IS NOT NULL AND sector != ''
GROUP BY sector
ORDER BY cantidad DESC";

$result = $conn->query($sql_sectores);
$stats_sectores = [];
while ($row = $result->fetch_assoc()) {
    $stats_sectores[] = $row;
}

// ============================
// 📏 ESTADÍSTICAS POR TAMAÑO DE EMPRESA
// ============================
$sql_tamanos = "SELECT
    tamano,
    COUNT(*) as cantidad,
    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM registros WHERE tamano IS NOT NULL AND tamano != '')), 1) as porcentaje
FROM registros
WHERE tamano IS NOT NULL AND tamano != ''
GROUP BY tamano
ORDER BY
    CASE tamano
        WHEN '1-10 empleados' THEN 1
        WHEN '11-50 empleados' THEN 2
        WHEN '51-200 empleados' THEN 3
        WHEN '201-500 empleados' THEN 4
        WHEN '501+ empleados' THEN 5
        ELSE 6
    END";

$result = $conn->query($sql_tamanos);
$stats_tamanos = [];
while ($row = $result->fetch_assoc()) {
    $stats_tamanos[] = $row;
}

// ============================
// 💼 ESTADÍSTICAS DE CARGOS
// ============================
$sql_cargos = "SELECT
    cargo,
    COUNT(*) as cantidad
FROM registros
WHERE cargo IS NOT NULL AND cargo != ''
GROUP BY cargo
ORDER BY cantidad DESC
LIMIT 10";

$result = $conn->query($sql_cargos);
$stats_cargos = [];
while ($row = $result->fetch_assoc()) {
    $stats_cargos[] = $row;
}

// ============================
// 🎯 ESTADÍSTICAS DE INTERESES
// ============================
$sql_intereses = "SELECT intereses FROM registros WHERE intereses IS NOT NULL AND intereses != ''";
$result = $conn->query($sql_intereses);

$intereses_count = [];
while ($row = $result->fetch_assoc()) {
    $intereses_array = array_map('trim', explode(',', $row['intereses']));
    foreach ($intereses_array as $interes) {
        if (!empty($interes)) {
            if (!isset($intereses_count[$interes])) {
                $intereses_count[$interes] = 0;
            }
            $intereses_count[$interes]++;
        }
    }
}

arsort($intereses_count);
$stats_intereses = [];
foreach ($intereses_count as $interes => $cantidad) {
    $stats_intereses[] = [
        'interes' => $interes,
        'cantidad' => $cantidad
    ];
}

// ============================
// 📈 ESTADÍSTICAS POR FUENTE
// ============================
$sql_fuentes = "SELECT
    fuente,
    COUNT(*) as cantidad,
    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM registros WHERE fuente IS NOT NULL AND fuente != '')), 1) as porcentaje
FROM registros
WHERE fuente IS NOT NULL AND fuente != ''
GROUP BY fuente
ORDER BY cantidad DESC";

$result = $conn->query($sql_fuentes);
$stats_fuentes = [];
while ($row = $result->fetch_assoc()) {
    $stats_fuentes[] = $row;
}

// ============================
// 📅 ESTADÍSTICAS POR FECHA DE REGISTRO
// ============================
$sql_fechas = "SELECT
    DATE(fecha_registro) as fecha,
    COUNT(*) as cantidad
FROM registros
WHERE fecha_registro IS NOT NULL
GROUP BY DATE(fecha_registro)
ORDER BY fecha ASC";

$result = $conn->query($sql_fechas);
$stats_fechas = [];
while ($row = $result->fetch_assoc()) {
    $stats_fechas[] = $row;
}

// ============================
// 🔝 TOP EMPRESAS
// ============================
$sql_empresas = "SELECT
    empresa,
    COUNT(*) as cantidad,
    pais
FROM registros
WHERE empresa IS NOT NULL AND empresa != ''
GROUP BY empresa, pais
ORDER BY cantidad DESC
LIMIT 15";

$result = $conn->query($sql_empresas);
$stats_empresas = [];
while ($row = $result->fetch_assoc()) {
    $stats_empresas[] = $row;
}

// ============================
// 🤝 ESTADÍSTICAS RUEDA DE NEGOCIOS
// ============================
$sql_rueda = "SELECT
    rueda,
    COUNT(*) as cantidad,
    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM registros)), 1) as porcentaje
FROM registros
GROUP BY rueda
ORDER BY cantidad DESC";

$result = $conn->query($sql_rueda);
$stats_rueda = [];
while ($row = $result->fetch_assoc()) {
    $stats_rueda[] = $row;
}

// ============================
// 📊 REGISTROS INACAP
// ============================
$sql_inacap = "SELECT COUNT(*) as total_inacap FROM registros_inacap";
$result = $conn->query($sql_inacap);
$stats_inacap = $result->fetch_assoc();

$conn->close();

// ============================
// 📤 RESPUESTA JSON
// ============================
echo json_encode([
    'general' => $stats_general,
    'asistencia' => $stats_asistencia,
    'paises' => $stats_paises,
    'sectores' => $stats_sectores,
    'tamanos' => $stats_tamanos,
    'cargos' => $stats_cargos,
    'intereses' => $stats_intereses,
    'fuentes' => $stats_fuentes,
    'fechas_registro' => $stats_fechas,
    'empresas' => $stats_empresas,
    'rueda' => $stats_rueda,
    'inacap' => $stats_inacap
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
