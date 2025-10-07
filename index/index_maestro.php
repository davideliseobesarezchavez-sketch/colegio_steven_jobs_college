<?php
session_start();
// NOTA: Asegúrate de que la ruta a 'conexion.php' sea correcta.
include '../conexion.php';

// Verificar que es maestro
if(!isset($_SESSION['id']) || $_SESSION['rol'] != "maestro"){
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['id'];
$sql = "SELECT nombre, apellido FROM usuarios WHERE id = '$id' LIMIT 1"; // Se agregó apellido para mejor bienvenida
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $nombre_completo = htmlspecialchars($usuario['nombre'] . ' ' . ($usuario['apellido'] ?? ''));
} else {
    // Si no se encuentra el maestro en la BD, cerrar sesión
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Panel Maestro - Colegio Steve Jobs</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Carga de Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Usando una fuente moderna */
        body { font-family: 'Inter', sans-serif; }
        
        /* Efecto de sombra para el encabezado */
        .shadow-header {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Encabezado Fijo y Responsive -->
    <header class="bg-white shadow-header sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col md:flex-row justify-between items-center">
            
            <!-- Logo y Título -->
            <div class="flex items-center space-x-3 mb-4 md:mb-0">
                <!-- Placeholder para el logo -->
                <img src="https://placehold.co/50x50/1e3a8a/ffffff?text=SJ" alt="Logo Colegio" class="rounded-full shadow-lg">
                <div class="text-xl font-extrabold text-gray-900">
                    🎓 <span class="text-indigo-700">Panel Maestro - Colegio Steve Jobs</span>
                </div>
            </div>

            <!-- Información del Usuario y Logout -->
            <div class="flex items-center space-x-4">
                <div class="text-sm font-medium text-gray-700 hidden sm:block">
                    👋 Bienvenido(a) Maestro(a): <span class="font-bold text-indigo-600"><?php echo $nombre_completo; ?></span>
                </div>
                <a href="../logout.php" class="flex items-center space-x-2 bg-red-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-red-600 transition duration-200 shadow-md">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="text-sm">Cerrar sesión</span>
                </a>
            </div>
        </div>

        <!-- Menú de Navegación (Responsive) -->
        <nav class="bg-indigo-700 border-t border-indigo-600 py-3">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap justify-center space-x-2 md:space-x-6 text-sm font-semibold">
                    <a href="../includes/gestionar_tareas.php" class="nav-item">
                        <i class="fas fa-plus-circle mr-1"></i> Crear Tareas
                    </a>
                    <a href="../includes/ver_tareas.php" class="nav-item">
                        <i class="fas fa-search mr-1"></i> Ver Tareas
                    </a>
                    <a href="../includes/crear_examen.php" class="nav-item">
                        <i class="fas fa-file-alt mr-1"></i> Crear Exámenes
                    </a>
                    <a href="../includes/progreso_estudiantes.php" class="nav-item">
                        <i class="fas fa-clipboard-check mr-1"></i> Poner Notas
                    </a>
                    <a href="../includes/gestionar_clases.php" class="nav-item">
                        <i class="fas fa-school mr-1"></i> Gestionar Clases
                    </a>
                    <a href="../includes/asistencia_estudiantes.php" class="nav-item">
                        <i class="fas fa-user-clock mr-1"></i> Asistencia
                    </a>
                    <a href="../includes/registrar_logros.php" class="nav-item">
                        <i class="fas fa-trophy mr-1"></i> Logros
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto p-6 sm:p-8">
        
        <!-- Tarjeta de Bienvenida -->
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-xl border-t-4 border-indigo-500 mb-10">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-2">¡Hola, Maestro(a) <?php echo htmlspecialchars($usuario['nombre']); ?>!</h2>
            <p class="text-gray-600 text-lg">
                Administra tus cursos y el progreso de tus estudiantes con estas herramientas.
            </p>
        </div>

        <!-- Sección de Herramientas (Grid) -->
        <section>
            <h2 class="text-2xl font-bold text-center text-indigo-700 mb-8">Herramientas Docentes</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Tarjeta 1: Crear Tareas -->
                <div class="card-tool group" onclick="location.href='../includes/gestionar_tareas.php'">
                    <div class="icon-bg bg-indigo-100 text-indigo-600"><i class="fas fa-pencil-alt text-3xl"></i></div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-indigo-600 transition">📝 Crear Tareas</h3>
                    <p class="text-gray-500 mt-2">Asigna nuevas tareas y gestiona las entregas de tu clase.</p>
                </div>

                <!-- Tarjeta 2: Crear Exámenes -->
                <div class="card-tool group" onclick="location.href='../includes/crear_examen.php'">
                    <div class="icon-bg bg-yellow-100 text-yellow-600"><i class="fas fa-clipboard-list text-3xl"></i></div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-yellow-600 transition">📄 Crear Exámenes</h3>
                    <p class="text-gray-500 mt-2">Diseña evaluaciones y registra resultados.</p>
                </div>

                <!-- Tarjeta 3: Poner Notas de Progreso -->
                <div class="card-tool group" onclick="location.href='../includes/progreso_estudiantes.php'">
                    <div class="icon-bg bg-green-100 text-green-600"><i class="fas fa-chart-line text-3xl"></i></div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-green-600 transition">📈 Poner Notas de Progreso</h3>
                    <p class="text-gray-500 mt-2">Consulta y actualiza las calificaciones de tus estudiantes.</p>
                </div>

                <!-- Tarjeta 4: Gestionar Clases -->
                <div class="card-tool group" onclick="location.href='../includes/gestionar_clases.php'">
                    <div class="icon-bg bg-purple-100 text-purple-600"><i class="fas fa-chalkboard text-3xl"></i></div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-purple-600 transition">🏫 Gestionar Clases</h3>
                    <p class="text-gray-500 mt-2">Administra los horarios y enlaces de tus sesiones.</p>
                </div>

                <!-- Tarjeta 5: Asistencia Estudiantes -->
                <div class="card-tool group" onclick="location.href='../includes/asistencia_estudiantes.php'">
                    <div class="icon-bg bg-blue-100 text-blue-600"><i class="fas fa-user-check text-3xl"></i></div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition">📋 Tomar Asistencia</h3>
                    <p class="text-gray-500 mt-2">Registra la presencia diaria de tus estudiantes.</p>
                </div>
                
                <!-- Tarjeta 6: Registrar Logros -->
                <div class="card-tool group" onclick="location.href='../includes/registrar_logros.php'">
                    <div class="icon-bg bg-red-100 text-red-600"><i class="fas fa-medal text-3xl"></i></div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-red-600 transition">🏆 Registrar Logros</h3>
                    <p class="text-gray-500 mt-2">Añade reconocimientos y premios especiales a los alumnos.</p>
                </div>

                <!-- Tarjeta 7: Ver Tareas (Se añade como extra para completar el grid) -->
                <div class="card-tool group" onclick="location.href='../includes/ver_tareas.php'">
                    <div class="icon-bg bg-pink-100 text-pink-600"><i class="fas fa-eye text-3xl"></i></div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-pink-600 transition">📝 Ver Tareas Asignadas</h3>
                    <p class="text-gray-500 mt-2">Consulta el listado de todas las tareas activas.</p>
                </div>

                <!-- Tarjeta 8: Contacto (Se añade como extra para completar el grid) -->
                <div class="card-tool group" onclick="location.href='../includes/contacto.php'">
                    <div class="icon-bg bg-teal-100 text-teal-600"><i class="fas fa-comments text-3xl"></i></div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-teal-600 transition">💬 Contacto Padres/Estudiantes</h3>
                    <p class="text-gray-500 mt-2">Envía comunicaciones importantes sobre el curso.</p>
                </div>

            </div>

            <!-- Estilos de las tarjetas y los íconos -->
            <style>
            .card-tool {
                background-color: white;
                padding: 1.5rem;
                border-radius: 1rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                cursor: pointer;
                border: 1px solid #e0e7ff; /* light-indigo border */
            }
            .card-tool:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }
        </style>

            <div class="text-center mt-12">
                <a href="https://www.stevejobshco.edu.pe" target="_blank" class="inline-block px-8 py-3 bg-indigo-500 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-600 transition transform hover:scale-105">
                    🌐 Visita nuestro sitio oficial
                </a>
            </div>
        </section>
    </main>

    <!-- Pie de página -->
    <footer class="bg-gray-800 text-white text-center py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm">
                &copy; 2025 Colegio Steve Jobs. Plataforma Educativa
            </p>
        </div>
    </footer>

</body>
</html>
<?php $conn->close(); ?>
