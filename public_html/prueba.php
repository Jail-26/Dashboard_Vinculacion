<?php
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /');
    exit;
}
$obtener = (json_decode(file_get_contents("http://saboresprohibidos.com/api/proyectos.php?proyecto_id=$id"),true));
$proyecto = $obtener[0];
echo $proyecto['nombre'];
?>


<main class="proyecto-page">



</main>


<?php require_once '../includes/footer.php'; ?>



