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

// Configuración de paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener el historial de cambios
$db->query("SELECT h.*, u.nombre, u.apellido FROM historial_cambios h LEFT JOIN usuarios u ON h.id_usuario = u.id_usuario ORDER BY h.fecha_cambio DESC LIMIT :offset, :limit");
$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
$historial = $db->resultSet();

// Obtener el total de registros para la paginación
$db->query("SELECT COUNT(*) as total FROM historial_cambios");
$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <h1>Historial de Cambios</h1>
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Cambios</h5>
        </div>
        <div class="card-body">
            <?php if (count($historial) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Tabla</th>
                                <th>Acción</th>
                                <th>Fecha</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $cambio): ?>
                                <tr>
                                    <td><?php echo $cambio['nombre'] . ' ' . $cambio['apellido']; ?></td>
                                    <td><?php echo ucfirst($cambio['tabla_afectada']); ?></td>
                                    <td><?php echo ucfirst($cambio['tipo_cambio']); ?></td>
                                    <td><?php echo formatearFecha($cambio['fecha_cambio'], 'd/m/Y H:i'); ?></td>
                                    <td><?php echo $cambio['ip_usuario']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Paginación de historial">
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
                <p class="text-center">No se encontraron registros en el historial.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>