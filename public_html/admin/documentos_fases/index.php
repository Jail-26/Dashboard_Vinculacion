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

$db->query("SELECT COUNT(*) as total FROM documentos_fases");
$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

$db->query("SELECT df.*, f.nombre as nombre_fase FROM documentos_fases df 
            LEFT JOIN fases f ON df.id_fase = f.id_fase 
            ORDER BY df.fecha_subida DESC LIMIT :offset, :limit");
$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
$documentos = $db->resultSet();
if (!$documentos) {
    $documentos = [];
}

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Documentos de Fases</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Agregar Documento
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <?php
        $tipo_alerta = 'success';
        $mensaje = isset($_GET['mensaje']) ? ($_GET['mensaje'] == 'creado' ? 'Documento subido exitosamente.' : ($_GET['mensaje'] == 'actualizado' ? 'Documento actualizado.' : ($_GET['mensaje'] == 'eliminado' ? 'Documento eliminado.' : 'Error'))) : '';
        $tipo_alerta = isset($_GET['mensaje']) && $_GET['mensaje'] == 'error' ? 'danger' : 'success';
        ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Documentos</h5>
        </div>
        <div class="card-body">
            <?php if (count($documentos) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Fase</th>
                                <th>Tipo</th>
                                <th>Fecha Subida</th>
                                <th>Enlace</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $doc): ?>
                                <tr>
                                    <td><?php echo truncarTexto($doc['nombre'], 30); ?></td>
                                    <td><?php echo $doc['nombre_fase'] ?: 'Sin fase'; ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst($doc['tipo']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatearFecha($doc['fecha_subida'], 'd/m/Y H:i'); ?></td>
                                    <td>
                                        <?php if ($doc['archivo_url']): ?>
                                            <a href="<?php echo htmlspecialchars($doc['archivo_url']); ?>" target="_blank" class="btn btn-sm btn-info" title="Abrir documento">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="editar.php?id=<?php echo $doc['id_documento']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="eliminar.php?id=<?php echo $doc['id_documento']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Seguro?');">
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
                <p class="text-center">No hay documentos registrados.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
