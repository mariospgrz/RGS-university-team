<?php
require_once "../Include/header.php";
require_once "../Include/config.php";
require_once "../Include/db.php";
?>

<?php
session_start();

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'User' && ($_SESSION['role'] ?? '') !== 'Politician')) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];

    $stmt = $pdo->prepare("UPDATE users SET first_name = :first_name, last_name = :last_name, phone = :phone WHERE user_id = :id");
    $stmt->execute([
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'phone'     => $phone,
            'id'        => $user_id
    ]);

    // Refresh data after update
    header("Location: profile.php");
    exit;
}

$stmt = $pdo->prepare("SELECT first_name, last_name, phone, email FROM users WHERE user_id = :id");
$stmt->execute(['id' => $user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<head> 
    <link rel="stylesheet" href="../Assets/css/bodyblocker.css"> <!-- Must for the navbar to work properly -->
    <link rel="stylesheet" href="../Assets/css/navbar-sticky.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</head>

<body class="body">
    <?php require_once "../Include/dashboard_navbar.php"; ?>
    <div class="page-content">
        <div class="profile-container">
            <h1>My Profile</h1>

            <form action="#" method="POST">

                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?=htmlspecialchars($user['first_name'])?>" required>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?=htmlspecialchars($user['last_name'])?>" required>


                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" value="<?=htmlspecialchars($user['phone'])?>" required>

                <label for="email">Email (Cannot be changed):</label>
                <input type="email" id="email" name="email" value="<?=htmlspecialchars($user['email'])?>" readonly>

                <button type="button" id="successBtn">Save Changes</button>

                <script>
                    const btn = document.getElementById('successBtn');

                    btn.addEventListener('click', () => {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "This action cannot be undone!",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Apply',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {

                                // Get form input values
                                const first_name = document.getElementById('first_name').value;
                                const last_name  = document.getElementById('last_name').value;
                                const phone     = document.getElementById('phone').value;

                                // Prepare FormData
                                const formData = new FormData();
                                formData.append('first_name', first_name);
                                formData.append('last_name', last_name);
                                formData.append('phone', phone);

                                // Send AJAX request
                                fetch('profile.php', {
                                    method: 'POST',
                                    body: formData,
                                })
                                    .then(response => response.text())
                                    .then(data => {
                                        // Show success alert after PHP updates
                                        Swal.fire(
                                            'Updated!',
                                            'Your profile has been updated successfully.',
                                            'success'
                                        );
                                    })
                                    .catch(error => {
                                        Swal.fire(
                                            'Error!',
                                            'Something went wrong. Please try again.',
                                            'error'
                                        );
                                        console.error(error);
                                    });
                            }
                        });
                    });
                </script>
            </form>
        </div>
    </div>
<?php
require_once "../Include/footer.php";
?>

</body>
