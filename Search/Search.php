<?php
/* NAME: Search.php
 * DESCRIPTION: This page allows users to search for politicians by name || position and view their submissions.
 * v0.2 Under development -> submission buttons
 * This php serves as the List.php for the Second Milestone.
 * The thematic feature is the submissions and the Search of the politicians using the database(db.php)
 * 
 * v0.3 added position filtering for the search so the user can see what positions to look for added error handling aswel.
 * 
 */
session_start();

require_once "../Include/db.php";

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'User' && ($_SESSION['role'] ?? '') !== 'Politician')) {
    header("Location: ../index.php");
    exit;
}

$errors = [];
$results = [];
$keyword = trim($_GET['keyword'] ?? '');
$selected_position = trim($_GET['position'] ?? '');
$keyword_param = array_key_exists('keyword', $_GET);
$position_filter = ($selected_position !== '');
$runsearch = $keyword_param || $position_filter;

if ($_SERVER["REQUEST_METHOD"] === "GET" && $runsearch) {
    if ($position_filter && !ctype_digit($selected_position)) {
        $errors[] = "***Μη έγκυρο φίλτρο θέσης.";
    }

    if ($keyword_param && $keyword === '' && !$position_filter) {
        $errors[] = "***Πρέπει να εισάγετε όνομα, επώνυμο ή θέση για αναζήτηση.";
    }

    if (empty($errors)) {
        $sql = "
            SELECT DISTINCT users.user_id, users.first_name, users.last_name, positions.position_name AS position
            FROM users
            JOIN positions ON users.position_id = positions.position_id
            WHERE users.role = 'Politician'
        ";

        $params = [];

        if ($keyword !== '') { //change line and add this on the previous sql to make the search work with the keyword 
            $sql .= "\n AND (users.first_name LIKE :keyword_first OR users.last_name LIKE :keyword_last OR positions.position_name LIKE :keyword_position)";
            $params['keyword_first'] = '%' . $keyword . '%';
            $params['keyword_last'] = '%' . $keyword . '%';
            $params['keyword_position'] = '%' . $keyword . '%';
        }

        //add filter to the previous query
        if ($position_filter) {
            $sql .= "\n AND users.position_id = :position_id";
            $params['position_id'] = (int)$selected_position;
        }

        $sql .= "\n ORDER BY users.last_name ASC, users.first_name ASC";

        $stmt = $pdo->prepare($sql); // prepare statement
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

//creating filter positions for the search module for the users to know which positions they can search for
$position_sql = "SELECT position_id, position_name FROM positions WHERE position_name <> 'Citizen' ORDER BY position_name ASC";
$position_stmt = $pdo->prepare($position_sql);
$position_stmt->execute();
$positions = $position_stmt->fetchAll(PDO::FETCH_ASSOC);

$search_css_version = @filemtime(__DIR__ . '/../Assets/css/searchpage.css') ?: time();//force css for the filter


?>

<?php require_once "../Include/header.php"; ?>
<head>
    <link rel="stylesheet" href="../Assets/css/navbar-sticky.css">
    <link rel="stylesheet" href="../Assets/css/searchpage.css?v=<?php echo $search_css_version; ?>">
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
                <input type="hidden" name="position" value="<?php echo htmlspecialchars($selected_position); ?>">
                <button type="submit">Search</button>
            </form>

        <form method="GET" action="" class="position-filter-form">
            <label for="position">Filter by Position:</label>
                <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                <select name="position" id="position">
                    <option value="">All Positions</option>
                        <?php foreach ($positions as $position): ?>
                        <option value="<?php echo htmlspecialchars($position['position_id']); ?>"
                        <?php if ((string)$position['position_id'] === (string)$selected_position) echo 'selected'; ?>><?php echo htmlspecialchars($position['position_name']); ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <button type="submit" class="filter-btn">Apply Filter</button>
        </form>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($_SERVER["REQUEST_METHOD"] === "GET" && $runsearch && empty($errors)): ?>
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
                        <p class="no-results">Δε βρέθηκαν αποτελέσματα.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>

