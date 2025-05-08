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

<h2 class="page-title">Mi Panel</h2>
<div class="card" style="margin-bottom: 30px;">
    <div class="card-content">
        <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'Usuario'); ?></strong>!</p>

        <?php
        $message = get_session_message();
        if ($message) {
            echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
        }
        ?>
        
        <div class="plan-actions">
            <a href="index.php?action=create_plan" class="btn btn-primary">Crear Nuevo Plan</a>
            <a href="index.php?action=home" class="btn btn-secondary">Explorar Planes Disponibles</a>
        </div>
    </div>
</div>

<h3 class="page-title">Mis Planes Creados</h3>
<div class="plans-grid">
    <?php if (!empty($viewUserCreatedPlans)): ?>
        <?php foreach ($viewUserCreatedPlans as $plan): ?>
            <div class="card plan-card">
                <h4 class="card-title"><?php echo htmlspecialchars($plan['title']); ?></h4>
                <div class="card-content">
                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($plan['plan_date']))); ?></p>
                    <p><strong>Lugar:</strong> <?php echo htmlspecialchars($plan['location']); ?></p>
                    <div class="plan-actions">
                        <a href="index.php?action=view_plan&id=<?php echo htmlspecialchars($plan['id']); ?>" class="btn btn-primary">Ver detalles</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <div class="card-content">
                <p>Aún no has creado ningún plan.</p>
                <div class="plan-actions">
                    <a href="index.php?action=create_plan" class="btn btn-success">¡Crea uno ahora!</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<h3 class="page-title">Planes a los que me he Apuntado</h3>
<div class="plans-grid">
    <?php if (!empty($viewUserJoinedPlans)): ?>
        <?php foreach ($viewUserJoinedPlans as $plan): ?>
            <div class="card plan-card">
                <h4 class="card-title"><?php echo htmlspecialchars($plan['title']); ?></h4>
                <div class="card-content">
                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($plan['plan_date']))); ?></p>
                    <p><strong>Lugar:</strong> <?php echo htmlspecialchars($plan['location']); ?></p>
                    <p><strong>Creado por:</strong> <?php echo htmlspecialchars($plan['creator_email']); ?></p>
                    <div class="plan-actions">
                        <a href="index.php?action=view_plan&id=<?php echo htmlspecialchars($plan['id']); ?>" class="btn btn-primary">Ver detalles</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <div class="card-content">
                <p>No te has apuntado a ningún plan todavía.</p>
                <div class="plan-actions">
                    <a href="index.php?action=home" class="btn btn-success">Explora planes</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>