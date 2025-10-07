<?php
session_start();
include '../conexion.php';

// 🔒 Verificar sesión admin
if(!isset($_SESSION['id']) || $_SESSION['rol']!=='admin'){
    header("Location: ../login.php");
    exit();
}

// Obtener datos del admin para la bienvenida
$admin_id = $_SESSION['id'];
$admin_data = $conn->query("SELECT nombre, apellido FROM usuarios WHERE id=$admin_id")->fetch_assoc();

// 💾 Función para sanitizar inputs
function limpiar($dato){ return htmlspecialchars(trim($dato)); }

// ==========================
// Lógica de Reordenar, Crear, Registrar Asistencia, Eliminar
// (Mantenida del código original)
// ==========================

// Reordenar IDs alfabéticamente por rol
function reordenarIDs($conn, $rol){
    $res = $conn->query("SELECT id FROM usuarios WHERE rol='$rol' ORDER BY nombre ASC, apellido ASC");
    $nuevo_id = 1;
    $mapa_ids = [];
    while($row = $res->fetch_assoc()){
        $mapa_ids[$row['id']] = $nuevo_id;
        $nuevo_id++;
    }
    if(empty($mapa_ids)) return;

    $conn->query("CREATE TEMPORARY TABLE tmp_ids (old_id INT PRIMARY KEY, new_id INT)");
    foreach($mapa_ids as $viejo => $nuevo){
        $stmt = $conn->prepare("INSERT INTO tmp_ids (old_id,new_id) VALUES (?,?)");
        $stmt->bind_param("ii",$viejo,$nuevo);
        $stmt->execute();
        $stmt->close();
    }

    $conn->query("UPDATE usuarios u JOIN tmp_ids t ON u.id = t.old_id SET u.id = t.new_id");

    foreach($mapa_ids as $viejo => $nuevo){
        if($viejo != $nuevo){
            // Actualizar referencias en otras tablas (ej. asistencias)
            $conn->query("UPDATE asistencias SET id_estudiante=$nuevo WHERE id_estudiante=$viejo");
            $conn->query("UPDATE asistencias SET id_maestro=$nuevo WHERE id_maestro=$viejo");
        }
    }
    $conn->query("DROP TEMPORARY TABLE IF EXISTS tmp_ids");
}

// Crear usuario (Lógica simplificada para el front-end)
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['nuevo_usuario'])){
    $nombre=limpiar($_POST['nombre']);
    $apellido=limpiar($_POST['apellido']);
    $email=limpiar($_POST['email']);
    $password=password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol=limpiar($_POST['rol']);

    $nivel = in_array($rol,['maestro','estudiante','tutor']) ? limpiar($_POST['nivel'] ?? '') : null;
    $grado = in_array($rol,['maestro','estudiante','tutor']) ? limpiar($_POST['grado'] ?? '') : null;
    $seccion = in_array($rol,['maestro','estudiante','tutor']) ? limpiar($_POST['seccion'] ?? '') : null;
    $area = $rol==='maestro' ? limpiar($_POST['area'] ?? '') : null;

    $stmt=$conn->prepare("SELECT id FROM usuarios WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $stmt->store_result();
    $message = '';
    if($stmt->num_rows>0){
        $message = "<p class='text-red-500'>❌ El email ya está registrado.</p>";
        $stmt->close();
    } else {
        $stmt->close();
        $stmt=$conn->prepare("INSERT INTO usuarios (nombre,apellido,email,password,rol,nivel,grado,area,seccion) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssssss",$nombre,$apellido,$email,$password,$rol,$nivel,$grado,$area,$seccion);
        $stmt->execute();
        $stmt->close();

        reordenarIDs($conn, $rol);
        $message = "<p class='text-green-600'>✅ Usuario creado correctamente y reordenado alfabéticamente.</p>";
    }
    // Usar variable de sesión para mostrar mensaje después de redireccionar
    $_SESSION['message'] = $message;
    header("Location: index_admin.php");
    exit();
}

// Registrar asistencia rápida
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['registrar_asistencia'])){
    $id_usuario = intval($_POST['id_usuario']);
    $estado = limpiar($_POST['estado']);
    // El rol no es necesario en la inserción, solo el ID
    $stmt = $conn->prepare("INSERT INTO asistencias (id_maestro,id_estudiante,fecha,estado) VALUES (?,?,NOW(),?)");
    // Usamos el mismo ID para ambos campos; la base de datos debería manejar la nulidad si es estudiante/maestro
    $stmt->bind_param("iis",$id_usuario,$id_usuario,$estado);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "<p class='text-blue-600'>✅ Asistencia registrada para el usuario ID: {$id_usuario}.</p>";
    header("Location: index_admin.php");
    exit();
}

// Eliminar usuario
if(isset($_GET['eliminar'])){
    $id = intval($_GET['eliminar']);
    $res = $conn->query("SELECT rol FROM usuarios WHERE id=$id");
    if($res->num_rows>0){
        $rol = $res->fetch_assoc()['rol'];
        $conn->query("DELETE FROM usuarios WHERE id=$id");
        reordenarIDs($conn, $rol);
        $_SESSION['message'] = "<p class='text-red-600'>🗑️ Usuario {$id} eliminado correctamente.</p>";
    }
    header("Location: index_admin.php");
    exit();
}

// Obtener asistencia (últimas 5)
function obtenerAsistencia($conn, $id_usuario){
    $stmt=$conn->prepare("SELECT fecha,estado FROM asistencias WHERE id_estudiante=? OR id_maestro=? ORDER BY fecha DESC LIMIT 5");
    $stmt->bind_param("ii",$id_usuario,$id_usuario);
    $stmt->execute();
    return $stmt->get_result();
}

// Usuarios por rol
$roles=['estudiante','maestro','tutor','auxiliar','secretaria','admin'];
$usuarios=[];
foreach($roles as $r){
    $usuarios[$r]=$conn->query("SELECT * FROM usuarios WHERE rol='$r' ORDER BY nivel, grado, seccion, nombre ASC, apellido ASC");
}

// Mostrar mensaje de sesión (si existe)
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
    <title>👑 Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Estilos para la tabla responsive */
        .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .tab-button { transition: background-color 0.2s, color 0.2s; }
        .tab-content.active { display: block; }
        .tab-content { display: none; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Encabezado Fijo y Responsive -->
    <header class="bg-blue-800 shadow-2xl sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            
            <!-- Título del Panel -->
            <div class="flex items-center space-x-3">
                <div class="text-2xl font-extrabold text-white">
                    <i class="fas fa-crown mr-2 text-yellow-300"></i> Panel de Administración
                </div>
            </div>

            <!-- Información del Usuario y Logout -->
            <div class="flex items-center space-x-4">
                <div class="text-sm font-medium text-white hidden sm:block">
                    👋 Admin: <span class="font-bold text-yellow-300"><?= htmlspecialchars($admin_data['nombre'] . ' ' . ($admin_data['apellido'] ?? '')) ?></span>
                </div>
                <a href="../logout.php" class="flex items-center space-x-2 bg-red-500 text-white px-3 py-2 rounded-lg font-semibold hover:bg-red-600 transition duration-200 shadow-md">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="text-sm hidden sm:inline">Salir</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto p-6 sm:p-8">

        <!-- Mensaje de Notificación -->
        <?php if ($session_message): ?>
            <div class="bg-white p-4 rounded-xl shadow-md mb-6 border-l-4 border-green-500">
                <?= $session_message ?>
            </div>
        <?php endif; ?>

        <!-- Tarjetas de Acciones Rápidas -->
        <section class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="registro.php" class="bg-green-500 text-white p-4 rounded-xl shadow-lg hover:bg-green-600 transition duration-200 text-center font-semibold flex items-center justify-center space-x-2">
                <i class="fas fa-user-plus text-xl"></i>
                <span>Crear Nuevo Usuario</span>
            </a>
            <div class="bg-blue-500 text-white p-4 rounded-xl shadow-lg text-center font-semibold flex items-center justify-center space-x-2">
                <i class="fas fa-lock text-xl"></i>
                <span>Gestión de Permisos</span>
            </div>
            <div class="bg-yellow-500 text-white p-4 rounded-xl shadow-lg text-center font-semibold flex items-center justify-center space-x-2">
                <i class="fas fa-history text-xl"></i>
                <span>Ver Logs del Sistema</span>
            </div>
        </section>

        <!-- Contenedor de Pestañas (Tabs) -->
        <section class="bg-white rounded-xl shadow-2xl p-4 sm:p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">📂 Registros de la Plataforma</h2>

            <!-- Botones de Pestañas -->
            <div class="flex flex-wrap border-b border-gray-200 mb-4" id="tab-nav">
                <?php $i = 0; foreach($roles as $rol): $i++; ?>
                    <button 
                        class="tab-button px-4 py-2 text-sm font-medium border-b-2 <?= $i === 1 ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' ?>" 
                        data-target="tab-<?= $rol ?>"
                    >
                        <?= ucfirst($rol) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Contenido de Pestañas -->
            <div id="tab-contents">
                <?php $i = 0; foreach($roles as $rol): $i++; ?>
                    <div id="tab-<?= $rol ?>" class="tab-content <?= $i === 1 ? 'active' : '' ?>">
                        <h3 class="text-xl font-semibold text-blue-700 mb-4"><?= ucfirst($rol) ?>s Registrados</h3>

                        <?php 
                        $res_usuarios = $usuarios[$rol];
                        if($res_usuarios->num_rows>0):
                            // Agrupar por Nivel/Grado/Sección (si aplica)
                            $agrupados = [];
                            while($u = $res_usuarios->fetch_assoc()){
                                $key = ($u['nivel'] && $u['grado'] && $u['seccion']) ? $u['nivel'].'-'.$u['grado'].'-'.$u['seccion'] : 'sin_asignacion';
                                $agrupados[$key][] = $u;
                            }
                        ?>
                            <?php foreach($agrupados as $grupo => $usuarios_grupo): ?>
                                <div class="mb-6 border rounded-lg p-3 bg-gray-50">
                                    <h4 class="text-lg font-bold text-gray-700 mb-3 border-b pb-2">
                                        <?= $grupo === 'sin_asignacion' ? 'Usuarios sin Asignación' : "Grupo: " . str_replace('-', ' ', $grupo) ?>
                                    </h4>
                                    
                                    <div class="table-wrapper">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-2 py-3 text-left text-xs font-bold text-gray-600 uppercase">ID</th>
                                                    <th class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase">Nombre Completo</th>
                                                    <th class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase hidden md:table-cell">Email</th>
                                                    <?php if($rol==='maestro'): ?><th class="px-3 py-3 text-left text-xs font-bold text-gray-600 uppercase">Área</th><?php endif; ?>
                                                    <?php if(in_array($rol,['maestro','estudiante'])): ?><th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase">Asistencia Rápida</th><?php endif; ?>
                                                    <th class="px-3 py-3 text-center text-xs font-bold text-gray-600 uppercase">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <?php foreach($usuarios_grupo as $u): ?>
                                                <tr>
                                                    <td class="px-2 py-3 whitespace-nowrap text-sm font-medium text-gray-900"><?= $u['id'] ?></td>
                                                    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-700"><?= htmlspecialchars($u['nombre'] . ' ' . ($u['apellido'] ?? '')) ?></td>
                                                    <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell"><?= htmlspecialchars($u['email']) ?></td>
                                                    <?php if($rol==='maestro'): ?><td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($u['area'] ?? 'N/A') ?></td><?php endif; ?>
                                                    
                                                    <?php if(in_array($rol,['maestro','estudiante'])): ?>
                                                    <td class="px-3 py-3 whitespace-nowrap">
                                                        <?php 
                                                        // Verificar si ya se registró hoy
                                                        $stmt = $conn->prepare("SELECT estado FROM asistencias WHERE (id_estudiante=? OR id_maestro=?) AND DATE(fecha)=CURDATE()");
                                                        $stmt->bind_param("ii", $u['id'], $u['id']);
                                                        $stmt->execute();
                                                        $stmt->bind_result($estado_hoy);
                                                        $stmt->fetch();
                                                        $yaRegistradoHoy = $stmt->num_rows > 0;
                                                        $stmt->close();
                                                        ?>
                                                        
                                                        <?php if(!$yaRegistradoHoy): ?>
                                                            <form method="post" class="mt-1 inline-block w-36">
                                                                <input type="hidden" name="registrar_asistencia" value="1">
                                                                <input type="hidden" name="id_usuario" value="<?= $u['id'] ?>">
                                                                <select name="estado" class="border rounded px-2 py-1 text-sm bg-white hover:bg-gray-100 w-full" onchange="this.form.submit()">
                                                                    <option value="">Registrar</option>
                                                                    <option value="Presente">Presente</option>
                                                                    <option value="Ausente">Ausente</option>
                                                                    <option value="Tarde">Tarde</option>
                                                                </select>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-xs font-semibold px-2 py-1 rounded-full <?= match($estado_hoy){'Presente' => 'bg-green-100 text-green-700', 'Ausente' => 'bg-red-100 text-red-700', 'Tarde' => 'bg-yellow-100 text-yellow-700', default => 'bg-gray-100 text-gray-700'}; ?>">
                                                                Registrado: <?= $estado_hoy ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Menú Acciones -->
                                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                                        <div class="relative inline-block text-left">
                                                            <button type="button" class="action-button inline-flex items-center justify-center px-3 py-1 bg-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-300" onclick="toggleDropdown(this)">
                                                                Opciones <i class="fas fa-caret-down ml-1"></i>
                                                            </button>
                                                            <div class="dropdown-menu origin-top-right absolute right-0 mt-1 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden z-20">
                                                                <div class="py-1">
                                                                    <a href="editar_usuario.php?id=<?= $u['id'] ?>" class="block px-4 py-2 text-sm text-blue-600 hover:bg-blue-100"><i class="fas fa-edit mr-2"></i>Editar</a>
                                                                    <a href="ver_historial.php?id=<?= $u['id'] ?>" class="block px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-100"><i class="fas fa-chart-bar mr-2"></i>Historial</a>
                                                                    <a href="?eliminar=<?= $u['id'] ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-100" onclick="return confirm('¿Desea eliminar a <?= htmlspecialchars($u['nombre'] . ' ' . ($u['apellido'] ?? '')) ?>? Esta acción es irreversible.');"><i class="fas fa-trash-alt mr-2"></i>Eliminar</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        <?php else: ?>
                            <p class="text-gray-500 italic">No hay <?= $rol ?>s registrados en la base de datos.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>
    </main>

    <!-- Script de Tabs y Dropdown (JavaScript) -->
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
            
            // Función para manejar el menú dropdown
            window.toggleDropdown = function(button) {
                const menu = button.nextElementSibling;
                // Ocultar todos los menús abiertos
                document.querySelectorAll('.dropdown-menu').forEach(d => {
                    if (d !== menu) d.classList.add('hidden');
                });
                // Toggle del menú clickeado
                menu.classList.toggle('hidden');
            }

            // Ocultar dropdown al hacer click fuera
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.dropdown-menu') && !e.target.closest('.action-button')) {
                    document.querySelectorAll('.dropdown-menu').forEach(d => {
                        d.classList.add('hidden');
                    });
                }
            });

            // Inicializar la primera pestaña
            const firstButton = tabNav.querySelector('.tab-button');
            if (firstButton) {
                firstButton.click();
            }
        });
    </script>

    <!-- Pie de página -->
    <footer class="bg-gray-800 text-white text-center py-6 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-sm">
                &copy; 2025 Colegio Steve Jobs. Plataforma de Administración Central.
            </p>
        </div>
    </footer>

</body>
</html>
<?php $conn->close(); ?>