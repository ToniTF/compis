<?php include_once __DIR__ . '/../../templates/header.php'; ?>

<h2>Registrarse</h2>
<?php
// Usar la función de ayuda para mostrar mensajes
require_once __DIR__ . '/../../src/lib/helpers.php';
$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}
?>
<form action="index.php?action=register" method="POST">
    <div>
        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>
    <div>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <label for="confirm_password">Confirmar Contraseña:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <button type="submit" name="register_submit">Registrarse</button>
</form>
<p>¿Ya tienes una cuenta? <a href="index.php?action=login">Inicia sesión aquí</a></p>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>