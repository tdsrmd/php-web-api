<?php
// src/routes/web.php

use App\Controllers\UserController;
use App\Controllers\FileController;
use App\Controllers\AdminController;

$router = new AltoRouter();

$pdo = require __DIR__ . '/../config/database.php';
$userController = new UserController($pdo);
$fileController = new FileController($pdo);
$adminController = new AdminController($pdo);

require_once __DIR__ . '/../helpers/jwt_helper.php';

// CORS başlıklarını ayarlama
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$router->map('POST', '/register', function() use ($userController) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $response = $userController->register($data);
    echo json_encode($response);
});

$router->map('POST', '/login', function() use ($userController) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $response = $userController->login($data);
    echo json_encode($response);
});

$router->map('POST', '/upload', function() use ($fileController) {
    header('Content-Type: application/json');
    $jwt = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $jwt = str_replace('Bearer ', '', $jwt);
    $response = $fileController->upload($_FILES, $jwt);
    echo json_encode($response);
});

$router->map('GET', '/user/files', function() use ($fileController) {
    header('Content-Type: application/json');
    $jwt = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $jwt = str_replace('Bearer ', '', $jwt);
    $response = $fileController->getUserFiles($jwt);
    echo json_encode($response);
});

$router->map('GET', '/download/[i:userId]/[*:fileName]', function($userId, $fileName) use ($fileController) {
    $fileController->downloadFile($userId, $fileName . ".pdf");
});


$router->map('GET', '/admin/files', function() use ($adminController) {
    header('Content-Type: application/json');
    $response = $adminController->listAllFiles();
    echo json_encode($response);
});

$router->map('POST', '/admin/status', function() use ($adminController) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $fileId = $data['file_id'];
    $status = $data['status'];
    $response = $adminController->updateFileStatus($fileId, $status);
    echo json_encode($response);
});

$match = $router->match();

if ($match && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    header('Content-Type: application/json');
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    echo json_encode(['message' => 'Not Found']);
}
?>
