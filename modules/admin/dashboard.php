<?php
require_once "../../includes/config.php";
require_once "../../includes/functions.php";
require_once "../../includes/header.php";
require_once "../../includes/nav.php";

$userRole = ROLE_ADMIN; // simulation
?>

<h1>Admin Dashboard</h1>

<?php
if ($userRole === ROLE_ADMIN) {
    echo "<p>Welcome Admin!</p>";
} elseif ($userRole === ROLE_SUBMIT) {
    echo "<p>You are not allowed here.</p>";
} else {
    echo "<p>Unauthorized access.</p>";
}
?>

<?php require_once "../../includes/footer.php"; ?>