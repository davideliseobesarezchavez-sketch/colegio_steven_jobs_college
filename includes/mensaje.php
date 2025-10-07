<?php
session_start();
include '../conexion.php';

if(!isset($_SESSION['id'])){ 
    header("Location: ../login.php"); exit(); 
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $id_receptor = $_POST["id_receptor"];
    $asunto = $_POST["asunto"];
    $mensaje = $_POST["mensaje"];
    $id_emisor = $_SESSION['id'];

    $stmt = $conn->prepare("INSERT INTO mensajes (id_emisor, id_receptor, asunto, mensaje) VALUES (?,?,?,?)");
    $stmt->bind_param("iiss", $id_emisor, $id_receptor, $asunto, $mensaje);
    $stmt->execute();
}

// Lista de usuarios para enviar mensajes
$usuarios = $conn->query("SELECT id, nombre FROM usuarios WHERE id!='".$_SESSION['id']."'");

// Mensajes recibidos
$recibidos = $conn->query("SELECT m.*, u.nombre as emisor FROM mensajes m JOIN usuarios u ON m.id_emisor=u.id WHERE m.id_receptor='".$_SESSION['id']."' ORDER BY fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>💬 Mensajes</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f9fafb;
        margin: 0;
        padding: 20px;
    }
    h2, h3 {
        text-align: center;
        color: #2563eb;
        margin-bottom: 20px;
    }
    form {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        max-width: 600px;
        margin: 0 auto 30px auto;
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    }
    select, input, textarea, button {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 14px;
    }
    button {
        background: #2563eb;
        color: white;
        border: none;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
    }
    button:hover {
        background: #1d4ed8;
    }
    .mensaje {
        background: #ffffff;
        border-radius: 10px;
        padding: 15px;
        margin: 15px auto;
        max-width: 700px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .mensaje b {
        color: #111827;
    }
    .fecha {
        font-size: 13px;
        color: #6b7280;
        margin-top: 5px;
        display: block;
    }
</style>
</head>
<body>

<h2>💬 Enviar Mensaje</h2>
<form method="POST">
    <select name="id_receptor" required>
        <option value="">Selecciona destinatario</option>
        <?php while($u=$usuarios->fetch_assoc()): ?>
        <option value="<?= $u['id']; ?>"><?= htmlspecialchars($u['nombre']); ?></option>
        <?php endwhile; ?>
    </select>
    <input type="text" name="asunto" placeholder="Asunto">
    <textarea name="mensaje" placeholder="Escribe el mensaje" rows="4" required></textarea>
    <button type="submit">📤 Enviar</button>
</form>

<h3>📥 Mensajes Recibidos</h3>
<?php while($m=$recibidos->fetch_assoc()): ?>
<div class="mensaje">
    <b>De:</b> <?= htmlspecialchars($m['emisor']); ?><br>
    <b>Asunto:</b> <?= htmlspecialchars($m['asunto']); ?><br><br>
    <?= nl2br(htmlspecialchars($m['mensaje'])); ?><br>
    <span class="fecha">📅 <?= $m['fecha']; ?></span>
</div>
<?php endwhile; ?>

</body>
</html>
