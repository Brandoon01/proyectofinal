<?php
// Conexión a la base de datos
require_once 'php/conexion.php';

class AdminPanel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Función para agregar alumno
    public function agregarAlumno($documento, $nombre, $apellido, $carrera, $email, $contraseña, $telefono) {
        $stmt = $this->db->prepare("INSERT INTO Alumnos (documento, nombre, apellido, carrera, email, contraseña, telefono) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $documento, $nombre, $apellido, $carrera, $email, $contraseña, $telefono);
        return $stmt->execute();
    }

    // Función para eliminar alumno y registrar en AlumnosEliminados
    public function eliminarAlumno($documento) {
        // Obtener datos del alumno antes de eliminar
        $alumno = $this->buscarAlumno($documento);

        if ($alumno) {
            // Insertar datos en la tabla de eliminados
            $stmt = $this->db->prepare("INSERT INTO AlumnosEliminados (documento, nombre, apellido, carrera, email, telefono) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $alumno['documento'], $alumno['nombre'], $alumno['apellido'], $alumno['carrera'], $alumno['email'], $alumno['telefono']);
            $stmt->execute();

            // Eliminar el alumno
            $stmt = $this->db->prepare("DELETE FROM Alumnos WHERE documento = ?");
            $stmt->bind_param("s", $documento);
            return $stmt->execute();
        }
        return false;
    }

    // Función para modificar alumno por documento
    public function modificarAlumno($documento, $nombre, $apellido, $carrera, $email, $contraseña, $telefono) {
        $stmt = $this->db->prepare("UPDATE Alumnos SET nombre = ?, apellido = ?, carrera = ?, email = ?, contraseña = ?, telefono = ? WHERE documento = ?");
        $stmt->bind_param("sssssss", $nombre, $apellido, $carrera, $email, $contraseña, $telefono, $documento);
        return $stmt->execute();
    }

    // Función para buscar alumno por documento
    public function buscarAlumno($documento) {
        $stmt = $this->db->prepare("SELECT * FROM Alumnos WHERE documento = ?");
        $stmt->bind_param("s", $documento);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Función para obtener los registros de alumnos eliminados
    public function obtenerAlumnosEliminados() {
        $stmt = $this->db->prepare("SELECT * FROM AlumnosEliminados ORDER BY fecha_eliminacion DESC");
        $stmt->execute();
        return $stmt->get_result();
    }
}

// Crear una instancia del panel de administración
$adminPanel = new AdminPanel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'agregarAlumno':
                $adminPanel->agregarAlumno($_POST['documento'], $_POST['nombre'], $_POST['apellido'], $_POST['carrera'], $_POST['email'], $_POST['contraseña'], $_POST['telefono']);
                break;
            case 'eliminarAlumno':
                $adminPanel->eliminarAlumno($_POST['documento']);
                break;
            case 'modificarAlumno':
                $adminPanel->modificarAlumno($_POST['documento'], $_POST['nombre'], $_POST['apellido'], $_POST['carrera'], $_POST['email'], $_POST['contraseña'], $_POST['telefono']);
                break;
            case 'buscarAlumno':
                $alumno = $adminPanel->buscarAlumno($_POST['documento']);
                break;
        }
    }
}

// Obtener registros de alumnos eliminados
$alumnosEliminados = $adminPanel->obtenerAlumnosEliminados();
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
        <h1>Panel de Alumnos</h1>
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
        <!-- Formulario para agregar alumno -->
        <h2>Agregar Alumno</h2>
        <form method="POST">
            <input type="hidden" name="action" value="agregarAlumno">
            <input type="text" name="documento" placeholder="Documento" required>
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellido" placeholder="Apellido" required>
            <input type="text" name="carrera" placeholder="Carrera" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="contraseña" placeholder="Contraseña" required>
            <input type="text" name="telefono" placeholder="Teléfono" required>
            <input type="submit" value="Agregar Alumno">
        </form>

        <!-- Formulario para buscar alumno por documento -->
        <h2>Buscar Alumno</h2>
        <form method="POST">
            <input type="hidden" name="action" value="buscarAlumno">
            <input type="text" name="documento" placeholder="Documento del Alumno" required>
            <input type="submit" value="Buscar Alumno">
        </form>

        <?php if (isset($alumno) && $alumno): ?>
            <!-- Formulario para modificar alumno -->
            <h2>Modificar Alumno</h2>
            <form method="POST">
                <input type="hidden" name="action" value="modificarAlumno">
                <input type="hidden" name="documento" value="<?php echo $alumno['documento']; ?>">
                <input type="text" name="nombre" value="<?php echo $alumno['nombre']; ?>" placeholder="Nombre" required>
                <input type="text" name="apellido" value="<?php echo $alumno['apellido']; ?>" placeholder="Apellido" required>
                <input type="text" name="carrera" value="<?php echo $alumno['carrera']; ?>" placeholder="Carrera" required>
                <input type="email" name="email" value="<?php echo $alumno['email']; ?>" placeholder="Email" required>
                <input type="password" name="contraseña" placeholder="Contraseña">
                <input type="text" name="telefono" value="<?php echo $alumno['telefono']; ?>" placeholder="Teléfono" required>
                <input type="submit" value="Modificar Alumno">
            </form>
        <?php endif; ?>

        <!-- Formulario para eliminar alumno por documento -->
        <h2>Eliminar Alumno</h2>
        <form method="POST">
            <input type="hidden" name="action" value="eliminarAlumno">
            <input type="text" name="documento" placeholder="Documento del Alumno" required>
            <input type="submit" value="Eliminar Alumno">
        </form>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Mostrar alumnos eliminados -->
        <h2>Alumnos Eliminados</h2>
        <table>
            <tr>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Carrera</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Fecha de Eliminación</th>
            </tr>
            <?php while ($alumnoEliminado = $alumnosEliminados->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $alumnoEliminado['documento']; ?></td>
                    <td><?php echo $alumnoEliminado['nombre']; ?></td>
                    <td><?php echo $alumnoEliminado['apellido']; ?></td>
                    <td><?php echo $alumnoEliminado['carrera']; ?></td>
                    <td><?php echo $alumnoEliminado['email']; ?></td>
                    <td><?php echo $alumnoEliminado['telefono']; ?></td>
                    <td><?php echo $alumnoEliminado['fecha_eliminacion']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>
</body>
</html>
