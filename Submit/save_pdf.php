<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['first_name'] . '_' . $_SESSION['last_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['htmlContent'])) {
    $html = $_POST['htmlContent'];

    $baseDir = __DIR__ . '/../database/users_declarations/';
    $userDir = $baseDir . 'user_' . $userId . '/';

    if (!file_exists($userDir)) {
        mkdir($userDir, 0777, true);
    }

    $filename = 'declaration_' . $userId . '_' . date('Ymd_His') . '.html';
    $filepath = $userDir . $filename;

    file_put_contents($filepath, $html);

    echo json_encode(['status' => 'success', 'path' => $filepath]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No HTML content received']);
}