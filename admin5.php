<?php
// Conexión a la base de datos
require_once 'php/conexion.php';

class AdminPanel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Function to get course ID from course name
    private function obtenerIdCurso($nombre_curso) {
        $stmt = $this->db->prepare("SELECT id_curso FROM Cursos WHERE nombre = ?");
        $stmt->bind_param("s", $nombre_curso);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['id_curso'] ?? null;
    }

    // Function to get teacher ID from teacher document
    private function obtenerIdDocente($documento_docente) {
        $stmt = $this->db->prepare("SELECT id_docente FROM Docentes WHERE documento = ?");
        $stmt->bind_param("s", $documento_docente);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['id_docente'] ?? null;
    }

    // Function to add subcourse
    public function agregarSubcurso($nombre_subcurso, $descripcion, $nombre_curso, $documento_docente) {
        $id_curso = $this->obtenerIdCurso($nombre_curso);
        $id_docente = $this->obtenerIdDocente($documento_docente);

        if ($id_curso && $id_docente) {
            $stmt = $this->db->prepare("INSERT INTO Subcursos (nombre_subcurso, descripcion, id_curso, id_docente) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $nombre_subcurso, $descripcion, $id_curso, $id_docente);
            return $stmt->execute();
        }
        return false;
    }

    // Function to delete subcourse by ID
    public function eliminarSubcurso($nombre_subcurso) {
        $stmt = $this->db->prepare("DELETE FROM Subcursos WHERE nombre_subcurso = ?");
        $stmt->bind_param("s", $nombre_subcurso);
        return $stmt->execute();
    }

    // Function to modify subcourse by ID
    public function modificarSubcurso($id_subcurso, $nombre_subcurso, $descripcion, $nombre_curso, $documento_docente) {
        $id_curso = $this->obtenerIdCurso($nombre_curso);
        $id_docente = $this->obtenerIdDocente($documento_docente);

        if ($id_curso && $id_docente) {
            $stmt = $this->db->prepare("UPDATE Subcursos SET nombre_subcurso = ?, descripcion = ?, id_curso = ?, id_docente = ? WHERE id_subcurso = ?");
            $stmt->bind_param("ssiii", $nombre_subcurso, $descripcion, $id_curso, $id_docente, $id_subcurso);
            return $stmt->execute();
        }
        return false;
    }

    // Function to search subcourse by ID
    public function buscarSubcurso($id_subcurso) {
        $stmt = $this->db->prepare("SELECT * FROM Subcursos WHERE id_subcurso = ?");
        $stmt->bind_param("i", $id_subcurso);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

// Create an instance of the admin panel
$adminPanel = new AdminPanel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'agregarSubcurso':
                $adminPanel->agregarSubcurso($_POST['nombre_subcurso'], $_POST['descripcion'], $_POST['nombre_curso'], $_POST['documento_docente']);
                break;
            case 'eliminarSubcurso':
                $adminPanel->eliminarSubcurso($_POST['nombre_subcurso']);
                break;
            case 'modificarSubcurso':
                $adminPanel->modificarSubcurso($_POST['id_subcurso'], $_POST['nombre_subcurso'], $_POST['descripcion'], $_POST['nombre_curso'], $_POST['documento_docente']);
                break;
            case 'buscarSubcurso':
                $subcurso = $adminPanel->buscarSubcurso($_POST['id_subcurso']);
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
    <title>Administrador - Subcursos</title>
    <link rel="stylesheet" href="css/admi.css">
    <link rel="icon" href="img/logo.jpg" type="image/x-icon">
</head>
<body>
    <header>
        <h1>Panel de Subcursos</h1>
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
        <!-- Formulario para agregar subcurso -->
        <h2>Agregar Subcurso</h2>
        <form method="POST">
            <input type="hidden" name="action" value="agregarSubcurso">
            <input type="text" name="nombre_subcurso" placeholder="Nombre del Subcurso" required>
            <textarea name="descripcion" placeholder="Descripción" required></textarea>
            <input type="text" name="nombre_curso" placeholder="Nombre del Curso" required>
            <input type="text" name="documento_docente" placeholder="Documento del Docente" required>
            <input type="submit" value="Agregar Subcurso">
        </form>

        <!-- Formulario para buscar subcurso por ID -->
        <h2>Buscar Subcurso</h2>
        <form method="POST">
            <input type="hidden" name="action" value="buscarSubcurso">
            <input type="number" name="id_subcurso" placeholder="ID del Subcurso" required>
            <input type="submit" value="Buscar Subcurso">
        </form>

        <?php if (isset($subcurso) && $subcurso): ?>
            <!-- Formulario para modificar subcurso -->
            <h2>Modificar Subcurso</h2>
            <form method="POST">
                <input type="hidden" name="action" value="modificarSubcurso">
                <input type="hidden" name="id_subcurso" value="<?php echo $subcurso['id_subcurso']; ?>">
                <input type="text" name="nombre_subcurso" value="<?php echo $subcurso['nombre_subcurso']; ?>" placeholder="Nombre del Subcurso" required>
                <textarea name="descripcion" placeholder="Descripción" required><?php echo $subcurso['descripcion']; ?></textarea>
                <input type="text" name="nombre_curso" placeholder="Nombre del Curso" required>
                <input type="text" name="documento_docente" placeholder="Documento del Docente" required>
                <input type="submit" value="Modificar Subcurso">
            </form>
        <?php endif; ?>

        <!-- Formulario para eliminar subcurso por nombre -->
        <h2>Eliminar Subcurso</h2>
        <form method="POST">
            <input type="hidden" name="action" value="eliminarSubcurso">
            <input type="text" name="nombre_subcurso" placeholder="Nombre del Subcurso" required>
            <input type="submit" value="Eliminar Subcurso">
        </form>
    </main>
</body>
</html>
