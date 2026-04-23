<?php
session_start();

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'User' && ($_SESSION['role'] ?? '') !== 'Politician')) {
    header("Location: ../index.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$submissions = [];
$targetUserName = '';

require_once "../Include/db.php";

//handle the view submissions of a specific politican if the user asks to see from the search a politician's submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_user_id'])) {
    $viewUserId = (int)$_POST['view_user_id'];
    if ($viewUserId > 0) {
        $_SESSION['view_submissions_user_id'] = $viewUserId; //store the politician user id and name in the session
        $_SESSION['view_submissions_user_name'] = trim((string)($_POST['view_user_name'] ?? ''));
    }
    header('Location: submissions.php');
    exit;
}

if (isset($_GET['clear_view'])) { //if the user clears the view of the politician's submissions then go back to normal
    unset($_SESSION['view_submissions_user_id'], $_SESSION['view_submissions_user_name']);
    header('Location: submissions.php'); //delete the session variables and refresh page
    exit;
}

try {
    $targetUserId = (int)($_SESSION['view_submissions_user_id'] ?? $userId); //deafult is the logged in user
    $targetUserName = trim((string)($_SESSION['view_submissions_user_name'] ?? '')); // if from the POST req get the name and id of the politician
                                                                                     

    if ($targetUserId <= 0) {
        $targetUserId = $userId; //invalid id -> defautl
    }

    $userStmt = $pdo->prepare("SELECT first_name, last_name, role FROM users WHERE user_id = ?");
    $userStmt->execute([$targetUserId]); //use the target user id to get the name and role of the user.
    $targetUser = $userStmt->fetch();

    //check if target user is valid and politician
    if (!$targetUser || ($targetUserId !== $userId && ($targetUser['role'] ?? '') !== 'Politician')) {
        unset($_SESSION['view_submissions_user_id'], $_SESSION['view_submissions_user_name']); 
        $targetUserId = $userId;
        $targetUserName = '';
    } elseif ($targetUserName === '') {
        $targetUserName = trim(($targetUser['first_name'] ?? '') . ' ' . ($targetUser['last_name'] ?? ''));
    }

    //fetch the submissions of the target users
    $stmt = $pdo->prepare(
        "SELECT submission_id, year, submitted_at, status, pdf_path, notes
         FROM submissions
         WHERE user_id = ?
         ORDER BY submitted_at DESC"
    );
    $stmt->execute([$targetUserId]);
    $submissions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$navbar_css_version = @filemtime(__DIR__ . '/../Assets/css/navbar-sticky.css') ?: time();

require_once "../Include/header.php";
?>

<head>
    <link rel="stylesheet" href="../Assets/css/navbar-sticky.css?v=<?php echo $navbar_css_version; ?>">
    <link rel="stylesheet" href="../Assets/css/bodyblocker.css"> <!-- Must for the navbar to work properly -->
    <style>
        .submission-container {
            width: min(760px, calc(100vw - 40px));
        }

        .submissions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            text-align: left;
            font-size: 14px;
        }

        .submissions-table th,
        .submissions-table td {
            padding: 10px;
            border-bottom: 1px solid #e6e6e6;
        }

        .submissions-table th {
            color: #555;
            font-weight: 700;
            background: #f8f9fa;
        }

        .pdf-link {
            color: #007bff;
            font-weight: 700;
            text-decoration: none;
        }

        .pdf-link:hover {
            text-decoration: underline;
        }

        .empty-state {
            color: #777;
            padding: 18px 0;
        }
    </style>
</head>

<body class="body">
    <?php require_once "../Include/dashboard_navbar.php"; ?>
    <div class="page-content">
        <div class="submission-container">
            <h1><?php echo isset($_SESSION['view_submissions_user_id']) ? 'Politician Submissions' : 'My Submissions'; ?></h1>
            <p>
                <?php if (isset($_SESSION['view_submissions_user_id'])): ?>
                    Viewing submissions for <?php echo e($targetUserName ?: 'the selected politician'); ?>.
                <?php else: ?>
                    Συμπληρώστε το Πόθεν Έσχες σας και υποβάλετε για οριστική καταχώρηση.
                <?php endif; ?>
            </p>
            <?php if (isset($_SESSION['view_submissions_user_id'])): ?>
                <div>
                    <a href="submissions.php?clear_view=1" id="newDeclaration">Back to My Submissions</a>
                </div>
            <?php else: ?>
                <div>
                    <a href="declaration.php" id="newDeclaration">Make New Declaration</a>
                </div>
            <?php endif; ?>

            <div class="previous-declarations">
                <h3>Previous Declarations</h3>
                <div id="declarationsList">
                    <?php if (!$submissions): ?>
                        <p class="empty-state">No submissions found yet.</p>
                    <?php else: ?>
                        <table class="submissions-table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Year</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>PDF</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td><?= e((string)$submission['submission_id']) ?></td>
                                    <td><?= e((string)$submission['year']) ?></td>
                                    <td><?= e($submission['submitted_at']) ?></td>
                                    <td><?= e($submission['status']) ?></td>
                                    <td>
                                        <?php if (!empty($submission['pdf_path'])): ?>
                                            <a class="pdf-link" href="<?= e($submission['pdf_path']) ?>" target="_blank" rel="noopener">View PDF</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>



<?php
require_once "../Include/footer.php";
?>
</body>
