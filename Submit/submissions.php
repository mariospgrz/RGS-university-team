<?php
session_start();

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'User' && ($_SESSION['role'] ?? '') !== 'Politician')) {
    header("Location: ../index.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$submissions = [];

require_once "../Include/db.php";

try {
    $stmt = $pdo->prepare(
        "SELECT submission_id, year, submitted_at, status, pdf_path, notes
         FROM submissions
         WHERE user_id = ?
         ORDER BY submitted_at DESC"
    );
    $stmt->execute([$userId]);
    $submissions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

require_once "../Include/header.php";
?>

<head>
    <link rel="stylesheet" href="../Assets/css/navbar-sticky.css">
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
            <h1>My Submissions</h1>
            <p>Συμπληρώστε το Πόθεν Έσχες σας και υποβάλετε για οριστική καταχώρηση.</p>
            <div>
                <a href="declaration.php" id="newDeclaration">Make New Declaration</a>
            </div>

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
