<?php
session_start();
include '../conexion.php';

$id_usuario   = $_SESSION['id'] ?? 0;
$tipo_usuario = $_SESSION['rol'] ?? '';

if ($tipo_usuario !== 'maestro') {
    die("Acceso denegado.");
}

/* ======================
   📌 Consultar clases del maestro
====================== */
$res_clases = $conn->query("SELECT id, nombre FROM clases WHERE id_maestro = $id_usuario");
$clases_disponibles = ($res_clases && $res_clases->num_rows > 0);

/* ======================
   📌 Subir tarea
====================== */
if (isset($_POST['subir'])) {
    $id_clase = intval($_POST['id_clase'] ?? 0);

    if ($id_clase <= 0) {
        die("⚠️ Debes seleccionar una clase válida.");
    }

    $archivo = $_FILES['archivo']['name'];
    $tmp_name = $_FILES['archivo']['tmp_name'];
    $ruta = "archivos/" . time() . "_" . basename($archivo);

    if (!is_dir("archivos")) mkdir("archivos", 0777, true);

    if (move_uploaded_file($tmp_name, $ruta)) {
        $fecha_creacion = date("Y-m-d H:i:s");
        $titulo = $_POST['titulo'] ?? "Tarea sin título";
        $descripcion = $_POST['descripcion'] ?? "";
        $fecha_entrega = $_POST['fecha_entrega'] ?? date("Y-m-d", strtotime("+7 days"));

        $stmt = $conn->prepare("INSERT INTO tareas 
            (id_maestro, id_clase, titulo, descripcion, fecha_entrega, archivo, fecha_creacion) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssss", $id_usuario, $id_clase, $titulo, $descripcion, $fecha_entrega, $ruta, $fecha_creacion);

        if (!$stmt->execute()) {
            die("❌ Error al guardar tarea: " . $stmt->error);
        }
        $stmt->close();
        header("Location: ../gestionar_tareas.php");
        exit();
    }
}

/* ======================
   📌 Eliminar tarea
====================== */
if (isset($_GET['eliminar'])) {
    $id_tarea = intval($_GET['eliminar']);
    $result = $conn->query("SELECT archivo FROM tareas WHERE id=$id_tarea AND id_maestro=$id_usuario");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (file_exists($row['archivo'])) unlink($row['archivo']);
        $conn->query("DELETE FROM tareas WHERE id=$id_tarea");
    }
    header("Location: gestionar_tareas.php");
    exit();
}

/* ======================
   📌 Consultar tareas del maestro
====================== */
$result = $conn->query("SELECT t.*, c.nombre AS clase_nombre 
                        FROM tareas t
                        JOIN clases c ON t.id_clase = c.id
                        WHERE t.id_maestro=$id_usuario 
                        ORDER BY t.fecha_creacion DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📑 Gestionar Tareas (Maestro)</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-100 min-h-screen p-6">
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-10">
        <h1 class="text-4xl font-extrabold text-blue-700 drop-shadow">📑 Gestionar Tareas</h1>
        <a href="index_maestro.php" 
           class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg shadow transition">
            ⬅️ Volver al Inicio
        </a>
    </div>

    <!-- Subir tarea -->
    <form method="post" enctype="multipart/form-data" 
          class="mb-10 bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">➕ Subir nueva tarea</h2>

        <?php if (!$clases_disponibles): ?>
            <p class="text-red-600 font-medium">
                ⚠️ No tienes clases registradas. Crea una clase antes de subir tareas.
            </p>
        <?php else: ?>
            <!-- Seleccionar clase -->
            <select name="id_clase" required 
                    class="border p-3 rounded-lg w-full mb-3 focus:ring-2 focus:ring-blue-400">
                <option value="">📌 Selecciona una clase</option>
                <?php while($c = $res_clases->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endwhile; ?>
            </select>

            <input type="text" name="titulo" placeholder="Título de la tarea" required 
                   class="border p-3 rounded-lg w-full mb-3 focus:ring-2 focus:ring-blue-400">
            <textarea name="descripcion" placeholder="Descripción de la tarea" 
                      class="border p-3 rounded-lg w-full mb-3 focus:ring-2 focus:ring-blue-400"></textarea>
            <input type="date" name="fecha_entrega" required 
                   class="border p-3 rounded-lg w-full mb-3 focus:ring-2 focus:ring-blue-400">
            <input type="file" name="archivo" required 
                   class="border p-2 rounded-lg w-full mb-4">
            <button type="submit" name="subir" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded-lg transition">
                📤 Subir Tarea
            </button>
        <?php endif; ?>
    </form>

    <!-- Listado de tareas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200 hover:shadow-lg transition">
                <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= htmlspecialchars($row['titulo']) ?></h3>
                <p class="text-gray-600 mb-2"><?= htmlspecialchars($row['descripcion']) ?></p>
                <p class="text-sm text-gray-500">🏫 <b>Clase:</b> <?= htmlspecialchars($row['clase_nombre']) ?></p>
                <p class="text-sm text-gray-500">📅 <b>Entrega:</b> <?= htmlspecialchars($row['fecha_entrega']) ?></p>
                <p class="text-sm text-gray-500">⏰ <b>Creación:</b> <?= htmlspecialchars($row['fecha_creacion']) ?></p>

                <div class="mt-4 flex gap-3">
                    <a href="<?= htmlspecialchars($row['archivo']) ?>" target="_blank" download
                       class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-sm transition">📥 Descargar</a>
                    <a href="?eliminar=<?= $row['id'] ?>" 
                       onclick="return confirm('¿Eliminar tarea?')" 
                       class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-sm transition">🗑️ Eliminar</a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class='text-gray-500 text-center col-span-2 italic'>📭 No hay tareas para mostrar.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
