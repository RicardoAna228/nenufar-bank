<?php
// GET /api/historial.php?usuario_documento=1094899647&busqueda=orejas
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/db.php';

$usuario_documento = $_GET['usuario_documento'] ?? '1094899647';
$busqueda = $_GET['busqueda'] ?? '';

// Consulta base con JOINs según tu schema
$sql = "SELECT 
            g.id, 
            g.fecha, 
            g.monto, 
            g.descripcion, 
            g.tamalbits_ganados,
            p.nombre AS producto_nombre, 
            p.imagen,
            p.precio AS producto_precio,
            c.nombre AS categoria_nombre,
            u.nombre AS usuario_nombre
        FROM gastos g
        JOIN productos p ON g.id_producto = p.id
        JOIN categorias c ON p.id_categoria = c.id
        JOIN usuarios u ON g.id_usuario = u.documento
        WHERE g.id_usuario = ?";
$params = [$usuario_documento];

if ($busqueda) {
    $sql .= " AND (p.nombre LIKE ? OR g.descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

$sql .= " ORDER BY g.fecha DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$gastos = $stmt->fetchAll();

// Mapear emoji según categoría para el frontend
$emojis_por_categoria = [
    'Comida' => '🍔',
    'Tecnologia' => '🎧',
    'Hogar' => '💡',
    'Ropa' => '👕'
];

$resultado = array_map(function($g) use ($emojis_por_categoria) {
    return [
        'id' => $g['id'],
        'fecha' => $g['fecha'],
        'monto' => $g['monto'],
        'descripcion' => $g['descripcion'],
        'nombre' => $g['producto_nombre'],
        'categoria' => strtolower($g['categoria_nombre']),
        'categoria_nombre' => $g['categoria_nombre'],
        'emoji' => $emojis_por_categoria[$g['categoria_nombre']] ?? '📦',
        'tamalbits_ganados' => $g['tamalbits_ganados'],
        'usuario_nombre' => $g['usuario_nombre'],
        'imagen' => $g['imagen']
    ];
}, $gastos);

echo json_encode($resultado);
?>