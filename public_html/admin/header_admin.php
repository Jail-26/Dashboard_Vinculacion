<?php
// Verificar si la sesión está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

// Función auxiliar para verificar la página activa
function isActive($page)
{
    $current_file = basename($_SERVER['PHP_SELF']);
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));

    if ($current_file == $page) {
        return 'active';
    }

    if ($current_dir == $page) {
        return 'active';
    }

    return '';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - <?php echo PROJECT_NAME; ?></title>
    <link rel="icon" href="https://saboresprohibidos.com/icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        /* Works on Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: white gray;
        }

        /* Works on Chrome, Edge, and Safari */
        *::-webkit-scrollbar {
            width: 12px;
        }

        *::-webkit-scrollbar-track {
            background: orange;
        }

        *::-webkit-scrollbar-thumb {
            background-color: white;
            border-radius: 20px;
            border: 3px solid gray;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <style>
        body {
            background-color: #efefef;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }

        .sidebar-sticky {
            position: sticky;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, .75);
            padding: .75rem 1rem;
        }

        .sidebar .nav-link:hover {
            color: #fff;
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, .1);
        }

        .sidebar .nav-link i {
            margin-right: .5rem;
            width: 1.25rem;
            text-align: center;
        }

        .sidebar-divider {
            margin: .5rem 1rem;
            border-top: 1px solid rgba(255, 255, 255, .1);
        }

        .content {
            margin-left: 250px;
        }

        @media (max-width: 767.98px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="sidebar-sticky">
                    <div class="d-flex justify-content-between align-items-center p-3 mb-3 text-white">
                        <h5 class="mb-0">Panel de Admin</h5>
                        <button class="navbar-toggler d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                    </div>

                    <ul class="nav flex-column" id="sidebarMenu">

                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('index.php'); ?>" href="<?php echo ADMIN_URL; ?>/index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo ADMIN_URL; ?>/configuracion.php"><i class="fas fa-cogs"></i> General</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('planes'); ?>" href="<?php echo ADMIN_URL; ?>/planes/index.php">
                                <i class="fas fa-file-alt"></i> Planes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('programas'); ?>" href="<?php echo ADMIN_URL; ?>/programas/index.php">
                                <i class="fas fa-folder-open"></i> Programas
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('proyectos'); ?>" href="<?php echo ADMIN_URL; ?>/proyectos/index.php">
                                <i class="fas fa-tasks"></i> Proyectos
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('fases'); ?>" href="<?php echo ADMIN_URL; ?>/fases/index.php">
                                <i class="fas fa-sitemap"></i> Fases
                            </a>
                        </li>



                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('entidades_receptoras'); ?>" href="<?php echo ADMIN_URL; ?>/entidades_receptoras/index.php">
                                <i class="fas fa-building"></i> Entidades Receptoras
                            </a>
                        </li>

                        <div class="sidebar-divider"></div>

                        <div class="accordion" id="accordionBD">
                            <div class="accordion-item bg-dark border-0">
                                <h2 class="accordion-header" id="headingBD">
                                    <button class="accordion-button collapsed bg-secondary text-light"
                                        type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapseBD" aria-expanded="false"
                                        aria-controls="collapseBD"
                                        style="box-shadow:none;">
                                        <i class="fas fa-database me-2"></i> Base de Datos Académica
                                    </button>
                                </h2>

                                <div id="collapseBD" class="accordion-collapse collapse"
                                    aria-labelledby="headingBD" data-bs-parent="#accordionBD">
                                    <div class="accordion-body p-0">

                                        <ul class="nav flex-column">

                                            <li class="nav-item">
                                                <a class="nav-link <?php echo isActive('carreras'); ?>"
                                                    href="<?php echo ADMIN_URL; ?>/carreras/index.php">
                                                    <i class="fas fa-graduation-cap"></i> Carreras
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link <?php echo isActive('periodos_academicos'); ?>"
                                                    href="<?php echo ADMIN_URL; ?>/periodos_academicos/index.php">
                                                    <i class="fas fa-calendar"></i> Períodos
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link <?php echo isActive('docentes'); ?>"
                                                    href="<?php echo ADMIN_URL; ?>/docentes/index.php">
                                                    <i class="fas fa-chalkboard-user"></i> Docentes
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link <?php echo isActive('estudiantes'); ?>"
                                                    href="<?php echo ADMIN_URL; ?>/estudiantes/index.php">
                                                    <i class="fas fa-user-graduate"></i> Estudiantes
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link <?php echo isActive('docentes_fases'); ?>"
                                                    href="<?php echo ADMIN_URL; ?>/docentes_fases/index.php">
                                                    <i class="fas fa-link"></i> Docentes-Fases
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link <?php echo isActive('participaciones_estudiantes'); ?>"
                                                    href="<?php echo ADMIN_URL; ?>/participaciones_estudiantes/index.php">
                                                    <i class="fas fa-handshake"></i> Participaciones
                                                </a>
                                            </li>

                                            <li class="nav-item">
                                                <a class="nav-link <?php echo isActive('documentos_fases'); ?>"
                                                    href="<?php echo ADMIN_URL; ?>/documentos_fases/index.php">
                                                    <i class="fas fa-file-pdf"></i> Documentos
                                                </a>
                                            </li>

                                        </ul>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-divider"></div>

                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('temas'); ?>" href="<?php echo ADMIN_URL; ?>/temas/index.php">
                                <i class="fas fa-palette"></i> Temas
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?php echo isActive('historial'); ?>" href="<?php echo ADMIN_URL; ?>/historial/index.php">
                                <i class="fas fa-history"></i> Historial
                            </a>
                        </li>

                        <?php if ($_SESSION['rol'] == 'administrador'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo isActive('usuarios'); ?>" href="<?php echo ADMIN_URL; ?>/usuarios/index.php">
                                    <i class="fas fa-users"></i> Usuarios
                                </a>
                            </li>
                        <?php endif; ?>

                        <div class="sidebar-divider"></div>

                        <?php if ($_SESSION['rol'] == 'administrador'): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo isActive('reportes'); ?>" href="<?php echo ADMIN_URL; ?>/reportes/index.php">
                                    <i class="fa-solid fa-receipt"></i> Reportes
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Ver Dashboard Público
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo ADMIN_URL; ?>/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </li>


                    </ul>
                </div>
            </div>

            <!-- Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content">
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <div class="container-fluid">
                        <ol class="breadcrumb my-2">
                            <li class="breadcrumb-item"><a href="<?php echo ADMIN_URL; ?>/index.php">Panel</a></li>
                            <?php
                            $current_dir = basename(dirname($_SERVER['PHP_SELF']));
                            if ($current_dir != 'admin'): ?>
                                <li class="breadcrumb-item active"><?php echo ucfirst($current_dir); ?></li>
                            <?php endif; ?>
                        </ol>

                        <div class="ms-auto d-flex">
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/perfil.php"><i class="fas fa-user me-2"></i> Mi Perfil</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>