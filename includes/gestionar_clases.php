<?php
session_start();
include '../conexion.php';

if(!isset($_SESSION['id']) || $_SESSION['rol']!="maestro"){ 
    header("Location: ../login.php"); 
    exit(); 
}

$id_maestro = $_SESSION['id'];

// Traer datos del maestro (nivel, grado, sección)
$res = $conn->query("SELECT nivel, grado, seccion FROM usuarios WHERE id='$id_maestro'");
$maestro = $res->fetch_assoc();
$nivel   = $maestro['nivel'];
$grado   = $maestro['grado'];
$seccion = $maestro['seccion'];

// Crear clase
if (isset($_POST['crear'])) {
    $nombre = $_POST['nombre'];
    $stmt = $conn->prepare("INSERT INTO clases (nombre, id_maestro, nivel, grado, seccion) VALUES (?,?,?,?,?)");
    $stmt->bind_param("sisss", $nombre, $id_maestro, $nivel, $grado, $seccion);
    $stmt->execute();
    $stmt->close();
    header("Location: gestionar_clases.php");
    exit();
}

// Consultar clases de este maestro SOLO de su nivel, grado y sección
$clases = $conn->query("SELECT * FROM clases 
    WHERE id_maestro='$id_maestro' 
    AND nivel='$nivel' 
    AND grado='$grado' 
    AND seccion='$seccion'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📘 Gestionar Clases</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-blue-600 text-center mb-6">📘 Gestionar Clases</h1>

    <!-- Formulario para crear clase -->
    <form method="post" class="bg-white p-4 rounded shadow mb-6">
        <h2 class="text-xl font-semibold mb-3">➕ Crear Clase</h2>
        <input type="text" name="nombre" placeholder="Nombre de la clase" required 
               class="border p-2 rounded w-full mb-3">
        <p class="text-sm text-gray-600 mb-3">
            📌 Nivel: <b><?= $nivel ?></b> | Grado: <b><?= $grado ?></b> | Sección: <b><?= $seccion ?></b>
        </p>
        <button type="submit" name="crear" class="bg-blue-600 text-white px-4 py-2 rounded">
            Crear Clase
        </button>
    </form>

    <!-- Lista de clases -->
    <div class="grid gap-4">
        <?php if ($clases->num_rows > 0): ?>
            <?php while($c = $clases->fetch_assoc()): ?>
            <div class="bg-white p-4 rounded shadow">
                <h3 class="text-lg font-semibold"><?= htmlspecialchars($c['nombre']); ?></h3>
                <p class="text-gray-500 text-sm">
                    📌 <?= $c['nivel']; ?> - <?= $c['grado']; ?> - <?= $c['seccion']; ?>
                </p>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500 italic text-center">❌ Aún no tienes clases creadas en tu grupo.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
