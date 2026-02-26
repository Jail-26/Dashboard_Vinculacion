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
$registros_por_pagina = 10;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$busqueda = isset($_GET['busqueda']) ? limpiarDatos($_GET['busqueda']) : '';
$id_carrera = isset($_GET['id_carrera']) ? (int)$_GET['id_carrera'] : 0;
$busquedaLike = '%' . $busqueda . '%';

// Base query
$where = "WHERE 1=1";
if (!empty($busqueda)) {
    $where .= " AND (d.nombres LIKE :busqueda OR d.apellidos LIKE :busqueda1 OR d.cedula LIKE :busqueda2 OR d.correo LIKE :busqueda3)";
}
if ($id_carrera > 0) {
    $where .= " AND d.id_carrera = :id_carrera";
}

// Total de registros
$db->query("SELECT COUNT(*) as total FROM docentes d " . $where);
if (!empty($busqueda)) {
    $db->bind(':busqueda', $busquedaLike);
    $db->bind(':busqueda1', $busquedaLike);
    $db->bind(':busqueda2', $busquedaLike);
    $db->bind(':busqueda3', $busquedaLike);
}
if ($id_carrera > 0) {
    $db->bind(':id_carrera', $id_carrera);
}
$total_resultado = $db->single();
$total_registros = $total_resultado['total'] ?? 0;
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener docentes
$db->query("SELECT d.*, c.nombre as nombre_carrera FROM docentes d LEFT JOIN carreras c ON d.id_carrera = c.id_carrera " . $where . " ORDER BY d.fecha_registro DESC LIMIT :limit OFFSET :offset");
if (!empty($busqueda)) {
    $db->bind(':busqueda', $busquedaLike);
    $db->bind(':busqueda1', $busquedaLike);
    $db->bind(':busqueda2', $busquedaLike);
    $db->bind(':busqueda3', $busquedaLike);

}
if ($id_carrera > 0) {
    $db->bind(':id_carrera', $id_carrera);
}
$db->bind(':offset', $offset, PDO::PARAM_INT);
$db->bind(':limit', $registros_por_pagina, PDO::PARAM_INT);
$docentes = $db->resultSet();
if (!$docentes) {
    $docentes = [];
}

// Obtener carreras para filtro
$db->query("SELECT * FROM carreras WHERE estado = 'Activa' ORDER BY nombre");
$carreras = $db->resultSet();

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Docentes</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Docente
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
        <?php
        $tipo_alerta = 'success';
        $mensaje = '';
        
        switch ($_GET['mensaje']) {
            case 'creado':
                $mensaje = 'El docente ha sido creado exitosamente.';
                break;
            case 'actualizado':
                $mensaje = 'El docente ha sido actualizado exitosamente.';
                break;
            case 'eliminado':
                $mensaje = 'El docente ha sido eliminado exitosamente.';
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
    
    <div class="card mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="busqueda" class="form-control" placeholder="Buscar docentes..." value="<?php echo $busqueda; ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="id_carrera" class="form-select" onchange="this.form.submit()">
                        <option value="0">Todas las carreras</option>
                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?php echo $carrera['id_carrera']; ?>" <?php echo ($id_carrera == $carrera['id_carrera']) ? 'selected' : ''; ?>>
                                <?php echo $carrera['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <?php if (!empty($busqueda) || $id_carrera > 0): ?>
                        <a href="index.php" class="btn btn-outline-danger w-100">
                            <i class="fas fa-times me-2"></i>Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Docentes</h5>
        </div>
        <div class="card-body">
            <?php if (count($docentes) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Carrera</th>
                                <th>Estado</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($docentes as $docente): ?>
                                <tr>
                                    <td><?php echo $docente['cedula']; ?></td>
                                    <td><?php echo $docente['nombres'] . ' ' . $docente['apellidos']; ?></td>
                                    <td><?php echo $docente['correo']; ?></td>
                                    <td><?php echo $docente['nombre_carrera']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo ($docente['estado'] == 'Activo') ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($docente['estado']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatearFecha($docente['fecha_registro'], 'd/m/Y'); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="ver.php?id=<?php echo $docente['id_docente']; ?>" class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="editar.php?id=<?php echo $docente['id_docente']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="eliminar.php?id=<?php echo $docente['id_docente']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro?');">
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
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual - 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?><?php echo ($id_carrera > 0) ? '&id_carrera=' . $id_carrera : ''; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?><?php echo ($id_carrera > 0) ? '&id_carrera=' . $id_carrera : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pagina=<?php echo $pagina_actual + 1; ?><?php echo !empty($busqueda) ? '&busqueda=' . $busqueda : ''; ?><?php echo ($id_carrera > 0) ? '&id_carrera=' . $id_carrera : ''; ?>" aria-label="Siguiente">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-center">No se encontraron docentes.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
