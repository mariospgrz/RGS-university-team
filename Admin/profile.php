<?php
require_once "../Include/config.php";
require_once "../Include/db.php";

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$activePage = 'profile';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $hasProfileFields = isset($_POST['first_name'], $_POST['last_name'], $_POST['phone']);
    $hasProfilePicture = isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE;
    $profile_picture = null;

    if ($hasProfilePicture) {
        header('Content-Type: application/json');

        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Upload failed.']);
            exit;
        }

        $fileType = mime_content_type($file['tmp_name']);
        if (!isset($allowedTypes[$fileType])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Only JPG and PNG images are allowed.']);
            exit;
        }

        $uploadDir = __DIR__ . "/../Assets/media";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = $allowedTypes[$fileType];
        $newFileName = "user_" . $user_id . "_" . time() . "." . $extension;
        $destination = $uploadDir . "/" . $newFileName;
        $profile_picture = "../Assets/media/" . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not save uploaded image.']);
            exit;
        }
    }

    if ($hasProfileFields) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $phone = $_POST['phone'];

        if ($profile_picture !== null) {
            $stmt = $pdo->prepare("
                UPDATE users
                SET first_name = :first_name,
                    last_name = :last_name,
                    phone = :phone,
                    profile_picture = :profile_picture
                WHERE user_id = :id
            ");
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'profile_picture' => $profile_picture,
                'id' => $user_id,
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users
                SET first_name = :first_name,
                    last_name = :last_name,
                    phone = :phone
                WHERE user_id = :id
            ");
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'id' => $user_id,
            ]);
        }

        exit;
    }

    if ($profile_picture !== null) {
        $stmt = $pdo->prepare("
            UPDATE users
            SET profile_picture = :profile_picture
            WHERE user_id = :id
        ");
        $stmt->execute([
            'profile_picture' => $profile_picture,
            'id' => $user_id,
        ]);

        echo json_encode(['status' => 'success', 'path' => $profile_picture]);
        exit;
    }

    http_response_code(400);
    exit;
}

$stmt = $pdo->prepare("
    SELECT first_name, last_name, phone, email, position_name, profile_picture
    FROM users
    JOIN positions ON users.position_id = positions.position_id
    WHERE user_id = :id
");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_picture = $user['profile_picture'] ?? '../Assets/media/profile_placeholder.png';
?>
<!DOCTYPE html>
<html lang="el">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | Πόθεν Έσχες</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/mainstyle/global.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <style>
        .profile-shell {
            width: 100%;
            max-width: 1150px;
            margin: 0 auto;
        }

        .profile-header {
            margin-bottom: 24px;
        }

        .profile-header-text {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .profile-eyebrow {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #17324d;
        }

        .profile-title {
            margin: 0;
            font-size: 36px;
            line-height: 1.1;
            font-weight: 700;
            color: #111827;
        }

        .profile-subtitle {
            margin: 0;
            font-size: 15px;
            color: #6b7280;
        }

        .profile-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .profile-card-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            min-height: 100%;
        }

        .profile-sidebar {
            background: linear-gradient(180deg, #f8fbff 0%, #f3f6fb 100%);
            border-right: 1px solid #e5e7eb;
            padding: 32px 24px;
            box-sizing: border-box;
        }

        .profile-avatar-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            text-align: center;
        }

        .profile-avatar-wrap {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            padding: 6px;
            background: linear-gradient(135deg, #dbeafe, #e0e7ff);
            box-shadow: 0 12px 30px rgba(37, 99, 235, 0.12);
        }

        .profile-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
            background: #ffffff;
        }

        .profile-avatar-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
            max-width: 220px;
            align-items: center;
        }

        .profile-avatar-note p {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
            color: #6b7280;
        }

        .profile-main {
            padding: 32px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .profile-section {
            background: #ffffff;
            border: 1px solid #edf0f4;
            border-radius: 18px;
            padding: 24px;
        }

        .profile-section-heading {
            margin-bottom: 18px;
        }

        .profile-section-heading h2 {
            margin: 0 0 6px;
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }

        .profile-section-heading p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
        }

        .profile-fields-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 20px;
        }

        .profile-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
        }

        .profile-field-full {
            grid-column: 1 / -1;
        }

        .profile-label {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
        }

        .profile-input {
            width: 100%;
            height: 48px;
            padding: 0 15px;
            border: 1px solid #d1d5db;
            border-radius: 14px;
            background: #ffffff;
            box-sizing: border-box;
            font-family: "Quicksand", sans-serif;
            font-size: 15px;
            color: #111827;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .profile-input:focus {
            outline: none;
            border-color: #17324d;
            box-shadow: 0 0 0 4px rgba(23, 50, 77, 0.12);
        }

        .profile-input-readonly {
            background: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
        }

        .profile-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 24px 32px 32px;
            border-top: 1px solid #edf0f4;
            background: #fcfcfd;
        }

        .profile-footer-text p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
        }

        .profile-primary-btn,
        .profile-secondary-btn {
            border: none;
            outline: none;
            text-decoration: none;
            font-family: "Quicksand", sans-serif;
            font-size: 14px;
            font-weight: 700;
            border-radius: 999px;
            padding: 12px 18px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            box-sizing: border-box;
        }

        .profile-primary-btn {
            background: linear-gradient(135deg, #17324d, #1c5a7b);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(23, 50, 77, 0.18);
        }

        .profile-primary-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(23, 50, 77, 0.22);
        }

        .profile-secondary-btn {
            background: #ffffff;
            color: #111827;
            border: 1px solid #d1d5db;
        }

        .profile-secondary-btn:hover {
            background: #f9fafb;
            transform: translateY(-1px);
        }

        #fileInput {
            display: none;
        }

        @media (max-width: 960px) {
            .profile-card-grid {
                grid-template-columns: 1fr;
            }

            .profile-sidebar {
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }
        }
    </style>
</head>

<body class="admin-page">
    <div class="admin-container">
        <?php include 'includes/admin_nav.php'; ?>
        <main class="main-content">
            <div class="profile-shell">
                <div class="profile-header">
                    <div class="profile-header-text">
                        <p class="profile-eyebrow">Account settings</p>
                        <h1 class="profile-title">My Profile</h1>
                        <p class="profile-subtitle">Manage your personal information and profile picture.</p>
                    </div>
                </div>

                <form id="profileForm" class="profile-card" action="#" method="POST">
                    <div class="profile-card-grid">

                        <aside class="profile-sidebar">
                            <div class="profile-avatar-panel">
                                <div class="profile-avatar-wrap">
                                    <img id="preview" src="<?= htmlspecialchars($profile_picture) ?>" class="profile-avatar" alt="Profile Preview">
                                </div>

                                <div class="profile-avatar-actions">
                                    <label for="fileInput" class="profile-secondary-btn">
                                        <span>Choose Image</span>
                                    </label>
                                    <input type="file" id="fileInput" accept="image/*">

                                    <button type="button" class="profile-primary-btn" onclick="uploadImage()">
                                        Upload Photo
                                    </button>
                                </div>

                                <div class="profile-avatar-note">
                                    <p>Use a clear square image for best results.</p>
                                </div>
                            </div>
                        </aside>

                        <section class="profile-main">
                            <div class="profile-section">
                                <div class="profile-section-heading">
                                    <h2>Personal Information</h2>
                                    <p>Keep your details up to date.</p>
                                </div>

                                <div class="profile-fields-grid">
                                    <div class="profile-field">
                                        <label for="first_name" class="profile-label">First Name</label>
                                        <input type="text" id="first_name" name="first_name" class="profile-input" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                                    </div>

                                    <div class="profile-field">
                                        <label for="last_name" class="profile-label">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" class="profile-input" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                                    </div>

                                    <div class="profile-field profile-field-full">
                                        <label for="phone" class="profile-label">Phone</label>
                                        <input type="tel" id="phone" name="phone" class="profile-input" value="<?= htmlspecialchars($user['phone']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-section">
                                <div class="profile-section-heading">
                                    <h2>Account Details</h2>
                                    <p>These fields are read-only.</p>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 18px;">
                                    <div class="profile-field">
                                        <label for="email" class="profile-label">Email</label>
                                        <input type="email" id="email" name="email" class="profile-input profile-input-readonly" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                    </div>

                                    <div class="profile-field">
                                        <label for="position_name" class="profile-label">Position</label>
                                        <input type="text" id="position_name" name="position_name" class="profile-input profile-input-readonly" value="<?= htmlspecialchars($user['position_name']) ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="profile-footer">
                                <div class="profile-footer-text">
                                    <p>Review your changes before saving.</p>
                                </div>
                                <div class="profile-footer-actions">
                                    <button type="button" class="profile-primary-btn" id="successBtn">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const preview = document.getElementById('preview');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });

        function uploadImage() {
            const file = fileInput.files[0];

            if (!file) {
                Swal.fire('No file selected', 'Please select an image first.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('profile_picture', file);

            fetch('profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json().then(data => {
                    if (!res.ok || data.status !== 'success') {
                        throw new Error(data.message || 'Upload failed.');
                    }
                    return data;
                }))
                .then(data => {
                    preview.src = data.path;
                    fileInput.value = '';
                    Swal.fire('Uploaded!', 'Profile picture uploaded successfully.', 'success');
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error!', err.message, 'error');
                });
        }

        document.getElementById('successBtn').addEventListener('click', () => {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Your profile information will be updated.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Apply',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('first_name', document.getElementById('first_name').value);
                formData.append('last_name', document.getElementById('last_name').value);
                formData.append('phone', document.getElementById('phone').value);
                if (fileInput.files[0]) {
                    formData.append('profile_picture', fileInput.files[0]);
                }

                fetch('profile.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => {
                        if (!res.ok) {
                            return res.text().then(text => {
                                let message = 'Something went wrong. Please try again.';
                                try {
                                    message = JSON.parse(text).message || message;
                                } catch (error) {
                                    if (text) message = text;
                                }
                                throw new Error(message);
                            });
                        }
                    })
                    .then(() => {
                        Swal.fire('Updated!', 'Your profile has been updated successfully.', 'success')
                            .then(() => location.reload());
                    })
                    .catch((error) => {
                        console.error(error);
                        Swal.fire('Error!', error.message, 'error');
                    });
            });
        });
    </script>
</body>

</html>
