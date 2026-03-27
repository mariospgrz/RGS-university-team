<?php
// Includes/db.php

$host = '127.0.0.1';
$dbname = 'pothenesxes'; // Change this to your actual database name
$dbusername = 'root';
$dbpassword = 'Password1.';

// PDO με ATTR_ERRMODE = ERRMODE_EXCEPTION και ATTR_DEFAULT_FETCH_MODE = FETCH_ASSOC
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbusername, $dbpassword, $options);
} catch (PDOException $e) {
    // το die() ΔΕΝ εκθέτει $e->getMessage()
    error_log($e->getMessage()); // Save to error log silently
    die("Σφάλμα σύνδεσης με τη βάση δεδομένων.");
}
