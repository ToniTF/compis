<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Compis' : 'Compis - Encuentra planes con amigos'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <a href="index.php?action=home" class="logo">Compis</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php?action=home" class="btn btn-outline">Inicio</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="index.php?action=dashboard" class="btn btn-outline">Mi Panel</a></li>
                        <li><a href="index.php?action=create_plan" class="btn btn-primary">Crear Plan</a></li>
                        <li><a href="index.php?action=logout" class="btn btn-secondary">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="index.php?action=login" class="btn btn-primary">Iniciar Sesión</a></li>
                        <li><a href="index.php?action=register" class="btn btn-secondary">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="main-content container">