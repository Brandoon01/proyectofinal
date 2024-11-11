<?php
// Incluimos el archivo de conexión
include 'php/conexion.php';

// Obtener el id de actividad desde la URL, si existe
$id_actividad = isset($_GET['id_actividad']) ? $_GET['id_actividad'] : null;

// Asegurarse de que el id de actividad es válido
if ($id_actividad) {
    // Consulta para obtener las entregas de una actividad específica
    $query = "
        SELECT Entregas.id_entrega, Entregas.nombre_archivo, Entregas.ruta_archivo, Entregas.fecha_entrega,
               Alumnos.nombre AS nombre_alumno, Alumnos.apellido AS apellido_alumno,
               Actividades.titulo AS titulo_actividad, Actividades.descripcion AS descripcion_actividad,
               Entregas.calificacion
        FROM Entregas
        JOIN Alumnos ON Entregas.id_alumno = Alumnos.id_alumno
        JOIN Actividades ON Entregas.id_actividad = Actividades.id_actividad
        WHERE Actividades.id_actividad = $id_actividad
        ORDER BY Entregas.fecha_entrega DESC";
} else {
    // Si no se pasa id_actividad, mostrar un mensaje o redirigir
    echo "No se ha especificado una actividad.";
    exit;
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entregas de Alumnos</title>
    <link rel="stylesheet" href="css/deplo.css">
    <link rel="icon" href="img/logo.jpg" type="image/x-icon">
</head>
<body>
<header>
    <h1>AULA VIRTUAL</h1>
    <nav>
        <a href="Pinicio.php">Inicio</a>
        <a href="Ppersonal.php">Personal</a>    
        <a href="Pcursos.php">Cursos</a>
        <a href="Pperfil.php">Perfil</a>
        <a href="php/cerrar.php">Cerrar sesión</a>
    </nav>
</header>
<div class="container">
    <h1>Entregas de Alumnos</h1>

    <table>
        <tr>
            <th>Alumno</th>
            <th>Actividad</th>
            <th>Descripción</th>
            <th>Archivo</th>
            <th>Fecha de Entrega</th>
            <th>Calificación</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['nombre_alumno'] . ' ' . $row['apellido_alumno']; ?></td>
                <td><?php echo $row['titulo_actividad']; ?></td>
                <td><?php echo $row['descripcion_actividad']; ?></td>
                <td><a href="<?php echo $row['ruta_archivo']; ?>" target="_blank"><?php echo $row['nombre_archivo']; ?></a></td>
                <td><?php echo $row['fecha_entrega']; ?></td>
                <td>
                    <?php if ($row['calificacion'] === null): ?>
                        <form action="php/calificar.php" method="POST">
                            <input type="hidden" name="id_entrega" value="<?php echo $row['id_entrega']; ?>">
                            <input type="number" name="calificacion" min="0" max="5" step="0.1" placeholder="0.0-5.0" required>
                            <button type="submit">Calificar</button>
                        </form>
                    <?php else: ?>
                        <?php echo $row['calificacion']; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>

<?php
$conn->close();
?>
