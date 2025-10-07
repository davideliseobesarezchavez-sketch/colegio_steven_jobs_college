<?php
require 'conexion.php';
session_start();
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    
    if (empty($email)) {
        $error = "⚠️ Por favor, introduce tu correo electrónico.";
    } else {
        $mensaje = "✅ Si tu correo está en nuestro sistema, te hemos enviado un enlace para restablecer tu contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Steve Jobs College</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen p-5"
      style="background-image: url('colegio_steven_jobs_college.png'); background-size: cover; background-position: center;">
    <main class="flex flex-col lg:flex-row items-center justify-center w-full max-w-7xl gap-10 lg:gap-16 p-5 box-border">

        <!-- Contenido de Bienvenida -->
        <div class="flex-1 max-w-xl p-8 lg:p-10 rounded-3xl text-left text-white backdrop-blur-xl shadow-2xl
                     bg-gradient-to-br from-pink-400/40 to-pink-600/40">
            <div class="flex items-center space-x-4 mb-4">
                <img src="colegio steven jobs college.png" alt="colegio steven jobs college" class="mx-auto w-24 h-24 mb-6">
                <h1 class="text-3xl font-light tracking-wide text-yellow-300">Restablece tu contraseña</h1>
            </div>
            <p class="text-lg font-light leading-relaxed text-gray-200">
                Ingresa tu correo electrónico y te enviaremos un enlace para que restablezcas tu contraseña de forma segura.
            </p>
        </div>

    <!-- Contenedor principal centrado con gradiente. -->
    <main class="flex items-center justify-center w-full max-w-lg p-10 rounded-3xl text-center text-white backdrop-blur-md shadow-2xl
                 bg-gradient-to-br from-pink-500/80 via-rose-500/80 to-purple-500/80">
        
        <div class="w-full">
            <!-- Icono de usuario para el formulario. -->
            <img src="usuario.png" alt="Usuario" class="mx-auto w-24 h-24 mb-6">
            
            <!-- Título del formulario. -->
            <h2 class="text-3xl font-bold mb-6 tracking-wide text-white">Recuperar Contraseña</h2>
            
            <!-- Contenedor del formulario de email (Paso 1). -->
            <div id="email-form-container">
                <!-- Mensaje de instrucción para el usuario. -->
                <p class="text-white text-sm mb-6">Ingresa tu correo para recibir un enlace de recuperación.</p>

                <!-- Muestra el mensaje de éxito o error si la validación del servidor falla. -->
                <?php if (!empty($mensaje)): ?>
                    <p class="text-green-300 font-bold mb-4"><?php echo $mensaje; ?></p>
                <?php elseif (!empty($error)): ?>
                    <p class="text-red-300 font-bold mb-4"><?php echo $error; ?></p>
                <?php endif; ?>
                
                <!-- Formulario de recuperación de contraseña (email). -->
                <form id="email-form" class="w-full max-w-sm mx-auto">
                    <!-- Campo de Correo Electrónico. -->
                    <div class="mb-5 text-left">
                        <label for="email" class="block font-bold mb-2 text-sm text-white">Correo</label>
                        <input type="email" name="email" id="email" placeholder="Ingresa tu correo"
                               class="w-full p-3 rounded-lg border-none text-gray-900 bg-yellow-300 placeholder:text-gray-900 focus:outline-none focus:ring-4 focus:ring-yellow-400 transition-all duration-300" required>
                    </div>
                    
                    <!-- Botón para enviar el formulario. -->
                    <button type="submit"
                            class="w-full p-3 mt-4 rounded-lg bg-pink-600 text-white font-bold text-lg cursor-pointer transition-all duration-300 hover:bg-fuchsia-700 transform hover:-translate-y-1 hover:shadow-lg">
                        Enviar Enlace
                    </button>
                </form>
            </div>

            <!-- Contenedor del formulario de cambio de contraseña (Paso 2). Inicialmente oculto. -->
            <div id="password-form-container" class="hidden">
                <p class="text-white text-sm mb-6">Ingresa tu nueva contraseña y confírmala.</p>

                <!-- Mensaje para el formulario de contraseña. -->
                <p id="password-message" class="font-bold mb-4"></p>

                <form id="password-reset-form" class="w-full max-w-sm mx-auto">
                    <!-- Campo para la Nueva Contraseña. -->
                    <div class="mb-5 text-left">
                        <label for="new-password" class="block font-bold mb-2 text-sm text-white">Nueva Contraseña</label>
                        <input type="password" name="new-password" id="new-password" placeholder="Ingresa tu nueva contraseña"
                               class="w-full p-3 rounded-lg border-none text-gray-900 bg-yellow-300 placeholder:text-gray-900 focus:outline-none focus:ring-4 focus:ring-yellow-400 transition-all duration-300" required>
                    </div>
                    
                    <!-- Campo para Confirmar Contraseña. -->
                    <div class="mb-5 text-left">
                        <label for="confirm-password" class="block font-bold mb-2 text-sm text-white">Confirmar Contraseña</label>
                        <input type="password" name="confirm-password" id="confirm-password" placeholder="Confirma tu nueva contraseña"
                               class="w-full p-3 rounded-lg border-none text-gray-900 bg-yellow-300 placeholder:text-gray-900 focus:outline-none focus:ring-4 focus:ring-yellow-400 transition-all duration-300" required>
                    </div>
                    
                    <!-- Botón para cambiar la contraseña. -->
                    <button type="submit"
                            class="w-full p-3 mt-4 rounded-lg bg-pink-600 text-white font-bold text-lg cursor-pointer transition-all duration-300 hover:bg-fuchsia-700 transform hover:-translate-y-1 hover:shadow-lg">
                        Cambiar Contraseña
                    </button>
                </form>
            </div>

            <!-- Enlaces de navegación a otras páginas. -->
            <div class="flex justify-between max-w-sm mx-auto mt-6 text-sm">
                <a href="login.php" class="text-white hover:text-gray-300 transition-colors duration-300">Iniciar Sesión</a>
                <a href="registro.php" class="text-white hover:text-gray-300 transition-colors duration-300">Registrarse</a>
            </div>
        </div>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const emailForm = document.getElementById('email-form');
            const passwordFormContainer = document.getElementById('password-form-container');
            const emailFormContainer = document.getElementById('email-form-container');
            const passwordResetForm = document.getElementById('password-reset-form');
            const passwordMessage = document.getElementById('password-message');

            // Maneja el envío del formulario de correo electrónico
            emailForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const emailInput = document.getElementById('email');
                if (emailInput.value) {
                    // Simula el éxito, esconde el formulario de email y muestra el de contraseña
                    emailFormContainer.classList.add('hidden');
                    passwordFormContainer.classList.remove('hidden');
                }
            });

            // Maneja el envío del formulario de cambio de contraseña
            passwordResetForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const newPassword = document.getElementById('new-password').value;
                const confirmPassword = document.getElementById('confirm-password').value;

                // Validación simple para asegurar que las contraseñas coinciden.
                if (newPassword === confirmPassword) {
                    passwordMessage.textContent = "✅ Contraseña cambiada exitosamente. Redirigiendo a la página de inicio de sesión...";
                    passwordMessage.classList.add('text-green-300');
                    passwordMessage.classList.remove('text-red-300');

                    // Redirecciona después de 2 segundos.
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000); // 2000 milisegundos = 2 segundos
                    
                } else {
                    passwordMessage.textContent = "❌ Las contraseñas no coinciden.";
                    passwordMessage.classList.add('text-red-300');
                    passwordMessage.classList.remove('text-green-300');
                }
            });
        });
    </script>
</body>
</html>
