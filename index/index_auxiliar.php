<?php
session_start();
include '../conexion.php';

// Solo auxiliares
if(!isset($_SESSION['id']) || $_SESSION['rol'] != "auxiliar"){
    header("Location: ../login.php");
    exit();
}

$id = $_SESSION['id'];
$result_aux = $conn->query("SELECT nombre, apellido FROM usuarios WHERE id=$id");
$auxiliar = $result_aux->fetch_assoc();
$nombre_completo = htmlspecialchars($auxiliar['nombre'] . ' ' . ($auxiliar['apellido'] ?? ''));

// Listado de estudiantes y maestros, agrupados por clase
$estudiantes_res = $conn->query("SELECT * FROM usuarios WHERE rol='estudiante' ORDER BY nivel, grado, seccion, nombre ASC");
$maestros_res = $conn->query("SELECT * FROM usuarios WHERE rol='maestro' ORDER BY nombre ASC");

$estudiantes_agrupados = [];
while($e = $estudiantes_res->fetch_assoc()){
    $clase = "{$e['nivel']} {$e['grado']}-{$e['seccion']}";
    if (!isset($estudiantes_agrupados[$clase])) {
        $estudiantes_agrupados[$clase] = [];
    }
    $estudiantes_agrupados[$clase][] = $e;
}

// Registrar asistencia rápida
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['registrar_asistencia'])){
    $id_usuario = intval($_POST['id_usuario']);
    $estado = htmlspecialchars($_POST['estado']);
    $rol_usuario = htmlspecialchars($_POST['rol']);

    // Verificar si ya registró hoy para evitar duplicados
    $stmt_check = $conn->prepare("SELECT id FROM asistencias WHERE (id_estudiante=? OR id_maestro=?) AND DATE(fecha) = CURDATE()");
    $stmt_check->bind_param("ii", $id_usuario, $id_usuario);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows == 0) {
        $stmt_check->close();
        
        // Registrar la asistencia
        $id_tutor = !empty($id_tutor) ? $id_tutor : null;
        $stmt = $conn->prepare("INSERT INTO asistencias (id_estudiante, id_maestro, id_tutor, fecha, estado)
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
        // Usamos el mismo ID en ambos; el campo no relevante quedará sin valor real en la tabla si la BD lo permite, 
        // o asumimos que la lógica de la BD lo maneja.
        $stmt->bind_param("iiiiiss",$id_estudiante, $id_maestro, $id_tutor, $id_auxiliar, $id_secretaria, $fecha, $estado);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "<div class='bg-green-100 text-green-700 p-3 rounded-lg text-sm'>✅ Asistencia ('{$estado}') registrada para el usuario ID: {$id_usuario}.</div>";
    } else {
        $_SESSION['message'] = "<div class='bg-yellow-100 text-yellow-700 p-3 rounded-lg text-sm'>⚠️ El usuario ID: {$id_usuario} ya tiene asistencia registrada para hoy.</div>";
        $stmt_check->close();
    }

    // Redireccionar al índice para limpiar el POST y mostrar el mensaje
    header("Location: index_auxiliar.php");
    exit();
}

// Función para obtener asistencia (Últimos 3 registros)
function obtenerAsistencia($conn, $id_usuario){
    $stmt=$conn->prepare("SELECT fecha,estado FROM asistencias WHERE id_estudiante=? OR id_maestro=? ORDER BY fecha DESC LIMIT 3");
    $stmt->bind_param("ii",$id_usuario,$id_usuario);
    $stmt->execute();
    return $stmt->get_result();
}

// Recuperar mensaje de sesión
$session_message = '';
if (isset($_SESSION['message'])) {
    $session_message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>👷 Panel Auxiliar - Registro de Asistencia</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Carga de Font Awesome para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .tab-content.active { display: block; }
        .tab-content { display: none; }
        
        .btn-presente { background-color: #10B981; color: white; transition: transform 0.1s; } /* Emerald 500 */
        .btn-ausente { background-color: #EF4444; color: white; transition: transform 0.1s; } /* Red 500 */
        .btn-tarde { background-color: #F59E0B; color: white; transition: transform 0.1s; } /* Amber 500 */
        .btn-asistencia:active { transform: scale(0.95); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Encabezado Fijo y Responsive -->
    <header class="bg-gray-700 shadow-xl sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            
            <!-- Título del Panel -->
            <div class="flex items-center space-x-3">
                <div class="text-2xl font-extrabold text-white">
                    <i class="fas fa-user-shield mr-2 text-yellow-300"></i> Panel de Auxiliar
                </div>
            </div>

            <!-- Información del Usuario y Logout -->
            <div class="flex items-center space-x-4">
                <div class="text-sm font-medium text-white hidden sm:block">
                    👋 Bienvenido: <span class="font-bold text-yellow-300"><?= $nombre_completo ?></span>
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
        
        <!-- Mensaje de Notificación -->
        <?php if ($session_message): ?>
            <div class="mb-6">
                <?= $session_message ?>
            </div>
        <?php endif; ?>

        <!-- Panel de control centralizado (Tabs) -->
        <div class="bg-white rounded-2xl shadow-2xl p-4 sm:p-6 border-t-4 border-indigo-500">
            
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Registro Diario de Asistencia</h2>

            <!-- Botones de Pestañas -->
            <div class="flex flex-wrap border-b border-gray-200 mb-4" id="tab-nav">
                <button class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600" data-target="tab-estudiantes">
                    <i class="fas fa-user-graduate mr-1"></i> Estudiantes
                </button>
                <button class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700" data-target="tab-maestros">
                    <i class="fas fa-chalkboard-teacher mr-1"></i> Maestros
                </button>
            </div>

            <!-- Contenido de Pestañas -->
            <div id="tab-contents">
                
                <!-- Pestaña de Estudiantes -->
                <div id="tab-estudiantes" class="tab-content active">
                    <?php if (empty($estudiantes_agrupados)): ?>
                        <p class="text-gray-500 italic">No hay estudiantes registrados.</p>
                    <?php else: ?>
                        <?php foreach($estudiantes_agrupados as $clase => $estudiantes): ?>
                            <div class="mb-6 border rounded-lg p-3 bg-gray-50 shadow-sm">
                                <h3 class="text-lg font-bold text-indigo-700 mb-3 border-b pb-2">Clase: <?= htmlspecialchars($clase) ?></h3>
                                
                                <div class="table-wrapper">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase">Nombre</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase">Últimos Registros</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase">Acción Rápida HOY</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <?php foreach($estudiantes as $e): ?>
                                            <tr>
                                                <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($e['nombre'] . ' ' . ($e['apellido'] ?? '')) ?>
                                                </td>
                                                <td class="px-3 py-3 text-sm text-gray-500">
                                                    <ul class="text-xs space-y-0.5">
                                                    <?php 
                                                    $asistencias = obtenerAsistencia($conn, $e['id']); 
                                                    if($asistencias->num_rows>0): 
                                                        while($a=$asistencias->fetch_assoc()): 
                                                            $color = match($a['estado']){
                                                                'Presente' => 'text-green-600',
                                                                'Ausente' => 'text-red-600',
                                                                'Tarde' => 'text-yellow-600',
                                                                default => 'text-gray-600'
                                                            };
                                                    ?>
                                                        <li class="<?= $color ?>"><?= date('d/m', strtotime($a['fecha'])) ?>: <?= $a['estado'] ?></li>
                                                    <?php endwhile; else: ?>
                                                        <li class="text-gray-400 italic">Sin registros</li>
                                                    <?php endif; ?>
                                                    </ul>
                                                </td>
                                                <td class="px-3 py-3 text-center whitespace-nowrap">
                                                    <?php 
                                                    // Verificar si ya registró hoy
                                                    $stmt_check_today = $conn->prepare("SELECT estado FROM asistencias WHERE (id_estudiante=? OR id_maestro=?) AND DATE(fecha) = CURDATE()");
                                                    $stmt_check_today->bind_param("ii", $e['id'], $e['id']);
                                                    $stmt_check_today->execute();
                                                    $stmt_check_today->bind_result($estado_hoy);
                                                    $stmt_check_today->fetch();
                                                    $yaRegistradoHoy = $stmt_check_today->num_rows > 0;
                                                    $stmt_check_today->close();
                                                    ?>

                                                    <?php if(!$yaRegistradoHoy): ?>
                                                    <form method="post" class="flex justify-center space-x-1">
                                                        <input type="hidden" name="id_usuario" value="<?= $e['id'] ?>">
                                                        <input type="hidden" name="rol" value="estudiante">
                                                        <input type="hidden" name="registrar_asistencia" value="1">

                                                        <button type="submit" name="estado" value="Presente" class="btn-asistencia btn-presente text-xs font-semibold px-3 py-2 rounded-lg shadow-md hover:opacity-90">
                                                            P
                                                        </button>
                                                        <button type="submit" name="estado" value="Ausente" class="btn-asistencia btn-ausente text-xs font-semibold px-3 py-2 rounded-lg shadow-md hover:opacity-90">
                                                            A
                                                        </button>
                                                        <button type="submit" name="estado" value="Tarde" class="btn-asistencia btn-tarde text-xs font-semibold px-3 py-2 rounded-lg shadow-md hover:opacity-90">
                                                            T
                                                        </button>
                                                    </form>
                                                    <?php else: ?>
                                                        <span class="text-xs font-semibold px-2 py-1 rounded-full <?= match($estado_hoy){'Presente' => 'bg-green-100 text-green-700', 'Ausente' => 'bg-red-100 text-red-700', 'Tarde' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700'}; ?>">
                                                            <?= $estado_hoy ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pestaña de Maestros -->
                <div id="tab-maestros" class="tab-content">
                    <?php if ($maestros_res->num_rows == 0): ?>
                        <p class="text-gray-500 italic">No hay maestros registrados.</p>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase">Nombre</th>
                                        <th class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase">Área</th>
                                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase">Últimos Registros</th>
                                        <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase">Acción Rápida HOY</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while($m = $maestros_res->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($m['nombre'] . ' ' . ($m['apellido'] ?? '')) ?>
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($m['area'] ?? 'N/A') ?>
                                        </td>
                                        <td class="px-3 py-3 text-sm text-gray-500">
                                            <ul class="text-xs space-y-0.5">
                                            <?php 
                                            $asistencias = obtenerAsistencia($conn, $m['id']); 
                                            if($asistencias->num_rows>0): 
                                                while($a=$asistencias->fetch_assoc()): 
                                                    $color = match($a['estado']){
                                                        'Presente' => 'text-green-600',
                                                        'Ausente' => 'text-red-600',
                                                        'Tarde' => 'text-yellow-600',
                                                        default => 'text-gray-600'
                                                    };
                                            ?>
                                                <li class="<?= $color ?>"><?= date('d/m', strtotime($a['fecha'])) ?>: <?= $a['estado'] ?></li>
                                            <?php endwhile; else: ?>
                                                <li class="text-gray-400 italic">Sin registros</li>
                                            <?php endif; ?>
                                            </ul>
                                        </td>
                                        <td class="px-3 py-3 text-center whitespace-nowrap">
                                            <?php 
                                            // Verificar si ya registró hoy
                                            $stmt_check_today = $conn->prepare("SELECT estado FROM asistencias WHERE (id_estudiante=? OR id_maestro=?) AND DATE(fecha) = CURDATE()");
                                            $stmt_check_today->bind_param("ii", $m['id'], $m['id']);
                                            $stmt_check_today->execute();
                                            $stmt_check_today->bind_result($estado_hoy);
                                            $stmt_check_today->fetch();
                                            $yaRegistradoHoy = $stmt_check_today->num_rows > 0;
                                            $stmt_check_today->close();
                                            ?>

                                            <?php if(!$yaRegistradoHoy): ?>
                                            <form method="post" class="flex justify-center space-x-1">
                                                <input type="hidden" name="id_usuario" value="<?= $m['id'] ?>">
                                                <input type="hidden" name="rol" value="maestro">
                                                <input type="hidden" name="registrar_asistencia" value="1">

                                                <button type="submit" name="estado" value="Presente" class="btn-asistencia btn-presente text-xs font-semibold px-3 py-2 rounded-lg shadow-md hover:opacity-90">
                                                    P
                                                </button>
                                                <button type="submit" name="estado" value="Ausente" class="btn-asistencia btn-ausente text-xs font-semibold px-3 py-2 rounded-lg shadow-md hover:opacity-90">
                                                    A
                                                </button>
                                                <button type="submit" name="estado" value="Tarde" class="btn-asistencia btn-tarde text-xs font-semibold px-3 py-2 rounded-lg shadow-md hover:opacity-90">
                                                    T
                                                </button>
                                            </form>
                                            <?php else: ?>
                                                <span class="text-xs font-semibold px-2 py-1 rounded-full <?= match($estado_hoy){'Presente' => 'bg-green-100 text-green-700', 'Ausente' => 'bg-red-100 text-red-700', 'Tarde' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700'}; ?>">
                                                    <?= $estado_hoy ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <!-- Script de Tabs -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabNav = document.getElementById('tab-nav');
            const tabContents = document.getElementById('tab-contents');

            tabNav.addEventListener('click', (e) => {
                if (e.target.classList.contains('tab-button')) {
                    const targetId = e.target.dataset.target;

                    // Remover clase active de todos los botones
                    tabNav.querySelectorAll('.tab-button').forEach(btn => {
                        btn.classList.remove('border-indigo-600', 'text-indigo-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    });

                    // Agregar clase active al botón clickeado
                    e.target.classList.add('border-indigo-600', 'text-indigo-600');
                    e.target.classList.remove('border-transparent', 'text-gray-500');

                    // Mostrar el contenido de la pestaña
                    tabContents.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.remove('active');
                    });
                    document.getElementById(targetId).classList.add('active');
                }
            });
            
            // Inicializar la primera pestaña (ya está hecho en PHP, pero por si acaso)
            const firstButton = tabNav.querySelector('.tab-button');
            if (firstButton) {
                firstButton.click();
            }
        });
    </script>

    <!-- Pie de página -->
    <footer class="bg-gray-800 text-white text-center py-4 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm">
                &copy; 2025 Colegio Steve Jobs. Plataforma de Asistencia.
            </p>
        </div>
    </footer>

</body>
</html>
<?php $conn->close(); ?>
