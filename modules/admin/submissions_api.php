<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}
require_once '../../Include/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {

        case 'list':
            $year   = (int)($_POST['year']   ?? 0);
            $status = $_POST['status'] ?? '';

            $where = []; $params = [];
            if ($year)   { $where[] = 's.year = ?';   $params[] = $year; }
            if ($status) { $where[] = 's.status = ?'; $params[] = $status; }
            $wSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            $stmt = $pdo->prepare(
                "SELECT s.submission_id, s.year, s.submitted_at, s.status, s.pdf_path, s.notes,
                        u.user_id, u.first_name, u.last_name, u.email, u.role
                 FROM submissions s
                 JOIN users u ON s.user_id = u.user_id
                 $wSql
                 ORDER BY s.submitted_at DESC"
            );
            $stmt->execute($params);
            echo json_encode(['success' => true, 'submissions' => $stmt->fetchAll()]);
            break;

        case 'non_submitters':
            $year = (int)($_POST['year'] ?? date('Y'));
            $stmt = $pdo->prepare(
                "SELECT u.user_id, u.first_name, u.last_name, u.email, p.position_name
                 FROM users u
                 LEFT JOIN positions p ON u.position_id = p.position_id
                 WHERE u.role = 'Politician' 
                 AND u.user_id NOT IN (
                     SELECT user_id FROM submissions WHERE year = ?
                 )
                 ORDER BY u.last_name"
            );
            $stmt->execute([$year]);
            echo json_encode(['success' => true, 'non_submitters' => $stmt->fetchAll()]);
            break;

        case 'update_status':
            $id     = (int)($_POST['submission_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $notes  = trim($_POST['notes'] ?? '');

            if (!$id || !in_array($status, ['Approved','Rejected'])) {
                echo json_encode(['success' => false, 'error' => 'Μη έγκυρα δεδομένα']);
                break;
            }
            $stmt = $pdo->prepare("UPDATE submissions SET status=?, notes=? WHERE submission_id=? AND status='Pending'");
            $stmt->execute([$status, $notes, $id]);
            if ($stmt->rowCount() === 0) {
                echo json_encode(['success' => false, 'error' => 'This submission has already been handled.']);
                break;
            }
            echo json_encode(['success' => true, 'message' => 'Η κατάσταση ενημερώθηκε']);
            break;

        case 'delete':
            $id = (int)($_POST['submission_id'] ?? 0);
            if (!$id) { echo json_encode(['success' => false, 'error' => 'Μη έγκυρη υποβολή']); break; }
            $pdo->prepare("DELETE FROM submissions WHERE submission_id=?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Η υποβολή διαγράφηκε']);
            break;

        case 'years':
            $stmt = $pdo->query("SELECT DISTINCT year FROM submissions ORDER BY year DESC");
            echo json_encode(['success' => true, 'years' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Μη έγκυρη ενέργεια']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Σφάλμα βάσης δεδομένων: ' . $e->getMessage()]);
}
