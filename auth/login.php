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
                exit;
            }
            if ($user['role'] === 'User') {
                header("Location: ../Search/Search.php");
                exit;
            }
            if ($user['role'] === 'Politician') {
                header("Location: ../Search/Search.php");
                exit;
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

<body class="login-page">
    <main class="login-shell">
        <section class="login-panel" aria-label="Login">
            <aside class="login-intro">
                <div class="brand-mark">
                    <span class="material-icons" aria-hidden="true">account_balance</span>
                </div>
                <h1>Welcome back</h1>
                <p>Access your declaration workspace, review submissions, and continue securely.</p>
                <div class="intro-points" aria-label="Portal features">
                    <div><span class="material-icons" aria-hidden="true">lock</span> Protected account access</div>
                    <div><span class="material-icons" aria-hidden="true">search</span> Search public declarations</div>
                    <div><span class="material-icons" aria-hidden="true">task_alt</span> Manage your submissions</div>
                </div>
            </aside>

            <div class="login-card">
                <div class="form-heading">
                    <span class="eyebrow">Account access</span>
                    <h2>Login</h2>
                    <p>Enter your credentials to continue to the declaration system.</p>
                </div>

                <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
                    <div class="notice notice-success" role="status">
                        <span class="material-icons" aria-hidden="true">check_circle</span>
                        <span>Registration successful. Please log in.</span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="notice notice-error" role="alert">
                        <span class="material-icons" aria-hidden="true">error</span>
                        <span><?= htmlspecialchars($error_message) ?></span>
                    </div>
                <?php endif; ?>

                <form class="login-form" action="login.php" method="POST">
                    <label class="field" for="username">
                        <span>Username</span>
                        <input type="text" id="username" name="username" placeholder="Enter your username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    </label>

                    <label class="field" for="password">
                        <span>Password</span>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </label>

                    <button class="login-submit" type="submit">
                        <span class="material-icons" aria-hidden="true">login</span>
                        Login
                    </button>
                </form>

                <p class="register-link">Don't have an account? <a href="register.php">Sign up</a></p>
            </div>
        </section>
    </main>

    <style>
        body.login-page {
            min-height: 100vh;
            margin: 0;
            display: block;
            font-family: "Quicksand", sans-serif;
            background:
                linear-gradient(135deg, rgba(28, 90, 123, 0.16), rgba(218, 237, 232, 0.5)),
                #f5f7fb;
            color: #1f2933;
        }

        .login-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            box-sizing: border-box;
        }

        .login-panel {
            width: min(900px, 100%);
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            background: #ffffff;
            border: 1px solid rgba(31, 41, 51, 0.08);
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(31, 41, 51, 0.16);
            overflow: hidden;
        }

        .login-intro {
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

        .login-intro h1 {
            margin: 0 0 14px;
            font-size: 36px;
            line-height: 1.08;
            letter-spacing: 0;
        }

        .login-intro p {
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

        .login-card {
            padding: 48px 42px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-heading {
            margin-bottom: 24px;
        }

        .eyebrow {
            display: block;
            margin-bottom: 6px;
            color: #17324d;
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

        .notice {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 9px;
            text-align: left;
            font-size: 14px;
            font-weight: 700;
        }

        .notice .material-icons {
            font-size: 20px;
        }

        .notice-success {
            border: 1px solid #add8bd;
            background: #f0fbf4;
            color: #23663d;
        }

        .notice-error {
            border: 1px solid #f3b6b6;
            background: #fff4f4;
            color: #9f2a2a;
        }

        .login-form {
            display: grid;
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
            border-color: #17324d;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(23, 50, 77, 0.12);
        }

        .login-submit {
            width: 100%;
            min-height: 46px;
            margin-top: 6px;
            border: 0;
            border-radius: 8px;
            background: #17324d;
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

        .login-submit:hover {
            background: #1c5a7b;
            transform: translateY(-1px);
            box-shadow: 0 12px 22px rgba(23, 50, 77, 0.22);
        }

        .login-submit .material-icons {
            font-size: 20px;
        }

        .register-link {
            margin: 18px 0 0;
            text-align: center;
            color: #627080;
            font-size: 14px;
        }

        .register-link a {
            color: #17324d;
            font-weight: 800;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 760px) {
            .login-shell {
                padding: 18px;
                align-items: flex-start;
            }

            .login-panel {
                grid-template-columns: 1fr;
            }

            .login-intro {
                padding: 28px 24px;
            }

            .login-intro h1 {
                font-size: 29px;
            }

            .intro-points {
                margin-top: 22px;
            }

            .login-card {
                padding: 28px 24px;
            }
        }
    </style>
</body>

</html>
