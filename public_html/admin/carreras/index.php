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

$db = new Database();

// Configuración de paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Búsqueda
$busqueda = isset($_GET['busqueda']) ? limpiarDatos($_GET['busqueda']) : '';
$busquedaLike = '%' . $busqueda . '%';

// Obtener total de registros
if (!empty($busqueda)) {
    $db->query("SELECT COUNT(*) as total FROM carreras WHERE nombre LIKE :busqueda1 OR descripcion LIKE :busqueda2");
    $db->bind(':busqueda1', $busquedaLike);
    $db->bind(':busqueda2', $busquedaLike);
} else {
    $db->query("SELECT COUNT(*) as total FROM carreras");
}
$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener listado de carreras
if (!empty($busqueda)) {
    $db->query("SELECT * FROM carreras WHERE nombre LIKE :busqueda1 OR descripcion LIKE :busqueda2 ORDER BY fecha_creacion DESC LIMIT :offset, :limit");
    $db->bind(':busqueda1', $busquedaLike);
    $db->bind(':busqueda2', $busquedaLike);
    $db->bind(':offset', $offset, PDO::PARAM_INT);
    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
} else {
    $db->query("SELECT * FROM carreras ORDER BY fecha_creacion DESC LIMIT :offset, :limit");
    $db->bind(':offset', $offset, PDO::PARAM_INT);
    $db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
}
$carreras = $db->resultSet();
if (!$carreras) {
    $carreras = [];
}

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Carreras</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nueva Carrera
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <?php
        $tipo_alerta = 'success';
        $mensaje = '';
        
        switch ($_GET['mensaje']) {
            case 'creado':
                $mensaje = 'La carrera ha sido creada exitosamente.';
                break;
            case 'actualizado':
                $mensaje = 'La carrera ha sido actualizada exitosamente.';
                break;
            case 'eliminado':
                $mensaje = 'La carrera ha sido eliminada exitosamente.';
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
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar carreras..." value="<?php echo $busqueda; ?>">
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
    
    <!-- Listado de carreras -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Carreras</h5>
        </div>
        <div class="card-body">
            <?php if (count($carreras) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Docentes</th>
                                <th>Estudiantes</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carreras as $carrera): 
                                // Contar docentes y estudiantes
                                $db->query("SELECT COUNT(*) as total FROM docentes WHERE id_carrera = :id_carrera");
                                $db->bind(':id_carrera', $carrera['id_carrera']);
                                $total_docentes = $db->single()['total'];
                                
                                $db->query("SELECT COUNT(*) as total FROM estudiantes WHERE id_carrera = :id_carrera");
                                $db->bind(':id_carrera', $carrera['id_carrera']);
                                $total_estudiantes = $db->single()['total'];
                            ?>
                                <tr>
                                    <td><?php echo $carrera['id_carrera']; ?></td>
                                    <td><?php echo $carrera['nombre']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($carrera['estado'] == 'Activa') ? 'success' : 'danger'; ?>">
                                            <?php echo $carrera['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../docentes/index.php?id_carrera=<?php echo $carrera['id_carrera']; ?>" class="badge bg-info text-decoration-none">
                                            <?php echo $total_docentes; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="../estudiantes/index.php?id_carrera=<?php echo $carrera['id_carrera']; ?>" class="badge bg-warning text-decoration-none">
                                            <?php echo $total_estudiantes; ?>
                                        </a>
                                    </td>
                                    <td><?php echo formatearFecha($carrera['fecha_creacion'], 'd/m/Y'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $carrera['id_carrera']; ?>" class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="editar.php?id=<?php echo $carrera['id_carrera']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="eliminar.php?id=<?php echo $carrera['id_carrera']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta carrera?');">
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
                    <nav aria-label="Paginación de carreras">
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
                <p class="text-center">No se encontraron carreras.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
