<?php
// Configuración de la Base de Datos
$servername = "localhost"; 
$username = "root";     
$password = "";         
$dbname = "colegio_steve_jobs"; 

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión y manejar errores
if ($conn->connect_error) {
    // Registra el error detallado en el log del servidor
    error_log("Fallo de Conexión a la BD: ". $conn->connect_error);
    
    // Muestra un mensaje simple al usuario.
    // Si estás en producción, solo deja 'die("Error interno del servidor. Intente más tarde.");'
    die("Error de Conexión a la BD. Por favor, revisa las credenciales en 'conexion.php'.");
}

// Establecer el charset para prevenir problemas de codificación e inyección SQL
$conn->set_charset("utf8mb4");

// Omitir la etiqueta de cierre de PHP para prevenir espacios en blanco que arruinen las cabeceras/redirecciones
