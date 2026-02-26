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

$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 15;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$db->query("SELECT COUNT(*) as total FROM docentes_fases");
$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

$db->query("SELECT df.*, d.nombres, d.apellidos, f.nombre as nombre_fase FROM docentes_fases df 
            LEFT JOIN docentes d ON df.id_docente = d.id_docente 
            LEFT JOIN fases f ON df.id_fase = f.id_fase 
            ORDER BY df.fecha_asignacion DESC LIMIT :offset, :limit");
$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
$asignaciones = $db->resultSet();
if (!$asignaciones) {
    $asignaciones = [];
}

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Asignación de Docentes a Fases</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nueva Asignación
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <?php
        $tipo_alerta = 'success';
        $mensaje = isset($_GET['mensaje']) ? ($_GET['mensaje'] == 'creado' ? 'Asignación creada exitosamente.' : ($_GET['mensaje'] == 'actualizado' ? 'Asignación actualizada.' : ($_GET['mensaje'] == 'eliminado' ? 'Asignación eliminada.' : 'Error'))) : '';
        $tipo_alerta = isset($_GET['mensaje']) && $_GET['mensaje'] == 'error' ? 'danger' : 'success';
        ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Asignaciones</h5>
        </div>
        <div class="card-body">
            <?php if (count($asignaciones) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Docente</th>
                                <th>Fase</th>
                                <th>Rol</th>
                                <th>Fecha Asignación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($asignaciones as $asignacion): ?>
                                <tr>
                                    <td><?php echo $asignacion['nombres'] . ' ' . $asignacion['apellidos']; ?></td>
                                    <td><?php echo $asignacion['nombre_fase']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($asignacion['rol'] == 'Responsable') ? 'success' : 'info'; ?>">
                                            <?php echo ucfirst($asignacion['rol']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatearFecha($asignacion['fecha_asignacion'], 'd/m/Y'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="editar.php?id=<?php echo $asignacion['id_docente_fase']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="eliminar.php?id=<?php echo $asignacion['id_docente_fase']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Seguro?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Paginación">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">No hay asignaciones registradas.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
