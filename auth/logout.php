<?php
/**
 * Logout Page
 */
// session_start() → session_destroy() → header redirect → exit

session_start();
session_unset();
session_destroy();

// Redirect back to login page
header("Location: ../index.php");
exit;
