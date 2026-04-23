<?php if (!isset($activePage)) $activePage = ''; ?>
<aside class="sidebar">
    <h2>Admin</h2>
    <p>Πόθεν Έσχες</p>
    <nav>
        <ul>
            <li><a href="manager-users.php"<?= $activePage === 'users' ? ' class="nav-active"' : '' ?>>Manage Users</a></li>
            <li><a href="manager-submissions.php"<?= $activePage === 'submissions' ? ' class="nav-active"' : '' ?>>Manage Submissions</a></li>
            <li><a href="configure-system.php"<?= $activePage === 'configure' ? ' class="nav-active"' : '' ?>>Configure System</a></li>
            <li><a href="reports.php"<?= $activePage === 'reports' ? ' class="nav-active"' : '' ?>>Reports</a></li>
            <li><a href="../auth/logout.php" class="logout-btn">Logout</a></li>
        </ul>
    </nav>
</aside>
