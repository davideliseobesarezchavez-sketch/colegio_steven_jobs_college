<?php
session_start();
include '../conexion.php';

// Verificar sesión (solo estudiante)
if (!isset($_SESSION['id']) || $_SESSION['rol'] != "estudiante") {
    header("Location: ../login.php");
    exit();
}

$id_estudiante = $_SESSION['id'];

// 📤 Procesar entrega
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_tarea'])) {
    $id_tarea = $_POST['id_tarea'];
    $comentario = $_POST['comentario'] ?? "";

    $archivoNombre = null;
    if (isset($_FILES["archivo"]) && $_FILES["archivo"]["error"] == 0) {
        $carpeta = "uploads_estudiante/";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }
        $archivoNombre = $carpeta . time() . "_" . basename($_FILES["archivo"]["name"]);
        move_uploaded_file($_FILES["archivo"]["tmp_name"], $archivoNombre);
    }

    $sql = "INSERT INTO entregas_tareas (id_tarea, id_estudiante, archivo, comentario) 
            VALUES ('$id_tarea','$id_estudiante','$archivoNombre','$comentario')";
    if ($conn->query($sql)) {
        $mensaje = "✅ Tarea entregada correctamente.";
    } else {
        $error = "❌ Error en BD: " . $conn->error;
    }
}

// 📑 Tareas pendientes
$tareas = $conn->query("
    SELECT t.* 
    FROM tareas t
    INNER JOIN clases c ON t.id_clase = c.id
    WHERE c.nivel = (SELECT nivel FROM usuarios WHERE id = '$id_estudiante')
      AND c.grado = (SELECT grado FROM usuarios WHERE id = '$id_estudiante')
      AND c.seccion = (SELECT seccion FROM usuarios WHERE id = '$id_estudiante')
      AND t.id NOT IN (SELECT id_tarea FROM entregas_tareas WHERE id_estudiante = '$id_estudiante')
    ORDER BY t.fecha_entrega ASC
");

// 📑 Entregas del estudiante
$entregas = $conn->query("
    SELECT e.*, t.titulo 
    FROM entregas_tareas e
    JOIN tareas t ON e.id_tarea = t.id
    WHERE e.id_estudiante = '$id_estudiante'
    ORDER BY e.fecha_entrega DESC
");

$backLink = "index/index_estudiante.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📤 Subir Tareas</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-5xl mx-auto">

    <h2 class="text-3xl text-blue-600 text-center font-bold mb-8">📤 Subir Tareas</h2>

    <!-- Mensajes -->
    <?php if(!empty($mensaje)): ?>
        <p class="text-green-600 font-semibold mb-4"><?= $mensaje ?></p>
    <?php endif; ?>
    <?php if(!empty($error)): ?>
        <p class="text-red-600 font-semibold mb-4"><?= $error ?></p>
    <?php endif; ?>

    <div class="lg:flex lg:gap-8">

        <!-- Tareas Pendientes -->
        <div class="lg:w-1/2">
            <h3 class="text-xl font-semibold mb-4">📌 Tareas Pendientes</h3>
            <?php if ($tareas && $tareas->num_rows > 0): ?>
                <?php while ($t = $tareas->fetch_assoc()): ?>
                    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 mb-6 rounded-2xl shadow-md">
                        <b class="text-lg text-gray-800">📘 <?= htmlspecialchars($t['titulo']); ?></b>
                        <p class="mt-2 text-gray-700"><?= nl2br(htmlspecialchars($t['descripcion'])); ?></p>
                        <p class="mt-1 text-sm text-gray-500">⏰ Entregar antes de: <?= $t['fecha_entrega']; ?></p>

                        <?php if (!empty($t['archivo_docente'])): ?>
                            <p class="mt-1 text-sm">
                                📂 <a href="<?= htmlspecialchars($t['archivo_docente']); ?>" target="_blank" class="text-blue-600 underline">Descargar archivo del docente</a>
                            </p>
                        <?php endif; ?>

                        <input type="hidden" name="id_tarea" value="<?= $t['id']; ?>">

                        <!-- Input de archivo -->
                        <label class="block mt-4 text-gray-700 font-medium">Subir tu tarea</label>
                        <input 
                            type="file" 
                            name="archivo" 
                            required 
                            class="mt-2 w-full p-3 border-2 border-gray-400 rounded-lg bg-white cursor-pointer focus:outline-none focus:border-blue-500"
                        >

                        <!-- Comentario opcional -->
                        <label class="block mt-4 text-gray-700 font-medium">Comentario (opcional)</label>
                        <input 
                            type="text" 
                            name="comentario" 
                            placeholder="Escribe un comentario..." 
                            class="mt-2 w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                        >

                        <button 
                            type="submit" 
                            class="mt-4 w-full p-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                        >
                            📤 Subir Tarea
                        </button>
                    </form>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-600 mb-6">No tienes tareas pendientes 🎉</p>
            <?php endif; ?>
        </div>

        <!-- Entregas Realizadas -->
        <div class="lg:w-1/2 mt-8 lg:mt-0">
            <h3 class="text-xl font-semibold mb-4">✅ Mis Entregas</h3>
            <?php if ($entregas && $entregas->num_rows > 0): ?>
                <?php while($e=$entregas->fetch_assoc()): ?>
                    <div class="bg-white p-4 mb-4 rounded-xl shadow-md">
                        📘 <b><?= htmlspecialchars($e['titulo']); ?></b><br>
                        📝 <?= htmlspecialchars($e['comentario']); ?><br>
                        <?php if(!empty($e['archivo'])): ?>
                            📂 <a href="<?= htmlspecialchars($e['archivo']); ?>" target="_blank" class="text-blue-600 underline">Ver mi entrega</a><br>
                        <?php endif; ?>
                        📅 Fecha de entrega: <?= $e['fecha_entrega']; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-600 mb-6">Aún no has entregado tareas.</p>
            <?php endif; ?>
        </div>

    </div>

    <a href="<?= $backLink ?>" class="inline-block mt-6 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">🔙 Volver al Inicio</a>

</div>

</body>
</html>
