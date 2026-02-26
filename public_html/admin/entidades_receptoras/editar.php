<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$id_entidad = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = new Database();

// Obtener la entidad receptora actual
$db->query("SELECT * FROM entidades_receptoras WHERE id_entidad = :id_entidad");
$db->bind(':id_entidad', $id_entidad);
$entidad = $db->single();

if (!$entidad) {
    header('Location: index.php?mensaje=entidad_no_encontrada');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $representante_legal = $_POST['representante_legal'];
    $direccion = $_POST['direccion'];
    $canton = $_POST['canton'];
    $provincia = $_POST['provincia'];
    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];

    $db->query("UPDATE entidades_receptoras SET 
                nombre = :nombre, 
                representante_legal = :representante_legal, 
                direccion = :direccion, 
                canton = :canton, 
                provincia = :provincia, 
                latitud = :latitud, 
                longitud = :longitud 
                WHERE id_entidad = :id_entidad");
    $db->bind(':nombre', $nombre);
    $db->bind(':representante_legal', $representante_legal);
    $db->bind(':direccion', $direccion);
    $db->bind(':canton', $canton);
    $db->bind(':provincia', $provincia);
    $db->bind(':latitud', $latitud);
    $db->bind(':longitud', $longitud);
    $db->bind(':id_entidad', $id_entidad);

    if ($db->execute()) {
        header('Location: index.php?mensaje=entidad_actualizada');
        exit;
    } else {
        $error = 'Error al actualizar la entidad receptora.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Editar Entidad Receptora</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($entidad['nombre']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="representante_legal" class="form-label">Representante Legal</label>
            <input type="text" class="form-control" id="representante_legal" name="representante_legal" value="<?php echo htmlspecialchars($entidad['representante_legal']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo htmlspecialchars($entidad['direccion']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="canton" class="form-label">Cantón</label>
            <input type="text" class="form-control" id="canton" name="canton" value="<?php echo htmlspecialchars($entidad['canton']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="provincia" class="form-label">Provincia</label>
            <select class="form-control" id="provincia" name="provincia" required>
                <option value="">Seleccione una provincia</option>
                <?php
                $provincias = [
                    "Azuay", "Bolívar", "Cañar", "Carchi", "Chimborazo", "Cotopaxi", "El Oro", "Esmeraldas",
                    "Galápagos", "Guayas", "Imbabura", "Loja", "Los Ríos", "Manabí", "Morona Santiago", "Napo",
                    "Orellana", "Pastaza", "Pichincha", "Santa Elena", "Santo Domingo de los Tsáchilas",
                    "Sucumbíos", "Tungurahua", "Zamora Chinchipe"
                ];
                foreach ($provincias as $prov) {
                    $selected = ($entidad['provincia'] == $prov) ? 'selected' : '';
                    echo "<option value=\"$prov\" $selected>$prov</option>";
                }
                ?>
            </select>
        </div>
    <div id="map" style="height: 350px;  border-radius:15px; margin-bottom: 20px;"></div>

        <div class="mb-3">
            <label for="latitud" class="form-label">Latitud</label>
            <input type="text" class="form-control" id="latitud" name="latitud" value="<?php echo htmlspecialchars($entidad['latitud']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="longitud" class="form-label">Longitud</label>
            <input type="text" class="form-control" id="longitud" name="longitud" value="<?php echo htmlspecialchars($entidad['longitud']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var lat = <?php echo isset($entidad['latitud']) ? floatval($entidad['latitud']) : -1.831239; ?>;
            var lng = <?php echo isset($entidad['longitud']) ? floatval($entidad['longitud']) : -78.183406; ?>;

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
</div>

<?php include '../footer_admin.php'; ?>