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
                "SELECT u.user_id, u.first_name, u.last_name, u.email, u.Phone, u.role, a.username
                 FROM users u LEFT JOIN accounts a ON u.user_id = a.user_id
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

            if (!$fn || !$ln || !$em || !$un || !$pw) {
                echo json_encode(['success' => false, 'error' => 'Συμπληρώστε όλα τα υποχρεωτικά πεδία']);
                break;
            }
            $nextId = $pdo->query("SELECT COALESCE(MAX(user_id),0)+1 FROM users")->fetchColumn();

            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO users (user_id,first_name,last_name,email,Phone,role) VALUES (?,?,?,?,?,?)")
                ->execute([$nextId, $fn, $ln, $em, $ph, $rl]);
            $pdo->prepare("INSERT INTO accounts (user_id,username,password_hash) VALUES (?,?,?)")
                ->execute([$nextId, $un, $pw]);
            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Ο χρήστης προστέθηκε επιτυχώς']);
            break;

        case 'edit':
            $id = (int)($_POST['user_id']    ?? 0);
            $fn = trim($_POST['first_name']  ?? '');
            $ln = trim($_POST['last_name']   ?? '');
            $em = trim($_POST['email']       ?? '');
            $ph = trim($_POST['phone']       ?? '');
            $rl = $_POST['role']             ?? 'User';
            $un = trim($_POST['username']    ?? '');
            $pw = $_POST['password']         ?? '';

            if (!$id || !$fn || !$ln || !$em || !$un) {
                echo json_encode(['success' => false, 'error' => 'Συμπληρώστε όλα τα υποχρεωτικά πεδία']);
                break;
            }
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE users SET first_name=?,last_name=?,email=?,Phone=?,role=? WHERE user_id=?")
                ->execute([$fn, $ln, $em, $ph, $rl, $id]);
            if ($pw) {
                $pdo->prepare("UPDATE accounts SET username=?,password_hash=? WHERE user_id=?")
                    ->execute([$un, $pw, $id]);
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

            $pdo->beginTransaction();
            $pdo->prepare("DELETE FROM govOfficers WHERE user_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM accounts    WHERE user_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM users       WHERE user_id=?")->execute([$id]);
            $pdo->commit();

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
