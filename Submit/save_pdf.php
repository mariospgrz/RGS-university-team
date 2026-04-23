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

require_once '../Include/db.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['htmlContent'])) {

    try {
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
        $publicPath = '../database/users_declarations/user_' . $userId . '/' . $filename;

        if (file_put_contents($filepath, $dompdf->output()) === false) {
            throw new RuntimeException('Could not write PDF file');
        }

        $stmt = $pdo->prepare(
            "INSERT INTO submissions (user_id, year, pdf_path) VALUES (:user_id, :year, :pdf_path)"
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':year' => (int)date('Y'),
            ':pdf_path' => $publicPath,
        ]);

        echo json_encode([
            'status' => 'success',
            'submission_id' => $pdo->lastInsertId(),
            'path' => $publicPath,
        ]);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Could not save PDF submission']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No HTML content received']);
}
