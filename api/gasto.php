<?php
// POST /api/gasto.php - Registrar un gasto
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Validar datos
$producto_id = $input['producto_id'] ?? 0;
$usuario_documento = trim($input['usuario_documento'] ?? '1094899647'); // Documento por defecto
$descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : null;

// Verificar que el usuario existe
$stmt = $pdo->prepare("SELECT documento, nombre FROM usuarios WHERE documento = ?");
$stmt->execute([$usuario_documento]);
$usuario = $stmt->fetch();

if (!$usuario) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado. Use documento 1094899647 (Nicol Ocampo)']);
    exit;
}

// Obtener producto de la BD con su categoría
$stmt = $pdo->prepare("
    SELECT p.*, c.nombre AS categoria_nombre 
    FROM productos p 
    JOIN categorias c ON p.id_categoria = c.id 
    WHERE p.id = ? AND p.activo = 1
");
$stmt->execute([$producto_id]);
$producto = $stmt->fetch();

if (!$producto) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado o inactivo']);
    exit;
}

// Verificar saldo vía API antes de proceder
$api_base = 'http://localhost:8083';
$ch = curl_init($api_base . '/saldo');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$saldo_response = curl_exec($ch);
curl_close($ch);
$saldo_data = json_decode($saldo_response, true);
$saldo_disponible = $saldo_data['saldo'] ?? 0;

if ($saldo_disponible < $producto['precio']) {
    echo json_encode([
        'success' => false, 
        'message' => 'Saldo insuficiente. Disponible: $' . number_format($saldo_disponible, 2)
    ]);
    exit;
}

// Llamar a la API para descontar
$ch = curl_init($api_base . '/gasto');
curl_setopt_array($ch, [
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => json_encode(['monto' => $producto['precio']]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 10
]);
$api_response = curl_exec($ch);
$api_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($api_response === false || $api_http_code !== 200) {
    echo json_encode(['success' => false, 'message' => 'La API bancaria rechazó la operación']);
    exit;
}

// Registrar en base de datos
try {
    $pdo->beginTransaction();
    
    // Calcular tamalbits si el producto los otorga
    $tamalbits_ganados = 0;
    $nombre_lower = strtolower($producto['nombre']);
    if (strpos($nombre_lower, 'oreja') !== false) {
        $tamalbits_ganados = floor($producto['precio'] / 10);
    }
    
    // Insertar gasto (usando tu schema)
    $stmt = $pdo->prepare("
        INSERT INTO gastos (id_usuario, id_producto, monto, descripcion, tamalbits_ganados) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $usuario_documento, 
        $producto['id'], 
        $producto['precio'], 
        $descripcion, 
        $tamalbits_ganados
    ]);
    $gasto_id = $pdo->lastInsertId();
    
    // Actualizar tamalbits del usuario en tabla usuarios
    if ($tamalbits_ganados > 0) {
        $stmt = $pdo->prepare("UPDATE usuarios SET tamalbits = tamalbits + ? WHERE documento = ?");
        $stmt->execute([$tamalbits_ganados, $usuario_documento]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Gasto registrado exitosamente',
        'gasto_id' => $gasto_id,
        'tamalbits' => $tamalbits_ganados,
        'nuevo_saldo' => $saldo_disponible - $producto['precio'],
        'usuario' => $usuario['nombre']
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al registrar en base de datos: ' . $e->getMessage()]);
}
?>