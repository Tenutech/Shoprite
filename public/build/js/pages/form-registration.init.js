/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Form wizard Js File
*/
document.getElementById('idNumber').addEventListener('input', function() {
    const idNumber = this.value;
    const guardianMobileContainer = document.getElementById('guardianMobileContainer');

    if (isUnder18(idNumber)) {
        guardianMobileContainer.style.display = 'block';
    } else {
        guardianMobileContainer.style.display = 'none';
    }
});

function isUnder18(id) {
    if (id.length < 6) return false;

    const year = parseInt(id.substring(0, 2), 10);
    const month = parseInt(id.substring(2, 4), 10);
    const day = parseInt(id.substring(4, 6), 10);

    const currentYear = new Date().getFullYear() % 100;
    const century = year > currentYear ? 1900 : 2000;
    const birthDate = new Date(century + year, month - 1, day);

    const ageDifMs = Date.now() - birthDate.getTime();
    const ageDate = new Date(ageDifMs);
    const age = Math.abs(ageDate.getUTCFullYear() - 1970);

    return age < 18;
}

// Save guardian's mobile number when the user clicks "Save"
document.getElementById('saveGuardianMobile').addEventListener('click', function() {
    const guardianMobile = document.getElementById('guardianMobile').value;
    const mobileError = document.getElementById('mobileError');

    if (isValidMobile(guardianMobile)) {
        mobileError.style.display = 'none';
        // You can now submit the form or handle the guardian's mobile number as needed
        console.log("Guardian's Mobile Number:", guardianMobile);
        // Close the modal
        bootstrap.Modal.getInstance(document.getElementById('guardianModal')).hide();
    } else {
        mobileError.style.display = 'block';
    }
});

function isValidMobile(mobile) {
    const phonePattern = /^[0-9]{10}$/;
    return phonePattern.test(mobile);
}

document.getElementById('formRegister').addEventListener('submit', function(event) {    
    // Get the country code and remove spaces
    let countryCode = document.querySelector('.country-codeno').innerText.trim().replace(/\s+/g, '');

    // Get the phone input element
    let phoneInput = document.getElementById('phone');

    // Extract just the numeric part of the country code (removing the `+`)
    let numericCountryCode = countryCode.replace('+', '');

    // Check if phoneInput already starts with the numeric part of the country code
    if(phoneInput.value.startsWith(numericCountryCode)) {
        phoneInput.value = '+' + phoneInput.value;
    } else {
        // Update the phone value to include the country code
        phoneInput.value = countryCode + phoneInput.value;
    }

    // Password matching validation
    var passwordInput = document.getElementById('password');
    var confirmPasswordInput = document.getElementById('input-password');

    // Check if passwords match
    if (passwordInput.value !== confirmPasswordInput.value) {
        // Prevent form submission
        event.preventDefault();

        // Remove 'was-validated' class from the form
        document.getElementById('formRegister').classList.remove('was-validated');

        // Add 'is-invalid' class to both password input elements
        passwordInput.classList.add('is-invalid');
        confirmPasswordInput.classList.add('is-invalid');

        // Show a custom error message
        var errorElement = confirmPasswordInput.parentNode.querySelector('.invalid-feedback');
        if (!errorElement) { // If the error message doesn't already exist, create it.
            errorElement = document.createElement('div');
            errorElement.className = "invalid-feedback";
            confirmPasswordInput.parentNode.appendChild(errorElement);
        }
        errorElement.innerText = "Passwords don't match";
    }
});