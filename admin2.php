<?php
// Conexión a la base de datos
require_once 'php/conexion.php';

class AdminPanel {
    private $db;

    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // Funciones para la tabla Docentes
    public function agregarDocente($documento, $nombre, $apellido, $email, $contraseña, $telefono) {
        $stmt = $this->db->prepare("INSERT INTO Docentes (documento, nombre, apellido, email, contraseña, telefono) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $documento, $nombre, $apellido, $email, $contraseña, $telefono);
        return $stmt->execute();
    }

    public function eliminarDocente($documento) {
        $stmt = $this->db->prepare("DELETE FROM Docentes WHERE documento = ?");
        $stmt->bind_param("s", $documento);
        return $stmt->execute();
    }

    public function modificarDocente($documento, $nombre, $apellido, $email, $contraseña, $telefono) {
        $stmt = $this->db->prepare("UPDATE Docentes SET nombre = ?, apellido = ?, email = ?, contraseña = ?, telefono = ? WHERE documento = ?");
        $stmt->bind_param("ssssss", $nombre, $apellido, $email, $contraseña, $telefono, $documento);
        return $stmt->execute();
    }

    public function buscarDocente($documento) {
        $stmt = $this->db->prepare("SELECT * FROM Docentes WHERE documento = ?");
        $stmt->bind_param("s", $documento);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

// Crear una instancia del panel de administración
$adminPanel = new AdminPanel($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'agregarDocente':
                $adminPanel->agregarDocente($_POST['documento'], $_POST['nombre'], $_POST['apellido'], $_POST['email'], $_POST['contraseña'], $_POST['telefono']);
                break;
            case 'eliminarDocente':
                $adminPanel->eliminarDocente($_POST['documento']);
                break;
            case 'modificarDocente':
                $adminPanel->modificarDocente($_POST['documento'], $_POST['nombre'], $_POST['apellido'], $_POST['email'], $_POST['contraseña'], $_POST['telefono']);
                break;
            case 'buscarDocente':
                $docente = $adminPanel->buscarDocente($_POST['documento']);
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
        <h1>Panel de Docentes</h1>
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
        <h2>Agregar Docente</h2>
        <form method="POST">
            <input type="hidden" name="action" value="agregarDocente">
            <input type="text" name="documento" placeholder="Documento" required>
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="text" name="apellido" placeholder="Apellido" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="contraseña" placeholder="Contraseña" required>
            <input type="text" name="telefono" placeholder="Teléfono" required>
            <input type="submit" value="Agregar Docente">
        </form>

        <h2>Buscar Docente</h2>
        <form method="POST">
            <input type="hidden" name="action" value="buscarDocente">
            <input type="text" name="documento" placeholder="Documento del Docente" required>
            <input type="submit" value="Buscar Docente">
        </form>

        <?php if (isset($docente) && $docente): ?>
            <h2>Modificar Docente</h2>
            <form method="POST">
                <input type="hidden" name="action" value="modificarDocente">
                <input type="hidden" name="documento" value="<?php echo $docente['documento']; ?>">
                <input type="text" name="nombre" value="<?php echo $docente['nombre']; ?>" placeholder="Nombre" required>
                <input type="text" name="apellido" value="<?php echo $docente['apellido']; ?>" placeholder="Apellido" required>
                <input type="email" name="email" value="<?php echo $docente['email']; ?>" placeholder="Email" required>
                <input type="password" name="contraseña" placeholder="Contraseña" required>
                <input type="text" name="telefono" value="<?php echo $docente['telefono']; ?>" placeholder="Teléfono" required>
                <input type="submit" value="Modificar Docente">
            </form>
        <?php endif; ?>

        <h2>Eliminar Docente</h2>
        <form method="POST">
            <input type="hidden" name="action" value="eliminarDocente">
            <input type="text" name="documento" placeholder="Documento del Docente" required>
            <input type="submit" value="Eliminar Docente">
        </form>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>
    </main>
</body>
</html>
