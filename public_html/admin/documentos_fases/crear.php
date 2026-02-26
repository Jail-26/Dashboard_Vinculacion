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

$db->query("SELECT * FROM fases WHERE estado IN ('Pendiente', 'En ejecución', 'Finalizada') ORDER BY nombre");
$fases = $db->resultSet();

$pre_id_fase = isset($_GET['fase']) ? (int)$_GET['fase'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_fase = (int)$_POST['id_fase'];
    $nombre = limpiarDatos($_POST['nombre']);
    $tipo = $_POST['tipo'];
    $archivo_url = null;
    $metadata = null;
    $archivo_url_externa = isset($_POST['archivo_url_externa']) ? trim($_POST['archivo_url_externa']) : '';

    // Validar que sea URL válida (http/https) - solo URLs externas
    if (!empty($archivo_url_externa)) {
        if (filter_var($archivo_url_externa, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//i', $archivo_url_externa)) {
            $archivo_url = $archivo_url_externa; // guardamos URL externa tal cual
        } else {
            $error = 'La URL proporcionada no es válida. Debe comenzar con http:// o https://';
        }
    } else {
        $error = 'Debes proporcionar una URL de documento (requerido).';
    }

    if (!isset($error)) {
        $db->query("INSERT INTO documentos_fases (id_fase, nombre, tipo, archivo_url, metadata, fecha_subida) 
                   VALUES (:id_fase, :nombre, :tipo, :archivo_url, :metadata, NOW())");
        $db->bind(':id_fase', $id_fase);
        $db->bind(':nombre', $nombre);
        $db->bind(':tipo', $tipo);
        $db->bind(':archivo_url', $archivo_url);
        $db->bind(':metadata', $metadata);

        if ($db->execute()) {
            $id_doc = $db->lastInsertId();
            $db->registrarCambio($_SESSION['id_usuario'], 'documentos_fases', $id_doc, 'creación', null, ['nombre' => $nombre, 'tipo' => $tipo]);
            header('Location: index.php?mensaje=creado');
            exit;
        } else {
            $error = 'Error al crear el documento.';
        }
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Agregar Nuevo Documento</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_fase" class="form-label">Fase *</label>
                            <select class="form-select" id="id_fase" name="id_fase" required>
                                <option value="">Selecciona una fase</option>
                                <?php foreach ($fases as $fase): ?>
                                    <option value="<?php echo $fase['id_fase']; ?>" <?php echo ($pre_id_fase && $pre_id_fase == $fase['id_fase']) ? 'selected' : ''; ?>>
                                        <?php echo $fase['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Documento *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Documento</label>
                            <select class="form-select" id="tipo" name="tipo">
                                <option value="Informe" selected>Informe</option>
                                <option value="Acta">Acta</option>
                                <option value="Evidencia">Evidencia</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="archivo_url_externa" class="form-label">URL del Documento *</label>
                            <input type="url" class="form-control" id="archivo_url_externa" name="archivo_url_externa" placeholder="https://dominio.com/archivo.pdf" required>
                            <small class="text-muted">URL externa del documento (https://)</small>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Agregar Documento
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
