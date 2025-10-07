<?php
include '../conexion.php'; // tu conexión a la base de datos

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $conn->real_escape_string($_POST["nombre"]);
    $correo = $conn->real_escape_string($_POST["correo"]);
    $texto = $conn->real_escape_string($_POST["mensaje"]);

    // Guardar en la base de datos
    $sql = "INSERT INTO contactos (nombre, correo, mensaje, fecha) 
            VALUES ('$nombre', '$correo', '$texto', NOW())";

    if ($conn->query($sql)) {
        $mensaje = "✅ Gracias <b>$nombre</b>, tu mensaje fue enviado correctamente.";
    } else {
        $mensaje = "❌ Error al enviar el mensaje: " . $conn->error;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Confirmación</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .confirmacion {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      width: 350px;
      text-align: center;
    }
    a {
      display: inline-block;
      margin-top: 15px;
      padding: 10px 20px;
      background: #4caf50;
      color: white;
      text-decoration: none;
      border-radius: 8px;
    }
    a:hover { background: #45a049; }
  </style>
</head>
<body>
  <div class="confirmacion">
    <h2><?php echo $mensaje; ?></h2>
    <a href="contacto.php">⬅️ Volver</a>
  </div>
</body>
</html>
