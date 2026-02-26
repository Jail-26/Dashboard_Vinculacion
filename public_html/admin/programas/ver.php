<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$id_programa = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = new Database();

// Obtener el programa con su plan asociado
$db->query("SELECT p.*, pl.nombre as plan_nombre FROM programas p 
            LEFT JOIN planes pl ON p.id_plan = pl.id_plan 
            WHERE p.id_programa = :id_programa");
$db->bind(':id_programa', $id_programa);
$programa = $db->single();

if (!$programa) {
    header('Location: index.php?mensaje=no_encontrado');
    exit;
}

// Obtener proyectos asociados a este programa
$db->query("SELECT COUNT(*) as total FROM proyectos WHERE id_programa = :id_programa");
$db->bind(':id_programa', $id_programa);
$total_proyectos = $db->single()['total'];

include '../header_admin.php';
?>

<div class="container py-4">
    <a href="index.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
    
    <h1 class="mb-4"><?php echo htmlspecialchars($programa['nombre']); ?></h1>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Información del Programa</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Plan Estratégico:</th>
                            <td><?php echo htmlspecialchars($programa['plan_nombre'] ?? 'Sin asignar'); ?></td>
                        </tr>
                        <tr>
                            <th>Nombre:</th>
                            <td><?php echo htmlspecialchars($programa['nombre']); ?></td>
                        </tr>
                        <tr>
                            <th>Descripción:</th>
                            <td><?php echo nl2br(htmlspecialchars($programa['descripcion'])); ?></td>
                        </tr>
                        <tr>
                            <th>Fecha de Inicio:</th>
                            <td><?php echo $programa['fecha_inicio'] ? formatearFecha($programa['fecha_inicio']) : '-'; ?></td>
                        </tr>
                        <tr>
                            <th>Fecha de Fin:</th>
                            <td><?php echo $programa['fecha_fin'] ? formatearFecha($programa['fecha_fin']) : '-'; ?></td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge bg-<?php echo ($programa['estado'] == 'Activo') ? 'success' : (($programa['estado'] == 'Finalizado') ? 'secondary' : 'warning'); ?>">
                                    <?php echo htmlspecialchars($programa['estado']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Publicado:</th>
                            <td>
                                <span class="badge bg-<?php echo $programa['publicado'] ? 'success' : 'danger'; ?>">
                                    <?php echo $programa['publicado'] ? 'Sí' : 'No'; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha de Creación:</th>
                            <td><?php echo formatearFecha($programa['fecha_creacion'], 'd/m/Y H:i'); ?></td>
                        </tr>
                        <tr>
                            <th>Última Modificación:</th>
                            <td><?php echo formatearFecha($programa['fecha_modificacion'], 'd/m/Y H:i'); ?></td>
                        </tr>
                    </table>
                    
                    <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                    <div class="mt-3">
                        <a href="editar.php?id=<?php echo $programa['id_programa']; ?>" class="btn btn-primary">
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
                    <div class="mb-3 text-center">
                        <h3 class="text-primary"><?php echo $total_proyectos; ?></h3>
                        <p class="text-muted">Proyectos Asociados</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
