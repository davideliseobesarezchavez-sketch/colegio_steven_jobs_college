<?php
session_start();
include '../conexion.php';

// 🔒 Verificar que el usuario sea estudiante
if(!isset($_SESSION['id']) || $_SESSION['rol']!="estudiante"){ 
    header("Location: ../login.php"); 
    exit(); 
}

$id_estudiante = $_SESSION['id'];

// 📌 Consultar los logros del estudiante logueado
$stmt = $conn->prepare("SELECT logro, fecha FROM logros WHERE id_estudiante = ? ORDER BY fecha DESC");
$stmt->bind_param("i", $id_estudiante);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🎓 Mis Logros</title>
<style>
body { font-family: Arial; background:#f0f9ff; padding:20px; }
h2 { color:#1d4ed8; }
.card { background:#dbeafe; padding:15px; margin:10px 0; border-radius:10px; }
</style>
</head>
<body>
<h2>🎓 Bienvenido <?= $_SESSION['nombre']; ?> </h2>
<h3>🏆 Mis Logros Registrados</h3>

<?php if($result->num_rows > 0): ?>
    <?php while($l = $result->fetch_assoc()): ?>
        <div class="card">
            🏆 <?= $l['logro']; ?><br>
            📅 <?= $l['fecha']; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>❌ Aún no tienes logros registrados.</p>
<?php endif; ?>

<a href="../index/index_estudiante.php">⬅️ Volver al Panel</a>
</body>
</html>
