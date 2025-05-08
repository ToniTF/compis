<?php
require_once __DIR__ . '/../../config/database.php'; // Para la conexión $pdo
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../models/User.php'; // Podría ser necesario si necesitamos datos del usuario
require_once __DIR__ . '/../lib/helpers.php'; // Para set_session_message, redirect, etc.

// El enrutador en public/index.php ya incluye este archivo según la acción.
$action = $_GET['action'] ?? null;
$planIdFromGet = isset($_GET['id']) ? (int)$_GET['id'] : null; // Para acciones GET o POST que pasan 'id' en la URL

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
        handleJoinPlanPost($pdo, $planModel); // Usa $_POST['plan_id'] internamente
    } elseif ($action === 'edit_plan' && isset($_POST['edit_plan_submit']) && $planIdFromGet) {
        handleEditPlanPost($pdo, $planModel, $planIdFromGet);
    } elseif ($action === 'leave_plan') { // Ahora es POST
        $planIdFromPost = isset($_POST['plan_id']) ? (int)$_POST['plan_id'] : null;
        if ($planIdFromPost) {
            handleLeavePlanPost($pdo, $planModel, $planIdFromPost);
        } else {
            set_session_message('ID del plan no proporcionado para abandonar.', 'error');
            redirect('index.php?action=dashboard');
        }
    } elseif ($action === 'delete_plan' && $planIdFromGet) { // Ahora es POST
        handleDeletePlanPost($pdo, $planModel, $planIdFromGet);
    }
} else { // Método GET
    if ($action === 'create_plan') {
        // La vista create_plan.php ya se encarga de la redirección si no está logueado.
        // El router principal (index.php) cargará src/views/create_plan.php
        // No se necesita acción adicional aquí para mostrar el formulario.
    } elseif ($action === 'view_plan' && $planIdFromGet) {
        handleViewPlanGet($pdo, $planModel, $planIdFromGet);
    } elseif ($action === 'edit_plan' && $planIdFromGet) {
        showEditPlanForm($pdo, $planModel, $planIdFromGet);
    }
}

/**
 * Muestra el formulario para editar un plan existente.
 */
function showEditPlanForm(PDO $pdo, Plan $planModel, int $planId): void {
    if (!isset($_SESSION['user_id'])) {
        set_session_message('Debes iniciar sesión para editar un plan.', 'error');
        redirect('index.php?action=login');
        return;
    }

    $plan = $planModel->findByIdWithCreator($planId); // Corregido: getPlanById -> findByIdWithCreator

    if (!$plan) {
        set_session_message('Plan no encontrado.', 'error');
        redirect('index.php?action=dashboard'); // Corregido: page= -> action=
        return;
    }

    // Verificar si el usuario actual es el creador del plan
    if ($plan['user_id'] !== $_SESSION['user_id']) {
        set_session_message('No tienes permiso para editar este plan.', 'error');
        redirect('index.php?action=view_plan&id=' . $planId); // Corregido: page=plan_details&action=plan_details -> action=view_plan
        return;
    }

    // Pasar los datos del plan a la vista
    $pageTitle = "Editar Plan";
    $viewPath = __DIR__ . '/../views/edit_plan.php';
    include __DIR__ . '/../../templates/header.php';
    include $viewPath;
    include __DIR__ . '/../../templates/footer.php';
}

/**
 * Maneja la actualización de un plan existente.
 */
function handleEditPlanPost(PDO $pdo, Plan $planModel, int $planId): void {
    if (!isset($_SESSION['user_id'])) {
        set_session_message('Debes iniciar sesión para actualizar un plan.', 'error');
        redirect('index.php?action=login'); // Corregido: page= -> action=
        return;
    }

    $currentUserId = $_SESSION['user_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $plan_date = trim($_POST['plan_date'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $max_capacity = filter_input(INPUT_POST, 'max_capacity', FILTER_VALIDATE_INT);

    // Validación básica (puedes expandirla)
    if (empty($title) || empty($description) || empty($plan_date) || empty($location) || $max_capacity === false || $max_capacity <= 0) {
        set_session_message('Todos los campos son obligatorios y la capacidad máxima debe ser un número positivo.', 'error');
        redirect('index.php?action=edit_plan&id=' . $planId); // Corregido: page=edit_plan&action=edit_plan -> action=edit_plan
        return;
    }
    
    // Formatear la fecha para la base de datos (YYYY-MM-DD HH:MM:SS)
    try {
        $dateTime = new DateTime($plan_date);
        $formattedPlanDate = $dateTime->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        set_session_message('Formato de fecha inválido. Usa YYYY-MM-DDTHH:MM.', 'error');
        redirect('index.php?action=edit_plan&id=' . $planId); // Corregido: page=edit_plan&action=edit_plan -> action=edit_plan
        return;
    }


    if ($planModel->update($planId, $currentUserId, $title, $description, $formattedPlanDate, $location, $max_capacity)) {
        set_session_message('Plan actualizado con éxito.', 'success');
        redirect('index.php?action=view_plan&id=' . $planId); // Corregido: page=plan_details&action=plan_details -> action=view_plan
    } else {
        set_session_message('Error al actualizar el plan o no tienes permiso.', 'error');
        redirect('index.php?action=edit_plan&id=' . $planId); // Corregido: page=edit_plan&action=edit_plan -> action=edit_plan
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

    $participants = []; // Inicializar como array vacío por defecto
    $isLoggedIn = isset($_SESSION['user_id']);
    $userId = $_SESSION['user_id'] ?? null;
    $isCreator = false;
    $hasJoined = false;
    $isFull = false;
    $participantCount = 0;

    if ($isLoggedIn) {
        $fetchedParticipants = $planModel->getPlanParticipants($planId);
        // Asegurarse de que lo que devuelve el modelo es un array
        if (is_array($fetchedParticipants)) {
            $participants = $fetchedParticipants;
        }
        // Si $fetchedParticipants no es un array (ej. false o string de error), $participants seguirá siendo []

        $isCreator = ($userId === $plan['user_id']);
        $hasJoined = $planModel->hasUserJoined($userId, $planId);
        $participantCount = $planModel->getParticipantCount($planId);
        $isFull = ($participantCount >= $plan['max_capacity']);
    }

    $is_plan_creator = false;
    if (isset($_SESSION['user_id']) && $plan && isset($plan['user_id']) && $_SESSION['user_id'] == $plan['user_id']) {
        $is_plan_creator = true;
    }

    $is_registered_to_plan = false;
    if (isset($_SESSION['user_id']) && is_array($participants)) { // Comprobar que $participants es un array
        foreach ($participants as $participant) {
            // Comprobar que $participant es un array y tiene la clave 'user_id' antes de acceder
            if (is_array($participant) && isset($participant['user_id']) && $participant['user_id'] == $_SESSION['user_id']) {
                $is_registered_to_plan = true;
                break;
            }
        }
    }

    // Estas variables estarán disponibles para la vista plan_details.php
    global $viewPlan, $viewParticipants, $viewIsLoggedIn, $viewUserId, $viewIsCreator, $viewHasJoined, $viewIsFull, $viewParticipantCount, $viewIsPlanCreator, $viewIsRegisteredToPlan;
    $viewPlan = $plan;
    $viewParticipants = $participants; // $participants es ahora un array (posiblemente vacío)
    $viewIsLoggedIn = $isLoggedIn;
    $viewUserId = $userId;
    $viewIsCreator = $isCreator;
    $viewHasJoined = $hasJoined;
    $viewIsFull = $isFull;
    $viewParticipantCount = $participantCount; // Asegúrate que getParticipantCount siempre devuelva un int
    $viewIsPlanCreator = $is_plan_creator;
    $viewIsRegisteredToPlan = $is_registered_to_plan;

    // El router principal (index.php) cargará src/views/plan_details.php
}

function handleJoinPlanPost(PDO $pdo, Plan $planModel) {
    if (!isset($_SESSION['user_id'])) {
        set_session_message('Debes iniciar sesión para unirte a un plan.', 'error');
        redirect('index.php?action=login'); // Corregido: page= -> action=
        return;
    }

    if (!isset($_POST['plan_id'])) {
        set_session_message('ID del plan no proporcionado.', 'error');
        redirect('index.php?action=dashboard'); // Corregido: page= -> action=
        return;
    }

    $planId = (int)$_POST['plan_id'];
    $userId = $_SESSION['user_id'];

    // Verificar si el plan existe y no está lleno
    $plan = $planModel->findByIdWithCreator($planId); // Corregido: getPlanById -> findByIdWithCreator
    if (!$plan) {
        set_session_message('El plan no existe.', 'error');
        redirect('index.php?action=dashboard'); // Corregido: page= -> action=
        return;
    }

    if ($planModel->hasUserJoined($userId, $planId)) { // Corregido: isUserInPlan -> hasUserJoined
        set_session_message('Ya estás unido a este plan.', 'info');
        redirect('index.php?action=view_plan&id=' . $planId); // Corregido: page=plan_details&action=plan_details -> action=view_plan
        return;
    }

    if ($planModel->getParticipantCount($planId) >= $plan['max_capacity']) { // Corregido: count(getParticipants) -> getParticipantCount
        set_session_message('Este plan ya ha alcanzado su capacidad máxima.', 'error');
        redirect('index.php?action=view_plan&id=' . $planId); // Corregido: page=plan_details&action=plan_details -> action=view_plan
        return;
    }

    if ($planModel->joinPlan($userId, $planId)) {
        set_session_message('Te has unido al plan con éxito.', 'success');
    } else {
        set_session_message('Error al unirse al plan. Inténtalo de nuevo.', 'error');
    }
    redirect('index.php?action=view_plan&id=' . $planId); // Corregido: page=plan_details&action=plan_details -> action=view_plan
}

/**
 * Maneja la acción de un usuario que abandona un plan (método POST).
 */
function handleLeavePlanPost(PDO $pdo, Plan $planModel, int $planId): void { // Renombrado y $planId como parámetro
    if (!isset($_SESSION['user_id'])) { // Corregido: is_user_logged_in
        set_session_message('Debes iniciar sesión para abandonar un plan.', 'error'); // Corregido: orden de parámetros
        redirect('index.php?action=login');
        return;
    }

    $userId = $_SESSION['user_id'] ?? null; // Ya estaba bien

    if (!$userId) { // $planId ya se valida como parámetro de función
        set_session_message('Error de sesión, no se pudo identificar al usuario.', 'error');
        redirect('index.php?action=login'); 
        return;
    }

    if ($planModel->leavePlan($planId, $userId)) {
        set_session_message('Has abandonado el plan correctamente.', 'success'); // Corregido: orden de parámetros
    } else {
        set_session_message('No se pudo abandonar el plan. Es posible que no estuvieras unido o hubo un error.', 'error'); // Corregido: orden de parámetros
    }

    // Redirigir a la página de detalles del plan o al dashboard
    $planDetails = $planModel->findByIdWithCreator($planId); // Corregido: getPlanById -> findByIdWithCreator
    if ($planDetails) {
        redirect('index.php?action=view_plan&id=' . $planId); // Corregido: action=plan_details -> action=view_plan
    } else {
        redirect('index.php?action=dashboard');
    }
}

/**
 * Maneja la eliminación de un plan (método POST).
 */
function handleDeletePlanPost(PDO $pdo, Plan $planModel, int $planId): void { // Renombrado
    if (!isset($_SESSION['user_id'])) {
        set_session_message('Debes iniciar sesión para eliminar un plan.', 'error');
        redirect('index.php?action=login');
        return;
    }

    $userId = $_SESSION['user_id'];
    $plan = $planModel->findByIdWithCreator($planId); 

    if (!$plan) {
        set_session_message('Plan no encontrado.', 'error');
        redirect('index.php?action=dashboard'); // Corregido: home -> dashboard (más apropiado)
        return;
    }

    if ($plan['user_id'] !== $userId) {
        set_session_message('No tienes permiso para eliminar este plan.', 'error');
        redirect('index.php?action=view_plan&id=' . $planId);
        return;
    }

    if ($planModel->deletePlanById($planId, $userId)) {
        set_session_message('Plan eliminado con éxito.', 'success'); // Usar helper para mensajes
        redirect('index.php?action=dashboard'); // Usar helper para redirección
    } else {
        set_session_message('Error al eliminar el plan.', 'error'); // Usar helper para mensajes
        redirect('index.php?action=view_plan&id=' . $planId); // Usar helper para redirección
    }
}
?>