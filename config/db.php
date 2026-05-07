<?php
// Nenúfar Bank - Configuración de conexión a la base de datos

define('DB_HOST', 'localhost'); 
define('DB_NAME', 'nenufar_bank');
define('DB_USER', 'root'); // Cambia por tu usuario
define('DB_PASS', '');     // Cambia por tu contraseña

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // En producción, registra el error en un log, no lo muestres al usuario.
    die("Error de conexión a la base de datos. Por favor, contacta al administrador.");
}

// Configuración de la API externa (Bank Service)
define('API_BASE_URL', 'http://localhost:8083'); // Ajusta si es necesario
?>