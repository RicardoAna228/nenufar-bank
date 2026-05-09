<?php
// Nenúfar Bank - Configuración de base de datos
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'nenufar_bank');
define('DB_USER', 'nenufar');      // Cambia si usas otro usuario
define('DB_PASS', '');          // Cambia si tienes contraseña

try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=3307;dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
?>