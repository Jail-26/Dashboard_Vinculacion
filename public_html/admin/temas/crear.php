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
    $background_main = $_POST['background_main'];
    $background_card = $_POST['background_card'];
    $border_separator = $_POST['border_separator'];
    $text_main = $_POST['text_main'];
    $text_secondary = $_POST['text_secondary'];
    $red_main = $_POST['red_main'];
    $red_accent = $_POST['red_accent'];
    $neutral_detail = $_POST['neutral_detail'];

    $db->query("INSERT INTO temas (nombre, background_main, background_card, border_separator, text_main, text_secondary, red_main, red_accent, neutral_detail) 
                VALUES (:nombre, :background_main, :background_card, :border_separator, :text_main, :text_secondary, :red_main, :red_accent, :neutral_detail)");
    $db->bind(':nombre', $nombre);
    $db->bind(':background_main', $background_main);
    $db->bind(':background_card', $background_card);
    $db->bind(':border_separator', $border_separator);
    $db->bind(':text_main', $text_main);
    $db->bind(':text_secondary', $text_secondary);
    $db->bind(':red_main', $red_main);
    $db->bind(':red_accent', $red_accent);
    $db->bind(':neutral_detail', $neutral_detail);

    if ($db->execute()) {
        header('Location: index.php?mensaje=tema_creado');
        exit;
    } else {
        $error = 'Error al crear el tema.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Crear Tema</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del Tema</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="background_main" class="form-label">Color Principal (background-main)</label>
            <div class="d-flex align-items-center">
                <input type="color" class="form-control me-2" id="background_main" name="background_main" required>
                <input type="text" class="form-control" id="background_main_rgb" name="background_main_rgb" placeholder="#000000" maxlength="7" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="background_card" class="form-label">Color de Tarjetas (background-card)</label>
            <div class="d-flex align-items-center">
                <input type="color" class="form-control me-2" id="background_card" name="background_card" required>
                <input type="text" class="form-control" id="background_card_rgb" name="background_card_rgb" placeholder="#000000" maxlength="7" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="border_separator" class="form-label">Color de Bordes (border-separator)</label>
            <div class="d-flex align-items-center">
                <input type="color" class="form-control me-2" id="border_separator" name="border_separator" required>
                <input type="text" class="form-control" id="border_separator_rgb" name="border_separator_rgb" placeholder="#000000" maxlength="7" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="text_main" class="form-label">Texto Principal (text-main)</label>
            <div class="d-flex align-items-center">
                <input type="color" class="form-control me-2" id="text_main" name="text_main" required>
                <input type="text" class="form-control" id="text_main_rgb" name="text_main_rgb" placeholder="#000000" maxlength="7" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="text_secondary" class="form-label">Texto Secundario (text-secondary)</label>
            <div class="d-flex align-items-center">
                <input type="color" class="form-control me-2" id="text_secondary" name="text_secondary" required>
                <input type="text" class="form-control" id="text_secondary_rgb" name="text_secondary_rgb" placeholder="#000000" maxlength="7" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="red_main" class="form-label">Rojo Principal (red-main)</label>
            <div class="d-flex align-items-center">
                <input type="color" class="form-control me-2" id="red_main" name="red_main" required>
                <input type="text" class="form-control" id="red_main_rgb" name="red_main_rgb" placeholder="#000000" maxlength="7" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="red_accent" class="form-label">Rojo Acento (red-accent)</label>
            <div class="d-flex align-items-center">
                <input type="color" class="form-control me-2" id="red_accent" name="red_accent" required>
                <input type="text" class="form-control" id="red_accent_rgb" name="red_accent_rgb" placeholder="#000000" maxlength="7" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="neutral_detail" class="form-label">Detalles Neutros (neutral-detail)</label>
            <div class="d-flex align-items-center">
                <input type="color" class="form-control me-2" id="neutral_detail" name="neutral_detail" required>
                <input type="text" class="form-control" id="neutral_detail_rgb" name="neutral_detail_rgb" placeholder="#000000" maxlength="7" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Crear Tema</button>
    </form>
</div>

<script>
    // Sincronizar el color picker con el input de texto
    document.querySelectorAll('input[type="color"]').forEach(function(colorInput) {
        const textInput = document.getElementById(colorInput.id + '_rgb');
        colorInput.addEventListener('input', function() {
            textInput.value = colorInput.value;
        });
        textInput.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(textInput.value)) {
                colorInput.value = textInput.value;
            }
        });
    });
</script>

<?php include '../footer_admin.php'; ?>