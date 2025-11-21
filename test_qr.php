<?php
require 'phpqrcode/qrlib.php';
QRcode::png('Hola Patricio!', 'qr_prueba.png', QR_ECLEVEL_L, 5);
echo "✅ QR generado: qr_prueba.png";
?>
