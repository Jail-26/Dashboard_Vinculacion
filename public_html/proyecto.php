<?php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /');
    exit;
}
$obtener = (json_decode(file_get_contents("http://saboresprohibidos.com/api/proyectos.php?proyecto_id=$id"), true));
$proyecto = $obtener[0];

if (!$proyecto || !is_array($proyecto) || empty($proyecto)) {
    header('Location: /');
    exit;
}
$nombre_proyecto = $proyecto['nombre'];
$fase = $proyecto['fase'];
$estado = $proyecto['estado'];
$descripcion_extendida = $proyecto['descripcion_extendida'];
$imagen_url = $proyecto['imagen_url'];
$cantidad_estudiantes = $proyecto['cantidad_estudiantes'];
$cantidad_profesores = $proyecto['cantidad_profesores'];
$cantidad_beneficiados = $proyecto['cantidad_beneficiados'];
$entidad_nombre = $proyecto['entidad_nombre'];
$entidad_latitud = $proyecto['entidad_latitud'];
$entidad_longitud = $proyecto['entidad_longitud'];
$pdf_url = $proyecto['pdf_url'];

?>


<main class="proyecto-page">
    <div class="proyecto-contenedor articulo-contenedor scrollable-content">
        <a href="/" style="max-width: 160px;" class="login-btn">Ver todos los proyectos</a>

        <div style="display: flex;align-items:center; justify-content:space-between">
            <h1><?= $nombre_proyecto ?></h1>
            <span style="margin:0;padding: 0.4rem 0.80rem; font-size:0.9rem;" class="lbl-estado"><?= $fase ?></span>
            <?php if ($pdf_url || !$pdf_url == ''): ?>
                
                <a href="<?= $pdf_url ?>" target="_blank" class="login-btn">ACTA ENTREGA</a>

                
            <?php endif; ?>
        </div>
        <img style="border-radius: 5px;" src="<?= $imagen_url ?>" alt="">
        <?= $descripcion_extendida ?>
        <p>Sí deseas más información sobre el proyecto, puedes escribirnos a:
            <a href="mailto:vinculacion@intsuperior.edu.ec?subject=Quiero más información sobre el proyecto de <?= $nombre_proyecto ?>">vinculacion@intsuperior.edu.ec</a>
        </p>
    </div>
    <div class="proyecto-contenedor mapa-estadisticas-contenedor">
        <div id="mapa-estadisticas">
            <div id="map" style="height: 100%; width: 100%; border-radius: 8px; margin-bottom: 1rem;"></div>

            <div class="stats-cards">
                <div class="stat-card">
                    <h4>Estudiantes</h4>
                    <p id="total-estudiantes"><?= $cantidad_estudiantes ?></p>
                </div>
                <div class="stat-card">
                    <h4>Profesores</h4>
                    <p id="total-profesores"><?= $cantidad_profesores ?></p>
                </div>
                <div class="stat-card">
                    <h4>Beneficiarios</h4>
                    <p id="total-beneficiarios"><?= $cantidad_beneficiados ?></p>
                </div>
                <div class="stat-card">
                    <h4>Estado</h4>
                    <p style="text-transform:uppercase" id="total-beneficiarios"><?= $estado ?></p>
                </div>
            </div>
        </div>
    </div>

</main>
<!-- Leaflet CSS -->
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-o5at1kP3pJ2nZs5JysG+DUC2gXqNxZHgGsKgWPHQfP8="
  crossorigin=""
/>

<!-- Leaflet JS -->
<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-o5at1kP3pJ2nZs5JysG+DUC2gXqNxZHgGsKgWPHQfP8="
  crossorigin=""
></script>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const map = L.map("map").setView([<?= $entidad_latitud ?>, <?= $entidad_longitud ?>], 17);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
    }).addTo(map);

    L.marker([<?= $entidad_latitud ?>, <?= $entidad_longitud ?>])
      .addTo(map)
      .bindPopup(`<b><?= addslashes($entidad_nombre) ?></b><br><?= addslashes($nombre_proyecto) ?>`)
      .openPopup();
  });
</script>

<?php require_once '../includes/footer.php'; ?>