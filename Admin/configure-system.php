<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php"); exit;
}
$activePage = 'configure';
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ρυθμίσεις Συστήματος | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/mainstyle/global.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .page-header h1 { margin:0 0 4px; font-size:1.6em; color:#2c2c2c; text-align:left; }
        .page-header p  { margin:0 0 24px; color:#888; font-size:.9em; }

        .config-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .config-section { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:28px; overflow:hidden; display:flex; flex-direction:column; }
        .config-section-header { display:flex; align-items:center; justify-content:space-between; padding:18px 24px; background:#f8f9fa; border-bottom:1px solid #e9ecef; }
        .config-section-header h2 { margin:0; font-size:1.05em; color:#333; }
        .config-section-body { padding:24px; flex:1; }

        /* Add form */
        .add-form { display:flex; flex-direction:column; gap:10px; margin-bottom:20px; }
        .add-form-row { display:flex; gap:10px; }
        .add-form input { flex:1; padding:9px 12px; border:1px solid #ddd; border-radius:6px; font-family:'Quicksand',sans-serif; font-size:.9em; }
        .add-form input:focus { outline:none; border-color:#007bff; }
        .btn-add { background:#27ae60; color:#fff; border:none; padding:9px 18px; border-radius:6px; cursor:pointer; font-family:'Quicksand',sans-serif; font-weight:600; font-size:.9em; white-space:nowrap; transition:.2s; }
        .btn-add:hover { background:#1e8449; }

        /* List items */
        .item-list { display:flex; flex-direction:column; gap:10px; }
        .item-row { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#f8f9fa; border-radius:8px; border:1px solid #e9ecef; }
        .item-left { display:flex; align-items:center; gap:12px; }
        .item-icon { width:28px; height:28px; background:#007bff; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78em; font-weight:700; flex-shrink:0; }
        .item-name { font-weight:600; color:#333; }
        .item-sub  { font-size:.82em; color:#888; margin-top:2px; }
        .btn-del { background:#e74c3c; color:#fff; border:none; padding:12px 16px; border-radius:4px; cursor:pointer; font-size:.8em; font-family:'Quicksand',sans-serif; transition:.2s; }
        .btn-del:hover { background:#c0392b; }
        .empty-state { text-align:center; color:#aaa; padding:30px; }
    </style>
</head>
<body class="admin-page">
<div class="admin-container">
    <?php include 'includes/admin_nav.php'; ?>
    <main class="main-content">

        <div class="page-header">
            <h1>Ρυθμίσεις Συστήματος</h1>
            <p>Διαχείριση θέσεων, κομμάτων και άλλων καθολικών παραμέτρων</p>
        </div>

        <div class="config-grid">
            <!-- Positions -->
            <div class="config-section">
                <div class="config-section-header">
                    <h2>📋 Θέσεις (Positions)</h2>
                    <span id="posCount" style="font-size:.85em;color:#888;"></span>
                </div>
                <div class="config-section-body">
                    <div class="add-form">
                        <div class="add-form-row">
                            <input type="text" id="newPosition" placeholder="Νέα θέση (π.χ. Υπουργός)...">
                            <button class="btn-add" onclick="addPosition()">+ Προσθήκη</button>
                        </div>
                    </div>
                    <div class="item-list" id="positionList">
                        <div class="empty-state">Φόρτωση...</div>
                    </div>
                </div>
            </div>

            <!-- Parties -->
            <div class="config-section">
                <div class="config-section-header">
                    <h2>🏛 Κόμματα (Parties)</h2>
                    <span id="partyCount" style="font-size:.85em;color:#888;"></span>
                </div>
                <div class="config-section-body">
                    <div class="add-form">
                        <div class="add-form-row">
                            <input type="text" id="newPartyName" placeholder="Όνομα Κόμματος (π.χ. Δημοκρατικός Συναγερμός)...">
                        </div>
                        <div class="add-form-row">
                            <input type="text" id="newPartyAcr" placeholder="Ακρωνύμιο (π.χ. ΔΗΣΥ)...">
                            <button class="btn-add" onclick="addParty()">+ Προσθήκη</button>
                        </div>
                    </div>
                    <div class="item-list" id="partyList">
                        <div class="empty-state">Φόρτωση...</div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>
<script>
function loadAll(){
    loadPositions();
    loadParties();
}

function loadPositions(){
    fetch('../modules/admin/config_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=list_positions'})
    .then(r=>r.json()).then(d=>{
        if(!d.success) return;
        const list=document.getElementById('positionList');
        document.getElementById('posCount').textContent=d.positions.length+' θέσεις';
        if(!d.positions.length){ list.innerHTML='<div class="empty-state">Δεν υπάρχουν θέσεις.</div>'; return; }
        list.innerHTML=d.positions.map((p,i)=>`
            <div class="item-row">
                <div class="item-left">
                    <div class="item-icon">${i+1}</div>
                    <div>
                        <div class="item-name">${esc(p.position_name)} ${p.position_id == 999 ? '<small>(Default)</small>' : ''}</div>
                        <div class="item-sub">${p.officer_count} χρήστης/ες</div>
                    </div>
                </div>
                ${p.position_id != 999 ? `<button class="btn-del" onclick="deletePosition(${p.position_id},'${esc(p.position_name)}')">🗑</button>` : ''}
            </div>`).join('');
    });
}

function addPosition(){
    const name=document.getElementById('newPosition').value.trim();
    if(!name) return;
    fetch('../modules/admin/config_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=add_position&position_name=${encodeURIComponent(name)}`})
    .then(r=>r.json()).then(res=>{
        if(res.success){ document.getElementById('newPosition').value=''; loadPositions(); Swal.fire({icon:'success',title:'Προστέθηκε',timer:1000,showConfirmButton:false}); }
        else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error});
    });
}

function deletePosition(id,name){
    Swal.fire({title:'Διαγραφή;',text:`Διαγραφή θέσης "${name}";`,icon:'warning',showCancelButton:true,confirmButtonColor:'#e74c3c',cancelButtonText:'Ακύρωση'}).then(r=>{
        if(r.isConfirmed) fetch('../modules/admin/config_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete_position&position_id=${id}`})
        .then(r=>r.json()).then(res=>{
            if(res.success){ loadPositions(); Swal.fire({icon:'success',title:'Διαγράφηκε',timer:1000,showConfirmButton:false}); }
            else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error});
        });
    });
}

function loadParties(){
    fetch('../modules/admin/config_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=list_parties'})
    .then(r=>r.json()).then(d=>{
        if(!d.success) return;
        const list=document.getElementById('partyList');
        document.getElementById('partyCount').textContent=d.parties.length+' κόμματα';
        if(!d.parties.length){ list.innerHTML='<div class="empty-state">Δεν υπάρχουν κόμματα.</div>'; return; }
        list.innerHTML=d.parties.map((p,i)=>`
            <div class="item-row">
                <div class="item-left">
                    <div class="item-icon" style="background:#8e44ad">${p.party_acronym || (i+1)}</div>
                    <div>
                        <div class="item-name">${esc(p.party_name)}</div>
                        <div class="item-sub">${p.user_count} μέλος/η</div>
                    </div>
                </div>
                <button class="btn-del" onclick="deleteParty(${p.party_id},'${esc(p.party_name)}')">🗑</button>
            </div>`).join('');
    });
}

function addParty(){
    const name=document.getElementById('newPartyName').value.trim();
    const acr =document.getElementById('newPartyAcr').value.trim();
    if(!name) return;
    fetch('../modules/admin/config_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=add_party&party_name=${encodeURIComponent(name)}&party_acronym=${encodeURIComponent(acr)}`})
    .then(r=>r.json()).then(res=>{
        if(res.success){ document.getElementById('newPartyName').value=''; document.getElementById('newPartyAcr').value=''; loadParties(); Swal.fire({icon:'success',title:'Προστέθηκε',timer:1000,showConfirmButton:false}); }
        else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error});
    });
}

function deleteParty(id,name){
    Swal.fire({title:'Διαγραφή;',text:`Διαγραφή κόμματος "${name}";`,icon:'warning',showCancelButton:true,confirmButtonColor:'#e74c3c',cancelButtonText:'Ακύρωση'}).then(r=>{
        if(r.isConfirmed) fetch('../modules/admin/config_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete_party&party_id=${id}`})
        .then(r=>r.json()).then(res=>{
            if(res.success){ loadParties(); Swal.fire({icon:'success',title:'Διαγράφηκε',timer:1000,showConfirmButton:false}); }
            else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error});
        });
    });
}

function esc(s){ return s==null?'':String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
document.addEventListener('DOMContentLoaded', loadAll);
</script>
</body>
</html>
