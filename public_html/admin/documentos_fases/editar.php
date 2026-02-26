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
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$db->query("SELECT * FROM documentos_fases WHERE id_documento = :id");
$db->bind(':id', $id);
$documento = $db->single();

if (!$documento) {
    header('Location: index.php?mensaje=error');
    exit;
}

$db->query("SELECT * FROM fases WHERE estado IN ('Pendiente', 'En ejecución', 'Finalizada') ORDER BY nombre");
$fases = $db->resultSet();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_fase = (int)$_POST['id_fase'];
    $nombre = limpiarDatos($_POST['nombre']);
    $tipo = $_POST['tipo'];
    $archivo_url_externa = isset($_POST['archivo_url_externa']) ? trim($_POST['archivo_url_externa']) : '';
    $datos_anteriores = $documento;

    if (!empty($archivo_url_externa)) {
        if (filter_var($archivo_url_externa, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $archivo_url_externa)) {
            $archivo_url = $archivo_url_externa;
        } else {
            $error = 'La URL proporcionada no es válida. Debe comenzar con http:// o https://';
        }
    } else {
        $error = 'Debes proporcionar una URL de documento (requerido).';
    }

    if (!isset($error)) {
        $db->query("UPDATE documentos_fases SET id_fase = :id_fase, nombre = :nombre, tipo = :tipo, archivo_url = :archivo_url WHERE id_documento = :id");
        $db->bind(':id_fase', $id_fase);
        $db->bind(':nombre', $nombre);
        $db->bind(':tipo', $tipo);
        $db->bind(':archivo_url', $archivo_url);
        $db->bind(':id', $id);

        if ($db->execute()) {
            $datos_nuevos = ['id_fase' => $id_fase, 'nombre' => $nombre, 'tipo' => $tipo];
            $db->registrarCambio($_SESSION['id_usuario'], 'documentos_fases', $id, 'modificación', $datos_anteriores, $datos_nuevos);
            header('Location: index.php?mensaje=actualizado');
            exit;
        } else {
            $error = 'Error al actualizar el documento.';
        }
    }
}

$db->query("SELECT nombre FROM fases WHERE id_fase = :id");
$db->bind(':id', $documento['id_fase']);
$fase = $db->single();

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Editar Documento</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_fase" class="form-label">Fase *</label>
                            <select class="form-select" id="id_fase" name="id_fase" required>
                                <option value="">Selecciona una fase</option>
                                <?php foreach ($fases as $f): ?>
                                    <option value="<?php echo $f['id_fase']; ?>" <?php echo ($f['id_fase'] == $documento['id_fase']) ? 'selected' : ''; ?>>
                                        <?php echo $f['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Documento *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $documento['nombre']; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Documento</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="Informe" <?php echo ($documento['tipo'] == 'Informe') ? 'selected' : ''; ?>>Informe</option>
                                <option value="Acta" <?php echo ($documento['tipo'] == 'Acta') ? 'selected' : ''; ?>>Acta</option>
                                <option value="Evidencia" <?php echo ($documento['tipo'] == 'Evidencia') ? 'selected' : ''; ?>>Evidencia</option>
                                <option value="Otro" <?php echo ($documento['tipo'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="archivo_url_externa" class="form-label">URL del Documento *</label>
                            <input type="url" class="form-control" id="archivo_url_externa" name="archivo_url_externa" placeholder="https://dominio.com/archivo.pdf" value="<?php echo htmlspecialchars($documento['archivo_url']); ?>" required>
                            <small class="text-muted">URL externa del documento (https://)</small>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
