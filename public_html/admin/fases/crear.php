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

$db->query("SELECT * FROM proyectos ORDER BY nombre");
$proyectos = $db->resultSet();

$db->query("SELECT * FROM periodos_academicos WHERE estado IN ('Activo', 'Planificado') ORDER BY nombre");
$periodos = $db->resultSet();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDatos($_POST['nombre']);
    $descripcion = ($_POST['descripcion']);
    $id_proyecto = (int)$_POST['id_proyecto'];
    $id_periodo = (int)$_POST['id_periodo'];
    $estado = $_POST['estado'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $banner = null;
    $cantidad_beneficiados = isset($_POST['cantidad_beneficiados']) ? (int)$_POST['cantidad_beneficiados'] : 0;
    $publicado = isset($_POST['publicado']) ? 1 : 0;

    // Manejo de la imagen/banner
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
        $resultado = subirImagen($_FILES['banner'], 'fases');
        if ($resultado['exito']) {
            $banner = $resultado['ruta'];
        }
    }

    $db->query("INSERT INTO fases (nombre, descripcion, id_proyecto, id_periodo, estado, fecha_inicio, fecha_fin, banner, cantidad_beneficiados,publicado) 
                VALUES (:nombre, :descripcion, :id_proyecto, :id_periodo, :estado, :fecha_inicio, :fecha_fin, :banner, :cantidad_beneficiados, :publicado)");
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

    if ($db->execute()) {
        $id_fase = $db->lastInsertId();
        $db->registrarCambio($_SESSION['id_usuario'], 'fases', $id_fase, 'creación', null, ['nombre' => $nombre]);
        header('Location: index.php?mensaje=creado');
        exit;
    } else {
        $error = 'Error al crear la fase.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Crear Nueva Fase</h1>
    
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
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_periodo" class="form-label">Período Académico *</label>
                            <select class="form-select" id="id_periodo" name="id_periodo" required>
                                <option value="">Selecciona un período</option>
                                <?php foreach ($periodos as $periodo): ?>
                                    <option value="<?php echo $periodo['id_periodo']; ?>">
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
                            <option value="<?php echo $proyecto['id_proyecto']; ?>">
                                <?php echo $proyecto['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="Pendiente" selected>Pendiente</option>
                        <option value="En ejecución">En ejecución</option>
                        <option value="Finalizada">Finalizada</option>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="cantidad_beneficiados" class="form-label">Cantidad de Beneficiados</label>
                            <input type="number" class="form-control" id="cantidad_beneficiados" name="cantidad_beneficiados" min="0" value="0">
                            <small class="text-muted">Número estimado de beneficiados por la fase</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="banner" class="form-label">Banner/Imagen</label>
                            <input type="file" class="form-control" id="banner" name="banner" accept=".jpg, .png, .jpeg, .webp">
                            <small class="text-muted">Imagen que se mostrará como banner de la fase</small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="publicado" name="publicado" value="1">
                    <label class="form-check-label" for="publicado">
                        Publicado en el dashboard público
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Crear Fase
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
