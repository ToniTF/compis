<?php include_once __DIR__ . '/../../templates/header.php'; ?>

<h2 class="page-title">Registrarse</h2>
<?php
// Usar la función de ayuda para mostrar mensajes
require_once __DIR__ . '/../../src/lib/helpers.php';
$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}
?>

<div class="card">
    <div class="card-content">
        <form action="index.php?action=register" method="POST" class="form-container">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <div class="form-group">
                <button type="submit" name="register_submit" class="btn btn-primary">Registrarse</button>
            </div>
        </form>
    </div>
</div>

<div style="text-align: center; margin-top: 20px;">
    <p>¿Ya tienes una cuenta? <a href="index.php?action=login" class="btn btn-outline">Iniciar sesión</a></p>
</div>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>