<?php
session_start();
include 'php/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ensure the user is a student (Alumno)
$stmt = $conn->prepare("SELECT id_alumno FROM Alumnos WHERE id_alumno = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Acceso denegado. Sólo los alumnos pueden acceder a esta página.";
    exit();
}
$stmt->close();

$sql = "SELECT DISTINCT Docentes.id_docente, Docentes.nombre, Docentes.apellido 
        FROM Inscripciones 
        JOIN Cursos ON Inscripciones.id_curso = Cursos.id_curso
        JOIN Docentes ON Cursos.id_docente = Docentes.id_docente
        WHERE Inscripciones.id_alumno = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teachers = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat Alumno</title>
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>
    <h2>Chat para Alumnos</h2>
    <div id="messages"></div>
    <form id="sendMessageForm">
        <label for="destinatario">Seleccione un docente:</label>
        <select id="destinatario" required>
            <option value="admin">Administrador</option>
            <?php while ($teacher = $teachers->fetch_assoc()): ?>
                <option value="<?php echo $teacher['id_docente']; ?>">
                    <?php echo htmlspecialchars($teacher['nombre'] . " " . $teacher['apellido']); ?>
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

        fetch("send_message.php", {
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
