<?php
session_start();
include '../conexion.php';

if(!isset($_SESSION['id']) || $_SESSION['rol']!="estudiante"){ 
    header("Location: ../login.php"); exit(); 
}

if(isset($_GET['id'])){
    $id_examen = $_GET['id'];
    $examen = $conn->query("SELECT * FROM examenes WHERE id='$id_examen'")->fetch_assoc();
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $id_examen = $_POST["id_examen"];
    $respuestas = $_POST["respuestas"];
    $id_estudiante = $_SESSION['id'];

    $stmt = $conn->prepare("INSERT INTO respuestas_examen (id_examen, id_estudiante, respuestas) VALUES (?,?,?)");
    $stmt->bind_param("iis", $id_examen, $id_estudiante, $respuestas);
    $stmt->execute();

    echo "✅ Respuestas enviadas correctamente.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📖 Hacer Examen</title>
</head>
<body>
<h2>📖 <?= $examen['titulo']; ?></h2>
<p><?= $examen['descripcion']; ?></p>
<form method="POST">
    <input type="hidden" name="id_examen" value="<?= $examen['id']; ?>">
    <textarea name="respuestas" placeholder="Escribe tus respuestas aquí" required></textarea><br><br>
    <button type="submit">📤 Enviar Examen</button>
</form>
</body>
</html>
