<?php
session_start();
// NOTA: Asegúrate de que la ruta a 'conexion.php' es correcta.
include '../conexion.php'; 

// Verificar que es estudiante
if(!isset($_SESSION['id']) || $_SESSION['rol'] != "estudiante"){
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['id'];
$sql = "SELECT nombre, apellido, nivel, grado FROM usuarios WHERE id = '$id' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $nombre_completo = htmlspecialchars($usuario['nombre'] . ' ' . ($usuario['apellido'] ?? ''));
    $clase = htmlspecialchars($usuario['nivel'] . ' ' . $usuario['grado']);
} else {
    // Si no se encuentra el usuario, redirigir
    header("Location: ../logout.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Estudiante - Colegio Steve Jobs</title>
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
                <!-- Nota: Usamos un placeholder para la imagen del logo -->
                <img src="https://placehold.co/50x50/1e3a8a/ffffff?text=SJ" alt="Logo Colegio" class="rounded-full shadow-lg">
                <div class="text-xl font-extrabold text-gray-900">
                    🎓 <span class="text-indigo-700">Colegio Steve Jobs</span>
                </div>
            </div>

            <!-- Información del Usuario y Logout -->
            <div class="flex items-center space-x-4">
                <div class="text-sm font-medium text-gray-700 hidden sm:block">
                    👋 Estudiante: <span class="font-bold text-indigo-600"><?php echo $nombre_completo; ?></span>
                </div>
                <a href="../logout.php" class="flex items-center space-x-2 bg-red-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-red-600 transition duration-200 shadow-md">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="text-sm">Salir</span>
                </a>
            </div>
        </div>

        <!-- Menú de Navegación (Responsive) -->
        <nav class="bg-indigo-700 border-t border-indigo-600 py-3">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap justify-center space-x-2 md:space-x-6 text-sm font-semibold">
                    <a href="../includes/clases_estudiante.php" class="text-white hover:text-indigo-200 transition duration-150 py-1 px-3 rounded-md">
                        <i class="fas fa-book mr-1"></i> Mis Clases
                    </a>
                    <a href="../includes/ver_tareas.php" class="text-white hover:text-indigo-200 transition duration-150 py-1 px-3 rounded-md">
                        <i class="fas fa-tasks mr-1"></i> Ver Tareas
                    </a>
                    <a href="../includes/entregar_tareas.php" class="text-white hover:text-indigo-200 transition duration-150 py-1 px-3 rounded-md">
                        <i class="fas fa-upload mr-1"></i> Subir Tareas
                    </a>
                    <a href="../includes/progreso_personal.php" class="text-white bg-indigo-800 rounded-lg hover:bg-indigo-900 transition duration-150 py-1 px-3 shadow-inner">
                        <i class="fas fa-chart-line mr-1"></i> Mi Progreso
                    </a>
                    <a href="../includes/ver_asistencia.php" class="text-white hover:text-indigo-200 transition duration-150 py-1 px-3 rounded-md">
                        <i class="fas fa-calendar-check mr-1"></i> Ver Asistencia
                    </a>
                    <a href="https://web.whatsapp.com/" target="_blank" class="text-white hover:text-indigo-200 transition duration-150 py-1 px-3 rounded-md">
                        <i class="fab fa-whatsapp mr-1"></i> Contacto
                    </a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto p-6 sm:p-8">
        
        <!-- Tarjeta de Bienvenida y Resumen -->
        <div class="bg-white p-6 md:p-8 rounded-2xl shadow-xl border-t-4 border-indigo-500 mb-10">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-2">¡Hola, <?php echo htmlspecialchars($usuario['nombre']); ?>!</h2>
            <p class="text-gray-600 text-lg">
                Estás en <?= $clase ?>. Revisa tus herramientas académicas a continuación.
            </p>
        </div>

        <!-- Sección de Herramientas -->
        <section>
            <h2 class="text-2xl font-bold text-center text-indigo-700 mb-8">Herramientas del Estudiante</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Tarjeta 1: Subir Tareas -->
                <div class="card-tool group" onclick="location.href='../includes/entregar_tareas.php'">
                    <div class="bg-indigo-100 p-4 rounded-full w-16 h-16 flex items-center justify-center text-indigo-600 mb-4 transition duration-300 group-hover:bg-indigo-200">
                        <i class="fas fa-cloud-upload-alt text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-indigo-600 transition">📝 Subir Tareas</h3>
                    <p class="text-gray-500 mt-2">Envía tus trabajos a tiempo. ¡No te demores!</p>
                </div>

                <!-- Tarjeta 2: Ver Tareas -->
                <div class="card-tool group" onclick="location.href='../includes/ver_tareas.php'">
                    <div class="bg-green-100 p-4 rounded-full w-16 h-16 flex items-center justify-center text-green-600 mb-4 transition duration-300 group-hover:bg-green-200">
                        <i class="fas fa-clipboard-list text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-green-600 transition">📝 Ver Tareas</h3>
                    <p class="text-gray-500 mt-2">Consulta las asignaciones y fechas límite pendientes.</p>
                </div>

                <!-- Tarjeta 3: Mi Progreso -->
                <div class="card-tool group" onclick="location.href='../includes/progreso_personal.php'">
                    <div class="bg-yellow-100 p-4 rounded-full w-16 h-16 flex items-center justify-center text-yellow-600 mb-4 transition duration-300 group-hover:bg-yellow-200">
                        <i class="fas fa-chart-bar text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-yellow-600 transition">📈 Mi Progreso</h3>
                    <p class="text-gray-500 mt-2">Visualiza tus calificaciones y avances académicos.</p>
                </div>

                <!-- Tarjeta 4: Mis Clases -->
                <div class="card-tool group" onclick="location.href='../includes/clases_estudiante.php'">
                    <div class="bg-purple-100 p-4 rounded-full w-16 h-16 flex items-center justify-center text-purple-600 mb-4 transition duration-300 group-hover:bg-purple-200">
                        <i class="fas fa-chalkboard-teacher text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-purple-600 transition">📚 Mis Clases</h3>
                    <p class="text-gray-500 mt-2">Ingresa a tus clases virtuales puntualmente.</p>
                </div>

                <!-- Tarjeta 5: Ver Asistencia -->
                <div class="card-tool group" onclick="location.href='../includes/ver_asistencia.php'">
                    <div class="bg-blue-100 p-4 rounded-full w-16 h-16 flex items-center justify-center text-blue-600 mb-4 transition duration-300 group-hover:bg-blue-200">
                        <i class="fas fa-user-check text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition">📚 Ver Asistencia</h3>
                    <p class="text-gray-500 mt-2">Revisa tu registro de asistencia diaria.</p>
                </div>

                <!-- Tarjeta 6: Contacto (WhatsApp) -->
                <div class="card-tool group" onclick="window.open('https://web.whatsapp.com/', '_blank')">
                    <div class="bg-teal-100 p-4 rounded-full w-16 h-16 flex items-center justify-center text-teal-600 mb-4 transition duration-300 group-hover:bg-teal-200">
                        <i class="fab fa-whatsapp text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 group-hover:text-teal-600 transition">💬 Contacto</h3>
                    <p class="text-gray-500 mt-2">Comunícate directamente con la institución.</p>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="https://www.stevejobshco.edu.pe" target="_blank" class="inline-block px-8 py-3 bg-indigo-500 text-white font-bold rounded-xl shadow-lg hover:bg-indigo-600 transition transform hover:scale-105">
                    🌐 Visita nuestro sitio oficial
                </a>
            </div>
        </section>

        <!-- CSS para las tarjetas -->
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
    </main>

    <!-- Pie de página -->
    <footer class="bg-gray-800 text-white text-center py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-sm italic mb-2">
                🚀 Educación de calidad para todas las etapas. ¡Aprende y crece cada día!
            </div>
            <p class="text-xs">
                &copy; 2025 Colegio Steve Jobs. Plataforma Educativa
            </p>
        </div>
    </footer>

</body>
</html>
<?php $conn->close(); ?>
