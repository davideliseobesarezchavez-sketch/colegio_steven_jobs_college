<?php
include '../conexion.php';
session_start();

// Validación de sesión
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$userId   = $_SESSION['id'];
$userRol  = $_SESSION['rol'];
$userName = $_SESSION['nombre'];

$asistencias = [];
$titulo  = "Reporte de Asistencia";
$mensaje = "";

try {
    if ($userRol === 'admin') {
        $sql = "SELECT 
                    a.id, 
                    u.nombre AS estudiante_nombre, 
                    a.fecha, 
                    a.estado
                FROM asistencias a
                JOIN usuarios u ON a.id_estudiante = u.id  
                ORDER BY a.fecha DESC";
        $stmt = $conn->prepare($sql);

    } elseif ($userRol === 'maestro') {
        $sql = "SELECT 
                    a.id, 
                    u.nombre AS estudiante_nombre, 
                    a.fecha, 
                    a.estado
                FROM asistencias a
                JOIN usuarios u ON a.id_estudiante = u.id
                JOIN clases c ON a.clase_id = c.id
                WHERE c.maestro_id = ?
                ORDER BY a.fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $titulo = "Reporte de Asistencia - Maestro";

    } elseif ($userRol === 'estudiante') {
        $sql = "SELECT 
                    a.id, 
                    u.nombre AS estudiante_nombre, 
                    a.fecha, 
                    a.estado
                FROM asistencias a
                JOIN usuarios u ON a.id_estudiante = u.id
                WHERE a.id_estudiante = ? 
                ORDER BY a.fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $titulo = "Mi Reporte de Asistencia";

    } elseif ($userRol === 'tutor') {
        $sql = "SELECT 
                    a.id, 
                    u.nombre AS estudiante_nombre, 
                    a.fecha, 
                    a.estado
                FROM asistencias a
                JOIN usuarios u ON a.id_estudiante = u.id
                WHERE u.tutor_id = ?
                ORDER BY a.fecha DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $titulo = "Reporte de Asistencia - Tutor";

    } else {
        $mensaje = "Rol de usuario no válido.";
        $stmt = null;
    }

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $asistencias[] = $row;
        }
        $stmt->close();
    }

} catch (mysqli_sql_exception $e) {
    $mensaje = "Error en la consulta: " . $e->getMessage();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6 font-sans">

<div class="max-w-6xl mx-auto bg-white p-8 rounded-xl shadow-lg">

    <h1 class="text-3xl font-bold text-center text-blue-600 mb-6"><?= $titulo ?></h1>

    <?php if(!empty($mensaje)): ?>
        <div class="bg-yellow-100 text-yellow-800 font-semibold p-4 rounded mb-6 text-center"><?= $mensaje ?></div>
    <?php endif; ?>

    <?php if(!empty($asistencias)): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg">
                <thead class="bg-blue-100 text-blue-800 uppercase text-sm font-semibold">
                    <tr>
                        <?php if ($userRol !== 'estudiante'): ?>
                            <th class="py-3 px-4 text-left">Estudiante</th>
                        <?php endif; ?>
                        <th class="py-3 px-4 text-left">Fecha</th>
                        <th class="py-3 px-4 text-left">Estado</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    <?php foreach($asistencias as $a): ?>
                        <tr class="hover:bg-gray-100">
                            <?php if ($userRol !== 'estudiante'): ?>
                                <td class="py-2 px-4"><?= htmlspecialchars($a['estudiante_nombre']) ?></td>
                            <?php endif; ?>
                            <td class="py-2 px-4"><?= htmlspecialchars($a['fecha']) ?></td>
                            <td class="py-2 px-4 font-semibold <?= strtolower($a['estado']) === 'presente' ? 'text-green-600' : (strtolower($a['estado']) === 'ausente' ? 'text-red-600' : 'text-yellow-500') ?>">
                                <?= htmlspecialchars($a['estado']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-gray-100 text-gray-700 p-6 rounded-lg text-center font-medium">
            No se encontraron registros de asistencia.
        </div>
    <?php endif; ?>

    <?php
    // Enlace de regreso según rol
    $backLink = '../index/index_estudiante.php';
    if ($userRol === 'admin') $backLink = '../index/index_admin.php';
    if ($userRol === 'maestro') $backLink = '../index/index_maestro.php';
    if ($userRol === 'tutor') $backLink = '../index/index_tutor.php';
    ?>
    <a href="<?= $backLink ?>" class="inline-block mt-6 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-bold shadow">
        🔙 Volver al Inicio
    </a>
</div>

</body>
</html>
