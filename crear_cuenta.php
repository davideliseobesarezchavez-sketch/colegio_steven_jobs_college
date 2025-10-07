<?php
session_start();
include 'conexion.php';

// Variables de mensajes
$mensaje = "";
$alerta  = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre    = $_POST['nombre'];
    $apellido  = $_POST['apellido'];
    $fecha_nac = $_POST['fecha_nacimiento'] ?: NULL;
    $email     = $_POST['email'];
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $rol       = $_POST['rol'];
    $nivel     = $_POST['nivel'] ?: NULL;
    $grado     = $_POST['grado'] ?: NULL;
    $area      = $_POST['area'] ?: NULL;
    $seccion   = $_POST['seccion'] ?: NULL;

    // Validar contraseñas
    if ($password !== $confirm) {
        $mensaje = "❌ Las contraseñas no coinciden.";
        $alerta  = "danger";
    } else {
        // Encriptar contraseña
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios 
                (nombre, apellido, fecha_nacimiento, email, password, rol, nivel, grado, area, seccion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss",
            $nombre, $apellido, $fecha_nac, $email, $passwordHash,
            $rol, $nivel, $grado, $area, $seccion
        );

        if ($stmt->execute()) {
            $mensaje = "✅ Cuenta creada correctamente para el usuario: " . htmlspecialchars($nombre);
            $alerta  = "success";
            header("Location: login.php?status=success");
                exit();
        } else {
            $mensaje = "❌ Error al crear la cuenta: " . $stmt->error;
            $alerta  = "danger";
        }

        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colegio Steve Jobs College - Crear Cuenta</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen p-5"
      style="background-image: url('colegio_steven_jobs_college.png'); background-size: cover; background-position: center;">
    <main class="flex flex-col lg:flex-row items-center justify-center w-full max-w-7xl gap-10 lg:gap-16 p-5">

        <!-- Bloque informativo -->
        <div class="flex-1 max-w-xl p-8 lg:p-10 rounded-3xl text-left text-white backdrop-blur-xl shadow-2xl
                     bg-gradient-to-br from-pink-400/40 to-pink-600/40">
            <div class="flex items-center space-x-4 mb-4">
                <img src="colegio steven jobs college.png" alt="colegio steven jobs college" class="mx-auto w-24 h-24 mb-6">
                <h1 class="text-3xl font-light tracking-wide text-yellow-300">Crea tu cuenta en Colegio Steve Jobs College</h1>
            </div>
            <p class="text-lg font-light leading-relaxed text-gray-200">
                Crea tu cuenta para acceder a tu perfil, consultar calificaciones, 
                horarios de clases y recursos educativos personalizados.
            </p>
        </div>

        <!-- Formulario -->
        <div class="flex-1 max-w-lg p-10 rounded-3xl text-black backdrop-blur-md shadow-2xl
                     bg-gradient-to-br from-pink-500/80 via-rose-500/80 to-purple-500/80">
            <img src="usuario.png" alt="Usuario" class="mx-auto w-24 h-24 mb-6">
            <h2 class="text-3xl font-bold mb-6 tracking-wide text-black text-center">Crear nueva cuenta</h2>

            <!-- Mensaje -->
            <?php if (!empty($mensaje)) : ?>
                <div class="mb-4 p-3 rounded-lg text-center font-semibold 
                    <?php echo $alerta === 'success' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-4">

                <div>
                    <label class="block font-bold mb-1 text-sm text-gray-800">Nombre</label>
                    <input type="text" name="nombre" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-sm text-gray-800">Apellido</label>
                    <input type="text" name="apellido" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-sm text-gray-800">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
                </div>

                <div>
                    <label class="block font-bold mb-1 text-sm text-gray-800">Email</label>
                    <input type="email" name="email" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-sm text-gray-800">Contraseña</label>
                    <input type="password" name="password" id="password" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-sm text-gray-800">Confirmar contraseña</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-sm text-gray-800">Rol</label>
                    <select name="rol" id="rol" class="w-full p-3 rounded-lg bg-yellow-300 text-black" required>
                        <option value="">Seleccione un rol</option>
                        <option value="admin">Admin</option>
                        <option value="maestro">Maestro</option>
                        <option value="estudiante">Estudiante</option>
                        <option value="tutor">Tutor</option>
                        <option value="secretaria">Secretaria</option>
                        <option value="auxiliar">Auxiliar</option>
                    </select>
                </div>

                <!-- Campos dinámicos -->
                <div id="campos-nivel" class="hidden">
                    <label class="block font-bold mb-1 text-sm text-gray-800">Nivel</label>
                    <input type="text" name="nivel" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
                </div>

                <div id="campos-grado" class="hidden">
                    <label class="block font-bold mb-1 text-sm text-gray-800">Grado</label>
                    <input type="text" name="grado" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
                </div>

                <div id="campos-area" class="hidden">
                    <label class="block font-bold mb-1 text-sm text-gray-800">Área</label>
                    <input type="text" name="area" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
                </div>

                <div id="campos-seccion" class="hidden">
                    <label class="block font-bold mb-1 text-sm text-gray-800">Sección</label>
                    <input type="text" name="seccion" class="w-full p-3 rounded-lg bg-yellow-300 text-black">
                </div>

                <button type="submit" class="w-full p-3 rounded-lg bg-pink-600 text-black font-bold text-lg cursor-pointer transition-all duration-300 hover:bg-fuchsia-700 transform hover:-translate-y-1 hover:shadow-lg">
                    Crear cuenta
                </button>
            </form>
        </div>
    </main>

    <script> 
        const rolSelect = document.getElementById('rol'); 
        const nivel = document.getElementById('campos-nivel'); 
        const grado = document.getElementById('campos-grado'); 
        const area = document.getElementById('campos-area'); 
        const seccion = document.getElementById('campos-seccion'); 
    
        rolSelect.addEventListener('change', function() { 
            // Ocultar todo por defecto 
            nivel.classList.add('hidden'); 
            grado.classList.add('hidden'); 
            area.classList.add('hidden'); 
            seccion.classList.add('hidden'); 
        
            if (this.value === 'maestro' || this.value === 'tutor') { 
                nivel.classList.remove('hidden'); 
                grado.classList.remove('hidden'); 
                area.classList.remove('hidden'); 
                seccion.classList.remove('hidden'); 
            } else if (this.value === 'estudiante') { 
                nivel.classList.remove('hidden'); 
                grado.classList.remove('hidden'); 
                seccion.classList.remove('hidden'); 
            } 
        }); 
    </script> 
</body> 
</html>