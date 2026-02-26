<?php
require_once '../includes/header.php';
?>

<main class="dashboard">
    <section class="dashboard-column">
        <h2>Programas</h2>
        <ul id="programas-list" class="scrollable-content list-group"></ul>
    </section>
    <section class="dashboard-column">
        <h2>Proyectos</h2>
        <ul id="proyectos-list" class="scrollable-content list-group"></ul>
    </section>
    <section class="dashboard-column">
        <div id="proyecto-detalles" class="scrollable-content">
            <img src="images/comunidad.webp" alt="comnidad nelson torres">
            <h5>Instituto Superior Nelson Torres</h5>
            <p>
                El Instituto Superior Nelson Torres, a través de su unidad de vinculación con la sociedad,
                ha contribuido significativamente al desarrollo social mediante proyectos que impactan
                positivamente en las comunidades. Su compromiso con la educación y el bienestar social
                lo posiciona como un referente en la región.
            </p>
        </div>
    </section>
    <section class="dashboard-column">
        <div id="mapa-estadisticas">
            <div id="map" style=" margin-bottom: 1rem;"></div>
            <div class="stats-cards">
                <div class="stat-card">
                    <h4>Estudiantes</h4>
                    <p id="total-estudiantes">0</p>
                    <progress id="progress-estudiantes" value="0" max="100"></progress>
                </div>
                <div class="stat-card">
                    <h4>Profesores</h4>
                    <p id="total-profesores">0</p>
                    <progress id="progress-profesores" value="0" max="100"></progress>
                </div>
                <div class="stat-card">
                    <h4>Beneficiarios</h4>
                    <p id="total-beneficiarios">0</p>
                    <progress id="progress-beneficiarios" value="0" max="100"></progress>
                </div>
                <div class="stat-card">
                    <h4>Proyectos</h4>
                    <p id="total-proyectos">0</p>
                    <progress id="progress-proyectos" value="0" max="100"></progress>
                </div>
            </div>
        </div>
    </section>
</main>
<div id="toast-mensaje" style="
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color: #333;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  font-size: 0.9rem;
  opacity: 0;
  transition: opacity 0.4s ease;
  z-index: 9999;
  pointer-events: none;
">
  Enlace copiado al portapapeles
</div>
<!-- Modal para detalles ampliados del proyecto -->
<div id="modal-detalles-proyecto" class="modal-detalles " style="display:none;">
    <div class="modal-contenido scrollable-content">
        <span id="cerrar-modal" class="cerrar-modal">&times;</span>
        <div id="modal-detalles-contenido">

        </div>
    </div>
</div>

<script src="js/scripts.js">
</script>


<?php require_once '../includes/footer.php'; ?>
