<?php
session_start();
// Attempts to connect to the database using the file one directory up
include '../conexion.php'; 

// === SECURITY CHECK: Only students can access this page ===
if(!isset($_SESSION['id']) || $_SESSION['rol']!="estudiante"){ 
    header("Location: ../login.php"); 
    exit(); 
}
// ---------------------------------------------------------

// Recuperamos datos de sesión (nivel, grado, seccion) para filtrar clases
$nivel   = $_SESSION['nivel'] ?? '';
$grado   = $_SESSION['grado'] ?? '';
$seccion = $_SESSION['seccion'] ?? '';

// === DATA RETRIEVAL: Using a SECURE Prepared Statement ===
// Selects all class details (c.*) and the teacher's name (u.nombre AS maestro)
$stmt = $conn->prepare("SELECT c.*, u.nombre AS maestro 
                         FROM clases c 
                         JOIN usuarios u ON c.id_maestro = u.id 
                         WHERE c.nivel=? AND c.grado=? AND c.seccion=?");

// Binds the session variables to the query placeholders (sss = three strings)
$stmt->bind_param("sss", $nivel, $grado, $seccion);
$stmt->execute();
$result = $stmt->get_result();
// ---------------------------------------------------------

// Enlace para volver
$backLink = "../index/index_estudiante.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📚 Mis Clases</title>
<link rel="stylesheet" href="../css/as.css">
</head>
<body>

<h2>📚 Mis Clases</h2>

<?php if($result->num_rows>0): ?>
    <?php while($row=$result->fetch_assoc()): ?>
        <div class="card">
            📘 <b><?= htmlspecialchars($row['nombre']); ?></b><br>
            <?= htmlspecialchars($row['descripcion']); ?><br>
            👨‍🏫 Profe: <?= htmlspecialchars($row['maestro']); ?><br>
            🎓 <?= htmlspecialchars($row['nivel']." ".$row['grado']." ".$row['seccion']); ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No tienes clases asignadas todavía.</p>
<?php endif; ?>

<a href="<?= $backLink ?>" class="btn-back">Volver al Inicio</a>

<?php 
// Close the statement and connection after use
$stmt->close();
$conn->close();
?>
</body>
</html>