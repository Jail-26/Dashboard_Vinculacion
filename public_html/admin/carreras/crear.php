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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = limpiarDatos($_POST['nombre']);
    $descripcion = limpiarDatos($_POST['descripcion']);
    $estado = $_POST['estado'];

    $db->query("INSERT INTO carreras (nombre, descripcion, estado) VALUES (:nombre, :descripcion, :estado)");
    $db->bind(':nombre', $nombre);
    $db->bind(':descripcion', $descripcion);
    $db->bind(':estado', $estado);

    if ($db->execute()) {
        $id_carrera = $db->lastInsertId();
        $db->registrarCambio($_SESSION['id_usuario'], 'carreras', $id_carrera, 'creación', null, ['nombre' => $nombre, 'descripcion' => $descripcion, 'estado' => $estado]);
        header('Location: index.php?mensaje=creado');
        exit;
    } else {
        $error = 'Error al crear la carrera.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Crear Nueva Carrera</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la Carrera *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="Activa" selected>Activa</option>
                        <option value="Inactiva">Inactiva</option>
                    </select>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Crear Carrera
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
