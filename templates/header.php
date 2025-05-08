<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplicación de Planes</title>
    <!-- Aquí puedes enlazar tus archivos CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1><a href="index.php?action=home">Planes entre Compis</a></h1>
        <nav>
            <ul>
                <li><a href="index.php?action=home">Inicio</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="index.php?action=dashboard">Mi Panel (<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>)</a></li>
                    <li><a href="index.php?action=create_plan">Crear Plan</a></li>
                    <li><a href="index.php?action=logout">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li><a href="index.php?action=login">Iniciar Sesión</a></li>
                    <li><a href="index.php?action=register">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>