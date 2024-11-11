<?php
// Conexión a la base de datos
require_once 'php/conexion.php';

class AdminPanel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Función para obtener el ID del alumno a partir de su documento
    private function obtenerIdAlumnoPorDocumento($documento) {
        $stmt = $this->db->prepare("SELECT id_alumno FROM Alumnos WHERE documento = ?");
        $stmt->bind_param("s", $documento);
        $stmt->execute();
        $result = $stmt->get_result();
        $alumno = $result->fetch_assoc();
        return $alumno ? $alumno['id_alumno'] : null;
    }

    // Función para obtener el ID del curso o subcurso a partir de su nombre
    private function obtenerIdCursoPorNombre($nombre_curso) {
        $stmt = $this->db->prepare("SELECT id_curso FROM Cursos WHERE nombre_curso = ?");
        $stmt->bind_param("s", $nombre_curso);
        $stmt->execute();
        $result = $stmt->get_result();
        $curso = $result->fetch_assoc();
        return $curso ? $curso['id_curso'] : null;
    }

    // Funciones para la tabla Inscripciones
    public function agregarInscripcion($documento, $nombre_curso) {
        $id_alumno = $this->obtenerIdAlumnoPorDocumento($documento);
        $id_curso = $this->obtenerIdCursoPorNombre($nombre_curso);

        if ($id_alumno && $id_curso) {
            $stmt = $this->db->prepare("INSERT INTO Inscripciones (id_alumno, id_curso) VALUES (?, ?)");
            $stmt->bind_param("ii", $id_alumno, $id_curso);
            return $stmt->execute();
        }
        return false;
    }

    public function eliminarInscripcion($documento, $nombre_curso) {
        $id_alumno = $this->obtenerIdAlumnoPorDocumento($documento);
        $id_curso = $this->obtenerIdCursoPorNombre($nombre_curso);

        if ($id_alumno && $id_curso) {
            $stmt = $this->db->prepare("DELETE FROM Inscripciones WHERE id_alumno = ? AND id_curso = ?");
            $stmt->bind_param("ii", $id_alumno, $id_curso);
            return $stmt->execute();
        }
        return false;
    }

    public function modificarInscripcion($id_inscripcion, $documento, $nombre_curso) {
        $id_alumno = $this->obtenerIdAlumnoPorDocumento($documento);
        $id_curso = $this->obtenerIdCursoPorNombre($nombre_curso);

        if ($id_alumno && $id_curso) {
            $stmt = $this->db->prepare("UPDATE Inscripciones SET id_alumno = ?, id_curso = ? WHERE id_inscripcion = ?");
            $stmt->bind_param("iii", $id_alumno, $id_curso, $id_inscripcion);
            return $stmt->execute();
        }
        return false;
    }
}

// Crear una instancia del panel de administración
$adminPanel = new AdminPanel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'agregarInscripcion':
                $adminPanel->agregarInscripcion($_POST['documento'], $_POST['nombre_curso']);
                break;
            case 'eliminarInscripcion':
                $adminPanel->eliminarInscripcion($_POST['documento'], $_POST['nombre_curso']);
                break;
            case 'modificarInscripcion':
                $adminPanel->modificarInscripcion($_POST['id_inscripcion'], $_POST['documento'], $_POST['nombre_curso']);
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
        <h1>Panel de Inscripciones</h1>
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
        <h2>Agregar Inscripción</h2>
        <form method="POST">
            <input type="hidden" name="action" value="agregarInscripcion">
            <input type="text" name="documento" placeholder="Documento del Alumno" required>
            <input type="text" name="nombre_curso" placeholder="Nombre del Curso" required>
            <input type="submit" value="Agregar Inscripción">
        </form>

        <h2>Modificar Inscripción</h2>
        <form method="POST">
            <input type="hidden" name="action" value="modificarInscripcion">
            <input type="number" name="id_inscripcion" placeholder="ID de la Inscripción" required>
            <input type="text" name="documento" placeholder="Documento del Alumno" required>
            <input type="text" name="nombre_curso" placeholder="Nuevo Nombre del Curso" required>
            <input type="submit" value="Modificar Inscripción">
        </form>

        <h2>Eliminar Inscripción</h2>
        <form method="POST">
            <input type="hidden" name="action" value="eliminarInscripcion">
            <input type="text" name="documento" placeholder="Documento del Alumno" required>
            <input type="text" name="nombre_curso" placeholder="Nombre del Curso" required>
            <input type="submit" value="Eliminar Inscripción">
        </form>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>
    </main>
</body>
</html>
