<?php
session_start();
require_once '../Include/db.php';

$errors = [];

// Ανάγνωση POST data — trim() σε text fields, ΟΧΙ trim() στο password
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Server-side validation (μαζεύουμε ΟΛΑ τα errors πριν εμφανίσουμε)
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($username) || empty($password) || empty($confirm_password)) {
        $errors[] = "Όλα τα πεδία είναι υποχρεωτικά.";
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Μη έγκυρη μορφή email.";
    }

    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.";
    }

    if (!empty($password) && $password !== $confirm_password) {
        $errors[] = "Οι κωδικοί δεν ταιριάζουν.";
    }

    // Έλεγχος uniqueness: email στο users, username στο accounts
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :e");
        $stmt->execute(['e' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Το email χρησιμοποιείται ήδη.";
        }

        $stmt2 = $pdo->prepare("SELECT user_id FROM accounts WHERE username = :u");
        $stmt2->execute(['u' => $username]);
        if ($stmt2->fetch()) {
            $errors[] = "Το username χρησιμοποιείται ήδη.";
        }
    }

    // INSERT με Prepared Statement + redirect σε login.php?registered=1 + exit
    if (empty($errors)) {
        // password_hash($password, PASSWORD_DEFAULT) — ΠΟΤΕ plain-text
        // ΠΡΟΣΟΧΗ: Το password_hash απαιτεί πεδίο τουλάχιστον 60 χαρακτήρων (π.χ. VARCHAR(255))
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            $sql_users = "INSERT INTO users (first_name, last_name, email, Phone, role) VALUES (:first_name, :last_name, :email, :phone, 'User')";
            $stmt_u = $pdo->prepare($sql_users);
            $stmt_u->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone
            ]);

            $user_id = $pdo->lastInsertId();

            $sql_accounts = "INSERT INTO accounts (user_id, username, password_hash) VALUES (:user_id, :username, :password_hash)";
            $stmt_a = $pdo->prepare($sql_accounts);
            $stmt_a->execute([
                'user_id' => $user_id,
                'username' => $username,
                'password_hash' => $hashed_password
            ]);

            $pdo->commit();
            header("Location: login.php?registered=1");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Συνέβη ένα σφάλμα κατά την εγγραφή. Δοκιμάστε ξανά.";
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="el">

<?php
require_once "../Include/header.php";
?>

<body class="body">
    <div class="login-container">
        <h2>Register</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-messages" style="color: red; margin-bottom: 15px; text-align: left;">
                <ul style="list-style-position: inside; padding: 0;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post">
            <label>
                <input class="register_input" type="text" name="first_name" placeholder="First Name"
                    value="<?= htmlspecialchars($first_name ?? '') ?>" required>
                <input class="register_input" type="text" name="last_name" placeholder="Last Name"
                    value="<?= htmlspecialchars($last_name ?? '') ?>" required>
                <input class="register_input" type="email" name="email" placeholder="Email Address"
                    value="<?= htmlspecialchars($email ?? '') ?>" required>
                <input class="register_input" type="text" name="phone" placeholder="Phone Number"
                    value="<?= htmlspecialchars($phone ?? '') ?>" required>
                <input class="register_input" type="text" name="username" placeholder="Username"
                    value="<?= htmlspecialchars($username ?? '') ?>" required>
                <input class="register_input" type="password" name="password" placeholder="Password" required>
                <input class="register_input" type="password" name="confirm_password" placeholder="Confirm Password"
                    required>
            </label>

            <button class="register_button" type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>

</html>