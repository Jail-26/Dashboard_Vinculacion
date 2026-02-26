<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';
require_once '../../../includes/functions.php';

// Verificar sesión y permisos
if (!estaLogueado() || ($_SESSION['rol'] != 'administrador' && $_SESSION['rol'] != 'coordinador')) {
    header('Location: ../../login.php');
    exit;
}

$db = new Database();

// Obtener periodos académicos para el filtro
$db->query("SELECT nombre, estado FROM periodos_academicos ORDER BY fecha_inicio DESC");
$periodos = $db->resultSet();

include '../header_admin.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-file-csv me-2"></i>Generador de Reportes</h1>
    </div>

    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Configuración de Exportación</h5>
                </div>
                <div class="card-body">
                    <form action="generar_csv.php" method="POST" target="_blank">
                        
                        <div class="mb-3">
                            <label for="tipo_reporte" class="form-label">Seleccione el Reporte:</label>
                            <select name="tipo_reporte" id="tipo_reporte" class="form-select" required onchange="toggleFiltros()">
                                <option value="" selected disabled>-- Seleccione una opción --</option>
                                <option value="maestro">Maestro de Proyectos (General)</option>
                                <option value="estudiantes">Cumplimiento Estudiantil (Por Periodo)</option>
                                <option value="docentes">Carga y Gestión Docente (Por Periodo)</option>
                                <option value="fases">Seguimiento Operativo de Fases</option>
                                <option value="auditoria">Auditoria de Usuarios del Sistema</option>
                            </select>
                            <div class="form-text text-muted">
                                Los reportes se generan en tiempo real basados en la información actual.
                            </div>
                        </div>

                        <div class="mb-4" id="div_periodo" style="display:none;">
                            <label for="periodo" class="form-label">Filtrar por Periodo Académico:</label>
                            <select name="periodo" id="periodo" class="form-select">
                                <option value="TODOS">-- Todos los Periodos --</option>
                                <?php foreach ($periodos as $p): ?>
                                    <option value="<?php echo $p['nombre']; ?>">
                                        <?php echo $p['nombre'] . ' (' . $p['estado'] . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-download me-2"></i>Descargar Excel (.csv)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-1"></i> <strong>Nota:</strong> El archivo descargado tiene formato CSV. Puedes abrirlo directamente con Excel, Google Sheets o cualquier hoja de cálculo.
            </div>
        </div>
    </div>
</div>

<script>
    // Pequeño script para mostrar/ocultar el filtro de periodo según el reporte
    function toggleFiltros() {
        const tipo = document.getElementById('tipo_reporte').value;
        const divPeriodo = document.getElementById('div_periodo');
        
        // Reportes que SÍ soportan filtro por periodo (según las vistas SQL que creamos)
        const reportesConPeriodo = ['estudiantes', 'docentes'];

        if (reportesConPeriodo.includes(tipo)) {
            divPeriodo.style.display = 'block';
        } else {
            divPeriodo.style.display = 'none';
        }
    }
</script>

<?php include '../footer_admin.php'; ?>