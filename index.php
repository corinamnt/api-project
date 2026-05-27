<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');

require_once 'config/database.php';
require_once 'controllers/ProductController.php';
require_once 'controllers/OrderController.php';

$database = new Database();
$db = $database->connect();

$productController = new ProductController($db);
$orderController = new OrderController($db);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = trim($requestUri, '/');

$segments = explode('/', $requestUri);
$segments = array_values(array_filter($segments, 'strlen'));

if (isset($segments[0]) && $segments[0] === 'softprim-api') {
    array_shift($segments);
}

if (!isset($segments[0])) {
    http_response_code(404);
    echo json_encode([
        'error' => 'No route'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($segments[0] === 'api' && ($segments[1] ?? null) === 'products') {

    if ($method === 'GET' && !isset($segments[2])) {
        $productController->getProducts();
        exit;
    }

    if ($method === 'GET' && isset($segments[2])) {
        $productController->getProductById($segments[2]);
        exit;
    }
}

if ($segments[0] === 'api' && ($segments[1] ?? null) === 'orders') {

    if ($method === 'POST') {
        $orderController->createOrder();
        exit;
    }
}

http_response_code(404);

echo json_encode([
    'error' => 'Route not found'
], JSON_UNESCAPED_UNICODE);