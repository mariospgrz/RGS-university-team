<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php"); exit;
}
$activePage = 'submissions';
$currentYear = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Υποβολών | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/mainstyle/global.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; }
        .page-header h1 { margin:0 0 4px; font-size:1.6em; color:#2c2c2c; text-align:left; }
        .page-header p  { margin:0; color:#888; font-size:.9em; }

        /* KPIs */
        .kpi-row { display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:24px; }
        .kpi-card { background:#fff; border-radius:10px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,.07); border-left:4px solid #ccc; }
        .kpi-card.blue   { border-left-color:#007bff; }
        .kpi-card.yellow { border-left-color:#f39c12; }
        .kpi-card.green  { border-left-color:#27ae60; }
        .kpi-card.red    { border-left-color:#e74c3c; }
        .kpi-card.purple { border-left-color:#8e44ad; }
        .kpi-value { font-size:1.9em; font-weight:700; color:#2c2c2c; }
        .kpi-label { font-size:.8em; color:#888; margin-top:4px; }

        /* Filters */
        .filters { display:flex; gap:12px; align-items:center; margin-bottom:18px; flex-wrap:wrap; }
        .filters select, .filters input {
            padding:9px 12px; border:1px solid #ddd; border-radius:6px;
            font-family:'Quicksand',sans-serif; font-size:.9em; background:#fff;
        }
        .filters select:focus, .filters input:focus { outline:none; border-color:#007bff; }
        .btn-filter { background:#007bff; color:#fff; border:none; padding:9px 18px; border-radius:6px; cursor:pointer; font-family:'Quicksand',sans-serif; font-weight:600; font-size:.9em; transition:.2s; }
        .btn-filter:hover { background:#0056b3; }

        /* Table */
        .table-card { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.07); overflow:hidden; margin-bottom:24px; }
        .table-card-header { padding:14px 20px; border-bottom:1px solid #f0f0f0; font-weight:600; color:#555; font-size:.95em; }
        table   { width:100%; border-collapse:collapse; font-size:.88em; }
        thead th { background:#f8f9fa; padding:10px 14px; text-align:left; font-size:.8em; font-weight:600; color:#666; border-bottom:2px solid #e9ecef; text-transform:uppercase; letter-spacing:.4px; }
        tbody td { padding:11px 14px; border-bottom:1px solid #f0f0f0; color:#333; vertical-align:middle; }
        tbody tr:last-child td { border-bottom:none; }
        tbody tr:hover { background:#f8f9ff; }
        td.empty { text-align:center; color:#bbb; padding:32px; }

        /* Badges */
        .s-badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:.78em; font-weight:600; }
        .s-badge.Pending  { background:#fff3cd; color:#856404; }
        .s-badge.Approved { background:#d4edda; color:#155724; }
        .s-badge.Rejected { background:#f8d7da; color:#721c24; }
        .r-badge { display:inline-block; padding:3px 9px; border-radius:12px; font-size:.78em; font-weight:600; }
        .r-badge.politician { background:#d4edda; color:#155724; }
        .r-badge.user       { background:#d1ecf1; color:#0c5460; }

        /* Action buttons */
        .action-cell { display:flex; gap:5px; flex-wrap:wrap; }
        .btn-approve { background:#27ae60; color:#fff; border:none; padding:4px 10px; border-radius:4px; cursor:pointer; font-size:.78em; font-family:'Quicksand',sans-serif; transition:.2s; }
        .btn-approve:hover { background:#1e8449; }
        .btn-reject  { background:#e74c3c; color:#fff; border:none; padding:4px 10px; border-radius:4px; cursor:pointer; font-size:.78em; font-family:'Quicksand',sans-serif; transition:.2s; }
        .btn-reject:hover  { background:#c0392b; }
        .btn-pending { background:#f39c12; color:#fff; border:none; padding:4px 10px; border-radius:4px; cursor:pointer; font-size:.78em; font-family:'Quicksand',sans-serif; transition:.2s; }
        .btn-pending:hover { background:#d68910; }
        .btn-del     { background:#6c757d; color:#fff; border:none; padding:4px 10px; border-radius:4px; cursor:pointer; font-size:.78em; font-family:'Quicksand',sans-serif; transition:.2s; }
        .btn-del:hover     { background:#545b62; }

        /* Non-submitters */
        .missing-list { display:flex; flex-direction:column; gap:8px; }
        .missing-item { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#fff8f8; border:1px solid #f5c6cb; border-radius:8px; }
        .missing-name { font-weight:600; color:#333; }
        .missing-pos  { font-size:.82em; color:#888; margin-top:2px; }

        /* Notes modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
        .modal-overlay.active { display:flex; }
        .modal { background:#fff; border-radius:12px; width:100%; max-width:460px; box-shadow:0 20px 60px rgba(0,0,0,.3); overflow:hidden; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; padding:16px 22px; background:#f8f9fa; border-bottom:1px solid #e9ecef; }
        .modal-header h3 { margin:0; font-size:1.05em; color:#333; }
        .modal-close { background:none; border:none; font-size:1.5em; cursor:pointer; color:#aaa; line-height:1; padding:0; margin-top:0; }
        .modal-close:hover { color:#333; }
        .modal-body   { padding:20px 22px; }
        .modal-footer { padding:14px 22px; background:#f8f9fa; border-top:1px solid #e9ecef; display:flex; justify-content:flex-end; gap:10px; }
        .form-group   { display:flex; flex-direction:column; gap:5px; }
        .form-group label { font-size:.83em; font-weight:600; color:#555; }
        .form-group select, .form-group textarea { padding:9px 11px; border:1px solid #ddd; border-radius:6px; font-family:'Quicksand',sans-serif; font-size:.9em; }
        .form-group select:focus, .form-group textarea:focus { outline:none; border-color:#007bff; }
        .btn-save { background:#007bff; color:#fff; border:none; padding:9px 18px; border-radius:6px; cursor:pointer; font-family:'Quicksand',sans-serif; font-weight:600; font-size:.9em; transition:.2s; }
        .btn-save:hover { background:#0056b3; }
        .btn-cancel { background:#6c757d; color:#fff; border:none; padding:9px 18px; border-radius:6px; cursor:pointer; font-family:'Quicksand',sans-serif; font-weight:600; font-size:.9em; }
        .btn-cancel:hover { background:#545b62; }
    </style>
</head>
<body class="admin-page">
<div class="admin-container">
    <?php include 'includes/admin_nav.php'; ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>Διαχείριση Υποβολών</h1>
                <p>Έλεγχος και διαχείριση υποβολών Πόθεν Έσχες ανά έτος</p>
            </div>
        </div>

        <!-- KPIs -->
        <div class="kpi-row">
            <div class="kpi-card blue">  <div class="kpi-value" id="k-total">—</div><div class="kpi-label">Σύνολο Υποβολών</div></div>
            <div class="kpi-card yellow"><div class="kpi-value" id="k-pend">—</div> <div class="kpi-label">Σε Εκκρεμότητα</div></div>
            <div class="kpi-card green"> <div class="kpi-value" id="k-appr">—</div> <div class="kpi-label">Εγκεκριμένες</div></div>
            <div class="kpi-card red">   <div class="kpi-value" id="k-rej">—</div>  <div class="kpi-label">Απορριφθείσες</div></div>
            <div class="kpi-card purple"><div class="kpi-value" id="k-miss">—</div> <div class="kpi-label">Δεν Υπέβαλαν</div></div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <select id="fYear">
                <option value="">Όλα τα έτη</option>
                <?php for($y = $currentYear; $y >= $currentYear - 10; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <select id="fStatus">
                <option value="">Όλες οι καταστάσεις</option>
                <option value="Pending">Σε Εκκρεμότητα</option>
                <option value="Approved">Εγκεκριμένη</option>
                <option value="Rejected">Απορριφθείσα</option>
            </select>
            <input type="text" id="fSearch" placeholder="🔍 Αναζήτηση ονόματος..." oninput="filterTable()">
            <button class="btn-filter" onclick="loadAll()">Εφαρμογή</button>
        </div>

        <!-- Submissions table -->
        <div class="table-card">
            <div class="table-card-header">📄 Υποβολές</div>
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Χρήστης</th><th>Ρόλος</th><th>Έτος</th>
                        <th>Ημ/νία Υποβολής</th><th>Κατάσταση</th><th>Σημειώσεις</th><th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody id="subBody"><tr><td colspan="8" class="empty">Φόρτωση...</td></tr></tbody>
            </table>
        </div>

        <!-- Non-submitters -->
        <div class="table-card">
            <div class="table-card-header">⚠️ Αξιωματούχοι που ΔΕΝ υπέβαλαν για το έτος <span id="missYear"><?= $currentYear ?></span></div>
            <div style="padding:16px;" id="missingWrap">
                <div class="empty" style="color:#bbb;padding:20px;text-align:center;">Φόρτωση...</div>
            </div>
        </div>

    </main>
</div>

<!-- Status modal -->
<div class="modal-overlay" id="statusModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Ενημέρωση Κατάστασης</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="mSubId">
            <div class="form-group" style="margin-bottom:14px;">
                <label>Κατάσταση</label>
                <select id="mStatus">
                    <option value="Pending">Σε Εκκρεμότητα</option>
                    <option value="Approved">Εγκεκριμένη</option>
                    <option value="Rejected">Απορριφθείσα</option>
                </select>
            </div>
            <div class="form-group">
                <label>Σημειώσεις</label>
                <textarea id="mNotes" rows="3" placeholder="Προαιρετικές σημειώσεις..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closeModal()">Ακύρωση</button>
            <button class="btn-save"   onclick="saveStatus()">Αποθήκευση</button>
        </div>
    </div>
</div>

<script>
const API = '../modules/admin/submissions_api.php';
function post(data){ return fetch(API,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:new URLSearchParams(data).toString()}).then(r=>r.json()); }
function esc(s){ return s==null?'':String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmt(dt){ return dt ? new Date(dt).toLocaleDateString('el-GR',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) : '—'; }

function loadAll(){
    const year = document.getElementById('fYear').value;
    const status = document.getElementById('fStatus').value;
    document.getElementById('missYear').textContent = year || '<?= $currentYear ?>';
    Promise.all([
        post({action:'list', year, status}),
        post({action:'non_submitters', year: year || '<?= $currentYear ?>'})
    ]).then(([subData, missData]) => {
        if(subData.success) renderSubs(subData.submissions);
        if(missData.success) renderMissing(missData.non_submitters);
    });
}

function renderSubs(subs){
    const t = document.getElementById('k-total');
    const p = document.getElementById('k-pend');
    const a = document.getElementById('k-appr');
    const r = document.getElementById('k-rej');
    t.textContent = subs.length;
    p.textContent = subs.filter(s=>s.status==='Pending').length;
    a.textContent = subs.filter(s=>s.status==='Approved').length;
    r.textContent = subs.filter(s=>s.status==='Rejected').length;

    const tb = document.getElementById('subBody');
    if(!subs.length){ tb.innerHTML='<tr><td colspan="8" class="empty">Δεν βρέθηκαν υποβολές για τα επιλεγμένα κριτήρια</td></tr>'; return; }
    tb.innerHTML = subs.map(s=>`
        <tr data-name="${esc(s.first_name)} ${esc(s.last_name)}">
            <td>${esc(s.submission_id)}</td>
            <td><strong>${esc(s.first_name)} ${esc(s.last_name)}</strong><br><small style="color:#aaa">${esc(s.email)}</small></td>
            <td><span class="r-badge ${s.role.toLowerCase()}">${esc(s.role)}</span></td>
            <td>${esc(s.year)}</td>
            <td>${fmt(s.submitted_at)}</td>
            <td><span class="s-badge ${esc(s.status)}">${statusLabel(s.status)}</span></td>
            <td style="max-width:140px;font-size:.82em;color:#666">${esc(s.notes)||'—'}</td>
            <td class="action-cell">
                <button class="btn-approve" onclick="quickStatus(${s.submission_id},'Approved')">✓ Έγκριση</button>
                <button class="btn-reject"  onclick="quickStatus(${s.submission_id},'Rejected')">✗ Απόρριψη</button>
                <button class="btn-pending" onclick="openModal(${s.submission_id},'${esc(s.status)}','${esc(s.notes||'')}')">✎</button>
                <button class="btn-del"     onclick="deleteSub(${s.submission_id})">🗑</button>
            </td>
        </tr>`).join('');
    filterTable();
}

function statusLabel(s){ return {Pending:'Εκκρεμεί',Approved:'Εγκρίθηκε',Rejected:'Απορρίφθηκε'}[s]||s; }

function renderMissing(list){
    const w = document.getElementById('missingWrap');
    document.getElementById('k-miss').textContent = list.length;
    if(!list.length){ w.innerHTML='<div style="color:#27ae60;text-align:center;padding:20px;font-weight:600">✓ Όλοι οι αξιωματούχοι έχουν υποβάλει για το επιλεγμένο έτος</div>'; return; }
    w.innerHTML='<div class="missing-list">'+list.map(u=>`
        <div class="missing-item">
            <div>
                <div class="missing-name">${esc(u.first_name)} ${esc(u.last_name)}</div>
                <div class="missing-pos">${esc(u.position_name||'Χωρίς θέση')} · ${esc(u.email)}</div>
            </div>
            <span style="color:#e74c3c;font-size:.82em;font-weight:600">Δεν υπέβαλε</span>
        </div>`).join('')+'</div>';
}

function filterTable(){
    const q = document.getElementById('fSearch').value.toLowerCase();
    document.querySelectorAll('#subBody tr[data-name]').forEach(r=>
        r.style.display = r.dataset.name.toLowerCase().includes(q) ? '' : 'none'
    );
}

function quickStatus(id, status){
    post({action:'update_status', submission_id:id, status, notes:''})
    .then(res=>{ if(res.success){ Swal.fire({icon:'success',title:statusLabel(status),timer:1000,showConfirmButton:false}); loadAll(); } else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error}); });
}

function openModal(id, status, notes){
    document.getElementById('mSubId').value = id;
    document.getElementById('mStatus').value = status;
    document.getElementById('mNotes').value  = notes;
    document.getElementById('statusModal').classList.add('active');
}
function closeModal(){ document.getElementById('statusModal').classList.remove('active'); }

function saveStatus(){
    const id     = document.getElementById('mSubId').value;
    const status = document.getElementById('mStatus').value;
    const notes  = document.getElementById('mNotes').value;
    post({action:'update_status', submission_id:id, status, notes})
    .then(res=>{ if(res.success){ closeModal(); Swal.fire({icon:'success',title:'Ενημερώθηκε',timer:1000,showConfirmButton:false}); loadAll(); } else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error}); });
}

function deleteSub(id){
    Swal.fire({title:'Διαγραφή;',text:'Η υποβολή θα διαγραφεί οριστικά.',icon:'warning',showCancelButton:true,
        confirmButtonColor:'#e74c3c',cancelButtonColor:'#555',confirmButtonText:'Ναι',cancelButtonText:'Ακύρωση'})
    .then(r=>{ if(r.isConfirmed) post({action:'delete',submission_id:id}).then(res=>{ if(res.success){ Swal.fire({icon:'success',title:'Διαγράφηκε',timer:1000,showConfirmButton:false}); loadAll(); } else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error}); }); });
}

document.getElementById('statusModal').addEventListener('click',function(e){ if(e.target===this) closeModal(); });
document.addEventListener('DOMContentLoaded', loadAll);
</script>
</body>
</html>
