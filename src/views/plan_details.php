<?php
include_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../src/lib/helpers.php'; // Para mensajes

// Las variables $viewPlan, $viewParticipants, etc., son definidas y pasadas por PlanController.php usando 'global'
global $viewPlan, $viewParticipants, $viewIsLoggedIn, $viewUserId, $viewIsCreator, $viewHasJoined, $viewIsFull, $viewParticipantCount;

$message = get_session_message();
if ($message) {
    echo "<p class='message " . htmlspecialchars($message['type']) . "'>" . htmlspecialchars($message['text']) . "</p>";
}

echo "<h2 class='page-title'>Detalles del Plan</h2>";

if ($viewPlan) {
    echo "<div class='card'>";
    echo "<h3 class='card-title'>" . htmlspecialchars($viewPlan['title']) . "</h3>";
    echo "<div class='card-content'>";
    echo "<p><strong>Descripción:</strong> " . nl2br(htmlspecialchars($viewPlan['description'])) . "</p>";
    $formattedDate = date('d/m/Y H:i', strtotime($viewPlan['plan_date']));
    echo "<p><strong>Fecha:</strong> " . htmlspecialchars($formattedDate) . "</p>";
    echo "<p><strong>Lugar:</strong> " . htmlspecialchars($viewPlan['location']) . "</p>";
    echo "<p><strong>Capacidad Máxima:</strong> " . htmlspecialchars($viewPlan['max_capacity']) . " personas</p>";

    if ($viewIsLoggedIn) {
        echo "<p><strong>Creado por:</strong> " . htmlspecialchars($viewPlan['creator_email']) . "</p>";
        
        echo "<h4>Participantes (" . htmlspecialchars($viewParticipantCount) . "/" . htmlspecialchars($viewPlan['max_capacity']) . "):</h4>";
        if (is_array($viewParticipants) && !empty($viewParticipants)) { // Asegurarse de que es un array y no está vacío
            echo "<ul class='participants-list'>";
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

        // Botones condicionales para gestionar el plan con estilos modernos
        if ($viewIsCreator) {
            echo "<div class='plan-actions'>";
            // Botón para modificar el plan con la clase btn-primary
            echo "<form action='index.php' method='get' style='display: inline;'>";
            echo "<input type='hidden' name='action' value='edit_plan'>";
            echo "<input type='hidden' name='id' value='" . htmlspecialchars($viewPlan['id']) . "'>";
            echo "<button type='submit' class='btn btn-primary'>Modificar Plan</button>";
            echo "</form>";
            
            // Botón para eliminar el plan con la clase btn-danger
            echo "<form action='index.php?action=delete_plan&id=" . htmlspecialchars($viewPlan['id']) . "' method='post' style='display: inline;' onsubmit='return confirm(\"¿Estás seguro de que quieres eliminar este plan?\");'>";
            echo "<button type='submit' class='btn btn-danger'>Eliminar Plan</button>";
            echo "</form>";
            echo "</div>";
        } elseif ($viewHasJoined) {
            echo "<div class='plan-actions'>";
            // Botón para cancelar la asistencia con la clase btn-warning
            echo "<form action='index.php?action=leave_plan' method='post'>";
            echo "<input type='hidden' name='plan_id' value='" . htmlspecialchars($viewPlan['id']) . "'>";
            echo "<button type='submit' class='btn btn-warning'>Cancelar mi asistencia</button>";
            echo "</form>";
            echo "</div>";
        } elseif (!$viewIsFull) {
            echo "<div class='plan-actions'>";
            // Botón para unirse al plan con la clase btn-success
            echo "<form action='index.php?action=join_plan' method='post'>";
            echo "<input type='hidden' name='plan_id' value='" . htmlspecialchars($viewPlan['id']) . "'>";
            echo "<button type='submit' class='btn btn-success'>Unirme al Plan</button>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "<p class='message info'>Este plan está completo.</p>";
        }

    } else {
        // Reemplazar el enlace por un botón
        echo "<div class='plan-actions'>";
        echo "<a href='index.php?action=login' class='btn btn-primary'>Iniciar sesión</a>";
        echo "<span class='ml-2'>para ver la lista de participantes y apuntarte.</span>";
        echo "</div>";
    }
    echo "</div>"; // Cierre de card-content
    echo "</div>"; // Cierre de card

} else {
    // Este mensaje ya se maneja con redirección en el controlador si el plan no se encuentra.
    // Pero por si acaso se accede a la vista directamente sin $viewPlan:
    echo "<p class='message error'>Plan no encontrado o no se ha especificado un ID de plan.</p>";
}

// Reemplazar el enlace para volver por un botón
echo "<div class='plan-actions' style='margin-top: 20px;'>";
echo "<a href='index.php?action=home' class='btn btn-outline'>Volver a la lista de planes</a>";
echo "</div>";

include_once __DIR__ . '/../../templates/footer.php';
?>