<?php
// Incluir tu conexión
include 'conexion.php';

// Consulta a la tabla usuarios
$sql = "SELECT id, nombre, apellido, email, rol FROM usuarios LIMIT 10";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<h2>✅ Usuarios encontrados en la base de datos:</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Rol</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["nombre"] . "</td>";
        echo "<td>" . $row["apellido"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "<td>" . $row["rol"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "⚠️ No se encontraron usuarios en la tabla.";
}

// Cerrar conexión
$conn->close();
?>
