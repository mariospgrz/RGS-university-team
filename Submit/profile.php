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
    <link rel="stylesheet" href="../Assets/css/bodyblocker.css"> <!-- Must for the navbar to work properly -->
    <link rel="stylesheet" href="../Assets/css/navbar-sticky.css"> 
</head>

<body class="body">
    <?php require_once "../Include/dashboard_navbar.php"; ?>
    <div class="page-content">
        <div class="profile-container">
            <h1>My Profile</h1>

            <form action="#" method="POST">

                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="FirstName" required>

                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="LastName" required>


                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" value="1234567890" required>

                <label for="email">Email (Cannot be changed):</label>
                <input type="email" id="email" name="email" value="test@example.com" readonly>

                <button type="submit">Save Changes</button>

            </form>
        </div>
    </div>
<?php
require_once "../Include/footer.php";
?>

</body>
