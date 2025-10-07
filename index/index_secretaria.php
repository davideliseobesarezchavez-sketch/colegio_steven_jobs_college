<?php
session_start();
// NOTA: Asegúrate de que la ruta a 'conexion.php' es correcta.
include '../conexion.php';

// Solo secretaria
if(!isset($_SESSION['id']) || $_SESSION['rol'] != "secretaria"){
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['id'];
$result_sec = $conn->query("SELECT nombre, apellido FROM usuarios WHERE id=$id");
$secretaria = $result_sec->fetch_assoc();
$nombre_completo = htmlspecialchars($secretaria['nombre'] . ' ' . ($secretaria['apellido'] ?? ''));

// Usuarios registrados
$usuarios = $conn->query("SELECT id, nombre, apellido, email, rol, nivel, grado, seccion FROM usuarios ORDER BY rol, nombre ASC");

// Generar reporte asistencia (función ya definida en tu código original)
function obtenerAsistencia($conn, $id_usuario){
    $stmt = $conn->prepare("SELECT fecha, estado FROM asistencias WHERE id_estudiante=? OR id_maestro=? ORDER BY fecha DESC LIMIT 5");
    $stmt->bind_param("ii", $id_usuario, $id_usuario);
    $stmt->execute();
    return $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>📋 Panel Secretaria - Colegio Steve Jobs</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Carga de Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Estilos específicos para la tabla y hacerlo responsive */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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
                    <i class="fas fa-clipboard-list mr-2"></i> Panel de Secretaría
                </div>
            </div>

            <!-- Información del Usuario y Logout -->
            <div class="flex items-center space-x-4">
                <div class="text-sm font-medium text-white hidden sm:block">
                    👋 Bienvenida: <span class="font-bold text-yellow-300"><?= $nombre_completo ?></span>
                </div>
                <a href="../logout.php" class="flex items-center space-x-2 bg-red-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-red-600 transition duration-200 shadow-md">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="text-sm hidden sm:inline">Cerrar sesión</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto p-6 sm:p-8">
        
        <!-- Tarjeta de Bienvenida y Herramientas Rápidas -->
        <section class="bg-white p-6 md:p-8 rounded-2xl shadow-xl border-t-4 border-indigo-500 mb-10">
            <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Herramientas de Gestión Central</h2>
            <p class="text-gray-600 text-lg mb-6">
                Desde aquí puedes consultar y gestionar todos los registros de usuarios y la asistencia.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Tarjeta 1: Gestión de Usuarios -->
                <div class="bg-blue-50 p-6 rounded-xl shadow-md border-l-4 border-blue-500 hover:shadow-lg transition cursor-pointer">
                    <h3 class="text-xl font-bold text-blue-800 mb-2"><i class="fas fa-users mr-2"></i> Gestión de Usuarios</h3>
                    <p class="text-gray-600">Alta, baja y modificación de estudiantes y maestros.</p>
                    <a href="../includes/gestion_usuarios.php" class="text-blue-600 hover:underline">
                        Gestión de Usuarios
                    </a>
                </div>
                <!-- Tarjeta 2: Comunicación -->
                <div class="bg-green-50 p-6 rounded-xl shadow-md border-l-4 border-green-500 hover:shadow-lg transition cursor-pointer">
                    <h3 class="text-xl font-bold text-green-800 mb-2"><i class="fas fa-envelope mr-2"></i> Comunicación Masiva</h3>
                    <p class="text-gray-600">Envío de comunicados y avisos a padres/tutores.</p>
                    <a href="../includes/comunicacion_masiva.php" class="text-blue-600 hover:underline">
                        Comunicación Masiva
                    </a>
                </div>
            </div>
        </section>

        <!-- Sección de Usuarios Registrados (Tabla) -->
        <section class="mb-10">
            <h2 class="text-2xl font-bold text-indigo-700 mb-6 border-b-2 pb-2">👥 Base de Datos de Usuarios</h2>
            
            <div class="bg-white rounded-xl shadow-2xl table-container">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider rounded-tl-xl">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden sm:table-cell">Email</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Clase</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider rounded-tr-xl">Últimas Asistencias</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        if ($usuarios->num_rows > 0) {
                            while($u = $usuarios->fetch_assoc()): 
                                // Determinar color del rol
                                $rol_color = [
                                    'secretaria' => 'bg-pink-100 text-pink-700',
                                    'maestro' => 'bg-indigo-100 text-indigo-700',
                                    'estudiante' => 'bg-green-100 text-green-700'
                                ][$u['rol']] ?? 'bg-gray-100 text-gray-700';

                                $clase_info = ($u['nivel'] && $u['grado'] && $u['seccion']) ? 
                                    htmlspecialchars($u['nivel'] . ' ' . $u['grado'] . $u['seccion']) : 
                                    'N/A';
                                
                                $nombre_usuario = htmlspecialchars($u['nombre'] . ' ' . ($u['apellido'] ?? ''));
                        ?>
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="px-3 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $rol_color ?>">
                                    <?= ucfirst($u['rol']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                <?= $nombre_usuario ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell">
                                <?= htmlspecialchars($u['email']) ?>
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                <?= $clase_info ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php $asistencias = obtenerAsistencia($conn, $u['id']); 
                                if($asistencias->num_rows > 0): 
                                    while($a = $asistencias->fetch_assoc()): 
                                        $estado_color = ($a['estado'] == 'Presente') ? 'text-green-600' : 'text-red-600';
                                ?>
                                    <div class="text-xs mb-1">
                                        <?= date('d/m', strtotime($a['fecha'])) ?>: 
                                        <span class="<?= $estado_color ?> font-semibold"><?= $a['estado'] ?></span>
                                    </div>
                                <?php 
                                    endwhile;
                                else: 
                                    echo "<span class='text-xs italic'>No hay registros recientes</span>";
                                endif; 
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; 
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-8 text-gray-500'>No hay usuarios registrados en la base de datos.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- Pie de página -->
    <footer class="bg-gray-800 text-white text-center py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm">
                &copy; 2025 Colegio Steve Jobs. Plataforma Educativa de Secretaría.
            </p>
        </div>
    </footer>

</body>
</html>
<?php $conn->close(); ?>
