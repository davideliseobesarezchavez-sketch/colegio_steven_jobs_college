<?php
session_start();
include '../conexion.php';

// 📌 Crear tabla asistencia si no existe
$conn->query("
CREATE TABLE IF NOT EXISTS asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_maestro INT NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('Presente','Tarde','Ausente') NOT NULL,
    FOREIGN KEY (id_estudiante) REFERENCES usuarios(id),
    FOREIGN KEY (id_maestro) REFERENCES usuarios(id)
) ENGINE=InnoDB;
");

// Solo maestros pueden entrar
if(!isset($_SESSION['id']) || $_SESSION['rol']!="maestro"){ 
    header("Location: ../login.php"); 
    exit(); 
}

$id_maestro = $_SESSION['id'];

// 📌 Obtenemos datos del maestro (nivel, grado, seccion)
$sql = "SELECT nivel, grado, seccion FROM usuarios WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_maestro);
$stmt->execute();
$maestro = $stmt->get_result()->fetch_assoc();

$nivel   = $maestro['nivel'];
$grado   = $maestro['grado'];
$seccion = $maestro['seccion'];

// 📌 Procesar asistencia enviada
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asistencia'])) {
    foreach ($_POST['asistencia'] as $id_estudiante => $estado) {
        $fecha = date("Y-m-d");

        // Verificamos si ya existe registro ese día
        $check = $conn->prepare("SELECT id FROM asistencia WHERE id_estudiante=? AND fecha=?");
        $check->bind_param("is", $id_estudiante, $fecha);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            // Actualizamos
            $row = $res->fetch_assoc();
            $upd = $conn->prepare("UPDATE asistencia SET estado=?, id_maestro=? WHERE id=?");
            $upd->bind_param("sii", $estado, $id_maestro, $row['id']);
            $upd->execute();
        } else {
            // Insertamos
            $ins = $conn->prepare("INSERT INTO asistencia (id_estudiante, id_maestro, fecha, estado) VALUES (?,?,?,?)");
            $ins->bind_param("iiss", $id_estudiante, $id_maestro, $fecha, $estado);
            $ins->execute();
        }
    }
    $mensaje = "✅ Asistencia guardada correctamente.";
}

// 📌 Listar estudiantes del mismo nivel, grado y sección
$sql = "SELECT id, nombre FROM usuarios WHERE rol='estudiante' AND nivel=? AND grado=? AND seccion=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nivel, $grado, $seccion);
$stmt->execute();
$estudiantes = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📋 Asistencia</title>
<link rel="stylesheet" href="css/is.css">
</head>
<body>
<h2>📋 Asistencia - <?= $nivel." ".$grado." ".$seccion; ?></h2>

<?php if(isset($mensaje)) echo "<p style='color:green;font-weight:bold;'>$mensaje</p>"; ?>

<form method="POST">
    <?php if($estudiantes->num_rows > 0): ?>
        <?php while($row = $estudiantes->fetch_assoc()): ?>
            <div class="card">
                <b><?= $row['nombre']; ?></b><br>
                <label><input type="radio" name="asistencia[<?= $row['id']; ?>]" value="Presente" required> ✅ Presente</label>
                <label><input type="radio" name="asistencia[<?= $row['id']; ?>]" value="Tarde"> ⏰ Tarde</label>
                <label><input type="radio" name="asistencia[<?= $row['id']; ?>]" value="Ausente"> ❌ Ausente</label>
            </div>
        <?php endwhile; ?>
        <button type="submit">💾 Guardar Asistencia</button>
    <?php else: ?>
        <p>No hay estudiantes en tu clase.</p>
    <?php endif; ?>
</form>

<p><a href="index/index_maestro.php">🔙 Volver al inicio</a></p>
</body>
</html>
