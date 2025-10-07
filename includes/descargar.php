<?php
session_start();
include '../conexion.php';

// 🔒 Verificar sesión
if (!isset($_SESSION['id']) || $_SESSION['rol'] != "estudiante") {
    die("Acceso denegado");
}

$id_estudiante = $_SESSION['id'];

if (!isset($_GET['id'])) {
    die("Archivo no especificado");
}

$id_entrega = intval($_GET['id']);

// 🔹 Obtener archivo de la entrega
$stmt = $conn->prepare("SELECT archivo FROM entregas_tareas WHERE id = ? AND id_estudiante = ?");
$stmt->bind_param("ii", $id_entrega, $id_estudiante);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Archivo no encontrado o no tienes permiso");
}

$row = $result->fetch_assoc();
$archivo = $row['archivo'];

// 🔹 Forzar descarga
if (file_exists($archivo)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($archivo));
    readfile($archivo);
    exit;
} else {
    die("El archivo no existe en el servidor");
}
