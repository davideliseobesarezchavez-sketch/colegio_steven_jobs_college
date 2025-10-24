<?php
session_start();
include 'conexion.php';

$mensaje = "";

// --- Variables para mantener valores si hay error ---
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$rol = $_POST['rol'] ?? '';
$nivel = $_POST['nivel'] ?? '';
$grado = $_POST['grado'] ?? '';
$seccion = $_POST['seccion'] ?? '';
$area = trim($_POST['area'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

// --- Procesar formulario ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($confirm) || empty($rol)) {
        $mensaje = "⚠️ Todos los campos obligatorios deben ser llenados.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "❌ El correo electrónico no es válido.";
    } elseif ($password !== $confirm) {
        $mensaje = "❌ Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $mensaje = "❌ La contraseña debe tener al menos 6 caracteres.";
    } else {
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email=? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $mensaje = "⚠️ El correo ya está registrado. Por favor, inicia sesión.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (nombre, apellido, email, password, rol, nivel, grado, seccion, area)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $nombre, $apellido, $email, $hashed, $rol, $nivel, $grado, $seccion, $area);

            if ($stmt->execute()) {
                header("Location: login.php?success=1");
                exit();
            } else {
                $mensaje = "❌ Error al registrar usuario: " . $stmt->error;
            }
            $stmt->close();
        }
        $check->close();
    }

    // --- Estilo unificado para mensajes ---
    if (!empty($mensaje)) {
        if (strpos($mensaje, '❌') !== false) {
            $mensaje = "<p class='text-red-600 font-bold text-center mb-4'>$mensaje</p>";
        } elseif (strpos($mensaje, '⚠️') !== false) {
            $mensaje = "<p class='text-orange-500 font-bold text-center mb-4'>$mensaje</p>";
        } else {
            $mensaje = "<p class='text-green-600 font-bold text-center mb-4'>$mensaje</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro - Steve Jobs College</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen p-5"
      style="background-image: url('colegio_steven_jobs_college.png'); background-size: cover; background-position: center;">
    <main class="flex flex-col lg:flex-row items-center justify-center w-full max-w-7xl gap-10 lg:gap-16 p-5">

    <!-- Panel de bienvenida -->
    <div class="flex-1 max-w-xl p-8 lg:p-10 rounded-3xl text-left text-white backdrop-blur-xl shadow-2xl
                bg-gradient-to-br from-blue-400/40 to-indigo-600/40">
        <div class="flex items-center space-x-4 mb-4">
            <img src="colegio steven jobs college.png" alt="logo colegio" class="w-20 h-20">
            <h1 class="text-3xl font-light tracking-wide text-yellow-300">Bienvenido a Steve Jobs College</h1>
        </div>
        <p class="text-lg font-light text-gray-200">
            Regístrate para acceder a tu perfil, consultar calificaciones, horarios de clases y mucho más.
        </p>
    </div>

    <!-- Formulario de Registro -->
    <div class="flex-1 max-w-lg p-10 rounded-3xl text-black backdrop-blur-md shadow-2xl
                bg-gradient-to-br from-blue-500/80 via-indigo-500/80 to-purple-500/80">

        <img src="usuario.png" alt="Registro" class="mx-auto w-24 h-24 mb-6">
        <h2 class="text-3xl font-bold mb-6 text-center text-white">Registro de Usuario</h2>

        <?= $mensaje ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block font-bold text-sm text-white">Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" 
                       class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
            </div>

            <div>
                <label class="block font-bold text-sm text-white">Apellido</label>
                <input type="text" name="apellido" value="<?= htmlspecialchars($apellido) ?>" 
                       class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
            </div>

            <div>
                <label class="block font-bold text-sm text-white">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" 
                       class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
            </div>

            <div>
                <label class="block font-bold text-sm text-white">Contraseña</label>
                <input type="password" name="password" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
            </div>

            <div>
                <label class="block font-bold text-sm text-white">Confirmar Contraseña</label>
                <input type="password" name="confirm_password" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
            </div>

            <div>
                <label class="block font-bold text-sm text-white">Rol</label>
                <select name="rol" id="rol" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
                    <option value="">Seleccione un rol</option>
                    <option value="estudiante" <?= $rol==="estudiante"?"selected":"" ?>>Estudiante</option>
                    <option value="maestro" <?= $rol==="maestro"?"selected":"" ?>>Maestro</option>
                    <option value="tutor" <?= $rol==="tutor"?"selected":"" ?>>Tutor</option>
                    <option value="admin" <?= $rol==="admin"?"selected":"" ?>>Admin</option>
                    <option value="secretaria" <?= $rol==="secretaria"?"selected":"" ?>>Secretaria</option>
                    <option value="auxiliar" <?= $rol==="auxiliar"?"selected":"" ?>>Auxiliar</option>
                </select>
            </div>

            <!-- Campos dinámicos -->
            <div id="campos-nivel" class="hidden">
                <label class="block font-bold text-sm text-white">Nivel</label>
                <input type="text" name="nivel" value="<?= htmlspecialchars($nivel) ?>" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
            </div>

            <div id="campos-grado" class="hidden">
                <label class="block font-bold text-sm text-white">Grado</label>
                <input type="text" name="grado" value="<?= htmlspecialchars($grado) ?>" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
            </div>

            <div id="campos-seccion" class="hidden">
                <label class="block font-bold text-sm text-white">Sección</label>
                <input type="text" name="seccion" value="<?= htmlspecialchars($seccion) ?>" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
            </div>

            <div id="campos-area" class="hidden">
                <label class="block font-bold text-sm text-white">Área</label>
                <input type="text" name="area" value="<?= htmlspecialchars($area) ?>" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
            </div>

            <button type="submit" class="w-full p-3 rounded-lg bg-blue-600 text-white font-bold text-lg hover:bg-blue-700 transition-all">
                Registrarse
            </button>

            <div class="text-center mt-4">
                <a href="login" class="text-white font-semibold underline hover:text-yellow-200">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
        </form>
    </div>
</main>

<script>
const rolSelect = document.getElementById('rol');
const nivel = document.getElementById('campos-nivel');
const grado = document.getElementById('campos-grado');
const seccion = document.getElementById('campos-seccion');
const area = document.getElementById('campos-area');

rolSelect.addEventListener('change', function() {
    nivel.classList.add('hidden');
    grado.classList.add('hidden');
    seccion.classList.add('hidden');
    area.classList.add('hidden');

    if (this.value === 'estudiante') {
        nivel.classList.remove('hidden');
        grado.classList.remove('hidden');
        seccion.classList.remove('hidden');
    } else if (this.value === 'maestro' || this.value === 'tutor') {
        nivel.classList.remove('hidden');
        grado.classList.remove('hidden');
        seccion.classList.remove('hidden');
        area.classList.remove('hidden');
    }
});
</script>
</body>
</html>
