<?php
// session_start(); // La sesión ya debería estar iniciada por public/index.php
include_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../src/lib/helpers.php'; // Para redirect y mensajes

// Proteger esta página: redirigir si no está logueado (ya se hace en el controlador, pero doble check no daña)
if (!isset($_SESSION['user_id'])) {
    set_session_message('Debes iniciar sesión para acceder a esta página.', 'error');
    redirect('index.php?action=login');
}

// Las variables $viewUserCreatedPlans y $viewUserJoinedPlans son definidas y pasadas por UserController.php
global $viewUserCreatedPlans, $viewUserJoinedPlans;

?>

<h2>Mi Panel</h2>
<p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'Usuario'); ?></strong>!</p>

<?php
$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}
?>

<h3>Mis Planes Creados</h3>
<div class="plans-list">
    <?php if (!empty($viewUserCreatedPlans)): ?>
        <?php foreach ($viewUserCreatedPlans as $plan): ?>
            <div class="plan-card">
                <h4><?php echo htmlspecialchars($plan['title']); ?></h4>
                <p><strong>Fecha:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($plan['plan_date']))); ?></p>
                <p><strong>Lugar:</strong> <?php echo htmlspecialchars($plan['location']); ?></p>
                <p><a href="index.php?action=view_plan&id=<?php echo htmlspecialchars($plan['id']); ?>">Ver detalles</a></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aún no has creado ningún plan. <a href="index.php?action=create_plan">¡Crea uno ahora!</a></p>
    <?php endif; ?>
</div>

<h3>Planes a los que me he Apuntado</h3>
<div class="plans-list">
    <?php if (!empty($viewUserJoinedPlans)): ?>
        <?php foreach ($viewUserJoinedPlans as $plan): ?>
            <div class="plan-card">
                <h4><?php echo htmlspecialchars($plan['title']); ?></h4>
                <p><strong>Fecha:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($plan['plan_date']))); ?></p>
                <p><strong>Lugar:</strong> <?php echo htmlspecialchars($plan['location']); ?></p>
                <p><strong>Creado por:</strong> <?php echo htmlspecialchars($plan['creator_email']); ?></p>
                <p><a href="index.php?action=view_plan&id=<?php echo htmlspecialchars($plan['id']); ?>">Ver detalles</a></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No te has apuntado a ningún plan todavía. <a href="index.php?action=home">Explora planes</a>.</p>
    <?php endif; ?>
</div>

<h3>Explorar Otros Planes</h3>
<p><a href="index.php?action=home">Ver todos los planes disponibles</a></p>


<?php include_once __DIR__ . '/../../templates/footer.php'; ?>