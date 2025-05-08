<?php
// Punto de entrada principal de la aplicación
session_start();

// Configuración básica y conexión a la BD
require_once __DIR__ . '/../config/database.php'; // $pdo estará disponible globalmente
require_once __DIR__ . '/../src/lib/helpers.php';

// Lógica de enrutamiento
$action = $_GET['action'] ?? 'home'; // Acción por defecto es 'home'
$controllerFile = null;
$viewFile = null;

// Determinar el controlador y la vista según la acción
switch ($action) {
    case 'home':
        $controllerFile = __DIR__ . '/../src/controllers/HomeController.php';
        $viewFile = __DIR__ . '/../src/views/home.php';
        break;
    case 'login':
        // AuthController maneja tanto la muestra del formulario (GET) como el procesamiento (POST)
        $controllerFile = __DIR__ . '/../src/controllers/AuthController.php';
        // Si es GET, AuthController no hace nada y se carga la vista login.php
        // Si es POST, AuthController procesa y redirige, por lo que la vista no se carga directamente aquí.
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $viewFile = __DIR__ . '/../src/views/login.php';
        }
        break;
    case 'register':
        $controllerFile = __DIR__ . '/../src/controllers/AuthController.php';
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $viewFile = __DIR__ . '/../src/views/register.php';
        }
        break;
    case 'logout':
        $controllerFile = __DIR__ . '/../src/controllers/AuthController.php';
        // AuthController->handleLogout() redirige, no necesita vista.
        break;
    case 'dashboard':
        $controllerFile = __DIR__ . '/../src/controllers/UserController.php';
        $viewFile = __DIR__ . '/../src/views/dashboard.php';
        break;
    case 'create_plan':
        $controllerFile = __DIR__ . '/../src/controllers/PlanController.php';
        // Si es GET, se muestra el formulario. Si es POST, PlanController procesa.
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $viewFile = __DIR__ . '/../src/views/create_plan.php';
        }
        break;
    case 'view_plan':
        $controllerFile = __DIR__ . '/../src/controllers/PlanController.php';
        $viewFile = __DIR__ . '/../src/views/plan_details.php';
        break;
    case 'join_plan': // Esta acción es solo POST, manejada por PlanController
        $controllerFile = __DIR__ . '/../src/controllers/PlanController.php';
        // PlanController->handleJoinPlanPost() redirige, no necesita vista.
        break;
    default:
        $viewFile = __DIR__ . '/../src/views/404.php';
        break;
}

// Incluir el controlador si existe para la acción actual
// El controlador puede definir variables globales para la vista o manejar redirecciones.
if ($controllerFile && file_exists($controllerFile)) {
    require_once $controllerFile;
} elseif ($action !== 'home' && !in_array($action, ['login', 'register']) && $_SERVER['REQUEST_METHOD'] === 'GET' && !$viewFile) {
    // Si no hay controlador específico para una acción GET que no sea home/login/register
    // y no se ha definido una vista (ej. una acción POST que falló en encontrar controlador)
    // entonces es un 404.
    $viewFile = __DIR__ . '/../src/views/404.php';
}

// Incluir la vista si está definida y el script no ha sido terminado por una redirección en el controlador
// Los controladores POST (login, register, create_plan, join_plan) y logout manejan su propia redirección.
if ($viewFile && file_exists($viewFile)) {
    // No es necesario incluir header/footer aquí si las vistas individuales ya lo hacen.
    // Las vistas como home.php, login.php, etc., ya incluyen header.php y footer.php.
    require_once $viewFile;
} elseif (!$viewFile && $_SERVER['REQUEST_METHOD'] === 'GET' && !in_array($action, ['logout'])) {
    // Si después de todo, no hay vista para una solicitud GET (y no es logout que solo redirige)
    // es un 404.
    require_once __DIR__ . '/../src/views/404.php';
}

?>