<?php
session_start();
include 'php/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ensure the user is a teacher (Docente)
$stmt = $conn->prepare("SELECT id_docente FROM Docentes WHERE id_docente = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Acceso denegado. Sólo los docentes pueden acceder a esta página.";
    exit();
}
$stmt->close();

// Get a list of students enrolled in courses taught by this teacher
$sql = "SELECT DISTINCT Alumnos.id_alumno, Alumnos.nombre, Alumnos.apellido 
        FROM Inscripciones 
        JOIN Cursos ON Inscripciones.id_curso = Cursos.id_curso
        JOIN Alumnos ON Inscripciones.id_alumno = Alumnos.id_alumno
        WHERE Cursos.id_docente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$students = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat Docente</title>
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>
    <h2>Chat para Docentes</h2>
    <div id="messages"></div>
    <form id="sendMessageForm">
        <label for="destinatario">Seleccione un alumno:</label>
        <select id="destinatario" required>
            <option value="admin">Administrador</option>
            <?php while ($student = $students->fetch_assoc()): ?>
                <option value="<?php echo $student['id_alumno']; ?>">
                    <?php echo htmlspecialchars($student['nombre'] . " " . $student['apellido']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <textarea id="mensaje" placeholder="Escriba su mensaje aquí" required></textarea>
        <button type="submit">Enviar</button>
    </form>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const messagesDiv = document.getElementById("messages");
        const destinatarioSelect = document.getElementById("destinatario");

        function fetchMessages() {
            const destinatario = destinatarioSelect.value;
            fetch(`fetch_messages.php?destinatario=${encodeURIComponent(destinatario)}`)
                .then(response => response.text())
                .then(data => {
                    messagesDiv.innerHTML = data;
                })
                .catch(error => console.error("Error fetching messages:", error));
        }

        destinatarioSelect.addEventListener("change", fetchMessages);
        setInterval(fetchMessages, 3000);
    });

    document.getElementById("sendMessageForm").addEventListener("submit", function (event) {
        event.preventDefault();

        const destinatario = document.getElementById("destinatario").value;
        const mensaje = document.getElementById("mensaje").value;

        fetch("send_message_docente.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `destinatario=${encodeURIComponent(destinatario)}&mensaje=${encodeURIComponent(mensaje)}`,
        })
            .then(response => response.text())
            .then(data => {
                alert(data);
                document.getElementById("mensaje").value = "";
            })
            .catch(error => console.error("Error sending message:", error));
    });
    </script>
</body>
</html>
