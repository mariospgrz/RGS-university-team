function printAndSavePdf() {
window.print();

let content = document.body.innerHTML;

fetch('save_pdf.php', {
method: 'POST',
headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
body: 'htmlContent=' + encodeURIComponent(content)
})
.then(response => response.json())
.then(data => {
if (data.status === 'success') {
console.log('PDF saved on server: ' + data.path);
} else {
console.error('Error saving PDF');
}
})
.catch(err => console.error(err));
}