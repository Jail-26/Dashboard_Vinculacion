<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_periodo = (int)$_GET['id'];
$db = new Database();
$error = '';

$db->query("SELECT * FROM periodo_academico WHERE id_periodo = :id");
$db->bind(':id', $id_periodo, PDO::PARAM_INT);
$periodo = $db->single();

if (!$periodo) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDatos($_POST['nombre']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (empty($nombre) || empty($fecha_inicio) || empty($fecha_fin)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!$error) {
        $db->query("UPDATE periodo_academico SET nombre = :nombre, fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin, activo = :activo WHERE id_periodo = :id");
        $db->bind(':nombre', $nombre);
        $db->bind(':fecha_inicio', $fecha_inicio);
        $db->bind(':fecha_fin', $fecha_fin);
        $db->bind(':activo', $activo, PDO::PARAM_BOOL);
        $db->bind(':id', $id_periodo, PDO::PARAM_INT);

        if ($db->execute()) {
            header('Location: index.php?mensaje=editado');
            exit;
        } else {
            $error = 'Error al actualizar el periodo.';
        }
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Editar Periodo Académico</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($periodo['nombre']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $periodo['fecha_inicio']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $periodo['fecha_fin']; ?>" required>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="activo" name="activo" <?php echo ($periodo['activo']) ? 'checked' : ''; ?>>
            <label for="activo" class="form-check-label">Activo</label>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Periodo</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../footer_admin.php'; ?>