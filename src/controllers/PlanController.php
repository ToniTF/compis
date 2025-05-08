<?php
require_once __DIR__ . '/../../config/database.php'; // Para la conexión $pdo
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/User.php'; // Podría ser necesario si necesitamos datos del usuario
require_once __DIR__ . '/../lib/helpers.php'; // Para set_session_message, redirect, etc.

// El enrutador en public/index.php ya incluye este archivo según la acción.
$action = $_GET['action'] ?? null;
$planId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Asegurarse de que $pdo esté disponible
if (!isset($pdo)) {
    // Esto no debería suceder si database.php se incluye correctamente en index.php
    die("Error crítico: La conexión PDO no está disponible.");
}

$planModel = new Plan($pdo);

// Lógica principal del controlador de planes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create_plan' && isset($_POST['create_plan_submit'])) {
        handleCreatePlanPost($pdo, $planModel);
    } elseif ($action === 'join_plan' && isset($_POST['join_plan_submit'])) {
        handleJoinPlanPost($pdo, $planModel);
    }
} else { // Método GET
    if ($action === 'create_plan') {
        // La vista create_plan.php ya se encarga de la redirección si no está logueado.
        // El router principal (index.php) cargará src/views/create_plan.php
        // No se necesita acción adicional aquí para mostrar el formulario.
    } elseif ($action === 'view_plan' && $planId) {
        handleViewPlanGet($pdo, $planModel, $planId);
    }
}

function handleCreatePlanPost($pdo, Plan $planModel) {
    if (!isset($_SESSION['user_id'])) {
        set_session_message('Debes iniciar sesión para crear un plan.', 'error');
        redirect('index.php?action=login');
        return; // Salir de la función
    }

    $requiredFields = ['title', 'description', 'plan_date', 'location', 'max_capacity'];
    $missingFields = [];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = ucfirst(str_replace('_', ' ', $field));
        }
    }

    if (!empty($missingFields)) {
        set_session_message('Los siguientes campos son obligatorios: ' . implode(', ', $missingFields) . '.', 'error');
        // Guardar los datos del formulario en la sesión para repoblar el formulario
        $_SESSION['form_data'] = $_POST;
        redirect('index.php?action=create_plan');
        return;
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $planDateStr = $_POST['plan_date'];
    $location = trim($_POST['location']);
    $maxCapacity = filter_var($_POST['max_capacity'], FILTER_VALIDATE_INT);

    // Validar fecha (no en el pasado y formato correcto)
    $planTimestamp = strtotime($planDateStr);
    if ($planTimestamp === false || $planTimestamp < time()) {
        set_session_message('La fecha y hora del plan deben ser futuras y válidas.', 'error');
        $_SESSION['form_data'] = $_POST;
        redirect('index.php?action=create_plan');
        return;
    }
    $planDateFormatted = date('Y-m-d H:i:s', $planTimestamp);

    if ($maxCapacity === false || $maxCapacity <= 0) {
        set_session_message('La capacidad máxima debe ser un número positivo.', 'error');
        $_SESSION['form_data'] = $_POST;
        redirect('index.php?action=create_plan');
        return;
    }

    $userId = $_SESSION['user_id'];
    $newPlanId = $planModel->create($userId, $title, $description, $planDateFormatted, $location, $maxCapacity);

    if ($newPlanId) {
        unset($_SESSION['form_data']); // Limpiar datos del formulario de la sesión
        set_session_message('¡Plan creado con éxito!', 'success');
        redirect('index.php?action=view_plan&id=' . $newPlanId); // Redirigir a la vista del nuevo plan
    } else {
        set_session_message('Error al crear el plan. Inténtalo de nuevo.', 'error');
        $_SESSION['form_data'] = $_POST;
        redirect('index.php?action=create_plan');
    }
}

function handleViewPlanGet($pdo, Plan $planModel, int $planId) {
    $plan = $planModel->findByIdWithCreator($planId);

    if (!$plan) {
        set_session_message('Plan no encontrado.', 'error');
        redirect('index.php?action=home');
        return;
    }

    $participants = [];
    $isLoggedIn = isset($_SESSION['user_id']);
    $userId = $_SESSION['user_id'] ?? null;
    $isCreator = false;
    $hasJoined = false;
    $isFull = false;
    $participantCount = 0;

    if ($isLoggedIn) {
        $participants = $planModel->getPlanParticipants($planId);
        $isCreator = ($userId === $plan['user_id']);
        $hasJoined = $planModel->hasUserJoined($userId, $planId);
        $participantCount = $planModel->getParticipantCount($planId);
        $isFull = ($participantCount >= $plan['max_capacity']);
    }

    // Estas variables estarán disponibles para la vista plan_details.php
    global $viewPlan, $viewParticipants, $viewIsLoggedIn, $viewUserId, $viewIsCreator, $viewHasJoined, $viewIsFull, $viewParticipantCount;
    $viewPlan = $plan;
    $viewParticipants = $participants;
    $viewIsLoggedIn = $isLoggedIn;
    $viewUserId = $userId;
    $viewIsCreator = $isCreator;
    $viewHasJoined = $hasJoined;
    $viewIsFull = $isFull;
    $viewParticipantCount = $participantCount;

    // El router principal (index.php) cargará src/views/plan_details.php
}

function handleJoinPlanPost($pdo, Plan $planModel) {
    if (!isset($_SESSION['user_id'])) {
        set_session_message('Debes iniciar sesión para apuntarte a un plan.', 'error');
        redirect('index.php?action=login');
        return;
    }

    if (empty($_POST['plan_id'])) {
        set_session_message('No se especificó el plan.', 'error');
        redirect('index.php?action=home');
        return;
    }

    $planId = (int)$_POST['plan_id'];
    $userId = $_SESSION['user_id'];

    $plan = $planModel->findByIdWithCreator($planId); // Necesitamos max_capacity y user_id (creador)

    if (!$plan) {
        set_session_message('Plan no encontrado.', 'error');
        redirect('index.php?action=home');
        return;
    }

    if ($plan['user_id'] === $userId) {
        set_session_message('No puedes apuntarte a tu propio plan.', 'info');
        redirect('index.php?action=view_plan&id=' . $planId);
        return;
    }

    if ($planModel->hasUserJoined($userId, $planId)) {
        set_session_message('Ya estás apuntado a este plan.', 'info');
        redirect('index.php?action=view_plan&id=' . $planId);
        return;
    }

    $participantCount = $planModel->getParticipantCount($planId);
    if ($participantCount >= $plan['max_capacity']) {
        set_session_message('Este plan ya está completo.', 'error');
        redirect('index.php?action=view_plan&id=' . $planId);
        return;
    }

    if ($planModel->joinPlan($userId, $planId)) {
        set_session_message('¡Te has apuntado al plan con éxito!', 'success');
    } else {
        set_session_message('Error al apuntarse al plan. Inténtalo de nuevo.', 'error');
    }
    redirect('index.php?action=view_plan&id=' . $planId);
}

?>