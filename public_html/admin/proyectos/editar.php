<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

if ($_SESSION['rol'] == 'visualizador') {
    header('Location: index.php?mensaje=sin_permisos');
    exit;
}

$db = new Database();
$id_proyecto = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener el proyecto
$db->query("SELECT * FROM proyectos WHERE id_proyecto = :id");
$db->bind(':id', $id_proyecto);
$proyecto = $db->single();

if (!$proyecto) {
    header('Location: index.php?mensaje=error');
    exit;
}

// Obtener programas para el selector
$db->query("SELECT id_programa, nombre FROM programas WHERE estado = 'Activo' ORDER BY nombre");
$programas = $db->resultSet();

// Obtener entidades receptoras para el selector
$db->query("SELECT id_entidad, nombre FROM entidades_receptoras ORDER BY nombre");
$entidades = $db->resultSet();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_programa = (int)$_POST['id_programa'];
    $id_entidad = (int)$_POST['id_entidad'] ?? null;
    $nombre = limpiarDatos($_POST['nombre']);
    $descripcion_corta = limpiarDatos($_POST['descripcion_corta']);
    $descripcion_extendida = $_POST['descripcion_extendida'];
    $objetivos = limpiarDatos($_POST['objetivos']);
    $resultados_esperados = limpiarDatos($_POST['resultados_esperados']);
    $comunidad_beneficiaria = limpiarDatos($_POST['comunidad_beneficiaria']);
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $estado = $_POST['estado'] ?? 'En ejecución';
    $publicado = isset($_POST['publicado']) ? 1 : 0;
    $banner = $proyecto['banner'];
    $datos_anteriores = $proyecto;

    // Manejo de la imagen/banner
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
        // Eliminar imagen anterior si existe
        if ($proyecto['banner']) {
            $archivo_anterior = UPLOAD_PATH . 'proyectos/' . $proyecto['banner'];
            if (file_exists($archivo_anterior)) {
                unlink($archivo_anterior);
            }
        }
        
        $resultado = subirImagen($_FILES['banner'], 'proyectos');
        if ($resultado['exito']) {
            $banner = $resultado['ruta'];
        }
    }

    $db->query("UPDATE proyectos SET id_programa = :id_programa, id_entidad = :id_entidad, nombre = :nombre, 
                descripcion_corta = :descripcion_corta, descripcion_extendida = :descripcion_extendida,
                objetivos = :objetivos, resultados_esperados = :resultados_esperados, 
                comunidad_beneficiaria = :comunidad_beneficiaria, fecha_inicio = :fecha_inicio, 
                fecha_fin = :fecha_fin, estado = :estado, publicado = :publicado, banner = :banner 
                WHERE id_proyecto = :id");
    $db->bind(':id_programa', $id_programa);
    $db->bind(':id_entidad', $id_entidad);
    $db->bind(':nombre', $nombre);
    $db->bind(':descripcion_corta', $descripcion_corta);
    $db->bind(':descripcion_extendida', $descripcion_extendida);
    $db->bind(':objetivos', $objetivos);
    $db->bind(':resultados_esperados', $resultados_esperados);
    $db->bind(':comunidad_beneficiaria', $comunidad_beneficiaria);
    $db->bind(':fecha_inicio', $fecha_inicio);
    $db->bind(':fecha_fin', $fecha_fin);
    $db->bind(':estado', $estado);
    $db->bind(':publicado', $publicado, PDO::PARAM_INT);
    $db->bind(':banner', $banner);
    $db->bind(':id', $id_proyecto, PDO::PARAM_INT);

    if ($db->execute()) {
        $db->registrarCambio($_SESSION['id_usuario'], 'proyectos', $id_proyecto, 'modificación', $datos_anteriores, $_POST);
        header('Location: index.php?mensaje=actualizado');
        exit;
    } else {
        $error = 'Error al actualizar el proyecto.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Editar Proyecto</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_programa" class="form-label">Programa *</label>
                            <select class="form-select" id="id_programa" name="id_programa" required>
                                <option value="">Selecciona un programa</option>
                                <?php foreach ($programas as $programa): ?>
                                    <option value="<?php echo $programa['id_programa']; ?>"
                                        <?php echo ($proyecto['id_programa'] == $programa['id_programa']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($programa['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_entidad" class="form-label">Entidad Receptora</label>
                            <select class="form-select" id="id_entidad" name="id_entidad">
                                <option value="">Selecciona una entidad</option>
                                <?php foreach ($entidades as $entidad): ?>
                                    <option value="<?php echo $entidad['id_entidad']; ?>"
                                        <?php echo ($proyecto['id_entidad'] == $entidad['id_entidad']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($entidad['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre del Proyecto *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($proyecto['nombre']); ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="descripcion_corta" class="form-label">Descripción Corta</label>
                            <input type="text" class="form-control" id="descripcion_corta" name="descripcion_corta" value="<?php echo htmlspecialchars($proyecto['descripcion_corta']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="comunidad_beneficiaria" class="form-label">Comunidad Beneficiaria</label>
                            <input type="text" class="form-control" id="comunidad_beneficiaria" name="comunidad_beneficiaria" value="<?php echo htmlspecialchars($proyecto['comunidad_beneficiaria']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion_extendida" class="form-label">Descripción Extendida</label>
                    <div style="background-color: white;">
                        <input type="hidden" class="form-control" id="contenido" name="descripcion_extendida">
                        <div id="editor" style="background:white; min-height:150px;">
                        </div>
                    </div>
                </div>
                
                
                <div class="mb-3">
                    <label for="objetivos" class="form-label">Objetivos</label>
                    <textarea class="form-control" id="objetivos" name="objetivos" rows="3"><?php echo htmlspecialchars($proyecto['objetivos']); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="resultados_esperados" class="form-label">Resultados Esperados</label>
                    <textarea class="form-control" id="resultados_esperados" name="resultados_esperados" rows="3"><?php echo htmlspecialchars($proyecto['resultados_esperados']); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($proyecto['fecha_inicio']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($proyecto['fecha_fin']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="En ejecución" <?php echo ($proyecto['estado'] == 'En ejecución') ? 'selected' : ''; ?>>En ejecución</option>
                                <option value="Finalizado" <?php echo ($proyecto['estado'] == 'Finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                                <option value="Suspendido" <?php echo ($proyecto['estado'] == 'Suspendido') ? 'selected' : ''; ?>>Suspendido</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="banner" class="form-label">Banner/Imagen</label>
                            <input type="file" class="form-control" id="banner" name="banner" accept=".jpg, .png, .jpeg, .webp">
                        </div>
                    </div>
                </div>
                
                <?php if ($proyecto['banner']): ?>
                    <div class="alert alert-info mb-3">
                        <strong>Banner actual:</strong><br>
                        <img src="<?php echo UPLOAD_URL . 'proyectos/' . htmlspecialchars($proyecto['banner']); ?>" alt="Banner" class="img-thumbnail" style="max-width: 200px;">
                    </div>
                <?php endif; ?>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="publicado" name="publicado" value="1" <?php echo $proyecto['publicado'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="publicado">
                        Publicado en el dashboard público
                    </label>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar Proyecto
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