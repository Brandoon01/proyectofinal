<?php
session_start();
include 'php/conexion.php';

if (!isset($_SESSION['user_id'])) {
    echo "No hay sesión activa.";
    exit();
}

$user_id = $_SESSION['user_id'];
$destinatario = $_GET['destinatario'];

// Check if the user is a student or teacher
$stmt = $conn->prepare("SELECT id_alumno FROM Alumnos WHERE id_alumno = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$is_student = $result->num_rows > 0;
$stmt->close();

// Prepare SQL query based on user role
if ($is_student) {
    // If the user is a student, retrieve messages with teacher names
    $sql = "SELECT mensajes.mensaje, mensajes.fecha,
            CASE 
                WHEN mensajes.id_alumno = ? THEN 'Tú' 
                ELSE CONCAT(Docentes.nombre, ' ', Docentes.apellido)
            END AS sender_name
            FROM mensajes
            LEFT JOIN Docentes ON mensajes.destinatario = Docentes.id_docente
            WHERE (mensajes.id_alumno = ? AND mensajes.destinatario = ?) 
               OR (mensajes.id_alumno = ? AND mensajes.destinatario = ?)
            ORDER BY mensajes.fecha ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $user_id, $user_id, $destinatario, $destinatario, $user_id);

} else {
    // If the user is a teacher, retrieve messages with student names
    $sql = "SELECT mensajes.mensaje, mensajes.fecha,
            CASE 
                WHEN mensajes.id_docente = ? THEN 'Tú' 
                ELSE CONCAT(Alumnos.nombre, ' ', Alumnos.apellido)
            END AS sender_name
            FROM mensajes
            LEFT JOIN Alumnos ON mensajes.destinatario = Alumnos.id_alumno
            WHERE (mensajes.id_docente = ? AND mensajes.destinatario = ?) 
               OR (mensajes.id_docente = ? AND mensajes.destinatario = ?)
            ORDER BY mensajes.fecha ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $user_id, $user_id, $destinatario, $destinatario, $user_id);
}

$stmt->execute();
$messages = $stmt->get_result();

while ($message = $messages->fetch_assoc()) {
    echo "<p><strong>" . htmlspecialchars($message['sender_name']) . ":</strong> " . htmlspecialchars($message['mensaje']) . "<br><small>" . $message['fecha'] . "</small></p>";
}

$stmt->close();
$conn->close();
?>
