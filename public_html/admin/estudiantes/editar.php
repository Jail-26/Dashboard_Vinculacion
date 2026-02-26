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

$db->query("SELECT * FROM estudiantes WHERE id_estudiante = :id");
$db->bind(':id', $id);
$estudiante = $db->single();

if (!$estudiante) {
    header('Location: index.php?mensaje=error');
    exit;
}

$db->query("SELECT * FROM carreras WHERE estado = 'Activa' ORDER BY nombre");
$carreras = $db->resultSet();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombres = limpiarDatos($_POST['nombres']);
    $apellidos = limpiarDatos($_POST['apellidos']);
    $correo = limpiarDatos($_POST['correo']);
    $id_carrera = (int)$_POST['id_carrera'];
    $estado = $_POST['estado'];
    
    $datos_anteriores = $estudiante;

    $db->query("UPDATE estudiantes SET nombres = :nombres, apellidos = :apellidos, correo = :correo, id_carrera = :id_carrera, estado = :estado WHERE id_estudiante = :id");
    $db->bind(':nombres', $nombres);
    $db->bind(':apellidos', $apellidos);
    $db->bind(':correo', $correo);
    $db->bind(':id_carrera', $id_carrera);
    $db->bind(':estado', $estado);
    $db->bind(':id', $id);

    if ($db->execute()) {
        $datos_nuevos = ['nombres' => $nombres, 'apellidos' => $apellidos, 'estado' => $estado];
        $db->registrarCambio($_SESSION['id_usuario'], 'estudiantes', $id, 'modificación', $datos_anteriores, $datos_nuevos);
        header('Location: index.php?mensaje=actualizado');
        exit;
    } else {
        $error = 'Error al actualizar el estudiante.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Editar Estudiante</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="cedula" class="form-label">Cédula</label>
                            <input type="text" class="form-control" id="cedula" value="<?php echo $estudiante['cedula']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_carrera" class="form-label">Carrera *</label>
                            <select class="form-select" id="id_carrera" name="id_carrera" required>
                                <?php foreach ($carreras as $carrera): ?>
                                    <option value="<?php echo $carrera['id_carrera']; ?>" <?php echo ($estudiante['id_carrera'] == $carrera['id_carrera']) ? 'selected' : ''; ?>>
                                        <?php echo $carrera['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombres" class="form-label">Nombres *</label>
                            <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo $estudiante['nombres']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo $estudiante['apellidos']; ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $estudiante['correo']; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="Activo" <?php echo ($estudiante['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                        <option value="Inactivo" <?php echo ($estudiante['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Actualizar Estudiante
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer_admin.php'; ?>
