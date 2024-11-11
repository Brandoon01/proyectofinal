<?php
include 'php/conexion.php';

// Iniciar sesión
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Obtener el ID del subcurso desde la URL
$id_subcurso = isset($_GET['id_subcurso']) ? intval($_GET['id_subcurso']) : 0;

// Inicializar mensaje y datos del formulario
$mensaje = '';
$titulo = '';
$descripcion = '';
$fecha_entrega = '';
$documento = '';

// Insertar nueva actividad
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha_entrega = $_POST['fecha_entrega'];
    $id_docente = $_SESSION['user_id']; // Asumimos que el ID del docente está en la sesión

    // Manejo de carga de archivo
    if (isset($_FILES['documento']) && $_FILES['documento']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['documento']['tmp_name'];
        $file_name = basename($_FILES['documento']['name']);
        $upload_dir = 'uploads/'; // Asegúrate de que esta carpeta tenga permisos de escritura

        // Mover el archivo a la carpeta de uploads
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $documento = $upload_dir . $file_name;
        } else {
            $mensaje = "Error al subir el documento.";
        }
    }

    // Verificar si ya existe una actividad con el mismo título en el mismo subcurso
    $sql_check = "SELECT COUNT(*) FROM Actividades WHERE titulo = ? AND id_subcurso = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('si', $titulo, $id_subcurso);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        $mensaje = "Error: Ya existe una actividad con el mismo título en este subcurso.";
    } else {
        $sql = "INSERT INTO Actividades (titulo, descripcion, fecha_entrega, id_subcurso, id_docente, archivo_adjunto) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssisi', $titulo, $descripcion, $fecha_entrega, $id_subcurso, $id_docente, $documento);

        if ($stmt->execute()) {
            // Redireccionar a la misma página para evitar reenvío del formulario
            header("Location: actividades.php?id_subcurso=" . $id_subcurso . "&mensaje=Actividad creada exitosamente");
            exit(); // Asegúrate de salir después de redirigir
        } else {
            $mensaje = "Error al crear la actividad: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Mostrar actividades del subcurso específico
$sql = "SELECT * FROM Actividades WHERE id_subcurso = ?";
$stmt_actividades = $conn->prepare($sql);
$stmt_actividades->bind_param('i', $id_subcurso);
$stmt_actividades->execute();
$result = $stmt_actividades->get_result();

// Obtener el nombre del subcurso
$sql_subcurso = "SELECT nombre_subcurso FROM Subcursos WHERE id_subcurso = ?";
$stmt_subcurso = $conn->prepare($sql_subcurso);
$stmt_subcurso->bind_param('i', $id_subcurso);
$stmt_subcurso->execute();
$stmt_subcurso->bind_result($nombre_subcurso);
$stmt_subcurso->fetch();
$stmt_subcurso->close();

// Obtener mensaje de la URL si existe
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Actividades</title>
    <link rel="stylesheet" href="css/archivo.css">
    <link rel="icon" href="img/logo.jpg" type="image/x-icon">
    <style>
        /* Estilos para el cuadro de diálogo */
        #mensajeDialog {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        #mensajeDialog .close {
            cursor: pointer;
            float: right;
        }
    </style>
    <script>
        function mostrarMensaje(mensaje) {
            document.getElementById('mensajeTexto').innerText = mensaje;
            document.getElementById('mensajeDialog').style.display = 'block';

            // Redirigir después de 3 segundos (3000 ms)
            setTimeout(function() {
                window.location.href = "actividades.php?id_subcurso=<?php echo $id_subcurso; ?>"; // Cambia a la URL correcta
            }, 3000);
        }

        function cerrarMensaje() {
            document.getElementById('mensajeDialog').style.display = 'none';
            window.location.href = "actividades.php?id_subcurso=<?php echo $id_subcurso; ?>"; // Cambia a la URL correcta
        }

        window.onload = function() {
            var mensaje = "<?php echo addslashes($mensaje); ?>";
            if (mensaje) {
                mostrarMensaje(mensaje);
            }
        }
    </script>
</head>
<body>
<header>
    <h1>Actividades del Docente</h1>
    <nav>
        <a href="Pinicio.php">Inicio</a>
        <a href="Ppersonal.php">Personal</a>    
        <a href="Pcursos.php">Cursos</a>
        <a href="Pperfil.php">Perfil</a>
        <a href="php/cerrar.php">Cerrar sesion</a>
    </nav>
</header>

<h1>Crear Nueva Actividad</h1>
<form action="actividades.php?id_subcurso=<?php echo $id_subcurso; ?>" method="post" enctype="multipart/form-data">
    <label for="titulo">Título:</label>
    <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" required><br>

    <label for="descripcion">Descripción:</label>
    <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($descripcion); ?></textarea><br>

    <label for="fecha_entrega">Fecha de Entrega:</label>
    <input type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($fecha_entrega); ?>" required><br>

    <label for="documento">Documento:</label>
    <input type="file" id="documento" name="documento" accept=".pdf,.doc,.docx,.ppt,.pptx"><br>

    <button type="submit">Crear Actividad</button>
</form>

<h2>Lista de Actividades del Subcurso: <?php echo htmlspecialchars($nombre_subcurso); ?></h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Descripción</th>
        <th>Fecha de Entrega</th>
        <th>ID Subcurso</th>
        <th>Documento</th>
        <th>Ver Entregas</th>
    </tr>
    <?php while ($actividad = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $actividad['id_actividad']; ?></td>
        <td><?php echo htmlspecialchars($actividad['titulo']); ?></td>
        <td><?php echo htmlspecialchars($actividad['descripcion']); ?></td>
        <td><?php echo htmlspecialchars($actividad['fecha_entrega']); ?></td>
        <td><?php echo htmlspecialchars($actividad['id_subcurso']); ?></td>
        <td>
            <?php if (!empty($actividad['documento'])): ?>
                <a href="test/<?php echo htmlspecialchars($actividad['documento']); ?>" target="_blank">Ver Documento</a>
            <?php else: ?>
                Sin documento
            <?php endif; ?>
        </td>
        <td>
            <a href="entregadas.php?id_actividad=<?php echo $actividad['id_actividad']; ?>">Ver Entregas</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Cuadro de diálogo para mensajes -->
<div id="mensajeDialog">
    <span class="close" onclick="cerrarMensaje()">✖</span>
    <p id="mensajeTexto"></p>
</div>

</body>
</html>
