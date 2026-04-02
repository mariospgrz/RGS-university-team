<?php
require_once "../Include/header.php";
?>

<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'User') {
    header("Location: ../index.php");
    exit;
}
?>

<head>
    <link rel="stylesheet" href="../Assets/css/navbar-sticky.css">
    <link rel="stylesheet" href="../Assets/css/bodyblocker.css"> <!-- Must for the navbar to work properly -->
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
                    <button style="color: black">2024-01-15 — Submitted</button>
                    <button style="color: black">2023-01-13 — Verified</button>
                </div>
            </div>
        </div>
    </div>



<?php
require_once "../Include/footer.php";
?>
</body>