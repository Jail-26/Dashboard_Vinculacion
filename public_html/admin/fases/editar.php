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

$db->query("SELECT * FROM fases WHERE id_fase = :id");
$db->bind(':id', $id);
$fase = $db->single();

if (!$fase) {
    header('Location: index.php?mensaje=error');
    exit;
}

$db->query("SELECT * FROM proyectos ORDER BY nombre");
$proyectos = $db->resultSet();

$db->query("SELECT * FROM periodos_academicos WHERE estado IN ('Activo', 'Planificado') ORDER BY nombre");
$periodos = $db->resultSet();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDatos($_POST['nombre']);
    $descripcion = limpiarDatos($_POST['descripcion']);
    $id_proyecto = (int)$_POST['id_proyecto'];
    $id_periodo = (int)$_POST['id_periodo'];
    $estado = $_POST['estado'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $banner = $fase['banner'];
    $cantidad_beneficiados = isset($_POST['cantidad_beneficiados']) ? (int)$_POST['cantidad_beneficiados'] : (int)$fase['cantidad_beneficiados'];
    $publicado = isset($_POST['publicado']) ? 1 : 0;

    $datos_anteriores = $fase;

    // Manejo de la imagen/banner
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
        // Eliminar imagen anterior si existe
        if ($fase['banner']) {
            $archivo_anterior = UPLOAD_PATH . 'fases/' . $fase['banner'];
            if (file_exists($archivo_anterior)) {
                unlink($archivo_anterior);
            }
        }

        $resultado = subirImagen($_FILES['banner'], 'fases');
        if ($resultado['exito']) {
            $banner = $resultado['ruta'];
        }
    }

    $db->query("UPDATE fases SET nombre = :nombre, descripcion = :descripcion, id_proyecto = :id_proyecto, id_periodo = :id_periodo, estado = :estado, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, banner = :banner, cantidad_beneficiados = :cantidad_beneficiados, publicado = :publicado WHERE id_fase = :id");
    $db->bind(':nombre', $nombre);
    $db->bind(':descripcion', $descripcion);
    $db->bind(':id_proyecto', $id_proyecto);
    $db->bind(':id_periodo', $id_periodo);
    $db->bind(':estado', $estado);
    $db->bind(':fecha_inicio', $fecha_inicio);
    $db->bind(':fecha_fin', $fecha_fin);
    $db->bind(':banner', $banner);
    $db->bind(':cantidad_beneficiados', $cantidad_beneficiados);
    $db->bind(':publicado', $publicado);
    $db->bind(':id', $id);


    if ($db->execute()) {
        $datos_nuevos = ['nombre' => $nombre, 'estado' => $estado];
        $db->registrarCambio($_SESSION['id_usuario'], 'fases', $id, 'modificación', $datos_anteriores, $datos_nuevos);
        header('Location: index.php?mensaje=actualizado');
        exit;
    } else {
        $error = 'Error al actualizar la fase.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Editar Fase</h1>

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
                            <label for="nombre" class="form-label">Nombre de la Fase *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $fase['nombre']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_periodo" class="form-label">Período Académico *</label>
                            <select class="form-select" id="id_periodo" name="id_periodo" required>
                                <?php foreach ($periodos as $periodo): ?>
                                    <option value="<?php echo $periodo['id_periodo']; ?>" <?php echo ($fase['id_periodo'] == $periodo['id_periodo']) ? 'selected' : ''; ?>>
                                        <?php echo $periodo['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <div style="background-color: white;">
                        <input type="hidden" class="form-control" id="contenido" name="descripcion">
                        <div id="editor" style="background:white; min-height:150px;">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="id_proyecto" class="form-label">Proyecto Asociado</label>
                    <select class="form-select" id="id_proyecto" name="id_proyecto">
                        <option value="">Sin proyecto asignado</option>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <option value="<?php echo $proyecto['id_proyecto']; ?>" <?php echo ($fase['id_proyecto'] == $proyecto['id_proyecto']) ? 'selected' : ''; ?>>
                                <?php echo $proyecto['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo $fase['fecha_inicio']; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo $fase['fecha_fin']; ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="Pendiente" <?php echo ($fase['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="En ejecución" <?php echo ($fase['estado'] == 'En ejecución') ? 'selected' : ''; ?>>En ejecución</option>
                        <option value="Finalizada" <?php echo ($fase['estado'] == 'Finalizada') ? 'selected' : ''; ?>>Finalizada</option>
                    </select>
                </div>

                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="cantidad_beneficiados" class="form-label">Cantidad de Beneficiados</label>
                            <input type="number" class="form-control" id="cantidad_beneficiados" name="cantidad_beneficiados" min="0" value="<?php echo (int)$fase['cantidad_beneficiados']; ?>">
                            <small class="text-muted">Número estimado de beneficiados por la fase</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="banner" class="form-label">Banner/Imagen</label>
                            <input type="file" class="form-control" id="banner" name="banner" accept=".jpg, .png, .jpeg, .webp">
                            <small class="text-muted">Imagen que se mostrará como banner de la fase</small>
                        </div>
                        <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="publicado" name="publicado" value="1" <?php echo $fase['publicado'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="publicado">
                            Publicado en el dashboard público
                        </label>
                    </div>
                    </div>
                </div>

                <?php if ($fase['banner']): ?>
                    <div class="alert alert-info mb-3">
                        <strong>Banner actual:</strong><br>
                        <img src="<?php echo UPLOAD_URL . 'fases/' . htmlspecialchars($fase['banner']); ?>" alt="Banner" class="img-thumbnail" style="max-width: 200px;">
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar Fase
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {

    const toolbarOptions = [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline'],
        ['link', 'image', 'video'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['clean']
    ];

    const quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: toolbarOptions,
                handlers: {
                    image: function () {
                        const url = prompt("Pega la URL de la imagen:");
                        if (url) {
                            const range = this.quill.getSelection(true);
                            this.quill.insertEmbed(range.index, 'image', url, Quill.sources.USER);
                        }
                    }
                }
            }
        }
    });

    // Manejar envío del formulario
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', function () {
            document.querySelector('#contenido').value = quill.root.innerHTML;
        });
    }

});
</script>

<?php include '../footer_admin.php'; ?>