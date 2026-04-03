<?php
session_start();
header('Content-Type: application/json');
require_once '../Include/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['htmlContent'])) {

    $html = $_POST['htmlContent'];

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // Useful if you load external assets
    $options->set('defaultFont', 'DejaVu Sans'); // Set default to a Unicode font

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);

    // Optional: set paper size
    $dompdf->setPaper('A4', 'portrait');

    // Render PDF
    $dompdf->render();

    // Prepare user folder
    $baseDir = __DIR__ . '/../database/users_declarations/';
    $userDir = $baseDir . 'user_' . $userId . '/';
    if (!file_exists($userDir)) mkdir($userDir, 0777, true);

    // Save PDF
    $filename = 'declaration_' . $userId . '_' . date('Ymd_His') . '.pdf';
    $filepath = $userDir . $filename;

    file_put_contents($filepath, $dompdf->output());

    echo json_encode(['status' => 'success', 'path' => $filepath]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No HTML content received']);
}