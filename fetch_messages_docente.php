<?php
session_start();
include 'php/conexion.php';

if (!isset($_SESSION['user_id'])) {
    echo "No hay sesión activa.";
    exit();
}

$user_id = $_SESSION['user_id']; // Teacher's ID

// Query to fetch messages where the teacher is either the sender or the receiver
$sql = "SELECT m.id, m.mensaje, m.fecha, a.nombre AS nombre_alumno, d.nombre AS nombre_docente 
        FROM mensajes m
        LEFT JOIN Alumnos a ON m.id_alumno = a.id_alumno
        LEFT JOIN Docentes d ON m.id_docente = d.id_docente
        WHERE m.id_docente = ? OR m.id_alumno IN (SELECT id_alumno FROM Inscripciones WHERE id_curso IN (SELECT id_curso FROM Cursos WHERE id_docente = ?)) 
        ORDER BY m.fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Display the message based on who is sending it
        if ($row['id_docente'] == $user_id) {
            $sender = "Profesor: " . $row['nombre_docente'];
        } else {
            $sender = "Estudiante: " . $row['nombre_alumno'];
        }

        echo "<div class='message'>";
        echo "<p><strong>$sender</strong> - " . $row['fecha'] . "</p>";
        echo "<p>" . $row['mensaje'] . "</p>";
        echo "</div>";
    }
} else {
    echo "No hay mensajes.";
}

$stmt->close();
$conn->close();
?>
