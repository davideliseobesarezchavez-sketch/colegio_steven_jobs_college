<?php
session_start();
include '../conexion.php';

// 🔒 Verificar sesión admin
if(!isset($_SESSION['id']) || $_SESSION['rol']!=='admin'){
    header("Location: ../login.php");
    exit();
}

// 💾 Función para sanitizar inputs
function limpiar($dato){ return htmlspecialchars(trim($dato)); }

// ==========================
// Validar ID de usuario
// ==========================
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die("❌ ID de usuario no válido.");
}
$id_usuario = intval($_GET['id']);

// ==========================
// Obtener datos del usuario
// ==========================
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id=? AND activo=1");
$stmt->bind_param("i",$id_usuario);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows==0){
    die("❌ Usuario no encontrado o inactivo.");
}
$usuario = $res->fetch_assoc();
$stmt->close();

// ==========================
// Procesar formulario
// ==========================
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['editar_usuario'])){
    $nombre = limpiar($_POST['nombre']);
    $email = limpiar($_POST['email']);
    $rol = limpiar($_POST['rol']);
    $nivel = isset($_POST['nivel'])?limpiar($_POST['nivel']):null;
    $grado = isset($_POST['grado'])?limpiar($_POST['grado']):null;
    $area = isset($_POST['area'])?limpiar($_POST['area']):null;
    $seccion = isset($_POST['seccion'])?limpiar($_POST['seccion']):null;
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // ✅ Verificar duplicados de email
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email=? AND id<>? AND activo=1");
    $stmt->bind_param("si",$email,$id_usuario);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows>0){
        echo "<p class='text-red-500'>❌ El email ya está registrado.</p>";
        $stmt->close();
    } else {
        $stmt->close();

        // 🔹 Preparar update
        if($password){
            $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=?, nivel=?, grado=?, area=?, seccion=? WHERE id=?");
            $stmt->bind_param("ssssssssi",$nombre,$email,$password,$rol,$nivel,$grado,$area,$seccion,$id_usuario);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, rol=?, nivel=?, grado=?, area=?, seccion=? WHERE id=?");
            $stmt->bind_param("sssssssi",$nombre,$email,$rol,$nivel,$grado,$area,$seccion,$id_usuario);
        }

        echo $stmt->execute()
            ? "<p class='text-green-600'>✅ Usuario actualizado correctamente.</p>"
            : "<p class='text-red-500'>❌ Error: {$conn->error}</p>";
        $stmt->close();

        // Recargar datos
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id=? AND activo=1");
        $stmt->bind_param("i",$id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $usuario = $res->fetch_assoc();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>✏️ Editar Usuario</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
<div class="max-w-3xl mx-auto">

    <h1 class="text-3xl font-bold text-blue-700 mb-6 text-center">✏️ Editar Usuario</h1>

    <div class="bg-white p-6 rounded-xl shadow mb-6">
        <form method="post" class="space-y-3">
            <input type="hidden" name="editar_usuario" value="1">
            <input type="text" name="nombre" placeholder="Nombre" required class="w-full border rounded p-2" value="<?= htmlspecialchars($usuario['nombre']) ?>">
            <input type="email" name="email" placeholder="Email" required class="w-full border rounded p-2" value="<?= htmlspecialchars($usuario['email']) ?>">
            <input type="password" name="password" placeholder="Nueva contraseña (opcional)" class="w-full border rounded p-2">
            <select name="rol" required class="w-full border rounded p-2">
                <option value="">Selecciona rol</option>
                <?php 
                $roles = ['admin','maestro','estudiante','tutor','auxiliar','secretaria'];
                foreach($roles as $r): ?>
                    <option value="<?= $r ?>" <?= $usuario['rol']==$r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="nivel" placeholder="Nivel (opcional)" class="w-full border rounded p-2" value="<?= htmlspecialchars($usuario['nivel']) ?>">
            <input type="text" name="grado" placeholder="Grado (opcional)" class="w-full border rounded p-2" value="<?= htmlspecialchars($usuario['grado']) ?>">
            <input type="text" name="area" placeholder="Área (opcional)" class="w-full border rounded p-2" value="<?= htmlspecialchars($usuario['area']) ?>">
            <input type="text" name="seccion" placeholder="Sección (opcional)" class="w-full border rounded p-2" value="<?= htmlspecialchars($usuario['seccion']) ?>">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">💾 Guardar Cambios</button>
        </form>
    </div>

    <a href="index_admin.php" class="text-blue-600 hover:underline">⬅ Volver al panel</a>

</div>
</body>
</html>
