<?php
// Incluir el archivo de conexión
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el ID de entrega y la calificación desde el formulario
    $id_entrega = $_POST['id_entrega'];
    $calificacion = $_POST['calificacion'];

    // Consulta para actualizar la calificación en la tabla Entregas
    $query = "UPDATE Entregas SET calificacion = ? WHERE id_entrega = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $calificacion, $id_entrega);

    // Ejecutar la consulta y verificar si fue exitosa
    if ($stmt->execute()) {
        echo "Calificación asignada con éxito.";
    } else {
        echo "Error al asignar la calificación: " . $conn->error;
    }

    // Cerrar el statement
    $stmt->close();
}

// Cerrar la conexión
$conn->close();

// Redirecciona de vuelta a la página de entregas después de calificar
header("Location: entregadas.php");
exit;
?>
