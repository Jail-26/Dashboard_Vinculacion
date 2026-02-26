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

// Obtener carreras para el select
$db->query("SELECT * FROM carreras WHERE estado = 'Activa' ORDER BY nombre");
$carreras = $db->resultSet();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = limpiarDatos($_POST['cedula']);
    $nombres = limpiarDatos($_POST['nombres']);
    $apellidos = limpiarDatos($_POST['apellidos']);
    $correo = limpiarDatos($_POST['correo']);
    $id_carrera = (int)$_POST['id_carrera'];
    $titulo_academico = limpiarDatos($_POST['titulo_academico']);
    $dependencia = limpiarDatos($_POST['dependencia']);
    $estado = $_POST['estado'];

    // Verificar si cédula ya existe
    $db->query("SELECT id_docente FROM docentes WHERE cedula = :cedula");
    $db->bind(':cedula', $cedula);
    if ($db->single()) {
        $error = 'La cédula ya está registrada.';
    } else {
        $db->query("INSERT INTO docentes (cedula, nombres, apellidos, correo, id_carrera, titulo_academico, dependencia, estado) 
                    VALUES (:cedula, :nombres, :apellidos, :correo, :id_carrera, :titulo_academico, :dependencia, :estado)");
        $db->bind(':cedula', $cedula);
        $db->bind(':nombres', $nombres);
        $db->bind(':apellidos', $apellidos);
        $db->bind(':correo', $correo);
        $db->bind(':id_carrera', $id_carrera);
        $db->bind(':titulo_academico', $titulo_academico);
        $db->bind(':dependencia', $dependencia);
        $db->bind(':estado', $estado);

        if ($db->execute()) {
            $id_docente = $db->lastInsertId();
            $db->registrarCambio($_SESSION['id_usuario'], 'docentes', $id_docente, 'creación', null, ['cedula' => $cedula, 'nombres' => $nombres, 'apellidos' => $apellidos]);
            header('Location: index.php?mensaje=creado');
            exit;
        } else {
            $error = 'Error al crear el docente.';
        }
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Crear Nuevo Docente</h1>
    
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
                            <label for="cedula" class="form-label">Cédula *</label>
                            <input type="text" class="form-control" id="cedula" name="cedula" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="id_carrera" class="form-label">Carrera *</label>
                            <select class="form-select" id="id_carrera" name="id_carrera" required>
                                <option value="">Selecciona una carrera</option>
                                <?php foreach ($carreras as $carrera): ?>
                                    <option value="<?php echo $carrera['id_carrera']; ?>">
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
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="titulo_academico" class="form-label">Título Académico</label>
                            <input type="text" class="form-control" id="titulo_academico" name="titulo_academico" placeholder="Ej: PhD en Ciencias">
                        </div>
                    </div>
                </div>
                
                <!-- <div class="mb-3">
                    <label for="dependencia" class="form-label">Dependencia</label>
                    <input type="text" class="form-control" id="dependencia" name="dependencia" placeholder="Ej: Facultad de Ingeniería">
                </div> -->
                
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="Activo" selected>Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Crear Docente
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
