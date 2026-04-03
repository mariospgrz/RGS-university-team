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

        case 'list_positions':
            $stmt = $pdo->query(
                "SELECT p.position_id, p.position_name, COUNT(g.user_id) AS officer_count
                 FROM positions p
                 LEFT JOIN govOfficers g ON p.position_id = g.officer_position
                 GROUP BY p.position_id, p.position_name
                 ORDER BY p.position_id"
            );
            echo json_encode(['success' => true, 'positions' => $stmt->fetchAll()]);
            break;

        case 'add_position':
            $name = trim($_POST['position_name'] ?? '');
            if (!$name) { echo json_encode(['success' => false, 'error' => 'Συμπληρώστε όνομα θέσης']); break; }

            $nextId = $pdo->query("SELECT COALESCE(MAX(position_id),0)+1 FROM positions")->fetchColumn();
            $pdo->prepare("INSERT INTO positions (position_id, position_name) VALUES (?,?)")
                ->execute([$nextId, $name]);

            echo json_encode(['success' => true, 'message' => 'Η θέση προστέθηκε επιτυχώς', 'position_id' => $nextId]);
            break;

        case 'delete_position':
            $id = (int)($_POST['position_id'] ?? 0);
            if (!$id) { echo json_encode(['success' => false, 'error' => 'Μη έγκυρη θέση']); break; }

            $inUse = $pdo->prepare("SELECT COUNT(*) FROM govOfficers WHERE officer_position=?");
            $inUse->execute([$id]);
            if ($inUse->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'error' => 'Δεν μπορείτε να διαγράψετε θέση που χρησιμοποιείται από αξιωματούχο']);
                break;
            }
            $pdo->prepare("DELETE FROM positions WHERE position_id=?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Η θέση διαγράφηκε επιτυχώς']);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Μη έγκυρη ενέργεια']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Σφάλμα βάσης δεδομένων']);
}
