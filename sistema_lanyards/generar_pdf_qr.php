<?php
// ==========================================
// 🔹 GENERADOR DE PDF CON QR CODES PARA LANYARDS
// ==========================================
// Genera PDF con recortes de 6x6 cm para imprimir y pegar en lanyards
// Incluye: QR Code, Nombre completo y País

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
$recorte_alto = 60;   // 6 cm en mm
$columnas = 3;        // 3 recortes por fila
$filas = 4;           // 4 recortes por columna
$recortes_por_pagina = $columnas * $filas; // 12 recortes por página

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
    $qr_path = __DIR__ . '/qrcodes/' . $codigo . '.png';

    // Verificar si existe el QR
    if (!file_exists($qr_path)) {
        // Si no existe el QR, generarlo
        require_once('phpqrcode/qrlib.php');
        QRcode::png($codigo, $qr_path, QR_ECLEVEL_L, 5);
    }

    // ==========================================
    // 🔹 CONTENIDO DEL RECORTE
    // ==========================================

    // 1. LOGO (si existe) - Pequeño arriba
    $logo_path = __DIR__ . '/logo_evento_small.png';
    if (file_exists($logo_path)) {
        $pdf->Image($logo_path, $x + ($recorte_ancho/2) - 5, $y + 3, 10, 0, 'PNG');
        $y_inicio_contenido = $y + 13;
    } else {
        $y_inicio_contenido = $y + 5;
    }

    // 2. QR CODE (centrado)
    $qr_size = 32; // 3.2 cm
    $qr_x = $x + ($recorte_ancho - $qr_size) / 2;
    $qr_y = $y_inicio_contenido;
    $pdf->Image($qr_path, $qr_x, $qr_y, $qr_size, $qr_size, 'PNG');

    // 3. NOMBRE (debajo del QR)
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetXY($x + 2, $qr_y + $qr_size + 2);
    $pdf->Cell($recorte_ancho - 4, 5, $nombre_completo, 0, 1, 'C', false);

    // 4. PAÍS (debajo del nombre)
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetXY($x + 2, $qr_y + $qr_size + 7);
    $pdf->Cell($recorte_ancho - 4, 4, $pais, 0, 1, 'C', false);

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
