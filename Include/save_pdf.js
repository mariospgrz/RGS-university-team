function printAndSavePdf() {
    // Sync values for text inputs and textareas
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        if (input.type === 'checkbox' || input.type === 'radio') {
            if (input.checked) input.setAttribute('checked', 'checked');
            else input.removeAttribute('checked');
        } else {
            // This places the typed text into the HTML attribute
            input.setAttribute('value', input.value);
            // For textareas, the text goes inside the tag
            if (input.tagName.toLowerCase() === 'textarea') {
                input.textContent = input.value;
            }
        }
    });

    // Optional: show print dialog
    window.print();

    const pdfDocument = document.documentElement.cloneNode(true);
    pdfDocument.querySelectorAll('.sticky-nav, .btn-row-add, .btn-row-del, script').forEach(element => {
        element.remove();
    });

    // Now 'outerHTML' includes the data the user typed, without UI-only controls
    let content = "<!DOCTYPE html>\n" + pdfDocument.outerHTML;

    fetch('save_pdf.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'htmlContent=' + encodeURIComponent(content)
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log('PDF saved on server: ' + data.path);

                // Automatically download the PDF
                const link = document.createElement('a');
                link.href = data.path;           // path returned by PHP
                link.download = data.path.split('/').pop(); // filename only
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                Swal.fire({
                    title: 'Success!',
                    text: 'Your PDF has been saved and downloaded.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'profile.php';
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Could not save PDF: ' + data.message,
                    icon: 'error'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'profile.php';
                    }
                })
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error!', 'Something went wrong while saving the PDF.', 'error');
        });
}
