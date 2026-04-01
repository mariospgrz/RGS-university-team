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

<?php
require_once "../Include/footer.php";
?>
