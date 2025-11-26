<?php
/**
 * Script para agregar el campo email a la tabla asistencias
 */

$servername = "localhost";
$username   = "seidev";
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

echo "<h2>🔧 Agregar campo EMAIL a tabla asistencias</h2>";

// Verificar si el campo ya existe
$check = $conn->query("SHOW COLUMNS FROM asistencias LIKE 'email'");

if ($check->num_rows > 0) {
    echo "<p style='color:orange;'>⚠️ El campo 'email' ya existe en la tabla asistencias</p>";
} else {
    // Agregar el campo email después de institucion
    $sql = "ALTER TABLE asistencias ADD COLUMN email VARCHAR(255) NULL AFTER institucion";

    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>✅ Campo 'email' agregado exitosamente a la tabla asistencias</p>";
    } else {
        echo "<p style='color:red;'>❌ Error al agregar campo: " . $conn->error . "</p>";
    }
}

// Mostrar estructura actualizada
echo "<h3>📋 Estructura actual de la tabla asistencias:</h3>";
$result = $conn->query("DESCRIBE asistencias");

if ($result) {
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
    echo "<tr style='background:#004aad;color:white;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th>";
    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
}

$conn->close();
?>
