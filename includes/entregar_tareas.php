<?php
session_start();
include '../conexion.php';

// Verificar sesión (solo estudiante)
if (!isset($_SESSION['id']) || $_SESSION['rol'] != "estudiante") {
    header("Location: ../login.php");
    exit();
}

$id_estudiante = $_SESSION['id'];

// 📤 Procesar entrega
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_tarea'])) {
    $id_tarea = $_POST['id_tarea'];
    $comentario = $_POST['comentario'] ?? "";

    $archivoNombre = null;
    if (isset($_FILES["archivo"]) && $_FILES["archivo"]["error"] == 0) {
        $carpeta = "uploads_estudiante/";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }
        $archivoNombre = $carpeta . time() . "_" . basename($_FILES["archivo"]["name"]);
        move_uploaded_file($_FILES["archivo"]["tmp_name"], $archivoNombre);
    }

    $sql = "INSERT INTO entregas_tareas (id_tarea, id_estudiante, archivo, comentario) 
            VALUES ('$id_tarea','$id_estudiante','$archivoNombre','$comentario')";
    if ($conn->query($sql)) {
        $mensaje = "✅ Tarea entregada correctamente.";
    } else {
        $error = "❌ Error en BD: " . $conn->error;
    }
}

// 📑 Tareas pendientes
$tareas = $conn->query("
    SELECT t.* 
    FROM tareas t
    INNER JOIN clases c ON t.id_clase = c.id
    WHERE c.nivel = (SELECT nivel FROM usuarios WHERE id = '$id_estudiante')
      AND c.grado = (SELECT grado FROM usuarios WHERE id = '$id_estudiante')
      AND c.seccion = (SELECT seccion FROM usuarios WHERE id = '$id_estudiante')
      AND t.id NOT IN (SELECT id_tarea FROM entregas_tareas WHERE id_estudiante = '$id_estudiante')
    ORDER BY t.fecha_entrega ASC
");

// 📑 Entregas del estudiante
$entregas = $conn->query("
    SELECT e.*, t.titulo 
    FROM entregas_tareas e
    JOIN tareas t ON e.id_tarea = t.id
    WHERE e.id_estudiante = '$id_estudiante'
    ORDER BY e.fecha_entrega DESC
");

$backLink = "../index/index_estudiante.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>📤 Subir Tareas - Vista Estudiante (Mockup)</title>
<script src="https://cdn.tailwindcss.com"></script>
<!-- Configuramos la fuente Inter por defecto -->
<style>
    body { font-family: 'Inter', sans-serif; }
</style>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-5xl mx-auto">
    
    <!-- Contenedor de mensajes dinámicos (para simulación de éxito/error) -->
    <div id="mockup-message-container" class="hidden p-3 text-center font-semibold rounded-lg mb-4">
        <!-- El mensaje aparecerá aquí -->
    </div>

    <h2 class="text-3xl text-blue-600 text-center font-bold mb-8">📤 Subir Tareas</h2>

    <div class="lg:flex lg:gap-8">

        <!-- Columna de Tareas Pendientes -->
        <div class="lg:w-1/2">
            <h3 class="text-xl font-semibold mb-4 text-gray-700">📌 Tareas Pendientes</h3>
            
            <!-- Tarea Pendiente 1: Solo Formulario Esencial de Subida -->
            <!-- Agregamos el onsubmit para manejar la acción en el mockup -->
            <form id="submission-form" class="bg-white p-6 mb-6 rounded-2xl shadow-lg border border-gray-200" onsubmit="handleMockupSubmit(event)">
                
                <!-- Solo la Fecha Límite -->
                <p class="text-sm text-red-500 font-medium mb-4">⏰ Fecha Límite: 25/10/2025</p>

                <!-- Campo de Subida de Archivo -->
                <label class="block text-gray-700 font-medium">Subir tu archivo</label>
                <input 
                    type="file" 
                    name="archivo" 
                    required 
                    class="mt-2 w-full p-2 border-2 border-gray-300 rounded-lg bg-gray-50 cursor-pointer text-sm focus:outline-none focus:border-blue-500"
                >

                <button 
                    type="submit" 
                    class="mt-4 w-full p-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition transform hover:scale-[1.01] shadow-md"
                >
                    📤 Entregar Tarea
                </button>
            </form>
            
        </div>

        <!-- Columna de Entregas Realizadas -->
        <div class="lg:w-1/2 mt-8 lg:mt-0">
            <h3 class="text-xl font-semibold mb-4 text-gray-700">✅ Mis Entregas Realizadas</h3>
            
            <!-- Mensaje de estado vacío para Entregas Realizadas -->
            <p class="text-gray-600 p-6 bg-white rounded-xl shadow-lg border border-gray-200 text-center">
                Aún no has registrado ninguna entrega.
            </p>
        </div>

    </div>

    <!-- Botón de Volver -->
    <a href="<?= $backLink ?>" class="inline-block mt-6 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-bold shadow-lg">🔙 Volver al Inicio</a>

</div>

<script>
    /**
     * Función para simular el envío del formulario en este mockup estático.
     * Evita la recarga de la página y muestra un mensaje de éxito.
     */
    function handleMockupSubmit(event) {
        event.preventDefault(); 
        
        const fileInput = document.querySelector('input[name="archivo"]');
        const messageContainer = document.getElementById('mockup-message-container');
        
        // Limpiar clases anteriores
        messageContainer.className = 'hidden p-3 text-center font-semibold rounded-lg mb-4';

        // 1. Validar que el archivo esté seleccionado
        if (fileInput.files.length === 0) {
             messageContainer.textContent = "⚠️ Debes seleccionar un archivo para entregar.";
             messageContainer.classList.remove('hidden');
             messageContainer.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700');
             return;
        }

        // 2. Simular éxito
        messageContainer.textContent = "✅ ¡Entrega simulada con éxito! (Acción bloqueada en el mockup).";
        messageContainer.classList.remove('hidden');
        messageContainer.classList.add('bg-green-100', 'border', 'border-green-400', 'text-green-700');
        
        // Opcional: limpiar el input después de la "entrega"
        fileInput.value = '';

        // 3. Ocultar el mensaje después de 5 segundos
        setTimeout(() => {
            messageContainer.classList.add('hidden');
        }, 5000);
    }
</script>

</body>
</html>