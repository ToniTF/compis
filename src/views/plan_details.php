<?php
include_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../src/lib/helpers.php'; // Para mensajes

// Las variables $viewPlan, $viewParticipants, etc., son definidas y pasadas por PlanController.php usando 'global'
global $viewPlan, $viewParticipants, $viewIsLoggedIn, $viewUserId, $viewIsCreator, $viewHasJoined, $viewIsFull, $viewParticipantCount;

$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}

echo "<h2>Detalles del Plan</h2>";

if ($viewPlan) {
    echo "<h3>" . htmlspecialchars($viewPlan['title']) . "</h3>";
    echo "<p><strong>Descripción:</strong> " . nl2br(htmlspecialchars($viewPlan['description'])) . "</p>";
    $formattedDate = date('d/m/Y H:i', strtotime($viewPlan['plan_date']));
    echo "<p><strong>Fecha:</strong> " . htmlspecialchars($formattedDate) . "</p>";
    echo "<p><strong>Lugar:</strong> " . htmlspecialchars($viewPlan['location']) . "</p>";
    echo "<p><strong>Capacidad Máxima:</strong> " . htmlspecialchars($viewPlan['max_capacity']) . " personas</p>";

    if ($viewIsLoggedIn) {
        echo "<p><strong>Creado por:</strong> " . htmlspecialchars($viewPlan['creator_email']) . "</p>";
        
        echo "<h4>Participantes (" . htmlspecialchars($viewParticipantCount) . "/" . htmlspecialchars($viewPlan['max_capacity']) . "):</h4>";
        if (!empty($viewParticipants)) {
            echo "<ul>";
            foreach ($viewParticipants as $participantEmail) {
                echo "<li>" . htmlspecialchars($participantEmail) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Aún no hay participantes.</p>";
        }

        // Lógica para apuntarse al plan
        if (!$viewIsCreator && !$viewHasJoined && !$viewIsFull) {
            echo "<form action='index.php?action=join_plan' method='POST' style='margin-top: 15px;'>";
            echo "<input type='hidden' name='plan_id' value='" . htmlspecialchars($viewPlan['id']) . "'>";
            echo "<button type='submit' name='join_plan_submit'>Apuntarse al Plan</button>";
            echo "</form>";
        } elseif ($viewHasJoined) {
            echo "<p style='color: green; margin-top: 15px;'><strong>Ya estás apuntado a este plan.</strong></p>";
        } elseif ($viewIsFull && !$viewHasJoined) {
            echo "<p style='color: red; margin-top: 15px;'><strong>Este plan ya está completo.</strong></p>";
        } elseif ($viewIsCreator) {
            echo "<p style='color: blue; margin-top: 15px;'><strong>Eres el creador de este plan.</strong></p>";
        }

    } else {
        echo "<p style='margin-top:15px;'><a href='index.php?action=login'>Inicia sesión</a> para ver la lista de participantes y apuntarte.</p>";
    }

} else {
    // Este mensaje ya se maneja con redirección en el controlador si el plan no se encuentra.
    // Pero por si acaso se accede a la vista directamente sin $viewPlan:
    echo "<p>Plan no encontrado o no se ha especificado un ID de plan.</p>";
}
?>
<p style="margin-top: 20px;"><a href="index.php?action=home">Volver a la lista de planes</a></p>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>