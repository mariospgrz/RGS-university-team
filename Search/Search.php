<?php
/* NAME: Search.php
 * DESCRIPTION: This page allows users to search for politicians by name || position and view their submissions.
 * v0.2 Under development -> submission buttons
 * This php serves as the List.php for the Second Milestone.
 * The thematic feature is the submissions and the Search of the politicians using the database(db.php)
 */
session_start();

require_once "../Include/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'User') {
    header("Location: ../index.php");
    exit;
}

$errors = [];
$results = [];
$keyword = '';

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']); /*Get the keyword from the search form */

    if ($keyword === '') {
        $errors[] = "***Πρέπει να εισάγετε όνομα, επώνυμο ή θέση για αναζήτηση.";
    } else {
        $sql = "
            SELECT DISTINCT users.user_id, first_name, last_name, positions.position_name AS position
            FROM users 
            JOIN positions ON users.position_id = positions.position_id
            WHERE role = 'Politician'
            AND (first_name LIKE :key1 OR last_name LIKE :key2)

            UNION

            SELECT DISTINCT users.user_id, users.first_name, users.last_name, positions.position_name AS position
            FROM users
            JOIN positions ON users.position_id = positions.position_id
            WHERE positions.position_name LIKE :key3
        ";

        $stmt = $pdo->prepare($sql);//prepare statement
        $stmt->execute([
            'key1' => '%' . $keyword . '%',
            'key2' => '%' . $keyword . '%',
            'key3' => '%' . $keyword . '%'
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>

<?php require_once "../Include/header.php"; ?>
<head>
    <link rel="stylesheet" href="../Assets/css/navbar-sticky.css">
    <link rel="stylesheet" href="../Assets/css/searchpage.css">
</head>

<body id="search-page" class="search-page">
    <?php require_once "../Include/dashboard_navbar.php"; ?>
    <main class="main-content">
        <section class="page-header">
            <h1>Search for a politician's submissions</h1>
        </section>

        <section class="search-section">
            <form method="GET" action="" class="search-form">
                <input
                    type="text"
                    name="keyword"
                    placeholder="Search politicians"
                    value="<?php echo htmlspecialchars($keyword); ?>"
                >
                <button type="submit">Search</button>
            </form>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['keyword']) && empty($errors)): ?>
                <div class="search-results">
                    <?php if (!empty($results)): ?>
                        <div class="result-header">
                            <div class="result-cell">First Name</div>
                            <div class="result-cell">Last Name</div>
                            <div class="result-cell">Position</div>
                            <div class="result-cell">Submissions</div>
                        </div>
                        <?php foreach ($results as $row): ?>
                            <div class="result-row">
                                <div class="result-cell"><?php echo htmlspecialchars($row['first_name']); ?></div>
                                <div class="result-cell"><?php echo htmlspecialchars($row['last_name']); ?></div>
                                <div class="result-cell"><?php echo htmlspecialchars($row['position']); ?></div>
                                <div class="result-actions">
                                     <!-- Needs to be linked into the submissions of the exact politican without 
                                      exploiting the user_id in the url(NOT YET IMPLEMENTED)-->
                                    <a class="view-btn">View Submissions</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-results">Δεν βρέθηκαν αποτελέσματα.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>

<?php require_once "../Include/footer.php"; ?>
