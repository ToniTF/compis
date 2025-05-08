<?php
require_once __DIR__ . '/../../config/database.php'; // Para la conexión $pdo
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../lib/helpers.php'; // Para set_session_message y redirect

$action = $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register' && isset($_POST['register_submit'])) {
        handleRegister($pdo);
    } elseif ($action === 'login' && isset($_POST['login_submit'])) {
        handleLogin($pdo);
    }
} elseif ($action === 'logout') {
    handleLogout();
}

function handleRegister($pdo) {
    // Validaciones básicas
    if (empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
        set_session_message('Todos los campos son obligatorios.', 'error');
        redirect('index.php?action=register');
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        set_session_message('Las contraseñas no coinciden.', 'error');
        redirect('index.php?action=register');
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        set_session_message('El formato del correo electrónico no es válido.', 'error');
        redirect('index.php?action=register');
    }

    $userModel = new User($pdo);
    $email = $_POST['email'];

    // Verificar si el email ya existe
    if ($userModel->findByEmail($email)) {
        set_session_message('Este correo electrónico ya está registrado.', 'error');
        redirect('index.php?action=register');
    }

    // Hashear la contraseña
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Guardar usuario
    $userId = $userModel->create($email, $hashedPassword);

    if ($userId) {
        set_session_message('Registro exitoso. Ahora puedes iniciar sesión.', 'success');
        redirect('index.php?action=login');
    } else {
        set_session_message('Error durante el registro. Inténtalo de nuevo.', 'error');
        redirect('index.php?action=register');
    }
}

function handleLogin($pdo) {
    if (empty($_POST['email']) || empty($_POST['password'])) {
        set_session_message('Correo electrónico y contraseña son obligatorios.', 'error');
        redirect('index.php?action=login');
    }

    $userModel = new User($pdo);
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = $userModel->findByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        // Opcional: Regenerar ID de sesión por seguridad
        // session_regenerate_id(true);
        redirect('index.php?action=dashboard');
    } else {
        set_session_message('Credenciales incorrectas.', 'error');
        redirect('index.php?action=login');
    }
}

function handleLogout() {
    session_unset();
    session_destroy();
    redirect('index.php?action=home');
    exit;
}

// Si se accede directamente a AuthController.php sin una acción POST válida o 'logout',
// podríamos redirigir a la home o a login.
// Por ahora, el enrutador de index.php se encarga de esto.
// Si la acción es 'login' o 'register' pero no es POST, se mostrará el formulario (manejado por index.php al incluir la vista).

// Ejemplo de cómo se cargarían las vistas si este controlador fuera más complejo:
// if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'GET') {
//     require_once __DIR__ . '/../views/login.php';
// }
// if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'GET') {
//     require_once __DIR__ . '/../views/register.php';
// }

?>