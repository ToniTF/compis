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
        if (is_array($viewParticipants) && !empty($viewParticipants)) { // Asegurarse de que es un array y no está vacío
            echo "<ul>";
            foreach ($viewParticipants as $participant) { // Cambiado $participantEmail a $participant
                // Asumir que $participant es un array y tiene una clave 'email'
                if (is_array($participant) && isset($participant['email'])) {
                    echo "<li>" . htmlspecialchars($participant['email']) . "</li>";
                } elseif (is_string($participant)) {
                    // Fallback por si acaso la estructura fuera un array de strings (emails)
                    // Aunque la lógica del controlador espera un array de arrays.
                    echo "<li>" . htmlspecialchars($participant) . "</li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p>Aún no hay participantes.</p>";
        }

        // Botones condicionales para gestionar el plan
        if ($viewIsCreator) {
            echo "<div class='plan-actions'>";
            // Modificar este formulario para incluir los parámetros como campos ocultos
            echo "<form action='index.php' method='get' style='display: inline;'>";
            echo "<input type='hidden' name='action' value='edit_plan'>";
            echo "<input type='hidden' name='id' value='" . htmlspecialchars($viewPlan['id']) . "'>";
            echo "<button type='submit'>Modificar Plan</button>";
            echo "</form>";
            
            echo "<form action='index.php?action=delete_plan&id=" . htmlspecialchars($viewPlan['id']) . "' method='post' style='display: inline;' onsubmit='return confirm(\"¿Estás seguro de que quieres eliminar este plan?\");'>";
            echo "<button type='submit'>Eliminar Plan</button>";
            echo "</form>";
            echo "</div>";
        } elseif ($viewHasJoined) {
            echo "<div class='plan-actions'>";
            echo "<form action='index.php?action=leave_plan' method='post'>";
            echo "<input type='hidden' name='plan_id' value='" . htmlspecialchars($viewPlan['id']) . "'>";
            echo "<button type='submit'>Cancelar mi asistencia</button>";
            echo "</form>";
            echo "</div>";
        } elseif (!$viewIsFull) {
            echo "<div class='plan-actions'>";
            echo "<form action='index.php?action=join_plan' method='post'>";
            echo "<input type='hidden' name='plan_id' value='" . htmlspecialchars($viewPlan['id']) . "'>";
            echo "<button type='submit'>Unirme al Plan</button>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "<p>Este plan está completo.</p>";
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