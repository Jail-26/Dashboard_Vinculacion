<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit;
}

$db = new Database();

// Obtener la configuración actual (asumimos solo una fila activa)
$db->query("SELECT cd.*, p.nombre as periodo_academico FROM configuracion_dashboard cd INNER JOIN periodos_academicos p ON cd.id_periodo_academico = p.id_periodo ORDER BY id_configuracion DESC LIMIT 1");
$config = $db->single();

// Obtener todos los períodos académicos disponibles
$db->query("SELECT id_periodo, nombre FROM periodos_academicos ORDER BY nombre DESC");
$periodos = $db->resultSet();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $id_periodo_academico = (int)$_POST['id_periodo_academico'];

    // Actualizar configuración
    $db->query("UPDATE configuracion_dashboard SET titulo = :titulo, id_periodo_academico = :id_periodo WHERE id_configuracion = :id");
    $db->bind(':titulo', $titulo);
    $db->bind(':id_periodo', $id_periodo_academico);
    $db->bind(':id', $config['id_configuracion']);
    $db->execute();

    header('Location: configuracion.php?exito=1');
    exit;
}


include 'header_admin.php';
?>

<div class="container py-4">
    <h2>Configuración General del Dashboard</h2>
    <?php if (isset($_GET['exito'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Configuración actualizada correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <form method="post">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="titulo" class="form-label">Título del Dashboard *</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($config['titulo'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="id_periodo_academico" class="form-label">Período Académico Actual *</label>
                    <select class="form-select" id="id_periodo_academico" name="id_periodo_academico" required>
                        <option value="">Selecciona un período académico</option>
                        <?php foreach ($periodos as $periodo): ?>
                            <option value="<?php echo $periodo['id_periodo']; ?>" 
                                <?php echo ($config['id_periodo_academico'] == $periodo['id_periodo']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($periodo['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>

<?php include 'footer_admin.php'; ?>