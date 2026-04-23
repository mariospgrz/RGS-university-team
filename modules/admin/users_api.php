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
            $stmt = $pdo->query(
                "SELECT u.user_id, u.first_name, u.last_name, u.email, u.Phone, u.role, u.position_id, u.party_id, a.username, p.position_name, pt.party_name
                 FROM users u 
                 LEFT JOIN accounts a ON u.user_id = a.user_id
                 LEFT JOIN positions p ON u.position_id = p.position_id
                 LEFT JOIN parties pt ON u.party_id = pt.party_id
                 ORDER BY u.user_id"
            );
            echo json_encode(['success' => true, 'users' => $stmt->fetchAll()]);
            break;

        case 'add':
            $fn  = trim($_POST['first_name'] ?? '');
            $ln  = trim($_POST['last_name']  ?? '');
            $em  = trim($_POST['email']      ?? '');
            $ph  = trim($_POST['phone']      ?? '');
            $rl  = $_POST['role']            ?? 'User';
            $un  = trim($_POST['username']   ?? '');
            $pw  = $_POST['password']        ?? '';
            $pos = (int)($_POST['officer_position'] ?? 999);
            $par = !empty($_POST['party_id']) ? (int)$_POST['party_id'] : null;

            if (!$fn || !$ln || !$em || !$un || !$pw) {
                echo json_encode(['success' => false, 'error' => 'Συμπληρώστε όλα τα υποχρεωτικά πεδία']);
                break;
            }
            
            $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, Phone, role, position_id, party_id) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$fn, $ln, $em, $ph, $rl, $pos, $par]);
            
            $newUserId = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO accounts (user_id, username, password_hash) VALUES (?,?,?)")
                ->execute([$newUserId, $un, $hashed_pw]);
            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Ο χρήστης προστέθηκε επιτυχώς']);
            break;

        case 'edit':
            $id  = (int)($_POST['user_id']   ?? 0);
            $fn  = trim($_POST['first_name']  ?? '');
            $ln  = trim($_POST['last_name']   ?? '');
            $em  = trim($_POST['email']       ?? '');
            $ph  = trim($_POST['phone']       ?? '');
            $rl  = $_POST['role']             ?? 'User';
            $un  = trim($_POST['username']    ?? '');
            $pw  = $_POST['password']         ?? '';
            $pos = (int)($_POST['officer_position'] ?? 999);
            $par = !empty($_POST['party_id']) ? (int)$_POST['party_id'] : null;

            if (!$id || !$fn || !$ln || !$em || !$un) {
                echo json_encode(['success' => false, 'error' => 'Συμπληρώστε όλα τα υποχρεωτικά πεδία']);
                break;
            }

            $pdo->beginTransaction();
            $pdo->prepare("UPDATE users SET first_name=?, last_name=?, email=?, Phone=?, role=?, position_id=?, party_id=? WHERE user_id=?")
                ->execute([$fn, $ln, $em, $ph, $rl, $pos, $par, $id]);
            
            if ($pw) {
                $hashed_pw = password_hash($pw, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE accounts SET username=?, password_hash=? WHERE user_id=?")
                    ->execute([$un, $hashed_pw, $id]);
            } else {
                $pdo->prepare("UPDATE accounts SET username=? WHERE user_id=?")
                    ->execute([$un, $id]);
            }
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Ο χρήστης ενημερώθηκε επιτυχώς']);
            break;

        case 'delete':
            $id = (int)($_POST['user_id'] ?? 0);
            if (!$id) { echo json_encode(['success' => false, 'error' => 'Μη έγκυρος χρήστης']); break; }

            $pdo->prepare("DELETE FROM users WHERE user_id=?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Ο χρήστης διαγράφηκε επιτυχώς']);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Μη έγκυρη ενέργεια']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Σφάλμα βάσης δεδομένων: ' . $e->getMessage()]);
}
