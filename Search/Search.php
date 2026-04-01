<?php
require_once "../Include/header.php";
Require_once "../Include/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'User') {
    header("Location: ../index.php");
    exit;
}

$errors = [];
if($_SERVER["REQUEST_METHOD"] === "POST") {
    $search_query = trim($_POST['search_query'] ?? '');

    if (empty($search_query)) {
        $errors[] = "Πρέπει να εισάγετε όνομα ή επώνυμο ή θέση για αναζήτηση.";
    }

    if (empty($errors)) {
        $sql = "SELECT first_name, last_name, 'Politician' AS source 
        FROM users
        WHERE (first_name LIKE :query OR last_name LIKE :query)
        AND role = 'Politician'

        UNION

        SELECT users.first_name, users.last_name, positions.position AS source
        FROM users
        JOIN govofficers ON users.user_id = govofficers.user_id
        JOIN positions ON govofficers.officer_position = positions.position_id
        WHERE positions.position LIKE :query";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['query' => '%' . $search_query . '%']);

        $results = $stmt->fetchAll(); }

    

}

?>

<body>
    <div class="search-container">
    <aside class="sidebar">
            <h2>Search Page</h2>
            <p>Πόθεν Έσχες</p>
            <nav>
                <ul>
                    <li><a href="Subscribe.html">Subscribe Tab</a></li>
                    <li><a href="Statistics.html">Statistics Tab</a></li>
                    <li><a href="../index.php" class="logout-btn">Logout</a></li>
                </ul>
            </nav>
        </aside>
    <content class = "main-content">
        <h2>Search Page</h2>
        <p>  </p>
    </content>
    </div>
</body>
<main class="main-searchcontent">
</main>


</html>

<?php
require_once "../Include/footer.php";
?>
