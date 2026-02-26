<?php
require_once '../includes/ini.php';
require_once '../includes/config.php';
require_once '../includes/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función auxiliar para verificar la página activa
function isActive($page)
{
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page == $page) {
        return 'active';
    }
    if (strpos($current_page, $page) === 0) {
        return 'active';
    }
    return '';
}

// Obtener el tema activo desde la base de datos
$db = new Database();
$db->query("
    SELECT *,
    p.nombre as periodo_academico
    FROM configuracion_dashboard cd
    INNER JOIN temas t ON cd.id_tema = t.id_tema
    INNER JOIN periodos_academicos p ON cd.id_periodo_academico = p.id_periodo 
    ORDER BY cd.fecha_seleccion DESC
    LIMIT 1
");
$temaActivo = $db->single();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vinculación con la Sociedad | Instituto Nelson Torres - Cayambe, Ecuador</title>

    <meta name="description" content="Conoce los proyectos de vinculación con la sociedad del Instituto Tecnológico Superior Nelson Torres en Cayambe, Ecuador. Formamos profesionales en Administración, Diseño Gráfico y Desarrollo de Software comprometidos con la comunidad." />
    <meta name="keywords" content="Vinculación con la sociedad, Instituto Nelson Torres, Cayambe, Educación Superior, Proyectos comunitarios, Diseño Gráfico, Desarrollo de Software, Administración, Ecuador" />
    <meta name="author" content="Instituto Tecnológico Superior Nelson Torres" />
    <meta name="geo.region" content="EC" />
    <meta name="geo.placename" content="Cayambe, Pichincha, Ecuador" />
    <meta name="geo.position" content="-0.0507;-78.1390" />
    <meta name="ICBM" content="-0.0507, -78.1390" />
    <link rel="canonical" href="https://vinculacionint.com/" />
    <link rel="icon" href="/images/icon.png" type="image/x-icon" />
    <!-- OPEN GRAPH (FACEBOOK, WHATSAPP, LINKEDIN...) -->
    <meta property="og:type" content="website" />
    <meta property="og:locale" content="es_EC" />
    <meta property="og:url" content="https://vinculacionint.com/" />
    <meta property="og:site_name" content="Instituto Nelson Torres" />
    <meta property="og:title" content="Vinculación con la Sociedad | Instituto Nelson Torres" />
    <meta property="og:description" content="Descubre cómo el Instituto Nelson Torres transforma comunidades a través de sus proyectos de vinculación. Educación superior al servicio del desarrollo social." />
    <meta property="og:image" content="https://vinculacionint.com/images/comunidad.webp" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />

    <!-- TWITTER CARD -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Proyectos de Vinculación | Instituto Nelson Torres" />
    <meta name="twitter:description" content="Proyectos sociales, educativos y tecnológicos que impactan en Cayambe y sus comunidades. Conoce nuestro compromiso institucional." />
    <meta name="twitter:image" content="https://vinculacionint.com/images/images/comunidad.webp />
    <meta name="twitter:site" content="@intsuperior" />
    <!-- ROBOTS (INDEXACIÓN) -->
    <meta name="robots" content="index, follow" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>css/style.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="shortcut icon" href="images/icon.png" type="image/x-icon">
    <!-- Estilos dinámicos del tema activo -->
    <?php if ($temaActivo): ?>
        <style>
            :root {
                --color-bg: <?php echo $temaActivo['background_main']; ?>;
                --color-surface: <?php echo $temaActivo['background_card']; ?>;
                --color-border: <?php echo $temaActivo['border_separator']; ?>;
                --color-text: <?php echo $temaActivo['text_main']; ?>;
                --color-muted: <?php echo $temaActivo['text_secondary']; ?>;
                --color-accent: <?php echo $temaActivo['red_main']; ?>;
                --color-accent-hover: <?php echo $temaActivo['red_accent']; ?>;
            }
        </style>
    <?php endif; ?>
</head>

<body>
    <header class="site-header">
        <div class="header-content">
            <div class="logo">
                <img src="<?php echo PUBLIC_URL; ?>images/logo-int.png" alt="Logo del Instituto Superior Tecnológico Nelson Torres" class="logo-img" width="115px">
            </div>
            <div class="title-pa">
                <h1 class="title"><?php echo $temaActivo['titulo']; ?></h1>
                <p class="periodo-academico">
                    <?= $temaActivo['periodo_academico']; ?>
                </p>
            </div>

            <?php if (isset($_SESSION['id_usuario'])): ?>
                <div class="user-info">
                    <span><i class="fas fa-user"></i> <?php echo $_SESSION['nombre']; ?>👤</span>
                    <a href="<?php echo ADMIN_URL; ?>/logout.php" class="login-btn">Cerrar Sesión</a>
                </div>
            <?php else: ?>
                <img src="<?php echo PUBLIC_URL; ?>images/logo-covins.png" alt="logo de vinculación con la socieedad" class="logo-img" width="165px">
            <?php endif; ?>
        </div>
    </header>