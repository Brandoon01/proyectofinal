<?php
// Conectar a la base de datos
include 'php/conexion.php'; // Asegúrate de que este archivo esté correctamente configurado

session_start(); // Inicia sesión para acceder a datos del alumno
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirigir al login si no hay sesión activa
    exit();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$alumno_id = $_SESSION['user_id'] ?? null; // Usa el operador de fusión nula
$id_subcurso = isset($_GET['id_subcurso']) ? intval($_GET['id_subcurso']) : 0; // ID del subcurso (deberías asegurarte de que esto esté definido)

if (!$alumno_id) {
    die("Error: No se encontró el ID del alumno en la sesión.");
}

if (!$id_subcurso) {
    die("Error: No se encontró el ID del subcurso en la sesión.");
}

// Consulta para obtener las actividades del alumno en el subcurso específico
$query = "
    SELECT a.id_actividad, a.titulo, a.descripcion, a.fecha_entrega, d.nombre AS docente_nombre 
    FROM Actividades a
    JOIN Subcursos s ON a.id_subcurso = s.id_subcurso
    JOIN Docentes d ON a.id_docente = d.id_docente
    JOIN Inscripciones i ON s.id_curso = i.id_curso
    WHERE i.id_alumno = ? AND a.id_subcurso = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $alumno_id, $id_subcurso);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actividades Disponibles</title>
    <link rel="stylesheet" href="css/desploy.css">
    <link rel="icon" href="img/logo.jpg" type="image/x-icon">
</head>
<body>
<header>
    <h1>Perfil del Alumno</h1>
    <nav>
        <a href="inicio.php">Inicio</a>
        <a href="personal.php">Personal</a>    
        <a href="cursos.php">Cursos</a>
        <a href="perfil.php">Perfil</a>
        <a href="php/cerrar.php">Cerrar sesión</a>
    </nav>
</header>

<h1>Actividades Disponibles en el Subcurso</h1>

<?php
// Verificar si hay actividades
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Título</th><th>Descripción</th><th>Fecha de Entrega</th><th>Docente</th><th>Acciones</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['descripcion']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fecha_entrega']) . "</td>";
        echo "<td>" . htmlspecialchars($row['docente_nombre']) . "</td>";
echo "<td><a href=\"entregas.php?id_subcurso={$id_subcurso}&id_actividad={$row['id_actividad']}\">Subir Entrega</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay actividades disponibles en este subcurso.</p>";
}

// Cerrar la conexión
$stmt->close();
$conn->close();
?>

</body>
</html>
