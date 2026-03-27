<?php
// session_start() — ΠΑΝΤΑ πρώτη γραμμή, πριν από οποιοδήποτε output
session_start();
// Require db connection
require_once '../Include/db.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // no trim on password

    if (!empty($email) && !empty($password)) {
        // SELECT * FROM users u JOIN accounts a ON u.user_id = a.user_id WHERE email = :email — Prepared Statement
        $stmt = $pdo->prepare("SELECT u.*, a.username, a.password_hash FROM users u INNER JOIN accounts a ON u.user_id = a.user_id WHERE u.email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // password_verify($password, $user['password_hash']) — ΠΟΤΕ == σύγκριση
        if ($user && password_verify($password, $user['password_hash'])) {
            // Επιτυχία: γέμισμα $_SESSION με user_id, role, username → redirect + exit
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'] ?? 'User';
            $_SESSION['username'] = $user['username'] ?? 'User';

            // Redirect to dashboard or home page
            header("Location: ../index.php");
            exit;
        } else {
            // Αποτυχία: γενικό μήνυμα «Λανθασμένα στοιχεία σύνδεσης.» — ποτέ συγκεκριμένο
            $error_message = "Λανθασμένα στοιχεία σύνδεσης.";
        }
    } else {
        $error_message = "Λανθασμένα στοιχεία σύνδεσης.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../Assets/mainstyle/global.css">
    <!-- CSS is now one level up in the hierarchy -->
</head>

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
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
</body>

</html>