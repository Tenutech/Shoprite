window.addEventListener('DOMContentLoaded', (event) => {
    let canvas = document.getElementById('signature-pad');
    let signaturePad = new SignaturePad(canvas);

    document.getElementById('clear-signature').addEventListener('click', function () {
        signaturePad.clear();
    });

    document.getElementById('save-signature').addEventListener('click', function () {
        if (signaturePad.isEmpty()) {
            alert('Please provide a signature first.');
        } else {
            document.getElementById('signature-data').value = signaturePad.toDataURL();
            document.getElementById('signature-form').submit();
        }
    });
});
