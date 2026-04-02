<?php
session_start();

require_once "../Include/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'User') {
    header("Location: ../index.php");
    exit;
}

$errors = [];
$results = [];
$search_query = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $search_query = trim($_POST['search_query'] ?? '');

    if ($search_query === '') {
        $errors[] = "Πρέπει να εισάγετε όνομα, επώνυμο ή θέση για αναζήτηση.";
    } else {
        $sql = "
            SELECT DISTINCT first_name, last_name, 'Politician' AS position
            FROM users
            WHERE role = 'Politician'
            AND (first_name LIKE :query OR last_name LIKE :query)

            UNION

            SELECT DISTINCT users.first_name, users.last_name, positions.position_name AS position
            FROM users
            JOIN govofficers ON users.user_id = govofficers.user_id
            JOIN positions ON govofficers.officer_position = positions.position_id
            WHERE positions.position_name LIKE :query
        ";

        $stmt = $pdo->prepare($sql);//prepare statement
        $stmt->execute(['query' => '%' . $search_query . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>

<?php require_once "../Include/header.php"; ?>
<style>
#search-page {
    display: block;
    justify-content: initial;
    align-items: initial;
    height: auto;
}

#search-page .top-navbar {
    position: sticky;
    top: 0;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 24px;
    background: #1e3a8a;
    color: #fff;
}

#search-page .navbar-brand {
    font-size: 1.2rem;
    font-weight: 700;
}

#search-page .navbar-links {
    list-style: none;
    display: flex;
    gap: 16px;
    margin: 0;
    padding: 0;
    align-items: center;
}

#search-page .navbar-links a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
}

#search-page .navbar-links a.active {
    text-decoration: underline;
}

#search-page .logout-btn {
    background: #2563eb;
    padding: 8px 14px;
    border-radius: 8px;
}

#search-page .main-content {
    max-width: 1100px;
    margin: 28px auto !important;
    padding: 0 20px !important;
}

#search-page .search-form {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 10px;
    width: 100%;
}

#search-page .search-form input {
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

#search-page .search-form button {
    white-space: nowrap;
    box-sizing: border-box;
}

#search-page .search-form input,
#search-page .search-form button {
    height: 44px;
    border-radius: 8px;
    border: 1px solid #cfcfcf;
    font-size: 1rem;
    padding: 0 12px;
}

#search-page .search-form button {
    background: #1677ff;
    color: white;
    border: none;
    cursor: pointer;
}

#search-page .results-grid {
    margin-top: 18px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 14px;
}

#search-page .result-card {
    background: #fff;
    border-radius: 10px;
    padding: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
</style>

<body id="search-page" class="search-page">
    <nav class="top-navbar">
        <div class="navbar-brand">Πόθεν Έσχες</div>
        <ul class="navbar-links">
            <li><a href="../Submit/UserDashBoard.php">Dashboard</a></li>
            <li><a href="../Search/Search.php" class="active">Αναζήτηση</a></li>
            <li><a href="../index.php" class="logout-btn">Logout</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <section class="page-header">
            <h1>Αναζητηση</h1>
        </section>

        <section class="search-section">
            <form method="POST" action="" class="search-form">
                <input
                    type="text"
                    name="search_query"
                    placeholder="Αναζητησε πολιτικους"
                    value="<?php echo htmlspecialchars($search_query); ?>"
                >
                <button type="submit">Αναζήτηση</button>
            </form>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($errors)): ?>
                <div class="results-grid">
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $row): ?>
                            <article class="result-card">
                                <h3><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></h3>
                                <p><?php echo htmlspecialchars($row['position']); ?></p>
                            </article>
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
