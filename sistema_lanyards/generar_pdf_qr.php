<?php
// ==========================================
// 🔹 GENERADOR DE PDF CON QR CODES PARA LANYARDS
// ==========================================
// Genera PDF con recortes de 2x6 cm para imprimir y pegar en lanyards
// Formato horizontal: QR a la izquierda, Nombre a la derecha

require_once('tcpdf/tcpdf.php');

// ==========================================
// 🔹 CONFIGURACIÓN
// ==========================================
$servername = "localhost";
$username   = "seidev";
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = "registro_evento";

// Conectar a la base de datos
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ==========================================
// 🔹 OBTENER TODOS LOS REGISTROS
// ==========================================
$sql = "SELECT nombre, apellido, pais, codigo_registro FROM registros ORDER BY apellido, nombre";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    die("No hay registros en la base de datos");
}

$registros = [];
while ($row = $result->fetch_assoc()) {
    $registros[] = $row;
}
$conn->close();

// ==========================================
// 🔹 CONFIGURAR PDF
// ==========================================
class MYPDF extends TCPDF {
    // Eliminar header y footer predeterminados
    public function Header() {}
    public function Footer() {}
}

// Crear PDF
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Configuración del documento
$pdf->SetCreator('Nodo Bioceánico Central');
$pdf->SetAuthor('Nodo Bioceánico Central');
$pdf->SetTitle('QR Codes - Lanyards');
$pdf->SetSubject('QR Codes para Lanyards del Evento');

// Configuración de página
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15); // Márgenes de 1.5 cm
$pdf->SetAutoPageBreak(false, 0);

// ==========================================
// 🔹 CONFIGURACIÓN DE RECORTES
// ==========================================
$recorte_ancho = 60;  // 6 cm en mm
$recorte_alto = 20;   // 2 cm en mm
$columnas = 3;        // 3 recortes por fila
$filas = 13;          // 13 recortes por columna (para optimizar hoja A4)
$recortes_por_pagina = $columnas * $filas; // 39 recortes por página

$margen_izquierdo = 15; // mm
$margen_superior = 15;  // mm
$espacio_entre = 0;     // Sin espacio entre recortes para facilitar el corte

// ==========================================
// 🔹 GENERAR RECORTES
// ==========================================
$contador = 0;
$total_registros = count($registros);

foreach ($registros as $registro) {
    // Nueva página cada 12 recortes
    if ($contador % $recortes_por_pagina == 0) {
        $pdf->AddPage();
    }

    // Calcular posición del recorte en la cuadrícula
    $posicion_en_pagina = $contador % $recortes_por_pagina;
    $columna = $posicion_en_pagina % $columnas;
    $fila = floor($posicion_en_pagina / $columnas);

    // Calcular coordenadas X e Y
    $x = $margen_izquierdo + ($columna * ($recorte_ancho + $espacio_entre));
    $y = $margen_superior + ($fila * ($recorte_alto + $espacio_entre));

    // Dibujar borde del recorte (línea punteada para facilitar el corte)
    $pdf->SetLineStyle(array('width' => 0.2, 'dash' => '2,2', 'color' => array(200, 200, 200)));
    $pdf->Rect($x, $y, $recorte_ancho, $recorte_alto);

    // Datos del registro
    $nombre_completo = trim($registro['nombre'] . ' ' . $registro['apellido']);
    $pais = $registro['pais'];
    $codigo = $registro['codigo_registro'];

    // Ruta al QR (en la carpeta padre: registro/qrcodes/)
    $qrcodes_dir = dirname(__DIR__) . '/qrcodes';
    $qr_path = $qrcodes_dir . '/' . $codigo . '.png';

    // Buscar el QR con diferentes variaciones de nombre
    if (!file_exists($qr_path)) {
        // Intentar con variaciones del código
        $posibles_nombres = [
            $codigo . '.png',
            'REG' . $codigo . '.png',
            strtoupper($codigo) . '.png',
            strtolower($codigo) . '.png'
        ];

        $encontrado = false;
        foreach ($posibles_nombres as $nombre_variante) {
            $path_variante = $qrcodes_dir . '/' . $nombre_variante;
            if (file_exists($path_variante)) {
                $qr_path = $path_variante;
                $encontrado = true;
                break;
            }
        }

        // Si aún no existe, generarlo
        if (!$encontrado) {
            if (!is_dir($qrcodes_dir)) {
                mkdir($qrcodes_dir, 0755, true);
            }
            require_once('phpqrcode/qrlib.php');
            QRcode::png($codigo, $qr_path, QR_ECLEVEL_L, 5);
        }
    }

    // ==========================================
    // 🔹 CONTENIDO DEL RECORTE (HORIZONTAL)
    // ==========================================
    // Layout: QR a la izquierda, Nombre a la derecha

    // 1. QR CODE (lado izquierdo)
    $qr_size = 16; // 1.6 cm (más pequeño para el formato horizontal)
    $qr_x = $x + 2; // 2mm de margen izquierdo
    $qr_y = $y + ($recorte_alto - $qr_size) / 2; // Centrado verticalmente
    $pdf->Image($qr_path, $qr_x, $qr_y, $qr_size, $qr_size, 'PNG');

    // 2. NOMBRE (lado derecho del QR)
    $nombre_x = $qr_x + $qr_size + 2; // 2mm de separación del QR
    $ancho_disponible = $recorte_ancho - $qr_size - 6; // Espacio disponible para el texto

    // Calcular el tamaño de fuente apropiado según la longitud del nombre
    $pdf->SetFont('helvetica', 'B', 9);
    $ancho_texto = $pdf->GetStringWidth($nombre_completo);

    if ($ancho_texto > $ancho_disponible) {
        // Si el nombre es muy largo, reducir tamaño de fuente
        $font_size = 9;
        while ($ancho_texto > $ancho_disponible && $font_size > 5) {
            $font_size -= 0.5;
            $pdf->SetFont('helvetica', 'B', $font_size);
            $ancho_texto = $pdf->GetStringWidth($nombre_completo);
        }
    }

    // Posicionar el nombre centrado verticalmente con el QR
    $nombre_y = $y + ($recorte_alto / 2) - 2;
    $pdf->SetXY($nombre_x, $nombre_y);
    $pdf->Cell($ancho_disponible, $recorte_alto, $nombre_completo, 0, 0, 'L', false);

    $contador++;
}

// ==========================================
// 🔹 GENERAR ARCHIVO PDF
// ==========================================
$fecha = date('Y-m-d_H-i-s');
$filename = "QR_Lanyards_" . $fecha . ".pdf";

// Salida del PDF
$pdf->Output(__DIR__ . '/' . $filename, 'F'); // Guardar en disco
$pdf->Output($filename, 'D'); // Descargar

echo "PDF generado exitosamente: $filename<br>";
echo "Total de registros procesados: $total_registros<br>";
echo "Total de páginas: " . ceil($total_registros / $recortes_por_pagina);
?>
