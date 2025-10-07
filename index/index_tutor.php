<?php
session_start();
include '../conexion.php';

// --- Verificación de Conexión ---
if (!isset($conn)) {
    die("Error Fatal: No se pudo conectar a la base de datos. Verifique '../conexion.php'.");
}

// Solo tutores
if (!isset($_SESSION['id']) || $_SESSION['rol'] != 'tutor') {
    header("Location: ../login.php");
    exit();
}

$id_Tutor = $_SESSION['id'];
$nombre_completo = "Tutor no encontrado";
$nivel_tutor = null;
$grado_tutor = null;
$seccion_tutor = null;
$has_class_info = false;
$estudiantes = false;

// 1. OBTENER DATOS COMPLETOS DEL TUTOR (USANDO CONSULTA PREPARADA PARA SEGURIDAD)
$stmt = $conn->prepare("SELECT nombre, apellido, nivel, grado, seccion FROM usuarios WHERE id = ? AND rol = 'tutor'");
if ($stmt === false) {
    die("Error al preparar consulta de tutor: " . $conn->error);
}
$stmt->bind_param("i", $id_Tutor);
$stmt->execute();
$result_tutor = $stmt->get_result();
$tutor = $result_tutor->fetch_assoc();
$stmt->close();

if ($tutor) {
    $nombre_completo = htmlspecialchars($tutor['nombre'] . ' ' . ($tutor['apellido'] ?? ''));
    $nivel_tutor = $tutor['nivel'] ?? null;
    $grado_tutor = $tutor['grado'] ?? null;
    $seccion_tutor = $tutor['seccion'] ?? null;
    $has_class_info = $nivel_tutor && $grado_tutor && $seccion_tutor;
}


// 2. Estudiantes a cargo (filtrados por Nivel, Grado, Sección del Tutor)
if ($has_class_info) {
    $stmt = $conn->prepare("
        SELECT u.nombre, u.apellido, u.nivel, u.grado, u.seccion 
        FROM usuarios u 
        WHERE u.rol = 'estudiante' 
        AND u.nivel = ? 
        AND u.grado = ? 
        AND u.seccion = ?
        ORDER BY u.nombre ASC
    ");
    if ($stmt === false) {
        die("Error al preparar consulta de estudiantes: " . $conn->error);
    }
    $stmt->bind_param("sss", $nivel_tutor, $grado_tutor, $seccion_tutor);
    $stmt->execute();
    $estudiantes = $stmt->get_result();
    $stmt->close();
}


// 3. Guardar nueva charla
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    if (!empty($mensaje)) {
        $mensaje_seguro = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
        
        // FIX CLAVE FORÁNEA: Se incluye id_estudiante con valor NULL explícito.
        // Esto asume que id_estudiante en la BD acepta NULL.
        $stmt = $conn->prepare("INSERT INTO charlas (id_tutor, id_estudiante, mensaje, fecha) VALUES (?, NULL, ?, NOW())");
        
        if ($stmt === false) {
            die("Error al preparar consulta de inserción de charla: " . $conn->error);
        }
        
        // El nuevo bind_param es 'is' porque el NULL se incluyó directamente en el SQL
        $stmt->bind_param("is", $id_Tutor, $mensaje_seguro);
        
        if (!$stmt->execute()) {
            // Si el error persiste, significa que id_estudiante NO acepta NULL en tu DB.
            die("Error al ejecutar la inserción en charlas (Revisar si id_estudiante permite NULL): " . $stmt->error);
        }
        $stmt->close();

        // Redirigir usando la ruta relativa correcta
        header("Location: index_tutor.php");
        exit();
    }
}

// 4. Listar charlas existentes (USANDO CONSULTA PREPARADA PARA SEGURIDAD)
$charlas = false;
$charlas_exist = $conn->query("SHOW TABLES LIKE 'charlas'");

if ($charlas_exist && $charlas_exist->num_rows > 0) {
    // Se usa consulta preparada para listar las charlas
    $stmt = $conn->prepare("SELECT mensaje, fecha FROM charlas WHERE id_tutor = ? ORDER BY fecha DESC");
    if ($stmt === false) {
        die("Error al preparar consulta de listar charlas: " . $conn->error);
    }
    $stmt->bind_param("i", $id_Tutor);
    $stmt->execute();
    $charlas = $stmt->get_result();
    $stmt->close();
}
// ----------------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>👨‍👩‍👦 Panel Tutor</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Carga de Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        /* Estilo para los mensajes del tutor */
        .chat-bubble-tutor {
            background-color: #3b82f6; /* Blue-500 */
            color: white;
            border-radius: 12px 12px 0 12px;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Encabezado Fijo y Responsive -->
    <header class="bg-indigo-700 shadow-xl sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
              
        <!-- Título del Panel -->
        <div class="flex items-center space-x-3">
            <div class="text-2xl font-extrabold text-white">
                <i class="fas fa-hand-holding-heart mr-2 text-yellow-300"></i> Panel de Tutoría
            </div>
        </div>

        <!-- Información del Usuario y Logout -->
        <div class="flex items-center space-x-4"><div class="text-sm font-medium text-white hidden sm:block">
            Tutor(a): <span class="font-bold text-yellow-300"><?= $nombre_completo ?></span>
        </div>
        <a href="../logout.php" class="flex items-center space-x-2 bg-red-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-red-600 transition duration-200 shadow-md text-sm">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span class="hidden sm:inline">Cerrar sesión</span>
        </a>
    </div>
</div>
</header>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto p-4 sm:p-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Columna 1: Estudiantes a Cargo -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl p-6 border-t-4 border-indigo-500 h-fit">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-users-class mr-2 text-indigo-500"></i> Estudiantes a Cargo
                </h2>

                <?php if (!$has_class_info): ?>
                    <div class="p-4 bg-red-100 border border-red-300 rounded-lg text-red-800">
                        <i class="fas fa-exclamation-circle mr-2"></i> Error de Configuración: Tu perfil de Tutor no tiene asignado **Nivel, Grado o Sección**.
                    </div>
                <?php elseif ($estudiantes && $estudiantes->num_rows > 0): ?>
                    <div class="mb-4 p-3 bg-indigo-50 rounded-lg text-indigo-700 font-semibold text-sm">
                        Mostrando estudiantes de: <?= htmlspecialchars($nivel_tutor) ?> <?= htmlspecialchars($grado_tutor) ?>-<?= htmlspecialchars($seccion_tutor) ?>
                    </div>
                    <div class="table-wrapper">
                        <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
                            <thead class="bg-indigo-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Nivel</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-indigo-700 uppercase tracking-wider">Clase</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <?php while($e=$estudiantes->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($e['nombre'] . ' ' . ($e['apellido'] ?? '')) ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars($e['nivel'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <?= htmlspecialchars(($e['grado'] ?? '') . '-' . ($e['seccion'] ?? '')) ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-4 bg-yellow-100 border border-yellow-300 rounded-lg text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i> No se encontraron estudiantes vinculados a tu clase asignada.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Columna 2: Mensajería (Chat) -->
            <div class="lg:col-span-1 bg-white rounded-2xl shadow-xl p-6 border-t-4 border-green-500">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-comments mr-2 text-green-500"></i> Comunicados
                </h2>

                <!-- Formulario de Envío de Mensajes -->
                <div class="mb-6 border p-4 rounded-xl bg-green-50">
                    <form method="post">
                        <p class="text-sm font-semibold text-green-700 mb-2">Enviar un mensaje a sus estudiantes:</p>
                        <textarea name="mensaje" rows="3" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 transition duration-150 resize-none" placeholder="Escribe un comunicado, aviso de reunión o nota importante..."></textarea><br>
                        <button type="submit" class="w-full mt-3 bg-green-600 text-white py-2 rounded-lg font-semibold hover:bg-green-700 transition duration-200 shadow-lg shadow-green-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-paper-plane"></i> <span>Enviar Mensaje</span>
                        </button>
                    </form>
                </div>

                <!-- Historial de Charlas -->
                <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Historial de Charlas</h3>
                <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                    <?php if ($charlas && $charlas->num_rows > 0): ?>
                        <?php while($c=$charlas->fetch_assoc()): ?>
                            <div class="flex justify-end">
                                <div class="max-w-xs sm:max-w-md p-3 chat-bubble-tutor shadow-md">
                                    <p class="text-sm break-words"><?= htmlspecialchars($c['mensaje']) ?></p>
                                    <small class="text-right block text-xs mt-1 opacity-80">
                                        <?= date('d/M H:i', strtotime($c['fecha'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-4 bg-gray-100 border border-gray-300 rounded-lg text-gray-600 text-sm italic text-center">
                            No hay mensajes enviados aún.
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </main>
    
    <!-- Pie de página -->
    <footer class="bg-gray-800 text-white text-center py-4 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm">
                &copy; 2025 Colegio Steve Jobs. Plataforma de Tutoría.
            </p>
        </div>
    </footer>

</body>
</html>
<?php $conn->close(); ?>
