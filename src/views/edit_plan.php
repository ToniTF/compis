<?php
// La variable $plan es pasada por showEditPlanForm en PlanController.php
// y contiene los datos del plan que se está editando.
// La variable $pageTitle también es definida por el controlador.

// Mostrar mensajes de sesión
$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}

// Formatear la fecha del plan para el input datetime-local
$planDateForInput = '';
if (isset($plan['plan_date'])) {
    try {
        $dateTime = new DateTime($plan['plan_date']);
        $planDateForInput = $dateTime->format('Y-m-d\TH:i');
    } catch (Exception $e) {
        // Manejar el error si la fecha no es válida
        $planDateForInput = '';
    }
}
?>

<h2 class="page-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Editar Plan'; ?></h2>

<?php if (isset($plan) && $plan): ?>
<div class="card">
    <div class="card-content">
        <form action="index.php?action=edit_plan&id=<?php echo htmlspecialchars($plan['id']); ?>" method="post" class="form-container">
            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($plan['title'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Descripción:</label>
                <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($plan['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="plan_date">Fecha y Hora:</label>
                <input type="datetime-local" id="plan_date" name="plan_date" class="form-control" value="<?php echo htmlspecialchars($planDateForInput); ?>" required>
            </div>

            <div class="form-group">
                <label for="location">Lugar:</label>
                <input type="text" id="location" name="location" class="form-control" value="<?php echo htmlspecialchars($plan['location'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="max_capacity">Capacidad Máxima:</label>
                <input type="number" id="max_capacity" name="max_capacity" class="form-control" value="<?php echo htmlspecialchars($plan['max_capacity'] ?? '1'); ?>" min="1" required>
            </div>

            <div class="form-group">
                <button type="submit" name="edit_plan_submit" class="btn btn-primary">Actualizar Plan</button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
    <p class="message error">No se pudo cargar la información del plan para editar.</p>
<?php endif; ?>

<div class="plan-actions" style="margin-top: 20px;">
    <a href="index.php?action=view_plan&id=<?php echo htmlspecialchars($plan['id'] ?? ''); ?>" class="btn btn-outline">Volver a los detalles del plan</a>
    <a href="index.php?action=dashboard" class="btn btn-secondary">Volver al Dashboard</a>
</div>