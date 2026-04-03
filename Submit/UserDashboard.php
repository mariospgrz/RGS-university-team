<?php
require_once "../Include/header.php";
session_start();

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'User' && ($_SESSION['role'] ?? '') !== 'Politician')) {
    header("Location: ../index.php");
    exit;
}
?>

<div class="user-dashboard-container">
    <h1>Dashboard</h1>

    <div class="user-dashboard-buttons">
        <a href="profile.php" class="user-dashboard-card">
            <span class="material-icons">person</span>
            <p>My Profile</p>
        </a>

        <a href="submissions.php" class="user-dashboard-card">
            <span class="material-icons">assignment</span>
            <p>My Submissions</p>
        </a>
    </div>

    <div class="user-dashboard-logout">
        <form action="../auth/logout.php" method="post">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

</div>


<?php
require_once "../Include/footer.php";
?>
