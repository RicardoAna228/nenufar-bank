<?php
// GET /api/saldo.php - Obtener saldo de la API bancaria
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$api_base_url = 'http://localhost:8083';

$ch = curl_init($api_base_url . '/saldo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => ['Accept: application/json']
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $http_code !== 200) {
    http_response_code(502);
    echo json_encode(['saldo' => 0, 'error' => 'No se pudo conectar con la API bancaria']);
    exit;
}

echo $response;
?>