<?php
// ==========================================
// 🔹 API DEMO PARA ESTADÍSTICAS DEL DASHBOARD
// ==========================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
date_default_timezone_set('America/Santiago');

// DATOS DE EJEMPLO PARA DEMOSTRACIÓN
// En producción, estos datos vendrían de la base de datos

$response = [
    'general' => [
        'total_registros' => 162,
        'emails_unicos' => 160,
        'paises_total' => 8,
        'emails_abiertos' => 145,
        'emails_sin_abrir' => 17,
        'tasa_apertura' => 89.5
    ],
    'asistencia' => [
        'total_asistentes' => 128,
        'dia_26' => 98,
        'dia_27' => 112,
        'dia_28' => 87,
        'inacap' => 45,
        'general' => 83,
        'tasa_asistencia' => 79.0
    ],
    'paises' => [
        ['pais' => 'Chile', 'cantidad' => 98, 'porcentaje' => 60.5],
        ['pais' => 'Argentina', 'cantidad' => 28, 'porcentaje' => 17.3],
        ['pais' => 'Bolivia', 'cantidad' => 15, 'porcentaje' => 9.3],
        ['pais' => 'Brasil', 'cantidad' => 8, 'porcentaje' => 4.9],
        ['pais' => 'Perú', 'cantidad' => 6, 'porcentaje' => 3.7],
        ['pais' => 'Paraguay', 'cantidad' => 4, 'porcentaje' => 2.5],
        ['pais' => 'Uruguay', 'cantidad' => 2, 'porcentaje' => 1.2],
        ['pais' => 'Colombia', 'cantidad' => 1, 'porcentaje' => 0.6]
    ],
    'sectores' => [
        ['sector' => 'Logística y Transporte', 'cantidad' => 42, 'porcentaje' => 25.9],
        ['sector' => 'Comercio Internacional', 'cantidad' => 35, 'porcentaje' => 21.6],
        ['sector' => 'Gobierno y Sector Público', 'cantidad' => 28, 'porcentaje' => 17.3],
        ['sector' => 'Educación', 'cantidad' => 18, 'porcentaje' => 11.1],
        ['sector' => 'Tecnología', 'cantidad' => 15, 'porcentaje' => 9.3],
        ['sector' => 'Turismo', 'cantidad' => 12, 'porcentaje' => 7.4],
        ['sector' => 'Minería', 'cantidad' => 8, 'porcentaje' => 4.9],
        ['sector' => 'Otro', 'cantidad' => 4, 'porcentaje' => 2.5]
    ],
    'tamanos' => [
        ['tamano' => '1-10 empleados', 'cantidad' => 45, 'porcentaje' => 27.8],
        ['tamano' => '11-50 empleados', 'cantidad' => 38, 'porcentaje' => 23.5],
        ['tamano' => '51-200 empleados', 'cantidad' => 32, 'porcentaje' => 19.8],
        ['tamano' => '201-500 empleados', 'cantidad' => 25, 'porcentaje' => 15.4],
        ['tamano' => '501+ empleados', 'cantidad' => 22, 'porcentaje' => 13.6]
    ],
    'cargos' => [
        ['cargo' => 'Gerente General', 'cantidad' => 28],
        ['cargo' => 'Director', 'cantidad' => 22],
        ['cargo' => 'Jefe de Área', 'cantidad' => 18],
        ['cargo' => 'Gerente de Logística', 'cantidad' => 15],
        ['cargo' => 'Consultor', 'cantidad' => 12],
        ['cargo' => 'Analista', 'cantidad' => 10],
        ['cargo' => 'Coordinador', 'cantidad' => 9],
        ['cargo' => 'Ejecutivo Comercial', 'cantidad' => 8],
        ['cargo' => 'Emprendedor', 'cantidad' => 7],
        ['cargo' => 'Académico', 'cantidad' => 6]
    ],
    'intereses' => [
        ['interes' => 'Integración Regional', 'cantidad' => 95],
        ['interes' => 'Corredores Bioceánicos', 'cantidad' => 88],
        ['interes' => 'Desarrollo Económico', 'cantidad' => 76],
        ['interes' => 'Comercio Internacional', 'cantidad' => 68],
        ['interes' => 'Infraestructura', 'cantidad' => 52],
        ['interes' => 'Logística', 'cantidad' => 48],
        ['interes' => 'Turismo', 'cantidad' => 38],
        ['interes' => 'Sustentabilidad', 'cantidad' => 32],
        ['interes' => 'Innovación', 'cantidad' => 28],
        ['interes' => 'Networking', 'cantidad' => 25]
    ],
    'fuentes' => [
        ['fuente' => 'Redes Sociales', 'cantidad' => 65, 'porcentaje' => 40.1],
        ['fuente' => 'Email Directo', 'cantidad' => 42, 'porcentaje' => 25.9],
        ['fuente' => 'Sitio Web', 'cantidad' => 28, 'porcentaje' => 17.3],
        ['fuente' => 'Recomendación', 'cantidad' => 18, 'porcentaje' => 11.1],
        ['fuente' => 'Prensa', 'cantidad' => 9, 'porcentaje' => 5.6]
    ],
    'fechas_registro' => [
        ['fecha' => '2025-11-01', 'cantidad' => 8],
        ['fecha' => '2025-11-05', 'cantidad' => 15],
        ['fecha' => '2025-11-08', 'cantidad' => 22],
        ['fecha' => '2025-11-12', 'cantidad' => 28],
        ['fecha' => '2025-11-15', 'cantidad' => 35],
        ['fecha' => '2025-11-18', 'cantidad' => 42],
        ['fecha' => '2025-11-22', 'cantidad' => 12]
    ],
    'empresas' => [
        ['empresa' => 'INACAP Arica', 'pais' => 'Chile', 'cantidad' => 12],
        ['empresa' => 'Puerto de Arica', 'pais' => 'Chile', 'cantidad' => 8],
        ['empresa' => 'Gobierno Regional Arica y Parinacota', 'pais' => 'Chile', 'cantidad' => 7],
        ['empresa' => 'Cámara de Comercio Arica', 'pais' => 'Chile', 'cantidad' => 6],
        ['empresa' => 'Aduana Chile', 'pais' => 'Chile', 'cantidad' => 5],
        ['empresa' => 'Zona Franca de Arica', 'pais' => 'Chile', 'cantidad' => 5],
        ['empresa' => 'Transportes Andinos SA', 'pais' => 'Argentina', 'cantidad' => 4],
        ['empresa' => 'Logística Bolivia', 'pais' => 'Bolivia', 'cantidad' => 4],
        ['empresa' => 'Universidad de Tarapacá', 'pais' => 'Chile', 'cantidad' => 3],
        ['empresa' => 'Servicio Nacional de Turismo', 'pais' => 'Chile', 'cantidad' => 3],
        ['empresa' => 'Ministerio de Relaciones Exteriores', 'pais' => 'Chile', 'cantidad' => 3],
        ['empresa' => 'Cámara de Comercio Argentina', 'pais' => 'Argentina', 'cantidad' => 2],
        ['empresa' => 'Independiente', 'pais' => 'Chile', 'cantidad' => 15],
        ['empresa' => 'Consultor Independiente', 'pais' => 'Chile', 'cantidad' => 8],
        ['empresa' => 'Otras Empresas', 'pais' => 'Varios', 'cantidad' => 77]
    ],
    'rueda' => [
        ['rueda' => 'No', 'cantidad' => 162, 'porcentaje' => 100.0],
        ['rueda' => 'Sí', 'cantidad' => 0, 'porcentaje' => 0.0]
    ],
    'inacap' => [
        'total_inacap' => 45
    ]
];

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
