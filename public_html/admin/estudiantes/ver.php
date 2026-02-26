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

$db->query("SELECT e.*, c.nombre as nombre_carrera FROM estudiantes e LEFT JOIN carreras c ON e.id_carrera = c.id_carrera WHERE e.id_estudiante = :id");
$db->bind(':id', $id);
$estudiante = $db->single();

if (!$estudiante) {
    header('Location: index.php?mensaje=error');
    exit;
}

$db->query("SELECT COUNT(*) as total FROM participaciones_estudiantes WHERE id_estudiante = :id");
$db->bind(':id', $id);
$total_participaciones = $db->single()['total'];

include '../header_admin.php';
?>

<div class="container py-4">
    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    
    <h1 class="mb-4"><?php echo $estudiante['nombres'] . ' ' . $estudiante['apellidos']; ?></h1>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Información del Estudiante</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Cédula:</th>
                            <td><?php echo $estudiante['cedula']; ?></td>
                        </tr>
                        <tr>
                            <th>Nombres:</th>
                            <td><?php echo $estudiante['nombres']; ?></td>
                        </tr>
                        <tr>
                            <th>Apellidos:</th>
                            <td><?php echo $estudiante['apellidos']; ?></td>
                        </tr>
                        <tr>
                            <th>Correo:</th>
                            <td><a href="mailto:<?php echo $estudiante['correo']; ?>"><?php echo $estudiante['correo']; ?></a></td>
                        </tr>
                        <tr>
                            <th>Carrera:</th>
                            <td><?php echo $estudiante['nombre_carrera']; ?></td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge bg-<?php echo ($estudiante['estado'] == 'Activo') ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($estudiante['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha Registro:</th>
                            <td><?php echo formatearFecha($estudiante['fecha_registro'], 'd/m/Y H:i'); ?></td>
                        </tr>
                    </table>
                    
                    <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                    <div class="mt-3">
                        <a href="editar.php?id=<?php echo $estudiante['id_estudiante']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Editar
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Estadísticas</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-primary"><?php echo $total_participaciones; ?></h3>
                        <p class="text-muted">Fases Participando</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
