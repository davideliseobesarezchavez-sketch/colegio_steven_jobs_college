<?php
session_start();
include '../conexion.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}
$id_usuario = $_SESSION['id'];
$id_tarea = $_POST['id'] ?? 0;
if ($id_tarea <= 0 || !isset($_FILES['archivo']) || $_FILES['archivo']['error'] != 0) {
    header("Location: archivos.php?error=no_archivo_o_id");
    exit();
}
$nombre_archivo_original = $_FILES['archivo']['name'];
$tmp_archivo = $_FILES['archivo']['tmp_name'];
$ruta_carpeta = "archivos/"; 
if (!is_dir($ruta_carpeta)) {
    mkdir($ruta_carpeta, 0777, true);
}
$ruta_nuevo_archivo = $ruta_carpeta . time() . "_" . $nombre_archivo_original;
if (move_uploaded_file($tmp_archivo, $ruta_nuevo_archivo)) {
    $archivo_anterior = null;
    $stmt_select = $conn->prepare("SELECT archivo FROM tareas WHERE id = ? AND id_usuario = ?");
    if ($stmt_select) {
        $stmt_select->bind_param("ii", $id_tarea, $id_usuario);
        $stmt_select->execute();
        $res = $stmt_select->get_result();
        if ($res && $fila = $res->fetch_assoc()) {
            $archivo_anterior = $fila['archivo'];
        }
        $stmt_select->close();
    }
    if ($archivo_anterior && file_exists($archivo_anterior)) {
        if (!unlink($archivo_anterior)) {
            error_log("Error al eliminar el archivo anterior: " . $archivo_anterior);
        }
    }
    $stmt_update = $conn->prepare("UPDATE tareas SET archivo = ?, fecha_envio = NOW() WHERE id = ? AND id_usuario = ?");
    if ($stmt_update) {
        $stmt_update->bind_param("sii", $ruta_nuevo_archivo, $id_tarea, $id_usuario);
        if (!$stmt_update->execute()) {
            error_log("Error al actualizar la tarea en DB: " . $stmt_update->error);
            header("Location: archivos.php?error=db_update_fallido");
            exit();
        }
        $stmt_update->close();
        header("Location: archivos.php?exito=tarea_actualizada");
        exit();
    } else {
        error_log("Error al preparar la consulta UPDATE: " . $conn->error);
        header("Location: archivos.php?error=db_error_preparacion");
        exit();
    }
} else {
    error_log("Error al mover el archivo subido a: " . $ruta_nuevo_archivo);
    header("Location: archivos.php?error=subida_fallida");
    exit();
}