<?php
session_start();
require '../conexion.php';

/* ================== CONFIG SMTP GMAIL ================== */
const MAIL_HOST   = 'smtp.gmail.com';
const MAIL_USER   = 'TU_CORREO@gmail.com';     // 👉 Cambia esto por tu Gmail institucional
const MAIL_PASS   = 'TU_CONTRASENA_APP_16C';   // 👉 Contraseña de aplicación (16 dígitos)
const MAIL_PORT   = 587;                       // 465 si usas SSL
const MAIL_SECURE = 'tls';                     // 'tls' o 'ssl'
const DEBUG_SMTP  = false;                     // true para depurar
/* ======================================================== */

/* ================== PHPMailer sin Composer ================== */
require __DIR__ . '/../phpmailer/vendor/phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/vendor/phpmailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
/* ============================================================ */

/* ========== Verificar sesión ========== */
if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
    header('Location: ../login.php');
    exit;
}

/* Variables básicas */
$id_usuario = $_SESSION['id'];
$rol        = $_SESSION['rol'];
$nombre     = $_SESSION['nombre'] ?? 'Usuario';
$feedback   = '';

/* ========== Personalización según rol ========== */
switch ($rol) {
    case 'maestro':
        $titulo_pagina = "📩 Contactar a Padres";
        $descripcion   = "Envía un correo sobre un estudiante de tu grupo.";
        break;
    case 'tutor':
        $titulo_pagina = "📩 Contactar con Maestro o Secretaría";
        $descripcion   = "Envía una consulta o comentario institucional.";
        break;
    case 'secretaria':
        $titulo_pagina = "📩 Enviar Comunicados";
        $descripcion   = "Envía información a tutores o docentes.";
        break;
    case 'auxiliar':
        $titulo_pagina = "📩 Notificar Actividades";
        $descripcion   = "Envía avisos de apoyo o coordinación.";
        break;
    case 'admin':
        $titulo_pagina = "📩 Notificar Usuarios";
        $descripcion   = "Envía mensajes a cualquier usuario del sistema.";
        break;
    default:
        $titulo_pagina = "📩 Centro de Mensajes";
        $descripcion   = "Envío de correos institucionales.";
}

/* ========== Obtener lista de destinatarios según rol ========== */
if ($rol === 'maestro') {
    $sql = "SELECT id, nombre FROM usuarios WHERE rol='estudiante' ORDER BY nombre";
} elseif ($rol === 'secretaria') {
    $sql = "SELECT id, nombre FROM usuarios WHERE rol IN ('tutor','maestro') ORDER BY nombre";
} elseif ($rol === 'tutor') {
    $sql = "SELECT id, nombre FROM usuarios WHERE rol IN ('maestro','secretaria') ORDER BY nombre";
} elseif ($rol === 'auxiliar') {
    $sql = "SELECT id, nombre FROM usuarios WHERE rol IN ('maestro','secretaria') ORDER BY nombre";
} elseif ($rol === 'admin') {
    $sql = "SELECT id, nombre FROM usuarios WHERE rol!='admin' ORDER BY nombre";
} else {
    $sql = "SELECT id, nombre FROM usuarios WHERE id = 0"; // vacío
}
$destinatarios = $conn->query($sql);

/* ========== Envío del formulario ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_destinatario = $_POST['id_destinatario'] ?? '';
    $mensaje         = trim($_POST['mensaje'] ?? '');

    $sqlU = "SELECT nombre, correo FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sqlU);
    $stmt->bind_param("i", $id_destinatario);
    $stmt->execute();
    $resU = $stmt->get_result();
    $destData = $resU->fetch_assoc();

    if (!$destData) {
        $feedback = "❌ Destinatario no encontrado.";
    } elseif ($mensaje === '') {
        $feedback = '❌ Escribe un mensaje.';
    } else {
        $destinatario = $destData['nombre'];
        $correoDestino = $destData['correo'] ?: MAIL_USER;

        try {
            $mail = new PHPMailer(true);

            if (DEBUG_SMTP) {
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = 'html';
            }
            $mail->CharSet = 'UTF-8';

            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = MAIL_SECURE;
            $mail->Port       = MAIL_PORT;

            $mail->setFrom(MAIL_USER, ucfirst($rol).' '.$nombre);
            $mail->addAddress($correoDestino, $destinatario);

            $mail->isHTML(true);
            $mail->Subject = "📩 Mensaje de ".ucfirst($rol)." ".$nombre;
            $mail->Body = "
                <div style='font-family:Arial,sans-serif;color:#111'>
                    <p><strong>Estimado(a) {$destinatario}:</strong></p>
                    <p>".nl2br(htmlspecialchars($mensaje))."</p>
                    <br>
                    <p>Atentamente,<br><strong>".ucfirst($rol)." {$nombre}</strong></p>
                </div>
            ";
            $mail->AltBody = "Estimado(a) {$destinatario}:\n\n{$mensaje}\n\nAtentamente,\n{$rol} {$nombre}";

            $mail->send();
            $feedback = "✅ Mensaje enviado correctamente a {$destinatario}.";
        } catch (Throwable $e) {
            $feedback = '❌ Error de envío: '.$e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($titulo_pagina) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-6">
  <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-2xl border border-gray-200">
    <h1 class="text-3xl font-extrabold text-indigo-700 text-center mb-2"><?= $titulo_pagina ?></h1>
    <p class="text-center text-gray-600 mb-6"><?= $descripcion ?></p>

    <?php if ($feedback): ?>
      <div class="mb-5 p-4 rounded-lg font-medium <?= strpos($feedback,'✅')!==false ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300' ?>">
        <?= $feedback ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
      <div>
        <label class="block text-gray-700 font-semibold mb-1">👤 Seleccionar destinatario</label>
        <select name="id_destinatario" required
                class="w-full border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 p-3">
          <option value="">-- Selecciona --</option>
          <?php if ($destinatarios) while ($row = $destinatarios->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nombre']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div>
        <label class="block text-gray-700 font-semibold mb-1">✍️ Mensaje</label>
        <textarea name="mensaje" rows="5" placeholder="Escribe tu mensaje aquí..." required
                  class="w-full border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-400 p-3"></textarea>
      </div>

      <button type="submit"
              class="w-full bg-indigo-600 text-white py-3 rounded-lg shadow-lg hover:bg-indigo-700 transition font-semibold">
        📤 Enviar Mensaje
      </button>

      <p class="text-xs text-gray-500 text-center mt-3">
        Consejo: usa lenguaje claro y respetuoso. El mensaje será enviado al correo institucional del destinatario.
      </p>
    </form>
  </div>
</body>
</html>
