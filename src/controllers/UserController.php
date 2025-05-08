<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../lib/helpers.php';

// El enrutador en public/index.php ya incluye este archivo cuando action=dashboard.

function showDashboard($pdo) {
    if (!isset($_SESSION['user_id'])) {
        set_session_message('Debes iniciar sesión para acceder a esta página.', 'error');
        redirect('index.php?action=login');
        return;
    }

    $userId = $_SESSION['user_id'];
    $planModel = new Plan($pdo);

    $createdPlans = $planModel->getPlansByUserId($userId);
    $joinedPlans = $planModel->getJoinedPlansByUserId($userId);

    // Hacemos que los datos estén disponibles para la vista dashboard.php
    global $viewUserCreatedPlans, $viewUserJoinedPlans;
    $viewUserCreatedPlans = $createdPlans;
    $viewUserJoinedPlans = $joinedPlans;

    // El router principal (index.php) cargará src/views/dashboard.php
}

// Llamamos a la función principal del controlador.
// El objeto $pdo ya está disponible porque database.php se incluye en public/index.php antes que este controlador.
if (isset($pdo)) {
    showDashboard($pdo);
} else {
    // Esto no debería ocurrir si la configuración es correcta.
    set_session_message('Error crítico: No se pudo establecer la conexión con la base de datos.', 'error');
    redirect('index.php?action=login'); // O a una página de error genérica
}

?>