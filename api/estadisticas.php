<?php
// GET /api/estadisticas.php?usuario_documento=1094899647
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/db.php';

$usuario_documento = $_GET['usuario_documento'] ?? '1094899647';
$mes = date('m');
$anio = date('Y');

// Total gastado y transacciones del mes
$stmt = $pdo->prepare("
    SELECT 
        COALESCE(SUM(g.monto), 0) AS total, 
        COUNT(*) AS transacciones 
    FROM gastos g
    WHERE g.id_usuario = ? 
      AND MONTH(g.fecha) = ? 
      AND YEAR(g.fecha) = ?
");
$stmt->execute([$usuario_documento, $mes, $anio]);
$resumen = $stmt->fetch();

// Gasto más alto del mes
$stmt = $pdo->prepare("
    SELECT g.monto, p.nombre AS producto_nombre
    FROM gastos g 
    JOIN productos p ON g.id_producto = p.id 
    WHERE g.id_usuario = ? 
      AND MONTH(g.fecha) = ? 
      AND YEAR(g.fecha) = ? 
    ORDER BY g.monto DESC 
    LIMIT 1
");
$stmt->execute([$usuario_documento, $mes, $anio]);
$mayor_gasto = $stmt->fetch();

// Total por categoría
$stmt = $pdo->prepare("
    SELECT 
        c.nombre AS categoria, 
        COALESCE(SUM(g.monto), 0) AS total
    FROM categorias c
    LEFT JOIN productos p ON c.id = p.id_categoria
    LEFT JOIN gastos g ON p.id = g.id_producto 
        AND g.id_usuario = ? 
        AND MONTH(g.fecha) = ? 
        AND YEAR(g.fecha) = ?
    GROUP BY c.id, c.nombre
");
$stmt->execute([$usuario_documento, $mes, $anio]);
$categorias_rows = $stmt->fetchAll();

$totales_categoria = [];
foreach ($categorias_rows as $row) {
    $totales_categoria[$row['categoria']] = $row['total'];
}

echo json_encode([
    'total_mes' => $resumen['total'],
    'transacciones' => $resumen['transacciones'],
    'promedio' => $resumen['transacciones'] > 0 ? $resumen['total'] / $resumen['transacciones'] : 0,
    'mayor_gasto' => $mayor_gasto ? [
        'monto' => $mayor_gasto['monto'],
        'nombre' => $mayor_gasto['producto_nombre']
    ] : null,
    'categorias' => $totales_categoria
]);
?>