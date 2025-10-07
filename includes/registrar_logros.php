<?php
session_start();
include '../conexion.php';
if(!isset($_SESSION['id']) || $_SESSION['rol']!="maestro"){ 
    header("Location: ../login.php"); exit(); }

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $id_estudiante = $_POST["id_estudiante"];
    $logro = $_POST["logro"];
    $conn->query("INSERT INTO logros (id_estudiante, logro, fecha) VALUES ('$id_estudiante','$logro',NOW())");
}

$estudiantes = $conn->query("SELECT id,nombre FROM usuarios WHERE rol='estudiante'");
$logros = $conn->query("SELECT l.*, u.nombre FROM logros l JOIN usuarios u ON l.id_estudiante=u.id ORDER BY l.fecha DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🏆 Logros</title>
<style>
body { font-family: Arial; background:#faf5ff; padding:20px; }
form { background:#e9d5ff; padding:20px; border-radius:10px; margin-bottom:20px; }
.card { background:#f3e8ff; padding:15px; margin:10px; border-radius:10px; }
</style>
</head>
<body>
<h2>🏆 Registrar Logros</h2>
<form method="POST">
    <select name="id_estudiante" required>
        <option value="">Selecciona estudiante</option>
        <?php while($e=$estudiantes->fetch_assoc()): ?>
        <option value="<?= $e['id']; ?>"><?= $e['nombre']; ?></option>
        <?php endwhile; ?>
    </select>
    <input type="text" name="logro" placeholder="Descripción del logro" required>
    <button type="submit">➕ Registrar Logro</button>
</form>
<h3>📋 Logros Registrados</h3>
<?php while($l=$logros->fetch_assoc()): ?>
<div class="card">
    👨‍🎓 <?= $l['nombre']; ?> <br>
    🏆 <?= $l['logro']; ?><br>
    📅 <?= $l['fecha']; ?>
</div>
<?php endwhile; ?>
</body>
</html>
