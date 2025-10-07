<?php
session_start();
include '../conexion.php'; // conexión en $conn

$mensaje = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['action'] ?? ''; // "login" o "register"
    $email  = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = "⚠️ Todos los campos son obligatorios.";
    } else {
        if ($accion === 'login') {
            // ======================
            // LOGIN
            // ======================
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();
            
            if (!$usuario) {
                $error = "❌ El usuario no existe.";
            } elseif (!password_verify($password, $usuario['password'])) {
                $error = "❌ Contraseña incorrecta.";
            } else {
                // Sesión iniciada
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol']    = $usuario['rol'];

                // Redirecciones por rol
                switch ($usuario['rol']) {
                    case 'admin':       header("Location: index/index_admin.php"); break;
                    case 'maestro':     header("Location: index/index_maestro.php"); break;
                    case 'estudiante':  header("Location: index/index_estudiante.php"); break;
                    case 'tutor':       header("Location: index/index_tutor.php"); break;
                    case 'secretaria':  header("Location: index/index_secretaria.php"); break;
                    case 'auxiliar':    header("Location: index/index_auxiliar.php"); break;
                    default: 
                        $error = "⚠️ Rol no reconocido.";
                        exit;
                }
                exit;
            }
        } elseif ($accion === 'register') {
            // ======================
            // REGISTRO
            // ======================
            $nombre = trim($_POST['nombre'] ?? '');

            if (!$nombre) {
                $error = "⚠️ Debes ingresar un nombre.";
            } else {
                // Verificar si ya existe el correo
                $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $error = "❌ El correo ya está registrado.";
                } else {
                    // Hashear contraseña
                    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                    // Insertar usuario como estudiante
                    $stmt = $conn->prepare("INSERT INTO usuarios (id, nombre, apellido, email, password, rol) 
                                            VALUES (NULL, ?, '', ?, ?, 'estudiante')");
                    $stmt->bind_param("sss", $nombre, $email, $passwordHash);

                    if ($stmt->execute()) {
                        $mensaje = "✅ Usuario registrado correctamente. <a href='login.php'>Inicia sesión</a>";
                    } else {
                        $error = "❌ Error al registrar: " . $stmt->error;
                    }
                }
            }
        } else {
            $error = "Acción no válida.";
        }
    }
}
?>
