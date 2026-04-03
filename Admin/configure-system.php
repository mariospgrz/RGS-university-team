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

        .config-section { background:#fff; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,.07); margin-bottom:28px; overflow:hidden; }
        .config-section-header { display:flex; align-items:center; justify-content:space-between; padding:18px 24px; background:#f8f9fa; border-bottom:1px solid #e9ecef; }
        .config-section-header h2 { margin:0; font-size:1.05em; color:#333; }
        .config-section-body { padding:24px; }

        /* Add form */
        .add-form { display:flex; gap:10px; margin-bottom:20px; }
        .add-form input { flex:1; padding:9px 12px; border:1px solid #ddd; border-radius:6px; font-family:'Quicksand',sans-serif; font-size:.9em; }
        .add-form input:focus { outline:none; border-color:#007bff; }
        .btn-add { background:#27ae60; color:#fff; border:none; padding:9px 18px; border-radius:6px; cursor:pointer; font-family:'Quicksand',sans-serif; font-weight:600; font-size:.9em; white-space:nowrap; transition:.2s; }
        .btn-add:hover { background:#1e8449; }

        /* Position list */
        .position-list { display:flex; flex-direction:column; gap:10px; }
        .position-item { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:#f8f9fa; border-radius:8px; border:1px solid #e9ecef; }
        .position-item-left { display:flex; align-items:center; gap:12px; }
        .position-num { width:28px; height:28px; background:#007bff; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78em; font-weight:700; flex-shrink:0; }
        .position-name { font-weight:600; color:#333; }
        .position-count { font-size:.82em; color:#888; margin-top:2px; }
        .btn-del { background:#e74c3c; color:#fff; border:none; padding:5px 12px; border-radius:4px; cursor:pointer; font-size:.8em; font-family:'Quicksand',sans-serif; transition:.2s; }
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
            <p>Διαχείριση θέσεων και άλλων καθολικών παραμέτρων του συστήματος</p>
        </div>

        <!-- Positions -->
        <div class="config-section">
            <div class="config-section-header">
                <h2>📋 Θέσεις (Positions)</h2>
                <span id="posCount" style="font-size:.85em;color:#888;"></span>
            </div>
            <div class="config-section-body">
                <div class="add-form">
                    <input type="text" id="newPosition" placeholder="Νέα θέση (π.χ. Υπουργός Οικονομικών)..." onkeydown="if(event.key==='Enter')addPosition()">
                    <button class="btn-add" onclick="addPosition()">+ Προσθήκη</button>
                </div>
                <div class="position-list" id="positionList">
                    <div class="empty-state">Φόρτωση...</div>
                </div>
            </div>
        </div>

    </main>
</div>
<script>
function loadPositions(){
    fetch('../modules/admin/config_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'action=list_positions'})
    .then(r=>r.json()).then(d=>{
        if(!d.success) return;
        const list=document.getElementById('positionList');
        document.getElementById('posCount').textContent=d.positions.length+' θέσεις';
        if(!d.positions.length){ list.innerHTML='<div class="empty-state">Δεν υπάρχουν θέσεις. Προσθέστε μία παραπάνω.</div>'; return; }
        list.innerHTML=d.positions.map((p,i)=>`
            <div class="position-item" id="pos-${p.position_id}">
                <div class="position-item-left">
                    <div class="position-num">${i+1}</div>
                    <div>
                        <div class="position-name">${esc(p.position_name)}</div>
                        <div class="position-count">${p.officer_count} αξιωματούχος/οι</div>
                    </div>
                </div>
                <button class="btn-del" onclick="deletePosition(${p.position_id},'${esc(p.position_name)}')">🗑 Διαγραφή</button>
            </div>`).join('');
    });
}

function addPosition(){
    const name=document.getElementById('newPosition').value.trim();
    if(!name) return;
    fetch('../modules/admin/config_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=add_position&position_name=${encodeURIComponent(name)}`})
    .then(r=>r.json()).then(res=>{
        if(res.success){ document.getElementById('newPosition').value=''; loadPositions(); Swal.fire({icon:'success',title:'Προστέθηκε',timer:1200,showConfirmButton:false}); }
        else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error});
    });
}

function deletePosition(id,name){
    Swal.fire({title:'Διαγραφή;',text:`Διαγραφή θέσης "${name}";`,icon:'warning',showCancelButton:true,
        confirmButtonColor:'#e74c3c',cancelButtonColor:'#555',confirmButtonText:'Ναι',cancelButtonText:'Ακύρωση'})
    .then(r=>{ if(r.isConfirmed)
        fetch('../modules/admin/config_api.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete_position&position_id=${id}`})
        .then(r=>r.json()).then(res=>{
            if(res.success){ loadPositions(); Swal.fire({icon:'success',title:'Διαγράφηκε',timer:1200,showConfirmButton:false}); }
            else Swal.fire({icon:'error',title:'Σφάλμα',text:res.error});
        });
    });
}

function esc(s){ return s==null?'':String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

document.addEventListener('DOMContentLoaded', loadPositions);
</script>
</body>
</html>
