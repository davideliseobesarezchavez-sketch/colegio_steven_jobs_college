<?php
session_start();
include '../conexion.php';

// Verificar rol docente
if(!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['maestro', 'tutor'])){
    header("Location: ../login.php");
    exit;
}

// Guardar asistencia si se envió el formulario
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $fecha = date('Y-m-d');
    $alumnos = $_POST['asistencia'] ?? [];

    foreach($alumnos as $id_alumno => $estado){
        // estado = 1 (presente), 0 (ausente)
        $stmt = $conn->prepare("INSERT INTO asistencia (id_alumno, fecha, presente) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE presente=?");
        $stmt->bind_param("isii", $id_alumno, $fecha, $estado, $estado);
        $stmt->execute();
    }
    $mensaje = "✅ Asistencia registrada correctamente.";
}

// Seleccionar estudiantes según nivel, grado y sección (opcional: desde GET)
$nivel = $_GET['nivel'] ?? '';
$grado = $_GET['grado'] ?? '';
$seccion = $_GET['seccion'] ?? '';
$estudiantes = [];

if($nivel && $grado && $seccion){
    $stmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE rol='estudiante' AND nivel=? AND grado=? AND seccion=?");
    $stmt->bind_param("sss", $nivel, $grado, $seccion);
    $stmt->execute();
    $res = $stmt->get_result();
    $estudiantes = $res->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar Asistencia</title>
<link rel="stylesheet" href="css/es.css">
</head>
<body>
<h2>📋 Registrar Asistencia</h2>

<form method="GET" action="">
    <label>Nivel:
        <select name="nivel" onchange="this.form.submit()">
            <option value="">-- Nivel --</option>
            <option value="Inicial" <?= $nivel=='Inicial'?'selected':'' ?>>Inicial</option>
            <option value="Primaria" <?= $nivel=='Primaria'?'selected':'' ?>>Primaria</option>
            <option value="Secundaria" <?= $nivel=='Secundaria'?'selected':'' ?>>Secundaria</option>
        </select>
    </label>
    <label>Grado:
        <select name="grado" onchange="this.form.submit()">
            <option value="">-- Grado --</option>
            <?php
            if($nivel=='Inicial') echo '<option value="1" '.($grado=='1'?'selected':'').'>1</option><option value="2" '.($grado=='2'?'selected':'').'>2</option>';
            if($nivel=='Primaria') echo '<option value="1" '.($grado=='1'?'selected':'').'>1</option><option value="2" '.($grado=='2'?'selected':'').'>2</option><option value="3" '.($grado=='3'?'selected':'').'>3</option>';
            if($nivel=='Secundaria') echo '<option value="1" '.($grado=='1'?'selected':'').'>1</option><option value="2" '.($grado=='2'?'selected':'').'>2</option><option value="3" '.($grado=='3'?'selected':'').'>3</option>';
            ?>
        </select>
    </label>
    <label>Sección:
        <select name="seccion" onchange="this.form.submit()">
            <option value="">-- Sección --</option>
            <option value="A" <?= $seccion=='A'?'selected':'' ?>>A</option>
            <option value="B" <?= $seccion=='B'?'selected':'' ?>>B</option>
            <option value="C" <?= $seccion=='C'?'selected':'' ?>>C</option>
        </select>
    </label>
</form>

<?php if(!empty($estudiantes)): ?>
<form method="POST" action="">
    <table>
        <tr>
            <th>Alumno</th>
            <th>Presente</th>
        </tr>
        <?php foreach($estudiantes as $alumno): ?>
        <tr>
            <td><?= htmlspecialchars($alumno['nombre']) ?></td>
            <td><input type="checkbox" name="asistencia[<?= $alumno['id'] ?>]" value="1"></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <button type="submit">Guardar Asistencia</button>
</form>
<?php endif; ?>

<?php if(!empty($mensaje)) echo '<div class="mensaje">'.$mensaje.'</div>'; ?>

</body>
</html>
