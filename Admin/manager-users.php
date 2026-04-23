<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php"); exit;
}
$activePage = 'users';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Χρηστών | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/mainstyle/global.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; }
        .page-header h1 { margin:0 0 4px; font-size:1.6em; color:#2c2c2c; text-align:left; }
        .page-header p  { margin:0; color:#888; font-size:.9em; }

        .btn-primary   { background:#007bff; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-family:'Quicksand',sans-serif; font-weight:600; font-size:.9em; transition:.2s; }
        .btn-primary:hover   { background:#0056b3; transform:translateY(-1px); }
        .btn-secondary { background:#6c757d; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-family:'Quicksand',sans-serif; font-weight:600; font-size:.9em; }
        .btn-secondary:hover { background:#545b62; }

        .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
        .stat-card  { background:#fff; border-radius:10px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,.07); border-left:4px solid #ccc; }
        .stat-card.blue   { border-left-color:#007bff; }
        .stat-card.green  { border-left-color:#27ae60; }
        .stat-card.orange { border-left-color:#e67e22; }
        .stat-value { font-size:2em; font-weight:700; color:#2c2c2c; }
        .stat-label { font-size:.85em; color:#888; margin-top:4px; }

        .search-bar { margin-bottom:16px; }
        .search-bar input { padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-family:'Quicksand',sans-serif; font-size:.9em; width:320px; box-sizing:border-box; }
        .search-bar input:focus { outline:none; border-color:#007bff; }

        .table-card { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.07); overflow:hidden; }
        table   { width:100%; border-collapse:collapse; font-size:.9em; }
        thead th { background:#f8f9fa; padding:12px 16px; text-align:left; font-weight:600; color:#555; border-bottom:2px solid #e9ecef; font-size:.82em; text-transform:uppercase; letter-spacing:.5px; }
        tbody td { padding:12px 16px; border-bottom:1px solid #f0f0f0; color:#333; vertical-align:middle; }
        tbody tr:last-child td { border-bottom:none; }
        tbody tr:hover { background:#f8f9ff; }
        td.empty { text-align:center; color:#aaa; padding:30px; }

        .role-badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:.78em; font-weight:600; }
        .role-badge.admin      { background:#fff3cd; color:#856404; }
        .role-badge.user       { background:#d1ecf1; color:#0c5460; }
        .role-badge.politician { background:#d4edda; color:#155724; }

        .party-tag { display:inline-block; font-size:.75em; color:#8e44ad; font-weight:600; margin-top:2px; }

        .action-cell { display:flex; gap:6px; }
        .btn-edit   { background:#17a2b8; color:#fff; border:none; padding:5px 11px; border-radius:4px; cursor:pointer; font-size:.8em; font-family:'Quicksand',sans-serif; transition:.2s; }
        .btn-edit:hover { background:#117a8b; }
        .btn-delete { background:#e74c3c; color:#fff; border:none; padding:5px 11px; border-radius:4px; cursor:pointer; font-size:.8em; font-family:'Quicksand',sans-serif; transition:.2s; }
        .btn-delete:hover { background:#c0392b; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center; }
        .modal-overlay.active { display:flex; }
        .modal { background:#fff; border-radius:12px; width:100%; max-width:520px; box-shadow:0 20px 60px rgba(0,0,0,.3); overflow:hidden; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; padding:18px 24px; background:#f8f9fa; border-bottom:1px solid #e9ecef; }
        .modal-header h3 { margin:0; font-size:1.05em; color:#333; }
        .modal-close { background:none; border:none; font-size:1.5em; cursor:pointer; color:#aaa; line-height:1; padding:0; margin-top:0; }
        .modal-close:hover { color:#333; }
        .modal-body   { padding:22px 24px; }
        .modal-footer { padding:14px 24px; background:#f8f9fa; border-top:1px solid #e9ecef; display:flex; justify-content:flex-end; gap:10px; }
        .form-row   { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .form-group { display:flex; flex-direction:column; gap:5px; margin-bottom:14px; }
        .form-group label { font-size:.83em; font-weight:600; color:#555; }
        .form-group input,
        .form-group select { padding:9px 11px; border:1px solid #ddd; border-radius:6px; font-family:'Quicksand',sans-serif; font-size:.9em; }
        .form-group input:focus,
        .form-group select:focus { outline:none; border-color:#007bff; }
        .form-group small { color:#999; font-size:.78em; }
    </style>
</head>
<body class="admin-page">
<div class="admin-container">
    <?php include 'includes/admin_nav.php'; ?>
    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>Διαχείριση Χρηστών</h1>
                <p>Προβολή και διαχείριση όλων των εγγεγραμμένων χρηστών</p>
            </div>
            <button class="btn-primary" onclick="openAddModal()">+ Νέος Χρήστης</button>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value" id="st-total">—</div>
                <div class="stat-label">Σύνολο</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-value" id="st-users">—</div>
                <div class="stat-label">Χρήστες</div>
            </div>
            <div class="stat-card green">
                <div class="stat-value" id="st-pol">—</div>
                <div class="stat-label">Πολιτικοί</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-value" id="st-adm">—</div>
                <div class="stat-label">Διαχειριστές</div>
            </div>
        </div>

        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="🔍 Αναζήτηση χρήστη..." oninput="filterTable()">
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Ονοματεπώνυμο</th><th>Email</th><th>Τηλέφωνο</th>
                        <th>Ρόλος / Θέση / Κόμμα</th><th>Username</th><th>Ενέργειες</th>
                    </tr>
                </thead>
                <tbody id="usersBody"><tr><td colspan="7" class="empty">Φόρτωση...</td></tr></tbody>
            </table>
        </div>

    </main>
</div>

<!-- Modal -->
<div class="modal-overlay" id="userModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Νέος Χρήστης</h3>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="userForm">
                <input type="hidden" id="fUserId" name="user_id">
                <div class="form-row">
                    <div class="form-group"><label>Όνομα *</label><input type="text" id="fFirst" name="first_name" required></div>
                    <div class="form-group"><label>Επώνυμο *</label><input type="text" id="fLast" name="last_name" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Email *</label><input type="email" id="fEmail" name="email" required></div>
                    <div class="form-group"><label>Τηλέφωνο</label><input type="text" id="fPhone" name="phone"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Ρόλος *</label>
                        <select id="fRole" name="role">
                            <option value="User">User</option>
                            <option value="Politician">Politician</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Username *</label><input type="text" id="fUname" name="username" required></div>
                </div>
                <div class="form-group">
                    <label id="pwLabel">Κωδικός *</label>
                    <input type="password" id="fPw" name="password">
                    <small id="pwHint" style="display:none">Αφήστε κενό για να μη αλλάξει</small>
                </div>
                
                <div id="politicianFields" style="display:none">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Θέση *</label>
                            <select id="fPosition" name="officer_position">
                                <!-- Populated by JS -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Κόμμα</label>
                            <select id="fParty" name="party_id">
                                <option value="">Χωρίς Κόμμα (Ανεξάρτητος)</option>
                                <!-- Populated by JS -->
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeModal()">Ακύρωση</button>
            <button class="btn-primary"   onclick="saveUser()">Αποθήκευση</button>
        </div>
    </div>
</div>

<script>
let editMode = false;
let allUsers = []; 
let positions = [];
let parties = [];

document.addEventListener('DOMContentLoaded', () => {
    loadAll();
    document.getElementById('fRole').addEventListener('change', togglePolFields);
});

function loadAll(){
    loadUsers();
    loadPositions();
    loadParties();
}

function togglePolFields() {
    const role = document.getElementById('fRole').value;
    document.getElementById('politicianFields').style.display = (role === 'Politician') ? 'block' : 'none';
}

function loadPositions() {
    fetch('../modules/admin/config_api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=list_positions'})
    .then(r=>r.json()).then(d => {
        if(d.success) {
            positions = d.positions;
            const sel = document.getElementById('fPosition');
            sel.innerHTML = positions.map(p => `<option value="${p.position_id}">${esc(p.position_name)}</option>`).join('');
        }
    });
}

function loadParties() {
    fetch('../modules/admin/config_api.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=list_parties'})
    .then(r=>r.json()).then(d => {
        if(d.success) {
            parties = d.parties;
            const sel = document.getElementById('fParty');
            const options = parties.map(p => `<option value="${p.party_id}">${esc(p.party_name)} (${esc(p.party_acronym)})</option>`).join('');
            sel.innerHTML = '<option value="">Χωρίς Κόμμα (Ανεξάρτητος)</option>' + options;
        }
    });
}

function esc(s){ return s==null?'':String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function loadUsers(){
    fetch('../modules/admin/users_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=list'})
    .then(r=>r.json()).then(d=>{ if(d.success) renderUsers(d.users); });
}

function renderUsers(users){
    allUsers = users; 
    document.getElementById('st-total').textContent = users.length;
    document.getElementById('st-users').textContent = users.filter(u=>u.role==='User').length;
    document.getElementById('st-pol').textContent   = users.filter(u=>u.role==='Politician').length;
    document.getElementById('st-adm').textContent   = users.filter(u=>u.role==='Admin').length;

    const tb = document.getElementById('usersBody');
    if(!users.length){ tb.innerHTML='<tr><td colspan="7" class="empty">Δεν βρέθηκαν χρήστες</td></tr>'; return; }
    tb.innerHTML = users.map((u, index)=>`
        <tr>
            <td>${esc(u.user_id)}</td>
            <td><strong>${esc(u.first_name)} ${esc(u.last_name)}</strong></td>
            <td>${esc(u.email)}</td>
            <td>${esc(u.Phone||'—')}</td>
            <td>
                <span class="role-badge ${u.role.toLowerCase()}">${esc(u.role)}</span>
                ${u.position_id != 999 ? `<br><small style="color:#555;font-weight:600">${esc(u.position_name)}</small>` : ''}
                ${u.party_id ? `<br><span class="party-tag">🏛 ${esc(u.party_name)}</span>` : ''}
            </td>
            <td>${esc(u.username||'—')}</td>
            <td class="action-cell">
                <button class="btn-edit"   onclick="editUser(${index})">✏</button>
                <button class="btn-delete" onclick="deleteUser(${u.user_id},'${esc(u.first_name)} ${esc(u.last_name)}')">🗑</button>
            </td>
        </tr>`).join('');
}

function filterTable(){
    const q=document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#usersBody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(q)?'':'none');
}

function openAddModal(){
    editMode=false;
    document.getElementById('modalTitle').textContent='Νέος Χρήστης';
    document.getElementById('userForm').reset();
    document.getElementById('fUserId').value='';
    document.getElementById('fPw').required=true;
    document.getElementById('pwLabel').textContent='Κωδικός *';
    document.getElementById('pwHint').style.display='none';
    togglePolFields();
    document.getElementById('userModal').classList.add('active');
}

function editUser(index){
    const u = allUsers[index];
    editMode=true;
    document.getElementById('modalTitle').textContent='Επεξεργασία Χρήστη';
    document.getElementById('fUserId').value=u.user_id;
    document.getElementById('fFirst').value=u.first_name;
    document.getElementById('fLast').value=u.last_name;
    document.getElementById('fEmail').value=u.email;
    document.getElementById('fPhone').value=u.Phone||'';
    document.getElementById('fRole').value=u.role;
    document.getElementById('fUname').value=u.username||'';
    document.getElementById('fPw').value='';
    document.getElementById('fPw').required=false;
    document.getElementById('pwLabel').textContent='Κωδικός';
    document.getElementById('pwHint').style.display='block';
    
    document.getElementById('fPosition').value = u.position_id || '999';
    document.getElementById('fParty').value    = u.party_id || '';
    
    togglePolFields();
    document.getElementById('userModal').classList.add('active');
}

function closeModal(){ document.getElementById('userModal').classList.remove('active'); }

function saveUser(){
    const fd=new FormData(document.getElementById('userForm'));
    fd.append('action', editMode?'edit':'add');
    fetch('../modules/admin/users_api.php',{method:'POST',body:fd})
    .then(r=>r.json()).then(res=>{
        if(res.success){ closeModal(); Swal.fire({icon:'success',title:'Επιτυχία',text:res.message,timer:1500,showConfirmButton:false}); loadUsers(); }
        else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error});
    });
}

function deleteUser(id,name){
    Swal.fire({title:'Διαγραφή;',text:`Διαγραφή χρήστη "${name}";`,icon:'warning',showCancelButton:true,
        confirmButtonColor:'#e74c3c',cancelButtonColor:'#555',confirmButtonText:'Ναι',cancelButtonText:'Ακύρωση'})
    .then(r=>{ if(r.isConfirmed)
        fetch('../modules/admin/users_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete&user_id=${id}`})
        .then(r=>r.json()).then(res=>{
            if(res.success){ Swal.fire({icon:'success',title:'Διαγράφηκε',timer:1200,showConfirmButton:false}); loadUsers(); }
            else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error});
        });
    });
}

document.getElementById('userModal').addEventListener('click',function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>
