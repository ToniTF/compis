<?php
// src/lib/helpers.php

// Este archivo puede contener funciones de ayuda generales para la aplicación.

if (!function_exists('redirect')) {
    /**
     * Redirige a otra URL.
     *
     * @param string $url La URL a la que redirigir.
     * @return void
     */
    function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('get_session_message')) {
    /**
     * Obtiene y limpia un mensaje flash de la sesión.
     *
     * @return array|null Un array con 'text' y 'type' del mensaje, o null si no hay mensaje.
     */
    function get_session_message(): ?array
    {
        if (isset($_SESSION['message'])) {
            $message = [
                'text' => $_SESSION['message'],
                'type' => $_SESSION['message_type'] ?? 'info'
            ];
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            return $message;
        }
        return null;
    }
}

if (!function_exists('set_session_message')) {
    /**
     * Establece un mensaje flash en la sesión.
     *
     * @param string $text El texto del mensaje.
     * @param string $type El tipo de mensaje (e.g., 'success', 'error', 'info').
     * @return void
     */
    function set_session_message(string $text, string $type = 'info'): void
    {
        $_SESSION['message'] = $text;
        $_SESSION['message_type'] = $type;
    }
}

// Puedes añadir más funciones de ayuda aquí según las necesites.
// por ejemplo, para sanitizar inputs, formatear fechas, etc.

?>