<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDatos($_POST['nombre']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!$error) {
        $db->query("INSERT INTO periodo_academico (nombre, fecha_inicio, fecha_fin, activo)
                    VALUES (:nombre, :fecha_inicio, :fecha_fin, :activo)");
        $db->bind(':nombre', $nombre);
        $db->bind(':fecha_inicio', $fecha_inicio);
        $db->bind(':fecha_fin', $fecha_fin);
        $db->bind(':activo', $activo, PDO::PARAM_BOOL);

        if ($db->execute()) {
            header('Location: index.php?mensaje=creado');
            exit;
        } else {
            $error = 'Error al crear el periodo.';
        }
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Crear Nuevo Periodo Académico</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
        </div>
        <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="activo" name="activo" checked>
            <label for="activo" class="form-check-label">Activo</label>
        </div>
        <button type="submit" class="btn btn-primary">Crear Periodo</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../footer_admin.php'; ?>