<?php 
include_once __DIR__ . '/../../templates/header.php'; 
// Las variables $viewLatestPlans, $viewCurrentPage, $viewTotalPages son definidas y pasadas por HomeController.php
global $viewLatestPlans, $viewCurrentPage, $viewTotalPages;
?>

<h2>Últimos Planes Creados</h2>

<?php
$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}
?>

<div class="plans-list">
    <?php if (!empty($viewLatestPlans)): ?>
        <?php foreach ($viewLatestPlans as $plan): ?>
            <div class="plan-card">
                <h3><?php echo htmlspecialchars($plan['title']); ?></h3>
                <p><strong>Fecha:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($plan['plan_date']))); ?></p>
                <p><strong>Lugar:</strong> <?php echo htmlspecialchars($plan['location']); ?></p>
                <p><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars(substr($plan['description'], 0, 150))) . (strlen($plan['description']) > 150 ? '...' : ''); ?></p>
                <p><strong>Capacidad:</strong> <?php echo htmlspecialchars($plan['max_capacity']); ?> personas</p>
                <a href="index.php?action=view_plan&id=<?php echo htmlspecialchars($plan['id']); ?>">Ver detalles</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No hay planes disponibles en este momento.</p>
    <?php endif; ?>
</div>

<?php if ($viewTotalPages > 1): ?>
<div class="pagination">
    <?php if ($viewCurrentPage > 1): ?>
        <a href="index.php?action=home&page=<?php echo $viewCurrentPage - 1; ?>">&laquo; Anterior</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $viewTotalPages; $i++): ?>
        <?php if ($i == $viewCurrentPage): ?>
            <span class="current-page"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="index.php?action=home&page=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($viewCurrentPage < $viewTotalPages): ?>
        <a href="index.php?action=home&page=<?php echo $viewCurrentPage + 1; ?>">Siguiente &raquo;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>