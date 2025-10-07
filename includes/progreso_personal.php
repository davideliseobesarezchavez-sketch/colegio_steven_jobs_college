<?php
session_start();
include '../conexion.php';

// Verificar sesión
if (!isset($_SESSION['id']) || $_SESSION['rol'] != "estudiante") {
    header("Location: ../login.php");
    exit();
}

$id_estudiante = $_SESSION['id'];

// ✅ Consulta del progreso por curso con JOIN a la tabla cursos
$sql = "SELECT c.nombre AS curso_nombre, p.tareas_entregadas, p.total_tareas,
               CASE WHEN p.total_tareas > 0 THEN (p.tareas_entregadas/p.total_tareas)*100 ELSE 0 END AS porcentaje
        FROM progreso_estudiante p
        JOIN cursos c ON p.id_curso = c.id
        WHERE p.id_estudiante = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("❌ Error en consulta: " . $conn->error);
}

$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$result = $stmt->get_result();

$progresos = [];
while ($row = $result->fetch_assoc()) {
    $progresos[] = $row;
}

$backLink = "../index/index_estudiante.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📊 Progreso por Curso</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6 flex flex-col items-center">

<h2 class="text-3xl font-bold text-blue-600 mb-8 text-center">📊 Progreso por Curso</h2>

<div class="w-full max-w-3xl grid gap-6">
    <?php if (!empty($progresos)): ?>
        <?php foreach ($progresos as $p): ?>
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4"><?= htmlspecialchars($p['curso_nombre']); ?></h3>
                
                <p class="text-gray-700 mb-1">✅ Tareas entregadas: <span class="font-bold"><?= $p['tareas_entregadas']; ?></span></p>
                <p class="text-gray-700 mb-1">📌 Total de tareas: <span class="font-bold"><?= $p['total_tareas']; ?></span></p>
                <p class="text-gray-700 mb-3">📈 Avance: <span class="font-bold"><?= round($p['porcentaje'], 2); ?>%</span></p>

                <!-- Barra de progreso -->
                <div class="w-full bg-gray-300 rounded-full h-4">
                    <div class="bg-blue-600 h-4 rounded-full" style="width: <?= $p['porcentaje']; ?>%;"></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-yellow-100 text-yellow-800 p-6 rounded-xl text-center">
            No se encontraron registros de progreso.
        </div>
    <?php endif; ?>
</div>

<!-- Botón de volver -->
<a href="<?= $backLink ?>" class="mt-8 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold shadow-md">
    🔙 Volver al Inicio
</a>

</body>
</html>
