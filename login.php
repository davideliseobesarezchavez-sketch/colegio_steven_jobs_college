<?php
require 'conexion.php';
session_start();

$mensaje = '';
$error = '';

// Si viene desde registro con éxito
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $mensaje = "✅ Registro exitoso. Ahora inicia sesión.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "⚠️ Ingresa tu correo y contraseña.";
    } else {
        $stmt = $conn->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email=? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['id'] = $user['id'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['rol'] = $user['rol'];

                switch ($user['rol']) {
                    case 'admin': header("Location: ../index/index_admin.php"); break;
                    case 'maestro': header("Location: ../index/index_maestro.php"); break;
                    case 'estudiante': header("Location: ../index/index_estudiante.php"); break;
                    case 'tutor': header("Location: ../index/index_tutor.php"); break;
                    case 'secretaria': header("Location: ../index/index_secretaria.php"); break;
                    default: header("Location: ../index/index_auxiliar.php"); break;
                }
                exit();
            } else {
                $error = "❌ Contraseña incorrecta.";
            }
        } else {
            $error = "❌ El correo no está registrado.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Steve Jobs College</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen p-5"
      style="background-image: url('colegio_steven_jobs_college.png'); background-size: cover; background-position: center;">
    <main class="flex flex-col lg:flex-row items-center justify-center w-full max-w-7xl gap-10 lg:gap-16 p-5 box-border">

        <!-- Panel informativo -->
        <div class="flex-1 max-w-xl p-8 lg:p-10 rounded-3xl text-left text-white backdrop-blur-xl shadow-2xl
                     bg-gradient-to-br from-blue-400/40 to-indigo-600/40">
            <div class="flex items-center space-x-4 mb-4">
                <img src="colegio steven jobs college.png" alt="colegio steven jobs college" class="w-20 h-20">
                <h1 class="text-3xl font-light tracking-wide text-yellow-300">Bienvenido a Steve Jobs College</h1>
            </div>
            <p class="text-lg font-light text-gray-200">
                Accede a tu cuenta según tu rol para gestionar clases, tareas, asistencias y mucho más.
            </p>
        </div>

        <!-- Panel del formulario -->
        <div class="flex-1 max-w-lg p-10 rounded-3xl text-black backdrop-blur-md shadow-2xl
                     bg-gradient-to-br from-blue-500/80 via-indigo-500/80 to-purple-500/80">

            <img src="usuario.png" alt="Login" class="mx-auto w-24 h-24 mb-6">
            <h2 class="text-3xl font-bold mb-6 text-center text-white">Iniciar Sesión</h2>

            <?php if (!empty($mensaje)): ?>
                <p class="text-green-300 font-bold mb-4 text-center"><?= $mensaje; ?></p>
            <?php elseif (!empty($error)): ?>
                <p class="text-red-300 font-bold mb-4 text-center"><?= $error; ?></p>
            <?php endif; ?>

            <form action="" method="POST" class="w-full max-w-sm mx-auto space-y-5">

                <div class="text-left">
                    <label class="block font-bold mb-2 text-sm text-white">Correo Electrónico</label>
                    <input type="email" name="email" placeholder="Ingresa tu correo"
                        class="w-full p-3 rounded-lg bg-yellow-300 text-gray-900 placeholder-gray-900 focus:outline-none focus:ring-4 focus:ring-yellow-400 transition-all duration-300" required>
                </div>

                <div class="text-left">
                    <label class="block font-bold mb-2 text-sm text-white">Contraseña</label>
                    <input type="password" name="password" placeholder="Ingresa tu contraseña"
                        class="w-full p-3 rounded-lg bg-yellow-300 text-gray-900 placeholder-gray-900 focus:outline-none focus:ring-4 focus:ring-yellow-400 transition-all duration-300" required>
                </div>

                <button type="submit"
                        class="w-full p-3 mt-4 rounded-lg bg-blue-600 text-white font-bold text-lg cursor-pointer transition-all duration-300 hover:bg-blue-700 transform hover:-translate-y-1 hover:shadow-lg">
                    Ingresar
                </button>

                <div class="flex justify-center items-center mt-8 space-x-10 text-sm text-white">
                    <a href="crear_cuenta" class="hover:text-yellow-200 hover:underline transition-all duration-200">Crear cuenta</a>
                    <a href="recuperar_contrasena" class="hover:text-yellow-200 hover:underline transition-all duration-200">¿Olvidaste tu contraseña?</a>
                    <a href="registro" class="hover:text-yellow-300 hover:underline transition-all duration-200">Registrarse</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
