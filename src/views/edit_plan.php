<?php
// La variable $plan es pasada por showEditPlanForm en PlanController.php
// y contiene los datos del plan que se está editando.
// La variable $pageTitle también es definida por el controlador.

// Incluir el header si no lo hace ya el controlador (showEditPlanForm ya lo hace)
// include_once __DIR__ . '/../../templates/header.php'; // Comentado porque PlanController lo maneja

// Mostrar mensajes de sesión
$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}

// Formatear la fecha del plan para el input datetime-local
// $plan['plan_date'] viene en formato 'Y-m-d H:i:s'
$planDateForInput = '';
if (isset($plan['plan_date'])) {
    try {
        $dateTime = new DateTime($plan['plan_date']);
        $planDateForInput = $dateTime->format('Y-m-d\TH:i');
    } catch (Exception $e) {
        // Manejar el error si la fecha no es válida, aunque no debería ocurrir si viene de la BD
        $planDateForInput = ''; // Dejar vacío o poner un valor por defecto
    }
}
?>

<h2><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Editar Plan'; ?></h2>

<?php if (isset($plan) && $plan): ?>
<form action="index.php?action=edit_plan&id=<?php echo htmlspecialchars($plan['id']); ?>" method="post" class="form-container">
    <div class="form-group">
        <label for="title">Título:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($plan['title'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Descripción:</label>
        <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($plan['description'] ?? ''); ?></textarea>
    </div>

    <div class="form-group">
        <label for="plan_date">Fecha y Hora:</label>
        <input type="datetime-local" id="plan_date" name="plan_date" value="<?php echo htmlspecialchars($planDateForInput); ?>" required>
    </div>

    <div class="form-group">
        <label for="location">Lugar:</label>
        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($plan['location'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
        <label for="max_capacity">Capacidad Máxima:</label>
        <input type="number" id="max_capacity" name="max_capacity" value="<?php echo htmlspecialchars($plan['max_capacity'] ?? '1'); ?>" min="1" required>
    </div>

    <div class="form-group">
        <button type="submit" name="edit_plan_submit">Actualizar Plan</button>
    </div>
</form>
<?php else: ?>
    <p>No se pudo cargar la información del plan para editar.</p>
<?php endif; ?>

<p><a href="index.php?action=view_plan&id=<?php echo htmlspecialchars($plan['id'] ?? ''); ?>">Cancelar y volver a los detalles del plan</a></p>
<p><a href="index.php?action=dashboard">Volver al Dashboard</a></p>

<?php
// Incluir el footer si no lo hace ya el controlador (showEditPlanForm ya lo hace)
// include_once __DIR__ . '/../../templates/footer.php'; // Comentado porque PlanController lo maneja
?>