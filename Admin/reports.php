<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php"); exit;
}
require_once '../Include/db.php';

// KPI queries
$total_users    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_admins   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='Admin'")->fetchColumn();
$total_pol      = $pdo->query("SELECT COUNT(*) FROM users WHERE role='Politician'")->fetchColumn();
$total_pos      = $pdo->query("SELECT COUNT(*) FROM positions")->fetchColumn();

// Charts data
$roles = $pdo->query("SELECT role, COUNT(*) AS cnt FROM users GROUP BY role")->fetchAll();
$officers_by_pos = $pdo->query(
    "SELECT p.position_name, COUNT(u.user_id) AS cnt
     FROM positions p LEFT JOIN users u ON p.position_id=u.position_id
     GROUP BY p.position_id, p.position_name ORDER BY cnt DESC"
)->fetchAll();

$roleLabels  = json_encode(array_column($roles, 'role'));
$roleCounts  = json_encode(array_map('intval', array_column($roles, 'cnt')));
$posLabels   = json_encode(array_column($officers_by_pos, 'position_name'));
$posCounts   = json_encode(array_map('intval', array_column($officers_by_pos, 'cnt')));

$activePage = 'reports';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αναφορές | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/mainstyle/global.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .page-header h1 { margin:0 0 4px; font-size:1.6em; color:#2c2c2c; text-align:left; }
        .page-header p  { margin:0 0 24px; color:#888; font-size:.9em; }

        .kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
        .kpi-card { background:#fff; border-radius:10px; padding:22px; box-shadow:0 2px 8px rgba(0,0,0,.07); text-align:center; border-top:4px solid #ccc; }
        .kpi-card.blue   { border-top-color:#007bff; }
        .kpi-card.green  { border-top-color:#27ae60; }
        .kpi-card.orange { border-top-color:#e67e22; }
        .kpi-card.purple { border-top-color:#8e44ad; }
        .kpi-value { font-size:2.4em; font-weight:700; color:#2c2c2c; line-height:1; }
        .kpi-label { font-size:.85em; color:#888; margin-top:6px; }

        .charts-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:28px; }
        .chart-card { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.07); padding:24px; }
        .chart-card h3 { margin:0 0 16px; font-size:1em; color:#555; border-bottom:1px solid #f0f0f0; padding-bottom:10px; }
        .chart-wrap { position:relative; height:240px; }

        .table-card { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.07); overflow:hidden; }
        .table-card h3 { margin:0; padding:16px 20px; font-size:1em; color:#555; border-bottom:1px solid #f0f0f0; }
        table { width:100%; border-collapse:collapse; font-size:.9em; }
        thead th { background:#f8f9fa; padding:10px 16px; text-align:left; font-size:.82em; font-weight:600; color:#555; text-transform:uppercase; letter-spacing:.4px; border-bottom:2px solid #e9ecef; }
        tbody td { padding:11px 16px; border-bottom:1px solid #f0f0f0; color:#333; }
        tbody tr:last-child td { border-bottom:none; }
        tbody tr:hover { background:#f8f9ff; }
        .badge { display:inline-block; padding:3px 9px; border-radius:12px; font-size:.78em; font-weight:600; }
        .badge.admin      { background:#fff3cd; color:#856404; }
        .badge.user       { background:#d1ecf1; color:#0c5460; }
        .badge.politician { background:#d4edda; color:#155724; }
        td.no-data { text-align:center; color:#bbb; padding:24px; }
    </style>
</head>
<body class="admin-page">
<div class="admin-container">
    <?php include 'includes/admin_nav.php'; ?>
    <main class="main-content">

        <div class="page-header">
            <h1>Αναφορές &amp; Στατιστικά</h1>
            <p>Συγκεντρωτικά στοιχεία του συστήματος Πόθεν Έσχες</p>
        </div>

        <!-- KPIs -->
        <div class="kpi-row">
            <div class="kpi-card blue">
                <div class="kpi-value"><?= $total_users ?></div>
                <div class="kpi-label">Σύνολο Χρηστών</div>
            </div>
            <div class="kpi-card green">
                <div class="kpi-value"><?= $total_pol ?></div>
                <div class="kpi-label">Πολιτικοί</div>
            </div>
            <div class="kpi-card orange">
                <div class="kpi-value"><?= $total_admins ?></div>
                <div class="kpi-label">Διαχειριστές</div>
            </div>
            <div class="kpi-card purple">
                <div class="kpi-value"><?= $total_pos ?></div>
                <div class="kpi-label">Θέσεις</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-row">
            <div class="chart-card">
                <h3>👥 Χρήστες ανά Ρόλο</h3>
                <div class="chart-wrap"><canvas id="roleChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>🏛 Αξιωματούχοι ανά Θέση</h3>
                <div class="chart-wrap"><canvas id="posChart"></canvas></div>
            </div>
        </div>

        <!-- Positions table -->
        <div class="table-card">
            <h3>📋 Αναλυτικά: Θέσεις &amp; Αξιωματούχοι</h3>
            <table>
                <thead><tr><th>Θέση</th><th>Αριθμός Αξιωματούχων</th></tr></thead>
                <tbody>
                <?php if(empty($officers_by_pos)): ?>
                    <tr><td colspan="2" class="no-data">Δεν υπάρχουν δεδομένα</td></tr>
                <?php else: foreach($officers_by_pos as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['position_name']) ?></td>
                        <td><?= (int)$row['cnt'] ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>
<script>
const roleColors = ['#007bff','#27ae60','#e67e22','#8e44ad','#e74c3c'];

new Chart(document.getElementById('roleChart'), {
    type: 'doughnut',
    data: {
        labels: <?= $roleLabels ?>,
        datasets: [{ data: <?= $roleCounts ?>, backgroundColor: roleColors, borderWidth: 2 }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
});

new Chart(document.getElementById('posChart'), {
    type: 'bar',
    data: {
        labels: <?= $posLabels ?>,
        datasets: [{
            label: 'Αξιωματούχοι',
            data: <?= $posCounts ?>,
            backgroundColor: '#007bff',
            borderRadius: 6
        }]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true, ticks:{ stepSize:1 } } }
    }
});
</script>
</body>
</html>
