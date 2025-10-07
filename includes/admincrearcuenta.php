<?php
// Inicia la sesión
session_start();
include '../conexion.php';

$mensaje = "";

// Variables para mantener valores si hay error
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$rol = $_POST['rol'] ?? '';
$nivel = $_POST['nivel'] ?? '';
$grado = $_POST['grado'] ?? '';
$seccion = $_POST['seccion'] ?? '';
$area = trim($_POST['area'] ?? '');
$password = $_POST['password'] ?? '';

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($rol)) {
        $mensaje = "⚠️ Todos los campos obligatorios deben ser llenados.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "❌ El correo electrónico no es válido.";
    } elseif (strlen($password) < 6) {
        $mensaje = "❌ La contraseña debe tener al menos 6 caracteres.";
    } else {
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email=? LIMIT 1");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $mensaje = "⚠️ El correo ya está registrado.";
            $check->close();
        } else {
            $check->close();
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios (nombre, apellido, email, password, rol, nivel, grado, seccion, area)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $nombre, $apellido, $email, $hashed, $rol, $nivel, $grado, $seccion, $area);

            if ($stmt->execute()) {
                $mensaje = "✅ Cuenta creada correctamente para: " . htmlspecialchars($nombre);
            } else {
                $mensaje = "❌ Error al crear la cuenta: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    if (!empty($mensaje) && strpos($mensaje, '❌') !== false) {
        $mensaje = "<span class='text-red-600 font-bold'>$mensaje</span>";
    } elseif (!empty($mensaje) && strpos($mensaje, '⚠️') !== false) {
        $mensaje = "<span class='text-orange-600 font-bold'>$mensaje</span>";
    } elseif (!empty($mensaje) && strpos($mensaje, '✅') !== false) {
        $mensaje = "<span class='text-green-600 font-bold'>$mensaje</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen p-5"
      style="background-image: url('admin_dashboard.png'); background-size: cover; background-position: center;">

    <div class="w-full max-w-lg p-10 rounded-3xl text-black backdrop-blur-md shadow-2xl
                bg-gradient-to-br from-indigo-500/80 via-purple-500/80 to-pink-500/80">
        
        <h2 class="text-3xl font-bold mb-6 text-center text-black">Crear Cuenta de Usuario</h2>

        <?php if (!empty($mensaje)) : ?>
            <div class="mb-4 p-3 rounded-lg text-center bg-white/70">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block font-bold text-sm text-gray-800">Nombre</label>
                <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>" 
                       class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
            </div>

            <div>
                <label class="block font-bold text-sm text-gray-800">Apellido</label>
                <input type="text" name="apellido" value="<?= htmlspecialchars($apellido) ?>" 
                       class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
            </div>

            <div>
                <label class="block font-bold text-sm text-gray-800">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" 
                       class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
            </div>

            <div>
                <label class="block font-bold text-sm text-gray-800">Contraseña</label>
                <input type="password" name="password" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
            </div>

            <div>
                <label class="block font-bold text-sm text-gray-800">Rol</label>
                <select name="rol" id="rol" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
                    <option value="">Seleccione un rol</option>
                    <option value="admin" <?= $rol==="admin"?"selected":"" ?>>Admin</option>
                    <option value="maestro" <?= $rol==="maestro"?"selected":"" ?>>Maestro</option>
                    <option value="estudiante" <?= $rol==="estudiante"?"selected":"" ?>>Estudiante</option>
                    <option value="tutor" <?= $rol==="tutor"?"selected":"" ?>>Tutor</option>
                    <option value="secretaria" <?= $rol==="secretaria"?"selected":"" ?>>Secretaria</option>
                    <option value="auxiliar" <?= $rol==="auxiliar"?"selected":"" ?>>Auxiliar</option>
                </select>
            </div>

            <!-- Campos dinámicos -->
            <div id="campos-nivel" class="hidden">
                <label class="block font-bold text-sm text-gray-800">Nivel</label>
                <input type="text" name="nivel" value="<?= htmlspecialchars($nivel) ?>" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
            </div>

            <div id="campos-grado" class="hidden">
                <label class="block font-bold text-sm text-gray-800">Grado</label>
                <input type="text" name="grado" value="<?= htmlspecialchars($grado) ?>" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
            </div>

            <div id="campos-seccion" class="hidden">
                <label class="block font-bold text-sm text-gray-800">Sección</label>
                <input type="text" name="seccion" value="<?= htmlspecialchars($seccion) ?>" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
            </div>

            <div id="campos-area" class="hidden">
                <label class="block font-bold text-sm text-gray-800">Área</label>
                <input type="text" name="area" value="<?= htmlspecialchars($area) ?>" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
            </div>

            <button type="submit" class="w-full p-3 rounded-lg bg-indigo-600 text-white font-bold text-lg hover:bg-indigo-700 transition-all">
                Crear Cuenta
            </button>
        </form>
    </div>

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