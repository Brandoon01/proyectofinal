<?php
// Include the database connection file
require_once 'php/conexion.php';

// Start session
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redirect to login if no active session
    exit();
}

// Get the student ID from the session
$alumno_id = $_SESSION['user_id'];

// SQL query to fetch activities and grades for the student
$sql = "SELECT a.titulo AS actividad, a.fecha_entrega, e.calificacion 
        FROM Actividades a 
        JOIN Entregas e ON a.id_actividad = e.id_actividad 
        WHERE e.id_alumno = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $alumno_id); // Use $alumno_id here
$stmt->execute();
$result = $stmt->get_result();

// Check if there are any results
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificaciones del Alumno</title>
    <link rel="stylesheet" href="css/deplo.css"> <!-- Enlace al archivo CSS -->
</head>
<body>
<header>
        <h1>AULA VIRTUAL</h1>
        <nav>
            <a href="inicio.php">Inicio</a>
            <a href="personal.php">Personal</a>    
            <a href="cursos.php">Cursos</a>
            <a href="perfil.php">Perfil</a>
            <a href="php/cerrar.php">Cerrar sesión</a>
        </nav>
    </header>

    <div class="container">
        <?php
        if ($result->num_rows > 0) {
            // Output data of each row
            echo "<h1>Calificaciones</h1>";
            echo "<table>
                    <tr>
                        <th>Actividad</th>
                        <th>Fecha de Entrega</th>
                        <th>Calificación</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['actividad']) . "</td>
                        <td>" . htmlspecialchars($row['fecha_entrega']) . "</td>
                        <td>" . htmlspecialchars($row['calificacion']) . "</td>
                      </tr>";
            }
            echo "</table>";

            // Add a button to generate PDF
            echo '<form method="post" action="generar.php">
                    <input type="hidden" name="alumno_id" value="' . htmlspecialchars($alumno_id) . '">
                    <input type="submit" value="Generar Informe PDF">
                  </form>';
        } else {
            echo "No se encontraron actividades o calificaciones.";
        }

        // Close the statement
        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>
