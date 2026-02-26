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

$db->query("SELECT d.*, c.nombre as nombre_carrera FROM docentes d LEFT JOIN carreras c ON d.id_carrera = c.id_carrera WHERE d.id_docente = :id");
$db->bind(':id', $id);
$docente = $db->single();

if (!$docente) {
    header('Location: index.php?mensaje=error');
    exit;
}

include '../header_admin.php';
?>

<div class="container py-4">
    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    
    <h1 class="mb-4"><?php echo $docente['nombres'] . ' ' . $docente['apellidos']; ?></h1>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Información del Docente</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Cédula:</th>
                            <td><?php echo $docente['cedula']; ?></td>
                        </tr>
                        <tr>
                            <th>Nombres:</th>
                            <td><?php echo $docente['nombres']; ?></td>
                        </tr>
                        <tr>
                            <th>Apellidos:</th>
                            <td><?php echo $docente['apellidos']; ?></td>
                        </tr>
                        <tr>
                            <th>Correo:</th>
                            <td><a href="mailto:<?php echo $docente['correo']; ?>"><?php echo $docente['correo']; ?></a></td>
                        </tr>
                        <tr>
                            <th>Carrera:</th>
                            <td><?php echo $docente['nombre_carrera']; ?></td>
                        </tr>
                        <tr>
                            <th>Título Académico:</th>
                            <td><?php echo $docente['titulo_academico']; ?></td>
                        </tr>
                        <!-- <tr>
                            <th>Dependencia:</th>
                            <td><?php echo $docente['dependencia']; ?></td>
                        </tr> -->
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge bg-<?php echo ($docente['estado'] == 'Activo') ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($docente['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha Registro:</th>
                            <td><?php echo formatearFecha($docente['fecha_registro'], 'd/m/Y H:i'); ?></td>
                        </tr>
                    </table>
                    
                    <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                    <div class="mt-3">
                        <a href="editar.php?id=<?php echo $docente['id_docente']; ?>" class="btn btn-primary">
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
                    <?php
                    $db->query("SELECT COUNT(*) as total FROM docentes_fases WHERE id_docente = :id");
                    $db->bind(':id', $id);
                    $total_fases = $db->single()['total'];
                    ?>
                    <div class="text-center mb-3">
                        <h3 class="text-primary"><?php echo $total_fases; ?></h3>
                        <p class="text-muted">Fases Asignadas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
