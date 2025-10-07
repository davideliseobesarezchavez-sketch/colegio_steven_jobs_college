<?php
session_start();
include '../conexion.php';

// 🔒 Verificar sesión
$rolesPermitidos = ['estudiante','maestro'];
if (!isset($_SESSION['id']) || !in_array($_SESSION['rol'], ['estudiante','maestro'])) {
    header("Location: ../login.php");
    exit();
}

$id_usuario = intval($_SESSION['id']);
$rol_usuario = $_SESSION['rol'];

// ----------------------------
// ESTUDIANTE
// ----------------------------
if ($rol_usuario === 'estudiante') {

    // 📑 Obtener tareas pendientes
    $sql_tareas = "
        SELECT t.*, c.nombre AS clase_nombre
        FROM tareas t
        JOIN clases c ON t.id_clase = c.id
        JOIN matriculas m ON m.nivel = c.nivel AND m.grado = c.grado AND m.seccion = c.seccion
        WHERE m.usuario_id = ?
          AND t.id NOT IN (SELECT id_tarea FROM entregas_tareas WHERE id_estudiante = ?)
        ORDER BY t.fecha_entrega ASC
    ";
    $stmt = $conn->prepare($sql_tareas);
    $stmt->bind_param("ii", $id_usuario, $id_usuario);
    $stmt->execute();
    $tareas = $stmt->get_result();
    $stmt->close();

    // 📑 Obtener entregas realizadas
    $sql_entregas = "
        SELECT e.*, t.titulo 
        FROM entregas_tareas e
        JOIN tareas t ON e.id_tarea = t.id
        WHERE e.id_estudiante = ?
        ORDER BY e.fecha_entrega DESC
    ";
    $stmt = $conn->prepare($sql_entregas);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $entregas = $stmt->get_result();
    $stmt->close();

}

// ----------------------------
// DOCENTE / MAESTRO
// ----------------------------
if (in_array($rol_usuario, ['docente','maestro'])) {

    // 📑 Obtener tareas creadas por el docente
    $sql_tareas = "
        SELECT t.*, c.nombre AS clase_nombre
        FROM tareas t
        JOIN clases c ON t.id_clase = c.id
        WHERE t.id_maestro = ?
        ORDER BY t.fecha_entrega ASC
    ";
    $stmt = $conn->prepare($sql_tareas);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $tareas = $stmt->get_result();
    $stmt->close();

    // 📑 Obtener entregas realizadas de todos los estudiantes
    $sql_entregas = "
        SELECT e.*, t.titulo, u.nombre AS estudiante_nombre
        FROM entregas_tareas e
        JOIN tareas t ON e.id_tarea = t.id
        JOIN usuarios u ON e.id_estudiante = u.id
        WHERE e.id_maestro = ?
        ORDER BY e.fecha_entrega DESC
    ";
    $stmt = $conn->prepare($sql_entregas);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $entregas = $stmt->get_result();
    $stmt->close();

}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📌 Tareas</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
<div class="max-w-6xl mx-auto">
    <h1 class="text-3xl font-bold text-blue-700 mb-6 text-center">📌 Tareas</h1>

    <?php if ($rol_usuario === 'estudiante'): ?>
        <!-- ESTUDIANTE: Tareas Pendientes -->
        <h2 class="text-xl font-semibold text-gray-700 mb-3">📌 Tareas Pendientes</h2>
        <?php if ($tareas && $tareas->num_rows > 0): ?>
            <div class="grid md:grid-cols-2 gap-6">
            <?php while ($t = $tareas->fetch_assoc()): ?>
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200">
                    <p class="text-lg font-bold text-blue-600">📘 <?= htmlspecialchars($t['titulo']); ?></p>
                    <p class="text-gray-600"><?= nl2br(htmlspecialchars($t['descripcion'])); ?></p>
                    <p class="text-sm text-gray-500">🏫 Clase: <?= htmlspecialchars($t['clase_nombre']); ?></p>
                    <?php if (!empty($t['archivo'])): ?>
                        <p class="mt-2">📂 
                            <a href="<?= htmlspecialchars($t['archivo']); ?>" target="_blank" class="text-green-600 hover:underline">📥 Descargar archivo</a>
                        </p>
                    <?php endif; ?>
                    <p class="text-sm text-red-500 mt-2">⏰ Entregar antes de: <?= htmlspecialchars($t['fecha_entrega']); ?></p>

                    <!-- Subir entrega -->
                    <form method="post" enctype="multipart/form-data" class="mt-3" action="subir_entrega.php">
                        <input type="hidden" name="id_tarea" value="<?= $t['id']; ?>">
                        <input type="file" name="archivo" required class="block w-full mb-2 text-sm">
                        <textarea name="comentario" placeholder="Comentario opcional" class="w-full border rounded p-2 text-sm mb-2"></textarea>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">📤 Entregar tarea</button>
                    </form>
                </div>
            <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">🎉 No tienes tareas pendientes.</p>
        <?php endif; ?>
    <?php endif; ?>

    <!-- ENTREGAS REALIZADAS -->
    <h2 class="text-xl font-semibold text-gray-700 mt-8 mb-3">✅ Entregas Realizadas</h2>
    <?php if ($entregas && $entregas->num_rows > 0): ?>
        <div class="space-y-4">
        <?php while ($e = $entregas->fetch_assoc()): ?>
            <div class="bg-white p-4 rounded-xl shadow border border-gray-200">
                <p class="text-lg font-bold text-green-600">📘 <?= htmlspecialchars($e['titulo']); ?></p>
                <?php if ($tipo_usuario === 'docente' || $tipo_usuario === 'maestro'): ?>
                    <p class="text-sm text-gray-500">👤 Estudiante: <?= htmlspecialchars($e['estudiante_nombre']); ?></p>
                <?php endif; ?>
                <?php if (!empty($e['archivo'])): ?>
                    <p class="text-gray-700">📂 
                        <a href="<?= htmlspecialchars($e['archivo']); ?>" target="_blank" class="text-blue-600 underline">Ver / Descargar</a>
                    </p>
                <?php endif; ?>
                <?php if (!empty($e['comentario'])): ?>
                    <p class="text-gray-600 mt-1">💬 <?= nl2br(htmlspecialchars($e['comentario'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($e['calificacion'])): ?>
                    <p class="text-sm text-yellow-600 mt-1">🏆 Calificación: <?= htmlspecialchars($e['calificacion']); ?></p>
                <?php endif; ?>
                <p class="text-sm text-gray-500 mt-1">📅 Fecha: <?= $e['fecha_entrega']; ?></p>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500 text-center italic">📭 No hay entregas registradas.</p>
    <?php endif; ?>

</div>
</body>
</html>
