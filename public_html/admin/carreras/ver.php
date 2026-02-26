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

// Obtener carrera
$db->query("SELECT * FROM carreras WHERE id_carrera = :id");
$db->bind(':id', $id);
$carrera = $db->single();

if (!$carrera) {
    header('Location: index.php?mensaje=error');
    exit;
}

// Obtener docentes y estudiantes asociados
$db->query("SELECT * FROM docentes WHERE id_carrera = :id");
$db->bind(':id', $id);
$docentes = $db->resultSet();

$db->query("SELECT * FROM estudiantes WHERE id_carrera = :id");
$db->bind(':id', $id);
$estudiantes = $db->resultSet();

include '../header_admin.php';
?>

<div class="container py-4">
    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    
    <h1 class="mb-4"><?php echo $carrera['nombre']; ?></h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Información de la Carrera</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">ID:</th>
                            <td><?php echo $carrera['id_carrera']; ?></td>
                        </tr>
                        <tr>
                            <th>Nombre:</th>
                            <td><?php echo $carrera['nombre']; ?></td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge bg-<?php echo ($carrera['estado'] == 'Activa') ? 'success' : 'danger'; ?>">
                                    <?php echo $carrera['estado']; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Descripción:</th>
                            <td><?php echo nl2br($carrera['descripcion']); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha Creación:</th>
                            <td><?php echo formatearFecha($carrera['fecha_creacion'], 'd/m/Y H:i'); ?></td>
                        </tr>
                    </table>
                    
                    <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                    <div class="mt-3">
                        <a href="editar.php?id=<?php echo $carrera['id_carrera']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h3 class="text-primary"><?php echo count($docentes); ?></h3>
                                <p class="text-muted">Docentes</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h3 class="text-info"><?php echo count($estudiantes); ?></h3>
                                <p class="text-muted">Estudiantes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
