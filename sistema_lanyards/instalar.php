<?php
// ==========================================
// 🔹 INSTALADOR AUTOMÁTICO DEL SISTEMA DE LANYARDS
// ==========================================
// Ejecuta este script UNA VEZ después de subir la carpeta al servidor
// URL: https://tu-dominio.com/sistema_lanyards/instalar.php

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Instalación - Sistema de Lanyards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #004aad;
        }
        .step {
            margin: 20px 0;
            padding: 15px;
            border-left: 4px solid #004aad;
            background: #f8f9fa;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        pre {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 Instalación - Sistema de Lanyards</h1>
        <p>Este script instalará automáticamente todos los componentes necesarios.</p>
";

// ==========================================
// PASO 1: Verificar PHP
// ==========================================
echo "<div class='step'>";
echo "<h3>Paso 1: Verificar PHP</h3>";
if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
    echo "<p class='success'>✓ PHP " . PHP_VERSION . " detectado (OK)</p>";
} else {
    echo "<p class='error'>✗ PHP " . PHP_VERSION . " - Se requiere PHP 7.0 o superior</p>";
    die("</div></div></body></html>");
}
echo "</div>";

// ==========================================
// PASO 2: Verificar extensiones PHP
// ==========================================
echo "<div class='step'>";
echo "<h3>Paso 2: Verificar Extensiones PHP</h3>";

$extensiones_requeridas = ['mysqli', 'gd', 'mbstring', 'zlib'];
$falta_extension = false;

foreach ($extensiones_requeridas as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✓ Extensión $ext: OK</p>";
    } else {
        echo "<p class='error'>✗ Extensión $ext: NO DISPONIBLE</p>";
        $falta_extension = true;
    }
}

if ($falta_extension) {
    echo "<p class='warning'>⚠️ Algunas extensiones faltan. El sistema puede no funcionar correctamente.</p>";
}
echo "</div>";

// ==========================================
// PASO 3: Verificar librería phpqrcode
// ==========================================
echo "<div class='step'>";
echo "<h3>Paso 3: Verificar Librería phpqrcode</h3>";

$phpqrcode_dir = __DIR__ . '/phpqrcode';

if (is_dir($phpqrcode_dir) && file_exists($phpqrcode_dir . '/qrlib.php')) {
    echo "<p class='success'>✓ phpqrcode está instalado correctamente</p>";
    echo "<p>Ubicación: $phpqrcode_dir</p>";
} else {
    echo "<p class='error'>✗ phpqrcode NO encontrado</p>";
    echo "<p class='warning'>⚠️ La librería phpqrcode debe estar incluida en la carpeta sistema_lanyards/</p>";
    echo "<p>Si falta, descarga el paquete completo nuevamente.</p>";
}
echo "</div>";

// ==========================================
// PASO 4: Descargar e instalar TCPDF
// ==========================================
echo "<div class='step'>";
echo "<h3>Paso 4: Instalar TCPDF</h3>";

$tcpdf_dir = __DIR__ . '/tcpdf';

if (is_dir($tcpdf_dir)) {
    echo "<p class='warning'>⚠️ TCPDF ya está instalado</p>";
    echo "<p>Ubicación: $tcpdf_dir</p>";
} else {
    echo "<p>Descargando TCPDF...</p>";

    $tcpdf_url = 'https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.2.tar.gz';
    $tcpdf_tar = __DIR__ . '/tcpdf.tar.gz';

    // Descargar TCPDF
    if (function_exists('file_get_contents')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 60,
                'user_agent' => 'Mozilla/5.0'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $tcpdf_content = @file_get_contents($tcpdf_url, false, $context);

        if ($tcpdf_content !== false) {
            file_put_contents($tcpdf_tar, $tcpdf_content);
            echo "<p class='success'>✓ TCPDF descargado correctamente</p>";

            // Extraer archivo
            try {
                $phar = new PharData($tcpdf_tar);
                $phar->extractTo(__DIR__);

                // Renombrar carpeta
                rename(__DIR__ . '/TCPDF-6.6.2', $tcpdf_dir);

                // Eliminar archivo temporal
                unlink($tcpdf_tar);

                echo "<p class='success'>✓ TCPDF instalado correctamente</p>";
                echo "<p>Ubicación: $tcpdf_dir</p>";
            } catch (Exception $e) {
                echo "<p class='error'>✗ Error al extraer TCPDF: " . $e->getMessage() . "</p>";
                echo "<p class='warning'>Intenta instalar manualmente:</p>";
                echo "<pre>wget https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.2.tar.gz
tar -xzf 6.6.2.tar.gz
mv TCPDF-6.6.2 tcpdf
rm 6.6.2.tar.gz</pre>";
            }
        } else {
            echo "<p class='error'>✗ No se pudo descargar TCPDF</p>";
            echo "<p class='warning'>Instala manualmente usando el siguiente comando SSH:</p>";
            echo "<pre>cd " . __DIR__ . "
wget https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.2.tar.gz
tar -xzf 6.6.2.tar.gz
mv TCPDF-6.6.2 tcpdf
rm 6.6.2.tar.gz</pre>";
        }
    } else {
        echo "<p class='error'>✗ file_get_contents() no disponible</p>";
        echo "<p class='warning'>Instala TCPDF manualmente por SSH</p>";
    }
}
echo "</div>";

// ==========================================
// PASO 5: Verificar permisos
// ==========================================
echo "<div class='step'>";
echo "<h3>Paso 5: Verificar Permisos</h3>";

$permisos_ok = true;

// Verificar permiso de escritura en directorio actual
if (is_writable(__DIR__)) {
    echo "<p class='success'>✓ Permisos de escritura: OK</p>";
} else {
    echo "<p class='error'>✗ No hay permisos de escritura en: " . __DIR__ . "</p>";
    $permisos_ok = false;
}

// Verificar carpeta qrcodes en directorio padre
$qrcodes_dir = dirname(__DIR__) . '/qrcodes';
if (is_dir($qrcodes_dir)) {
    if (is_writable($qrcodes_dir)) {
        echo "<p class='success'>✓ Carpeta QR codes: OK</p>";
    } else {
        echo "<p class='warning'>⚠️ No hay permisos de escritura en: $qrcodes_dir</p>";
        echo "<p>Ejecuta: <code>chmod 755 $qrcodes_dir</code></p>";
    }
} else {
    echo "<p class='warning'>⚠️ Carpeta QR codes no existe: $qrcodes_dir</p>";
    echo "<p>Se creará automáticamente al generar el PDF</p>";
}

echo "</div>";

// ==========================================
// PASO 6: Verificar base de datos
// ==========================================
echo "<div class='step'>";
echo "<h3>Paso 6: Verificar Conexión a Base de Datos</h3>";

// Leer configuración del archivo generar_pdf_qr.php
$config_file = __DIR__ . '/generar_pdf_qr.php';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);

    // Extraer credenciales (NOTA: Esto es solo para verificación, no es la mejor práctica de seguridad)
    preg_match('/\$servername\s*=\s*["\']([^"\']+)["\']/', $config_content, $host_match);
    preg_match('/\$username\s*=\s*["\']([^"\']+)["\']/', $config_content, $user_match);
    preg_match('/\$database\s*=\s*["\']([^"\']+)["\']/', $config_content, $db_match);

    if (!empty($host_match[1]) && !empty($user_match[1]) && !empty($db_match[1])) {
        echo "<p>Configuración encontrada:</p>";
        echo "<ul>";
        echo "<li>Servidor: " . $host_match[1] . "</li>";
        echo "<li>Usuario: " . $user_match[1] . "</li>";
        echo "<li>Base de datos: " . $db_match[1] . "</li>";
        echo "</ul>";
        echo "<p class='warning'>⚠️ Asegúrate de actualizar las credenciales en generar_pdf_qr.php si es necesario</p>";
    } else {
        echo "<p class='warning'>⚠️ No se pudo extraer la configuración de BD</p>";
    }
} else {
    echo "<p class='error'>✗ Archivo generar_pdf_qr.php no encontrado</p>";
}

echo "</div>";

// ==========================================
// RESUMEN FINAL
// ==========================================
echo "<div class='step' style='border-left-color: #28a745; background: #d4edda;'>";
echo "<h3>✅ Instalación Completada</h3>";

if (is_dir($tcpdf_dir) && is_dir($phpqrcode_dir) && $permisos_ok) {
    echo "<p class='success'>El sistema está listo para usar</p>";
    echo "<h4>Próximos pasos:</h4>";
    echo "<ol>";
    echo "<li>Actualiza las credenciales de BD en <code>generar_pdf_qr.php</code> si es necesario</li>";
    echo "<li>Accede a: <a href='generar_lanyards.html'>generar_lanyards.html</a></li>";
    echo "<li>Genera tu PDF con QR codes</li>";
    echo "<li><strong>IMPORTANTE:</strong> Elimina este archivo (instalar.php) por seguridad</li>";
    echo "</ol>";

    echo "<p style='margin-top: 20px;'>";
    echo "<a href='generar_lanyards.html' style='background:#004aad;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;display:inline-block;'>Ir al Generador de Lanyards →</a>";
    echo "</p>";
} else {
    echo "<p class='warning'>⚠️ La instalación tuvo algunos problemas</p>";
    echo "<p>Revisa los pasos anteriores y corrige los errores antes de continuar.</p>";
}

echo "</div>";

echo "</div></body></html>";
?>
