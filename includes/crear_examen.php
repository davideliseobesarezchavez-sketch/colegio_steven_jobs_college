<?php
session_start();
include '../conexion.php';
if(!isset($_SESSION['id']) || $_SESSION['rol']!="maestro"){
    header("Location: ../login.php"); exit(); }

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $titulo = $_POST["titulo"];
    $descripcion = $_POST["descripcion"];
    $id_maestro = $_SESSION['id'];

    $sql = "INSERT INTO examenes (titulo, descripcion, id_maestro) VALUES ('$titulo','$descripcion','$id_maestro')";
    $conn->query($sql);
    $mensaje = "✅ Examen creado con éxito.";
}

$result = $conn->query("SELECT * FROM examenes WHERE id_maestro='".$_SESSION['id']."'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📄 Crear Examen</title>
<style>
body { font-family: Arial; background:#f0f9ff; padding:20px; }
form { background:#bae6fd; padding:20px; border-radius:10px; margin-bottom:20px; }
input, textarea, button { width:100%; margin:10px 0; padding:10px; border-radius:8px; border:1px solid #ccc; }
button { background:#0284c7; color:white; border:none; cursor:pointer; }
button:hover { background:#0369a1; }
.card { background:#e0f2fe; padding:15px; margin-bottom:10px; border-radius:10px; }
</style>
</head>
<body>
<h2>📄 Crear Examen</h2>
<form method="POST">
    <input type="text" name="titulo" placeholder="Título del examen" required>
    <textarea name="descripcion" placeholder="Descripción / instrucciones" required></textarea>
    <button type="submit">Crear Examen</button>
</form>
<?php if(isset($mensaje)) echo "<p>$mensaje</p>"; ?>

<h3>📋 Exámenes creados</h3>
<?php while($row=$result->fetch_assoc()): ?>
<div class="card">
    <b><?= $row['titulo']; ?></b><br>
    <?= $row['descripcion']; ?><br>
    📅 <?= $row['fecha']; ?><br>
    <a href="revisar_examenes.php?id=<?= $row['id']; ?>">✏️ Revisar respuestas</a>
</div>
<?php endwhile; ?>
</body>
</html>
