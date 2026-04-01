<?php
session_start();
require_once '../Include/db.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT u.*, a.username, a.password_hash FROM users u INNER JOIN accounts a ON u.user_id = a.user_id WHERE a.username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'] ?? 'User';
            $_SESSION['username'] = $user['username'] ?? 'User';

            if ($user['role'] === 'Admin') {
                header("Location: ../Admin/Admin.php");
            }
            if ($user['role'] === 'User') {
                header("Location: ../Submit/UserDashboard.php");
            }
            if ($user['role'] === 'Politician') {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $error_message = "Wrong credentials";
        }
    } else {
        $error_message = "Wrong credentials";
    }
}
?>

<?php
 include_once "../Include/header.php";
?>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
            <p style="color: green; text-align: center; margin-bottom: 15px; font-weight: bold;">Επιτυχής εγγραφή! Παρακαλώ
                συνδεθείτε.</p>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <p style="color: red; text-align: center; margin-bottom: 15px; font-weight: bold;">
                <?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
</body>

</html>