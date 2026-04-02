<nav class="top-navbar">
    <div class="navbar-brand">Πόθεν Έσχες</div>
    <ul class="navbar-links">
        <li><a href="../Submit/profile.php" <?php echo (basename($_SERVER['PHP_SELF']) === 'profile.php') ? 'class="active"' : ''; ?>>Profile</a></li>
        <li><a href="../Submit/submissions.php" <?php echo (basename($_SERVER['PHP_SELF']) === 'submissions.php') ? 'class="active"' : ''; ?>>My Submissions</a></li>
        <li><a href="../Search/Search.php" <?php echo (basename($_SERVER['PHP_SELF']) === 'Search.php') ? 'class="active"' : ''; ?>>Search Page</a></li>
        <li><a href="../index.php" class="logout-btn">Logout</a></li>
    </ul>
</nav>
