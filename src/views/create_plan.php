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

<h2>Crear Nuevo Plan</h2>
<?php
$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}
?>
<form action="index.php?action=create_plan" method="POST">
    <div>
        <label for="title">Título:</label>
        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>">
    </div>
    <div>
        <label for="description">Descripción:</label>
        <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
    </div>
    <div>
        <label for="plan_date">Fecha y Hora:</label>
        <input type="datetime-local" id="plan_date" name="plan_date" required value="<?php echo htmlspecialchars($formData['plan_date'] ?? ''); ?>">
    </div>
    <div>
        <label for="location">Lugar:</label>
        <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($formData['location'] ?? ''); ?>">
    </div>
    <div>
        <label for="max_capacity">Capacidad Máxima:</label>
        <input type="number" id="max_capacity" name="max_capacity" min="1" required value="<?php echo htmlspecialchars($formData['max_capacity'] ?? '1'); ?>">
    </div>
    <button type="submit" name="create_plan_submit">Crear Plan</button>
</form>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>