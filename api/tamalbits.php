<?php
// GET /api/tamalbits.php?usuario_documento=1094899647
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/db.php';

$usuario_documento = $_GET['usuario_documento'] ?? '1094899647';

// Verificar que el usuario existe y traer su nombre
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE documento = ?");
$stmt->execute([$usuario_documento]);
$usuario = $stmt->fetch();

if (!$usuario) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuario no encontrado']);
    exit;
}

// Total de tamalbits ganados por el usuario según gastos reales
$stmt = $pdo->prepare("SELECT COALESCE(SUM(tamalbits_ganados), 0) AS total FROM gastos WHERE id_usuario = ?");
$stmt->execute([$usuario_documento]);
$totalTamalbits = (int) $stmt->fetchColumn();

// Historial de tamalbits ganados (de la tabla gastos)
$stmt = $pdo->prepare("
    SELECT 
        g.tamalbits_ganados AS cantidad,
        g.fecha,
        g.monto,
        p.nombre AS producto_nombre,
        p.imagen,
        c.nombre AS categoria_nombre
    FROM gastos g
    JOIN productos p ON g.id_producto = p.id
    JOIN categorias c ON p.id_categoria = c.id
    WHERE g.id_usuario = ? AND g.tamalbits_ganados > 0
    ORDER BY g.fecha DESC
");
$stmt->execute([$usuario_documento]);
$historial = $stmt->fetchAll();

// Mapear emoji según categoría
$emojis_por_categoria = [
    'Comida' => '🍔',
    'Tecnologia' => '🎧',
    'Hogar' => '💡',
    'Ropa' => '👕'
];

$historial_resultado = array_map(function($h) use ($emojis_por_categoria) {
    return [
        'cantidad' => $h['cantidad'],
        'fecha' => $h['fecha'],
        'monto' => $h['monto'],
        'nombre' => $h['producto_nombre'],
        'emoji' => $emojis_por_categoria[$h['categoria_nombre']] ?? '📦',
        'categoria' => $h['categoria_nombre']
    ];
}, $historial);

echo json_encode([
    'total' => $totalTamalbits,
    'nombre' => $usuario['nombre'],
    'historial' => $historial_resultado
]);
?>