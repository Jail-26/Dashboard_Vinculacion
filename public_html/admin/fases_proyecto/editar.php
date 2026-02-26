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

$id_fase = (int)$_GET['id'];
$db = new Database();
$error = '';

// Obtener fase actual
$db->query("SELECT * FROM fases_proyecto WHERE id_fase = :id");
$db->bind(':id', $id_fase, PDO::PARAM_INT);
$fase = $db->single();

if (!$fase) {
    header('Location: index.php');
    exit;
}

// Obtener proyectos y periodos
$db->query("SELECT id_proyecto, nombre FROM proyectos ORDER BY nombre ASC");
$proyectos = $db->resultSet();

$db->query("SELECT id_periodo, nombre FROM periodo_academico ORDER BY nombre ASC");
$periodos = $db->resultSet();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_proyecto = $_POST['id_proyecto'];
    $id_periodo = $_POST['id_periodo'];
    $nombre_fase = limpiarDatos($_POST['nombre_fase']);
    $descripcion = $_POST['descripcion'];
    $estado = $_POST['estado'];
    $comunidad_beneficiaria = limpiarDatos($_POST['comunidad_beneficiaria']);
    $cantidad_beneficiados = (int)$_POST['cantidad_beneficiados'];
    $cantidad_estudiantes = (int)$_POST['cantidad_estudiantes'];
    $cantidad_profesores = (int)$_POST['cantidad_profesores'];
    $pdf_url = limpiarDatos($_POST['pdf_url']);

    // Imagen
    $imagen = $_FILES['imagen'];
    $imagen_url = $fase['imagen_url'];
    if ($imagen['name']) {
        $directorio_destino = '../../uploads/fases/';
        if (!is_dir($directorio_destino)) mkdir($directorio_destino, 0755, true);
        $nombre_imagen = uniqid() . '-' . basename($imagen['name']);
        $ruta_imagen = $directorio_destino . $nombre_imagen;
        if (move_uploaded_file($imagen['tmp_name'], $ruta_imagen)) {
            $imagen_url = '/uploads/fases/' . $nombre_imagen;
        } else {
            $error = 'Error al subir la imagen.';
        }
    }

    if (empty($nombre_fase) || empty($id_proyecto) || empty($id_periodo)) {
        $error = 'Los campos de proyecto, periodo y nombre de fase son obligatorios.';
    } elseif (!$error) {
        $db->query("UPDATE fases_proyecto SET 
                    id_proyecto = :id_proyecto,
                    id_periodo = :id_periodo,
                    nombre_fase = :nombre_fase,
                    descripcion = :descripcion,
                    estado = :estado,
                    comunidad_beneficiaria = :comunidad_beneficiaria,
                    cantidad_beneficiados = :cantidad_beneficiados,
                    cantidad_estudiantes = :cantidad_estudiantes,
                    cantidad_profesores = :cantidad_profesores,
                    imagen_url = :imagen_url,
                    pdf_url = :pdf_url
                    WHERE id_fase = :id");

        $db->bind(':id_proyecto', $id_proyecto, PDO::PARAM_INT);
        $db->bind(':id_periodo', $id_periodo, PDO::PARAM_INT);
        $db->bind(':nombre_fase', $nombre_fase);
        $db->bind(':descripcion', $descripcion);
        $db->bind(':estado', $estado);
        $db->bind(':comunidad_beneficiaria', $comunidad_beneficiaria);
        $db->bind(':cantidad_beneficiados', $cantidad_beneficiados, PDO::PARAM_INT);
        $db->bind(':cantidad_estudiantes', $cantidad_estudiantes, PDO::PARAM_INT);
        $db->bind(':cantidad_profesores', $cantidad_profesores, PDO::PARAM_INT);
        $db->bind(':imagen_url', $imagen_url);
        $db->bind(':pdf_url', $pdf_url);
        $db->bind(':id', $id_fase, PDO::PARAM_INT);

        if ($db->execute()) {
            header('Location: index.php?mensaje=editado');
            exit;
        } else {
            $error = 'Error al actualizar la fase.';
        }
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Editar Fase</h1>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="id_proyecto" class="form-label">Proyecto</label>
            <select name="id_proyecto" id="id_proyecto" class="form-select" required>
                <option value="">Seleccione un proyecto</option>
                <?php foreach ($proyectos as $proyecto): ?>
                    <option value="<?php echo $proyecto['id_proyecto']; ?>" <?php echo ($fase['id_proyecto'] == $proyecto['id_proyecto']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($proyecto['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="id_periodo" class="form-label">Periodo Académico</label>
            <select name="id_periodo" id="id_periodo" class="form-select" required>
                <option value="">Seleccione un periodo</option>
                <?php foreach ($periodos as $periodo): ?>
                    <option value="<?php echo $periodo['id_periodo']; ?>" <?php echo ($fase['id_periodo'] == $periodo['id_periodo']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($periodo['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="nombre_fase" class="form-label">Nombre Fase</label>
            <input type="text" class="form-control" id="nombre_fase" name="nombre_fase" value="<?php echo htmlspecialchars($fase['nombre_fase']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion"><?php echo htmlspecialchars($fase['descripcion']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" id="estado" class="form-select" required>
                <option value="En ejecución" <?php echo ($fase['estado']=='En ejecución')?'selected':''; ?>>En ejecución</option>
                <option value="Ejecutada" <?php echo ($fase['estado']=='Ejecutada')?'selected':''; ?>>Ejecutada</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="comunidad_beneficiaria" class="form-label">Comunidad Beneficiaria</label>
            <input type="text" class="form-control" id="comunidad_beneficiaria" name="comunidad_beneficiaria" value="<?php echo htmlspecialchars($fase['comunidad_beneficiaria']); ?>">
        </div>

        <div class="mb-3">
            <label for="cantidad_beneficiados" class="form-label">Cantidad de Beneficiados</label>
            <input type="number" class="form-control" id="cantidad_beneficiados" name="cantidad_beneficiados" value="<?php echo $fase['cantidad_beneficiados']; ?>">
        </div>

        <div class="mb-3">
            <label for="cantidad_estudiantes" class="form-label">Cantidad de Estudiantes</label>
            <input type="number" class="form-control" id="cantidad_estudiantes" name="cantidad_estudiantes" value="<?php echo $fase['cantidad_estudiantes']; ?>">
        </div>

        <div class="mb-3">
            <label for="cantidad_profesores" class="form-label">Cantidad de Profesores</label>
            <input type="number" class="form-control" id="cantidad_profesores" name="cantidad_profesores" value="<?php echo $fase['cantidad_profesores']; ?>">
        </div>

        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen</label>
            <?php if($fase['imagen_url']): ?>
                <div class="mb-2"><img src="<?php echo $fase['imagen_url']; ?>" alt="Imagen" width="150"></div>
            <?php endif; ?>
            <input type="file" class="form-control" name="imagen" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="pdf_url" class="form-label">PDF URL</label>
            <input type="url" class="form-control" name="pdf_url" value="<?php echo htmlspecialchars($fase['pdf_url']); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Fase</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php include '../footer_admin.php'; ?>