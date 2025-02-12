ocument.addEventListener("DOMContentLoaded", function () {
    let signaturePad = new SignaturePad(document.getElementById("signature-pad"));
    let clearButton = document.getElementById("clear-signature");
    let saveButton = document.getElementById("save-signature");
    let signatureInput = document.getElementById("signature-data");

    clearButton.addEventListener("click", function () {
        signaturePad.clear();
    });

    saveButton.addEventListener("click", function () {
        if (signaturePad.isEmpty()) {
            alert("Please provide a signature first.");
        } else {
            signatureInput.value = signaturePad.toDataURL();
            document.getElementById("signature-form").submit();
        }
    });
});
