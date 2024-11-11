<?php
// Conexión a la base de datos
require_once 'php/conexion.php';

class AdminPanel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Función para obtener el ID del docente usando su documento
    private function obtenerIdDocentePorDocumento($documento) {
        $stmt = $this->db->prepare("SELECT id_docente FROM Docentes WHERE documento = ?");
        $stmt->bind_param("s", $documento);
        $stmt->execute();
        $stmt->bind_result($id_docente);
        $stmt->fetch();
        $stmt->close();
        return $id_docente;
    }

    // Función para agregar un curso con el documento del docente
    public function agregarCurso($nombre_curso, $documento_docente) {
        $id_docente = $this->obtenerIdDocentePorDocumento($documento_docente);
        if (!$id_docente) {
            return "Docente no encontrado.";
        }
        
        $stmt = $this->db->prepare("INSERT INTO Cursos (nombre_curso, id_docente) VALUES (?, ?)");
        $stmt->bind_param("si", $nombre_curso, $id_docente);
        return $stmt->execute() ? "Curso agregado con éxito." : "Error al agregar el curso.";
    }

    // Función para modificar un curso usando el documento del docente
    public function modificarCurso($id_curso, $nombre_curso, $documento_docente) {
        $id_docente = $this->obtenerIdDocentePorDocumento($documento_docente);
        if (!$id_docente) {
            return "Docente no encontrado.";
        }

        $stmt = $this->db->prepare("UPDATE Cursos SET nombre_curso = ?, id_docente = ? WHERE id_curso = ?");
        $stmt->bind_param("sii", $nombre_curso, $id_docente, $id_curso);
        return $stmt->execute() ? "Curso modificado con éxito." : "Error al modificar el curso.";
    }

    // Resto de funciones para eliminar curso...
}

// Crear una instancia del panel de administración
$adminPanel = new AdminPanel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'agregarCurso':
                $mensaje = $adminPanel->agregarCurso($_POST['nombre_curso'], $_POST['documento_docente']);
                break;
            case 'eliminarCurso':
                $mensaje = $adminPanel->eliminarCurso($_POST['id_curso']);
                break;
            case 'modificarCurso':
                $mensaje = $adminPanel->modificarCurso($_POST['id_curso'], $_POST['nombre_curso'], $_POST['documento_docente']);
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador</title>
    <link rel="stylesheet" href="css/admi.css">
    <link rel="icon" href="img/logo.jpg" type="image/x-icon">
</head>
<body>
    <header>
        <h1>Panel de Cursos</h1>
    </header>
    <nav>
        <a href="admin.php">Alumnos</a>
        <a href="admin2.php">Docentes</a>
        <a href="admin3.php">Cursos</a>
        <a href="admin5.php">Materias</a>
        <a href="admin4.php">Inscripciones</a>
        <a href="php/cerrar.php">cerrar sesion</a>
    </nav>
    <main>
        <!-- Formulario para agregar curso -->
        <h2>Agregar Curso</h2>
        <form method="POST">
            <input type="hidden" name="action" value="agregarCurso">
            <input type="text" name="nombre_curso" placeholder="Nombre del Curso" required>
            <input type="text" name="documento_docente" placeholder="Documento del Docente" required>
            <input type="submit" value="Agregar Curso">
        </form>

        <!-- Formulario para modificar curso -->
        <h2>Modificar Curso</h2>
        <form method="POST">
            <input type="hidden" name="action" value="modificarCurso">
            <input type="number" name="id_curso" placeholder="ID del Curso" required>
            <input type="text" name="nombre_curso" placeholder="Nuevo Nombre del Curso" required>
            <input type="text" name="documento_docente" placeholder="Documento del Docente" required>
            <input type="submit" value="Modificar Curso">
        </form>

        <!-- Formulario para eliminar curso -->
        <h2>Eliminar Curso</h2>
        <form method="POST">
            <input type="hidden" name="action" value="eliminarCurso">
            <input type="number" name="id_curso" placeholder="ID del Curso" required>
            <input type="submit" value="Eliminar Curso">
        </form>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>
    </main>
</body>
</html>