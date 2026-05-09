<?php
// GET /api/saldo.php - Obtener saldo del usuario desde la BD
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/db.php';

$usuario_documento = $_GET['usuario_documento'] ?? '1094899647';

$stmt = $pdo->prepare("SELECT saldo, nombre FROM usuarios WHERE documento = ?");
$stmt->execute([$usuario_documento]);
$usuario = $stmt->fetch();

if (!$usuario) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuario no encontrado']);
    exit;
}

echo json_encode([
    'saldo'  => $usuario['saldo'],
    'nombre' => $usuario['nombre']
]);
?>
