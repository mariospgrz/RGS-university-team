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
<?php
require_once "../Include/header.php";
?>

<body class="register-page">
    <main class="register-shell">
        <section class="register-panel" aria-label="Registration">
            <aside class="register-intro">
                <div class="brand-mark">
                    <span class="material-icons" aria-hidden="true">account_balance</span>
                </div>
                <h1>Create your account</h1>
                <p>Submit and manage your Πόθεν Έσχες declarations through a secure university portal.</p>
                <div class="intro-points" aria-label="Benefits">
                    <div><span class="material-icons" aria-hidden="true">verified_user</span> Secure access</div>
                    <div><span class="material-icons" aria-hidden="true">description</span> Saved declarations</div>
                    <div><span class="material-icons" aria-hidden="true">history</span> Submission history</div>
                </div>
            </aside>

            <div class="register-card">
                <div class="form-heading">
                    <span class="eyebrow">New account</span>
                    <h2>Register</h2>
                    <p>Use your real details so your declarations can be matched correctly.</p>
                </div>

        <?php if (!empty($errors)): ?>
                    <div class="error-messages" role="alert">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

                <form class="register-form" action="register.php" method="post">
                    <div class="form-grid">
                        <label class="field">
                            <span>First name</span>
                            <input type="text" name="first_name" placeholder="Andreas"
                                value="<?= htmlspecialchars($first_name ?? '') ?>" required>
                        </label>

                        <label class="field">
                            <span>Last name</span>
                            <input type="text" name="last_name" placeholder="Georgiou"
                                value="<?= htmlspecialchars($last_name ?? '') ?>" required>
                        </label>

                        <label class="field field-wide">
                            <span>Email address</span>
                            <input type="email" name="email" placeholder="name@example.com"
                                value="<?= htmlspecialchars($email ?? '') ?>" required>
                        </label>

                        <label class="field">
                            <span>Phone number</span>
                            <input type="text" name="phone" placeholder="99123456"
                                value="<?= htmlspecialchars($phone ?? '') ?>" required>
                        </label>

                        <label class="field">
                            <span>Username</span>
                            <input type="text" name="username" placeholder="andreasg"
                                value="<?= htmlspecialchars($username ?? '') ?>" required>
                        </label>

                        <label class="field">
                            <span>Password</span>
                            <input type="password" name="password" placeholder="Minimum 8 characters" required>
                        </label>

                        <label class="field">
                            <span>Confirm password</span>
                            <input type="password" name="confirm_password" placeholder="Repeat password" required>
                        </label>
                    </div>

                    <button class="register-submit" type="submit">
                        <span class="material-icons" aria-hidden="true">person_add</span>
                        Create account
                    </button>
                </form>

                <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
            </div>
        </section>
    </main>

    <style>
        body.register-page {
            min-height: 100vh;
            margin: 0;
            display: block;
            font-family: "Quicksand", sans-serif;
            background:
                linear-gradient(135deg, rgba(28, 90, 123, 0.16), rgba(218, 237, 232, 0.5)),
                #f5f7fb;
            color: #1f2933;
        }

        .register-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            box-sizing: border-box;
        }

        .register-panel {
            width: min(980px, 100%);
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            background: #ffffff;
            border: 1px solid rgba(31, 41, 51, 0.08);
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(31, 41, 51, 0.16);
            overflow: hidden;
        }

        .register-intro {
            background: #17324d;
            color: #ffffff;
            padding: 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-mark {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: #f4c95d;
            color: #17324d;
            margin-bottom: 26px;
        }

        .brand-mark .material-icons {
            font-size: 34px;
        }

        .register-intro h1 {
            margin: 0 0 14px;
            font-size: 36px;
            line-height: 1.08;
            letter-spacing: 0;
        }

        .register-intro p {
            margin: 0;
            font-size: 16px;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.78);
        }

        .intro-points {
            display: grid;
            gap: 12px;
            margin-top: 34px;
        }

        .intro-points div {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
        }

        .intro-points .material-icons {
            font-size: 21px;
            color: #f4c95d;
        }

        .register-card {
            padding: 42px;
        }

        .form-heading {
            margin-bottom: 24px;
        }

        .eyebrow {
            display: block;
            margin-bottom: 6px;
            color: #1c5a7b;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .form-heading h2 {
            margin: 0;
            text-align: left;
            font-size: 30px;
            color: #17212b;
        }

        .form-heading p {
            margin: 8px 0 0;
            color: #627080;
            font-size: 14px;
            line-height: 1.5;
        }

        .error-messages {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #f3b6b6;
            border-radius: 8px;
            background: #fff4f4;
            color: #9f2a2a;
            text-align: left;
            font-size: 14px;
        }

        .error-messages ul {
            margin: 0;
            padding-left: 18px;
        }

        .register-form {
            display: block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin: 0;
            font-weight: 700;
            color: #344252;
            font-size: 13px;
        }

        .field-wide {
            grid-column: 1 / -1;
        }

        .field input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #d6dde5;
            border-radius: 8px;
            padding: 12px 13px;
            font: inherit;
            background: #fbfcfe;
            color: #17212b;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .field input:focus {
            outline: none;
            border-color: #1c5a7b;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(28, 90, 123, 0.12);
        }

        .register-submit {
            width: 100%;
            min-height: 46px;
            margin-top: 22px;
            border: 0;
            border-radius: 8px;
            background: #1c5a7b;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        .register-submit:hover {
            background: #174963;
            transform: translateY(-1px);
            box-shadow: 0 12px 22px rgba(28, 90, 123, 0.22);
        }

        .register-submit .material-icons {
            font-size: 20px;
        }

        .login-link {
            margin: 18px 0 0;
            text-align: center;
            color: #627080;
            font-size: 14px;
        }

        .login-link a {
            color: #1c5a7b;
            font-weight: 800;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 760px) {
            .register-shell {
                padding: 18px;
                align-items: flex-start;
            }

            .register-panel {
                grid-template-columns: 1fr;
            }

            .register-intro {
                padding: 28px 24px;
            }

            .register-intro h1 {
                font-size: 29px;
            }

            .intro-points {
                grid-template-columns: 1fr;
                margin-top: 22px;
            }

            .register-card {
                padding: 28px 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .field-wide {
                grid-column: auto;
            }
        }
    </style>
</body>
</html>
