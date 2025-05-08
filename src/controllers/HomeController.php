<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Plan.php';
require_once __DIR__ . '/../lib/helpers.php';

function showHomePage($pdo) {
    $planModel = new Plan($pdo);

    // Paginación
    $plansPerPage = 6; // Número de planes por página
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($currentPage < 1) {
        $currentPage = 1;
    }

    $totalPlans = $planModel->getTotalPlansCount();
    $totalPages = ceil($totalPlans / $plansPerPage);
    if ($currentPage > $totalPages && $totalPages > 0) {
        $currentPage = $totalPages;
    }

    $offset = ($currentPage - 1) * $plansPerPage;
    $latestPlans = $planModel->getLatestPlans($plansPerPage, $offset);

    // Pasar los datos a la vista
    global $viewLatestPlans, $viewCurrentPage, $viewTotalPages;
    $viewLatestPlans = $latestPlans;
    $viewCurrentPage = $currentPage;
    $viewTotalPages = $totalPages;
}

showHomePage($pdo);

?>