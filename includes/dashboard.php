<?php
session_start();
include '../conexion.php';

// Verificar acceso admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$nombreAdmin = htmlspecialchars($_SESSION['nombre'] . " " . $_SESSION['apellido']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>👑 Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-blue-900 text-white min-h-screen p-6">
        <h2 class="text-2xl font-bold mb-8">⚡ Panel Admin</h2>
        <nav class="flex flex-col gap-3">
            <a href="index/index_admin.php" class="hover:bg-blue-700 px-3 py-2 rounded">🏠 Inicio</a>
            <a href="admincrearcuenta.php" class="hover:bg-blue-700 px-3 py-2 rounded">➕ Crear Usuario</a>
            <a href="index/index_estudiantes" class="hover:bg-blue-700 px-3 py-2 rounded">🎓 Estudiantes</a>
            <a href="index/index_maestro" class="hover:bg-blue-700 px-3 py-2 rounded">👨‍🏫 Maestros</a>
            <a href="index/index_tutores" class="hover:bg-blue-700 px-3 py-2 rounded">👪 Tutores</a>
            <a href="asistencias_estudiantes" class="hover:bg-blue-700 px-3 py-2 rounded">📋 Asistencias</a>
            <a href="asistencias_maestro" class="hover:bg-blue-700 px-3 py-2 rounded">📋 Asistencias</a>
            <a href="../logout.php" class="hover:bg-red-600 bg-red-500 px-3 py-2 rounded mt-10">🚪 Cerrar Sesión</a>
        </nav>
    </aside>

    <!-- Contenido -->
    <main class="flex-1 p-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">👋 Bienvenido, <?= $nombreAdmin ?></h1>
        <p class="text-gray-600 mb-8">Administra usuarios, asistencia y roles desde aquí.</p>

        <!-- Tarjetas de resumen -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h3 class="text-xl font-semibold">🎓 Estudiantes</h3>
                <p class="text-gray-500">Gestiona inscripciones y progreso.</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h3 class="text-xl font-semibold">👨‍🏫 Maestros</h3>
                <p class="text-gray-500">Administra materias y horarios.</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <h3 class="text-xl font-semibold">📋 Asistencia</h3>
                <p class="text-gray-500">Control de asistencia diaria.</p>
            </div>
        </div>

        <!-- Aquí puedes incluir tus tablas de usuarios y asistencias -->
        <section id="estudiantes">
            <h2 class="text-2xl font-bold text-blue-700 mb-4">🎓 Lista de Estudiantes</h2>
            <!-- Tabla estudiantes -->
        </section>

    </main>
</body>
</html>
