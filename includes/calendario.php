<?php
include '../conexion.php';
session_start();

$id_usuario = $_SESSION['usuario_id'] ?? null;
$rol = $_SESSION['usuario_rol'] ?? null;

if (!$id_usuario) {
    echo json_encode([]);
    exit;
}

if ($rol === 'estudiante') {
    $sql = "SELECT c.nombre AS title, h.dia, h.hora_inicio, h.hora_fin, h.aula
            FROM usuarios u
            JOIN matriculas m ON u.id = m.usuario_id
            JOIN clases c ON c.nivel = m.nivel AND c.grado = m.grado AND c.seccion = m.seccion
            JOIN horarios_clases h ON c.id = h.id_clase
            WHERE u.id = ?";
} elseif ($rol === 'maestro') {
    $sql = "SELECT c.nombre AS title, h.dia, h.hora_inicio, h.hora_fin, h.aula
            FROM clases c
            JOIN horarios_clases h ON c.id = h.id_clase
            WHERE c.id_maestro = ?";
} else {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$eventos = [];

// Mapear días a números (FullCalendar usa YYYY-MM-DD)
$diasSemana = [
    'Lunes' => 1,
    'Martes' => 2,
    'Miercoles' => 3,
    'Jueves' => 4,
    'Viernes' => 5,
    'Sabado' => 6,
    'Domingo' => 0
];

while ($row = $result->fetch_assoc()) {
    $eventos[] = [
        "title" => $row['title'] . " (Aula " . $row['aula'] . ")",
        "daysOfWeek" => [$diasSemana[$row['dia']]],
        "startTime" => $row['hora_inicio'],
        "endTime" => $row['hora_fin']
    ];
}

echo json_encode($eventos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Calendario Académico</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- FullCalendar con tema Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

  <style>
    body { background: #f8f9fa; }
    #calendar { max-width: 90%; margin: 30px auto; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
  </style>
</head>
<body>
  <div class="container text-center mt-4">
    <h2 class="mb-4">📅 Calendario Académico</h2>
    <div id="calendar"></div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- FullCalendar -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        themeSystem: 'bootstrap5', // usar bootstrap
        initialView: 'timeGridWeek',
        locale: 'es',
        allDaySlot: false,
        slotMinTime: "07:00:00",
        slotMaxTime: "20:00:00",
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: 'eventos.php'
      });
      calendar.render();
    });
  </script>
</body>
</html>