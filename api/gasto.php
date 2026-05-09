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

$producto_id       = $input['producto_id'] ?? 0;
$usuario_documento = trim($input['usuario_documento'] ?? '1094899647');
$descripcion       = isset($input['descripcion']) ? trim($input['descripcion']) : null;

// Verificar que el usuario existe y traer su saldo
$stmt = $pdo->prepare("SELECT documento, nombre, saldo FROM usuarios WHERE documento = ?");
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

// Verificar saldo directamente desde la BD (ya no necesitamos el puerto 8083)
$saldo_disponible = $usuario['saldo'];

if ($saldo_disponible < $producto['precio']) {
    echo json_encode([
        'success' => false,
        'message' => 'Saldo insuficiente. Disponible: $' . number_format($saldo_disponible, 2)
    ]);
    exit;
}

// Registrar en base de datos y descontar saldo en una sola transacción
try {
    $pdo->beginTransaction();

    // Calcular tamalbits (regla: si el nombre del producto contiene "oreja")
    $tamalbits_ganados = 0;
    if (strpos(strtolower($producto['nombre']), 'oreja') !== false) {
        $tamalbits_ganados = floor($producto['precio'] / 10);
    }

    // Insertar gasto
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

    // Descontar saldo del usuario
    $stmt = $pdo->prepare("UPDATE usuarios SET saldo = saldo - ? WHERE documento = ?");
    $stmt->execute([$producto['precio'], $usuario_documento]);

    // Sumar tamalbits si aplica
    if ($tamalbits_ganados > 0) {
        $stmt = $pdo->prepare("UPDATE usuarios SET tamalbits = tamalbits + ? WHERE documento = ?");
        $stmt->execute([$tamalbits_ganados, $usuario_documento]);
    }

    $pdo->commit();

    echo json_encode([
        'success'     => true,
        'message'     => 'Gasto registrado exitosamente',
        'gasto_id'    => $gasto_id,
        'tamalbits'   => $tamalbits_ganados,
        'nuevo_saldo' => $saldo_disponible - $producto['precio'],
        'usuario'     => $usuario['nombre']
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al registrar en base de datos: ' . $e->getMessage()]);
}
?>
