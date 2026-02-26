<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $representante_legal = $_POST['representante_legal'];
    $direccion = $_POST['direccion'];
    $canton = $_POST['canton'];
    $provincia = $_POST['provincia'];
    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];

    $db->query("INSERT INTO entidades_receptoras (nombre, representante_legal, direccion, canton, provincia, latitud, longitud) 
                VALUES (:nombre, :representante_legal, :direccion, :canton, :provincia, :latitud, :longitud)");
    $db->bind(':nombre', $nombre);
    $db->bind(':representante_legal', $representante_legal);
    $db->bind(':direccion', $direccion);
    $db->bind(':canton', $canton);
    $db->bind(':provincia', $provincia);
    $db->bind(':latitud', $latitud);
    $db->bind(':longitud', $longitud);

    if ($db->execute()) {
        header('Location: index.php?mensaje=entidad_creada');
        exit;
    } else {
        $error = 'Error al crear la entidad receptora.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Crear Entidad Receptora</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="representante_legal" class="form-label">Representante Legal</label>
            <input type="text" class="form-control" id="representante_legal" name="representante_legal" required>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" required>
        </div>
        <div class="mb-3">
            <label for="canton" class="form-label">Cantón</label>
            <input type="text" class="form-control" id="canton" name="canton" required>
        </div>
        <div class="mb-3">
            <label for="provincia" class="form-label">Provincia</label>
            <select class="form-control" id="provincia" name="provincia" required>
                <option value="">Seleccione una provincia</option>
                <option value="Azuay">Azuay</option>
                <option value="Bolívar">Bolívar</option>
                <option value="Cañar">Cañar</option>
                <option value="Carchi">Carchi</option>
                <option value="Chimborazo">Chimborazo</option>
                <option value="Cotopaxi">Cotopaxi</option>
                <option value="El Oro">El Oro</option>
                <option value="Esmeraldas">Esmeraldas</option>
                <option value="Galápagos">Galápagos</option>
                <option value="Guayas">Guayas</option>
                <option value="Imbabura">Imbabura</option>
                <option value="Loja">Loja</option>
                <option value="Los Ríos">Los Ríos</option>
                <option value="Manabí">Manabí</option>
                <option value="Morona Santiago">Morona Santiago</option>
                <option value="Napo">Napo</option>
                <option value="Orellana">Orellana</option>
                <option value="Pastaza">Pastaza</option>
                <option value="Pichincha">Pichincha</option>
                <option value="Santa Elena">Santa Elena</option>
                <option value="Santo Domingo de los Tsáchilas">Santo Domingo de los Tsáchilas</option>
                <option value="Sucumbíos">Sucumbíos</option>
                <option value="Tungurahua">Tungurahua</option>
                <option value="Zamora Chinchipe">Zamora Chinchipe</option>
            </select>
        </div>
    <div id="map" style="height: 350px; border-radius:15px; margin-bottom: 20px;"></div>

        <div class="mb-3">
            <label for="latitud" class="form-label">Latitud</label>
            <input type="text" class="form-control" id="latitud" name="latitud" required>
        </div>
        <div class="mb-3">
            <label for="longitud" class="form-label">Longitud</label>
            <input type="text" class="form-control" id="longitud" name="longitud" required>
        </div>
        <button type="submit" class="btn btn-primary">Crear</button>
    </form>
</div>
<script>
        document.addEventListener("DOMContentLoaded", function() {
            var lat = -1.831239;
            var lng = -78.183406;

            var map = L.map('map').setView([lat, lng], 7);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            var marker = L.marker([lat, lng], {draggable:true}).addTo(map);

            marker.on('dragend', function(e) {
                var position = marker.getLatLng();
                document.getElementById('latitud').value = position.lat;
                document.getElementById('longitud').value = position.lng;
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitud').value = e.latlng.lat;
                document.getElementById('longitud').value = e.latlng.lng;
            });

            // Soluciona problemas de visualización si el mapa está en un tab o modal
            setTimeout(function() {
                map.invalidateSize();
            }, 400);
        });
    </script>
<?php include '../footer_admin.php'; ?>