<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Cambia esto por tu usuario de BD
define('DB_PASS', ''); // Cambia esto por tu contraseña de BD
define('DB_NAME', 'compis_app'); // Cambia esto por el nombre de tu BD

// Intenta crear una conexión PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa"; // Descomentar para probar la conexión
} catch (PDOException $e) {
    die("ERROR: No se pudo conectar a la base de datos. " . $e->getMessage());
}

// No es necesario devolver $pdo aquí directamente si este archivo solo define constantes
// y se incluye donde se necesite.
// Si prefieres una función que devuelva la conexión:
/*
function getPDOConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("ERROR: No se pudo conectar a la base de datos. " . $e->getMessage());
        }
    }
    return $pdo;
}
*/
?>