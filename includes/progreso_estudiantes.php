<?php
session_start();
include '../conexion.php'; // Asegúrate de que esta ruta sea correcta

// Validar sesión (solo maestro)
if (!isset($_SESSION['id']) || $_SESSION['rol'] != "maestro") {
    header("Location: ../login.php");
    exit();
}

$id_maestro = $_SESSION['id'];
$mensaje = null;

// =========================================================================
// Lógica de procesamiento de notas cuando el formulario es enviado
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recoger el nombre de la asignación y las notas
    $nombre_asignacion = htmlspecialchars($_POST['nombre_asignacion'] ?? 'Asignación sin nombre');
    $notas = $_POST['notas'] ?? [];
    
    // Si esta sección se ejecuta con éxito, las notas se han "guardado" (simulación)
    $mensaje = "✅ Notas de '{$nombre_asignacion}' procesadas exitosamente (simulación de guardado).";
    
    // 2. AQUÍ DEBE IR LA LÓGICA REAL DE GUARDADO EN LA BD.
    // Para que el estudiante lo vea en progreso_personal.php, las notas deben
    // insertarse o actualizarse en las tablas 'entregas_tareas' o 'respuestas_examen'.
    /*
    foreach ($notas as $id_estudiante => $nota) {
        $nota_limpia = floatval($nota);
        $id_estudiante_limpio = intval($id_estudiante);
        
        // EJEMPLO CONCEPTUAL: Actualizar la tabla de entregas de tareas
        // $stmt_update = $conn->prepare("UPDATE entregas_tareas SET calificacion = ? WHERE id_estudiante = ? AND id_tarea = ?");
        // $stmt_update->bind_param("ssi", $nota_limpia, $id_estudiante_limpio, $id_tarea_actual);
        // $stmt_update->execute();
    }
    */
}


// 1. Traer nivel, grado y sección del maestro
$stmt = $conn->prepare("SELECT nivel, grado, seccion FROM usuarios WHERE id=?");
if (!$stmt) die("❌ Error al preparar la consulta de maestro: " . $conn->error);
$stmt->bind_param("i", $id_maestro);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) die("❌ Error: Usuario maestro no encontrado.");
$maestro = $res->fetch_assoc();
$nivel   = $maestro['nivel'];
$grado   = $maestro['grado'];
$seccion = $maestro['seccion'];
$stmt->close();

// 2. Consulta para obtener la lista de estudiantes del grupo
$sql = "
SELECT 
    u.id, 
    u.nombre,
    u.apellido
FROM usuarios u
WHERE u.rol='estudiante' AND u.nivel=? AND u.grado=? AND u.seccion=?
ORDER BY u.nombre
";

$stmt2 = $conn->prepare($sql);
if (!$stmt2) die("❌ Error al preparar SQL de estudiantes: " . $conn->error);

$stmt2->bind_param("sss", $nivel, $grado, $seccion);
$stmt2->execute();
$result = $stmt2->get_result();
$stmt2->close();

// Configuración de la URL de retorno: Sube un nivel (..) y va a includes/progreso_estudiantes.php
$url_progreso = "../includes/progreso_estudiantes.php"; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📝 Ingreso de Notas</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    body { font-family: 'Inter', sans-serif; }
</style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-indigo-100 min-h-screen p-6">
<div class="max-w-6xl mx-auto">
    <h1 class="text-4xl font-bold text-indigo-800 text-center mb-6 border-b-2 border-indigo-300 pb-3">📝 Ingreso de Calificaciones</h1>

    <!-- Mensaje de éxito al guardar -->
    <?php if (isset($mensaje)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-6 shadow-md" role="alert">
            <span class="block sm:inline"><?= $mensaje; ?></span>
        </div>
    <?php endif; ?>

    <p class="text-lg text-indigo-900 font-semibold mb-6 text-center bg-indigo-100 p-3 rounded-lg shadow-md">
        Clase Asignada: Nivel <?= htmlspecialchars($nivel) ?>, Grado <?= htmlspecialchars($grado) ?>, Sección <?= htmlspecialchars($seccion) ?>
    </p>

    <?php if ($result && $result->num_rows > 0): ?>
        <form method="POST" action="ingreso_notas.php" class="bg-white rounded-xl shadow-2xl p-6">
            
            <!-- Campo para identificar la asignación que se califica -->
            <div class="mb-6">
                <label for="nombre_asignacion" class="block text-sm font-medium text-gray-700 mb-2">
                    Nombre o ID de la Tarea/Examen (Para tu referencia)
                </label>
                <input type="text" name="nombre_asignacion" id="nombre_asignacion" required
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm p-3 border focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="Ej: Examen Unidad 1"
                       maxlength="100">
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-indigo-200">
                    <thead class="bg-indigo-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-indigo-900 uppercase tracking-wider rounded-tl-xl">
                                Estudiante
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-indigo-900 uppercase tracking-wider rounded-tr-xl">
                                📝 Nota (Ej: 0.00 - 100.00)
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-100">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-indigo-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-lg font-semibold text-gray-800">
                                    👨‍🎓 <?= htmlspecialchars($row['nombre']) . " " . htmlspecialchars($row['apellido']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <!-- CAMPO DE ENTRADA PARA LA NOTA -->
                                    <input type="number" 
                                           name="notas[<?= $row['id']; ?>]" 
                                           class="w-32 text-center rounded-lg border-gray-300 shadow-sm p-2 border focus:border-indigo-500 focus:ring-indigo-500"
                                           placeholder="Ej: 85.50" 
                                           min="0" max="100" step="0.01" 
                                           required>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-8 text-center">
                <button type="submit" class="inline-block px-8 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-xl hover:bg-indigo-700 transition transform hover:scale-105">
                    Guardar Todas las Notas <span class="ml-2">💾</span>
                </button>
            </div>
        </form>

    <?php else: ?>
        <!-- Mensaje de No Resultados -->
        <div class="bg-white rounded-xl p-8 shadow-2xl mt-10">
            <p class="text-center text-indigo-900 font-semibold text-xl">
                ❌ No hay estudiantes registrados en tu clase.
            </p>
            <p class="text-center text-gray-600 mt-2">Verifica la asignación de estudiantes a este curso.</p>
        </div>
    <?php endif; ?>

    <!-- Botón para volver al índice del maestro -->
    <div class="mt-10 text-center">
        <a href="../index/index_maestro.php" class="inline-block px-8 py-3 bg-gray-500 text-white font-bold rounded-lg shadow-lg hover:bg-gray-600 transition">
            <span class="mr-2">🔙</span> Volver al Panel
        </a>
    </div>

</div>
</body>
</html>
