<?php
// session_start(); // La sesión ya debería estar iniciada por public/index.php
include_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../src/lib/helpers.php'; // Para redirect y mensajes

// Proteger esta página
if (!isset($_SESSION['user_id'])) {
    set_session_message('Debes iniciar sesión para crear un plan.', 'error');
    redirect('index.php?action=login');
}

// Recuperar datos del formulario de la sesión si existen (después de una redirección por error)
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Limpiar para no reutilizarlos accidentalmente

?>

<h2 class="page-title">Crear Nuevo Plan</h2>
<?php
$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}
?>

<div class="card">
    <div class="card-content">
        <form action="index.php?action=create_plan" method="POST" class="form-container">
            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="description">Descripción:</label>
                <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="plan_date">Fecha y Hora:</label>
                <input type="datetime-local" id="plan_date" name="plan_date" class="form-control" required value="<?php echo htmlspecialchars($formData['plan_date'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="location">Lugar:</label>
                <input type="text" id="location" name="location" class="form-control" required value="<?php echo htmlspecialchars($formData['location'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="max_capacity">Capacidad Máxima:</label>
                <input type="number" id="max_capacity" name="max_capacity" class="form-control" min="1" required value="<?php echo htmlspecialchars($formData['max_capacity'] ?? '1'); ?>">
            </div>
            <div class="form-group">
                <button type="submit" name="create_plan_submit" class="btn btn-primary">Crear Plan</button>
            </div>
        </form>
    </div>
</div>

<div class="plan-actions" style="margin-top: 20px;">
    <a href="index.php?action=dashboard" class="btn btn-outline">Volver al Dashboard</a>
</div>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>