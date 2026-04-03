<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="el">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Πόθεν Έσχες</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../Assets/mainstyle/global.css">
</head>

<body class="admin-page">
    <div class="admin-container">
        <?php $activePage = '';
        include 'includes/admin_nav.php'; ?>
        <main class="main-content">
            <h1>Welcome to the Admin Dashboard!</h1>
            <p>Use the navigation above to manage users, submissions, configure the system, and generate reports.</p>
        </main>
    </div>
</body>

</html>