<?php
// GET /api/productos.php - Obtener catálogo de productos
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../config/db.php';

try {
    // Ahora hacemos JOIN con categorias para obtener el nombre
    $stmt = $pdo->query("
        SELECT 
            p.id, 
            p.nombre, 
            p.descripcion, 
            p.precio, 
            p.imagen,
            p.id_categoria,
            c.nombre AS categoria_nombre,
            p.activo
        FROM productos p
        JOIN categorias c ON p.id_categoria = c.id
        WHERE p.activo = 1
    ");
    
    $productos = $stmt->fetchAll();
    
    // Para mantener compatibilidad con el frontend, mapeamos campo categoría
    // y determinamos si otorga tamalbits (regla de negocio: orejas de pollo)
    // También asignamos emoji según categoría (para el frontend)
    $emojis_por_categoria = [
        'Comida' => '🍔',
        'Tecnologia' => '🎧',
        'Hogar' => '💡',
        'Ropa' => '👕'
    ];
    
    $resultado = array_map(function($p) use ($emojis_por_categoria) {
        $nombre_lower = strtolower($p['nombre']);
        return [
            'id' => $p['id'],
            'nombre' => $p['nombre'],
            'precio' => $p['precio'],
            'categoria' => strtolower($p['categoria_nombre']), // Para filtros
            'categoria_nombre' => $p['categoria_nombre'],       // Nombre original
            'descripcion' => $p['descripcion'] ?? '',
            'imagen' => $p['imagen'] ?? '',
            'emoji' => $emojis_por_categoria[$p['categoria_nombre']] ?? '📦',
            // Regla de negocio: si el nombre contiene "oreja", otorga tamalbits
            'otorga_tamalbit' => (strpos($nombre_lower, 'oreja') !== false) ? 1 : 0,
            'activo' => $p['activo']
        ];
    }, $productos);
    
    echo json_encode($resultado);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar productos: ' . $e->getMessage()]);
}
?>