<?php require_once "../Include/config.php";?>

<?php
session_start();

if (!isset($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'User' && ($_SESSION['role'] ?? '') !== 'Politician')) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Πόθεν Έσχες System</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300..700&family=Quicksand:wght@300..700&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif !important;
        }
        :root { --primary-color: #2c3e50; --accent-color: #3498db; --danger: #e74c3c; --success: #27ae60; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.5; color: #333; max-width: 1000px; margin: 40px auto; padding: 40px; border: 1px solid #ddd; background-color: #fff; }

        /* Sticky UI */
        .sticky-nav { position: fixed; top: 20px; left: 20px; z-index: 1000; }
        .btn-apply { background: var(--primary-color); color: white; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }

        header { text-align: center; border-bottom: 3px double #000; margin-bottom: 30px; padding-bottom: 10px; }
        .section-title { background-color: #f8f9fa; padding: 12px; border-left: 6px solid var(--primary-color); font-weight: bold; margin-top: 35px; text-transform: uppercase; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; }

        /* Inputs & Tables */
        .data-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 20px 0; }
        .field { display: flex; flex-direction: column; }
        .label { font-size: 0.8em; font-weight: bold; color: #666; margin-bottom: 4px; }
        input, textarea, select { padding: 8px; border: 1px solid #ccc; border-radius: 3px; font-size: 14px; }

        table { width: 100%; border-collapse: collapse; margin: 10px 0; background: white; }
        th, td { border: 1px solid #bbb; padding: 10px; text-align: left; }
        th { background: #f2f2f2; font-size: 0.85em; }

        .btn-row-add { background: var(--success); color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.75em; }
        .btn-row-del { background: var(--danger); color: white; border: none; padding: 4px 8px; cursor: pointer; border-radius: 3px; }

        .form-hint { font-size: 0.8em; color: #777; font-style: italic; margin-bottom: 5px; }

        @media print {
            .sticky-nav, .btn-row-add, .btn-row-del, .form-hint { display: none !important; }
            body { border: none; margin: 0; padding: 20px; }
            input { border: none !important; }
        }
    </style>
</head>
<body class="body">

<div class="sticky-nav">
    <script src="../Include/save_pdf.js"></script>
    <button class="btn-apply" onclick="printAndSavePdf()">Εκτύπωση / Αποθήκευση PDF</button>
</div>

<header>
    <h1>ΔΗΛΩΣΗ ΠΕΡΙΟΥΣΙΑΚΩΝ ΣΤΟΙΧΕΙΩΝ</h1>
    <p>Σύμφωνα με τον Νόμο [49(1), 269 (1) του 2004]</p>
</header>

<div class="section-title">1. ΠΡΟΣΩΠΙΚΑ ΣΤΟΙΧΕΙΑ</div>
<div class="data-grid">
    <div class="field"><span class="label">Ονοματεπώνυμο</span><input type="text"></div>
    <div class="field"><span class="label">Ιδιότητα-Αξίωμα</span><input type="text"></div>
    <div class="field"><span class="label">Διεύθυνση</span><input type="text"></div>
    <div class="field"><span class="label">Ημερομηνία Γεννήσεως</span><input type="date"></div>
    <div class="field"><span class="label">Αριθμός Ταυτότητας</span><input type="text"></div>
    <div class="field"><span class="label">Αριθμός ανήλικων τέκνων</span><input type="number" min="0"></div>
</div>

<div class="section-title">
    2. ΜΕΤΑΦΟΡΙΚΑ ΜΕΣΑ (Αυτοκίνητα, Σκάφη κ.α.)
    <button class="btn-row-add" onclick="addRow('motorTable')">+ Προσθήκη Μέσου</button>
</div>
<table id="motorTable">
    <thead>
    <tr>
        <th>Περιγραφή (Μάρκα/Μοντέλο)</th>
        <th>Αριθμός Εγγραφής</th>
        <th>Έτος Απόκτησης</th>
        <th>Τρόπος Απόκτησης / Αξία</th>
        <th width="30"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><button class="btn-row-del" onclick="removeRow(this)">×</button></td>
    </tr>
    </tbody>
</table>

<div class="section-title">
    3. ΣΥΜΜΕΤΟΧΗ ΣΕ ΕΠΙΧΕΙΡΗΣΕΙΣ
    <button class="btn-row-add" onclick="addRow('businessTable')">+ Προσθήκη Επιχείρησης</button>
</div>
<div class="form-hint">(Περιλαμβάνει ομόρρυθμες/ετερόρρυθμες εταιρείες, ιδιωτικές ή δημόσιες)</div>
<table id="businessTable">
    <thead>
    <tr>
        <th>Όνομα Επιχείρησης</th>
        <th>Είδος Συμμετοχής</th>
        <th>Κεφάλαιο / Μετοχές</th>
        <th>Ημερομηνία Έναρξης</th>
        <th width="30"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="date" style="width:95%"></td>
        <td><button class="btn-row-del" onclick="removeRow(this)">×</button></td>
    </tr>
    </tbody>
</table>

<div class="section-title">
    4. (α) & (β) ΧΡΕΟΓΡΑΦΑ, ΜΕΤΟΧΕΣ, ΤΙΤΛΟΙ
    <button class="btn-row-add" onclick="addRow('securitiesTable')">+ Προσθήκη Τίτλου</button>
</div>
<table id="securitiesTable">
    <thead>
    <tr>
        <th>Είδος Κινητής Αξίας (Μετοχές, Ομόλογα κ.α.)</th>
        <th>Περιγραφή / Εκδότης</th>
        <th>Αριθμός σε Κατοχή</th>
        <th>Αξία (€)</th>
        <th width="30"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><button class="btn-row-del" onclick="removeRow(this)">×</button></td>
    </tr>
    </tbody>
</table>

<div class="section-title">
    ΜΕΡΟΣ Γ΄: ΑΚΙΝΗΤΗ ΙΔΙΟΚΤΗΣΙΑ
    <button class="btn-row-add" onclick="addRow('realEstateTable')">+ Προσθήκη Ακινήτου</button>
</div>
<table id="realEstateTable">
    <thead>
    <tr>
        <th>Τοποθεσία / Είδος</th>
        <th>Έκταση / Μερίδιο</th>
        <th>Έτος & Τρόπος Απόκτησης</th>
        <th>Αξία (€)</th>
        <th width="30"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><button class="btn-row-del" onclick="removeRow(this)">×</button></td>
    </tr>
    </tbody>
</table>

<div class="section-title">
    4. (γ) ΚΑΤΑΘΕΣΕΙΣ ΣΕ ΤΡΑΠΕΖΕΣ
    <button class="btn-row-add" onclick="addRow('bankTable')">+ Προσθήκη Λογαριασμού</button>
</div>
<table id="bankTable">
    <thead>
    <tr>
        <th>Τράπεζα</th>
        <th>Είδος Λογαριασμού</th>
        <th>Υπόλοιπο (€)</th>
        <th>Ημερομηνία</th>
        <th width="30"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="date" style="width:95%"></td>
        <td><button class="btn-row-del" onclick="removeRow(this)">×</button></td>
    </tr>
    </tbody>
</table>

<div class="section-title">
    6. ΧΡΕΗ / ΥΠΟΧΡΕΩΣΕΙΣ
    <button class="btn-row-add" onclick="addRow('debtTable')">+ Προσθήκη Χρέους</button>
</div>
<table id="debtTable">
    <thead>
    <tr>
        <th>Πιστωτής</th>
        <th>Είδος Χρέους</th>
        <th>Υπόλοιπο (€)</th>
        <th width="30"></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><input type="text" style="width:95%"></td>
        <td><button class="btn-row-del" onclick="removeRow(this)">×</button></td>
    </tr>
    </tbody>
</table>

<div class="section-title">7. ΑΛΛΑ ΕΙΣΟΔΗΜΑΤΑ / ΑΙΤΙΟΛΟΓΗΣΗ ΔΙΑΦΟΡΟΠΟΙΗΣΗΣ</div>
<textarea style="width: 100%; height: 120px; margin-top: 10px;" placeholder="Συμπληρώστε τυχόν άλλα εισοδήματα ή εξηγήσεις για αλλαγές στην περιουσία σας..."></textarea>

<footer style="margin-top: 60px; display: flex; justify-content: space-between; border-top: 1px solid #000; padding-top: 20px;">
    <p>Ημερομηνία: ...........................</p>
    <p>Υπογραφή: ...................................................</p>
</footer>

<script>
    function addRow(tableId) {
        const tableBody = document.getElementById(tableId).querySelector('tbody');
        const firstRow = tableBody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);

        // Clear all inputs in the cloned row
        const inputs = newRow.querySelectorAll('input');
        inputs.forEach(input => input.value = '');

        tableBody.appendChild(newRow);
    }

    function removeRow(btn) {
        const tableBody = btn.closest('tbody');
        if (tableBody.rows.length > 1) {
            btn.closest('tr').remove();
        } else {
            alert("Πρέπει να υπάρχει τουλάχιστον μία γραμμή.");
        }
    }
</script>

</body>
</html>
