<?php
require_once '../../../includes/config.php';
require_once '../../../includes/db.php';
require_once '../../../includes/auth.php';

if (!estaLogueado()) {
    header('Location: ../login.php');
    exit;
}

$id_tema = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$db = new Database();

// Obtener el tema actual
$db->query("SELECT * FROM temas WHERE id_tema = :id_tema");
$db->bind(':id_tema', $id_tema);
$tema = $db->single();

if (!$tema) {
    header('Location: index.php?mensaje=tema_no_encontrado');
    exit;
}

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

    $db->query("UPDATE temas SET 
                nombre = :nombre, 
                background_main = :background_main, 
                background_card = :background_card, 
                border_separator = :border_separator, 
                text_main = :text_main, 
                text_secondary = :text_secondary, 
                red_main = :red_main, 
                red_accent = :red_accent, 
                neutral_detail = :neutral_detail 
                WHERE id_tema = :id_tema");
    $db->bind(':nombre', $nombre);
    $db->bind(':background_main', $background_main);
    $db->bind(':background_card', $background_card);
    $db->bind(':border_separator', $border_separator);
    $db->bind(':text_main', $text_main);
    $db->bind(':text_secondary', $text_secondary);
    $db->bind(':red_main', $red_main);
    $db->bind(':red_accent', $red_accent);
    $db->bind(':neutral_detail', $neutral_detail);
    $db->bind(':id_tema', $id_tema);

    if ($db->execute()) {
        header('Location: index.php?mensaje=tema_actualizado');
        exit;
    } else {
        $error = 'Error al actualizar el tema.';
    }
}

include '../header_admin.php';
?>

<div class="container py-4">
    <h1>Editar Tema</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del Tema</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($tema['nombre']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="background_main" class="form-label">Color Principal (background-main)</label>
            <input type="color" class="form-control" id="background_main" name="background_main" value="<?php echo htmlspecialchars($tema['background_main']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="background_card" class="form-label">Color de Tarjetas (background-card)</label>
            <input type="color" class="form-control" id="background_card" name="background_card" value="<?php echo htmlspecialchars($tema['background_card']); ?>" required>
            </div>
        <div class="mb-3">
            <label for="border_separator" class="form-label">Color de Bordes (border-separator)</label>
            <input type="color" class="form-control" id="border_separator" name="border_separator" value="<?php echo htmlspecialchars($tema['border_separator']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="text_main" class="form-label">Texto Principal (text-main)</label>
            <input type="color" class="form-control" id="text_main" name="text_main" value="<?php echo htmlspecialchars($tema['text_main']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="text_secondary" class="form-label">Texto Secundario (text-secondary)</label>
            <input type="color" class="form-control" id="text_secondary" name="text_secondary" value="<?php echo htmlspecialchars($tema['text_secondary']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="red_main" class="form-label">Rojo Principal (red-main)</label>
            <input type="color" class="form-control" id="red_main" name="red_main" value="<?php echo htmlspecialchars($tema['red_main']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="red_accent" class="form-label">Rojo Acento (red-accent)</label>
            <input type="color" class="form-control" id="red_accent" name="red_accent" value="<?php echo htmlspecialchars($tema['red_accent']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="neutral_detail" class="form-label">Detalles Neutros (neutral-detail)</label>
            <input type="color" class="form-control" id="neutral_detail" name="neutral_detail" value="<?php echo htmlspecialchars($tema['neutral_detail']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Editar Tema</button>
    </form>
</div>

<?php include '../footer_admin.php'; ?>