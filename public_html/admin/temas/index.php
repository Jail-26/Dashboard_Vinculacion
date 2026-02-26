<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();

// Obtener temas
$db->query("SELECT * FROM temas ORDER BY fecha_creacion DESC");
$temas = $db->resultSet();

// Obtener el tema activo
$db->query("
    SELECT id_tema
    FROM configuracion_dashboard
    ORDER BY fecha_seleccion DESC
    LIMIT 1
");
$temaActivo = $db->single();
$idTemaActivo = $temaActivo ? $temaActivo['id_tema'] : null;

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestión de Temas</h1>
        <?php if ($_SESSION['rol'] != 'visualizador'): ?>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Tema
        </a>
        <?php endif; ?>
    </div>

    <!-- Mostrar mensajes de la URL -->
    <?php if (isset($_GET['mensaje'])): ?>
        <?php
        $tipo_alerta = 'info';
        $mensaje = '';
        switch ($_GET['mensaje']) {
            case 'tema_actualizado':
                $mensaje = 'El tema se ha actualizado correctamente.';
                $tipo_alerta = 'success';
                break;
            case 'tema_eliminado':
                $mensaje = 'El tema se ha eliminado correctamente.';
                $tipo_alerta = 'success';
                break;
            case 'tema_activo_no_eliminable':
                $mensaje = 'No se puede eliminar el tema que está activo.';
                $tipo_alerta = 'danger';
                break;
            case 'tema_no_encontrado':
                $mensaje = 'El tema no se encontró.';
                $tipo_alerta = 'danger';
                break;
            default:
                $mensaje = 'Operación realizada con éxito.';
                break;
        }
        ?>
        <div class="alert alert-<?php echo $tipo_alerta; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Listado de Temas</h5>
        </div>
        <div class="card-body">
            <?php if (count($temas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Preview</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($temas as $tema): ?>
                                <tr <?php echo $tema['id_tema'] == $idTemaActivo ? 'style="background-color: #d4edda;"' : ''; ?>>
                                    <td><?php echo $tema['id_tema']; ?></td>
                                    <td>
                                        <?php echo $tema['nombre']; ?>
                                        <?php if ($tema['id_tema'] == $idTemaActivo): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Simulación del dashboard -->
                                        <div style="width: 150px; height: 100px; border-radius: 10px; overflow:hidden; border: 1px solid <?php echo $tema['border_separator']; ?>; display: flex; flex-direction: column; font-size: 12px;">
                                            <!-- Header -->
                                            <div style="height: 20%; background-color: <?php echo $tema['background_card']; ?>; color: <?php echo $tema['text_main']; ?>; text-align: center; display: flex; align-items: center; justify-content: center;">
                                                Cabecera
                                            </div>
                                            <!-- Main -->
                                            <div style="height: 60%; background-color: <?php echo $tema['background_main']; ?>; color: <?php echo $tema['text_secondary']; ?>; text-align: center; display: flex; align-items: center; justify-content: center;">
                                                Principal
                                            </div>
                                            <!-- Footer -->
                                            <div style="height: 20%; background-color: <?php echo $tema['background_card']; ?>; color: <?php echo $tema['text_main']; ?>; text-align: center; display: flex; align-items: center; justify-content: center;">
                                                Pie de página
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if ($_SESSION['rol'] != 'visualizador'): ?>
                                            <a href="editar.php?id=<?php echo $tema['id_tema']; ?>" class="btn btn-primary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="eliminar.php?id=<?php echo $tema['id_tema']; ?>" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este tema?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <a href="activar.php?id=<?php echo $tema['id_tema']; ?>" class="btn btn-success" title="Activar">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center">No se encontraron temas.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>