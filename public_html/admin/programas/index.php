<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Verificar si el usuario está logueado
if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

// Conexión a la base de datos
$db = new Database();

// Configuración de paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Búsqueda
$busqueda = isset($_GET['busqueda']) ? limpiarDatos($_GET['busqueda']) : '';
$busquedaLike = '%' . $busqueda . '%';

// Obtener total de registros (para la paginación)
if (!empty($busqueda)) {
    $db->query("
        SELECT COUNT(*) as total 
        FROM programas 
        WHERE nombre LIKE :nombre 
           OR descripcion LIKE :descripcion 
    ");
    $like = "%$busqueda%";
    $db->bind(':nombre', $like, PDO::PARAM_STR);
    $db->bind(':descripcion', $like, PDO::PARAM_STR);
} else {
    $db->query("SELECT COUNT(*) as total FROM programas");
}

$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener listado de programas
if (!empty($busqueda)) {
    $db->query("
        SELECT * FROM programas 
        WHERE nombre LIKE :nombre 
           OR descripcion LIKE :descripcion 
        ORDER BY fecha_creacion DESC 
        LIMIT :limit OFFSET :offset
    ");
    $like = "%$busqueda%";
    $db->bind(':nombre', $like, PDO::PARAM_STR);
    $db->bind(':descripcion', $like, PDO::PARAM_STR);
    $db->bind(':offset', $offset, PDO::PARAM_INT);
    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
} else {
    $db->query("SELECT * FROM programas ORDER BY fecha_creacion DESC LIMIT :limit OFFSET :offset");
    $db->bind(':offset', $offset, PDO::PARAM_INT);
    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
}


$programas = $db->resultSet();
if (!$programas) {
    $programas = []; // Asegurar que $programas sea un array vacío si no hay resultados
}

// Incluir el header
include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Programas</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Programa
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <?php
        $tipo_alerta = 'success';
        $mensaje = '';
        
        switch ($_GET['mensaje']) {
            case 'creado':
                $mensaje = 'El programa ha sido creado exitosamente.';
                break;
            case 'actualizado':
                $mensaje = 'El programa ha sido actualizado exitosamente.';
                break;
            case 'eliminado':
                $mensaje = 'El programa ha sido eliminado exitosamente.';
                break;
            case 'error':
                $tipo_alerta = 'danger';
                $mensaje = 'Ha ocurrido un error. Inténtalo de nuevo.';
                break;
        }
        ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filtros y búsqueda -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar programas..." value="<?php echo $busqueda; ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <?php if (!empty($busqueda)): ?>
                        <a href="index.php" class="btn btn-outline-danger">
                            <i class="fas fa-times me-2"></i>Limpiar filtros
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Listado de programas -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Programas</h5>
        </div>
        <div class="card-body">
            <?php if (count($programas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Proyectos</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($programas as $programa): 
                                // Contar proyectos asociados
                                $db->query("SELECT COUNT(*) as total FROM proyectos WHERE id_programa = :id_programa");
                                $db->bind(':id_programa', $programa['id_programa']);
                                $total_proyectos = $db->single()['total'];
                            ?>
                                <tr>
                                    <td><?php echo $programa['id_programa']; ?></td>
                                    <td><?php echo $programa['nombre']; ?></td>
                                    <td><?php echo $programa['fecha_inicio'] ? formatearFecha($programa['fecha_inicio']) : '-'; ?></td>
                                    <td><?php echo $programa['fecha_fin'] ? formatearFecha($programa['fecha_fin']) : '-'; ?></td>
                                    <td>
                                        <a href="../proyectos/index.php?id_programa=<?php echo $programa['id_programa']; ?>" class="badge bg-info text-decoration-none">
                                            <?php echo $total_proyectos; ?> proyectos
                                        </a>
                                    </td>
                                    <td><?php echo formatearFecha($programa['fecha_creacion'], 'd/m/Y H:i'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $programa['id_programa']; ?>" class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="editar.php?id=<?php echo $programa['id_programa']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="eliminar.php?id=<?php echo $programa['id_programa']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este programa?');">
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
                
                <!-- Paginación -->
                <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Paginación de programas">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">No se encontraron programas.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>