/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Form wizard Js File
*/
document.getElementById('idNumber').addEventListener('input', function() {
    const idNumberInput = this;
    const idNumber = this.value;
    const guardianMobileContainer = document.getElementById('guardianMobileContainer');
    const errorIdElement = idNumberInput.parentNode.querySelector('.invalid-feedback');

    if (isUnder18(idNumber)) {
        guardianMobileContainer.style.display = 'block';
    } else {
        guardianMobileContainer.style.display = 'none';
    }

    if (!isValidSAIdNumber(idNumber)) {
        
    } else {
        errorIdElement.remove();
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

function isValidSAIdNumber(id) {
    id = id.replace(/\D/g, '');
    
    if (id.length !== 13) {
        return false;
    }

    let sum = 0;
    const length = id.length;
    
    for (let i = 0; i < length - 1; i++) {
        let number = parseInt(id[i], 10);
        
        if ((length - i) % 2 === 0) {
            number *= 2;
            if (number > 9) {
                number -= 9;
            }
        }
        
        sum += number;
    }

    const checksum = (10 - (sum % 10)) % 10;

    return parseInt(id[length - 1], 10) === checksum;
}

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

    var idNumberInput = document.getElementById('idNumber');
    var idNumber = idNumberInput.value;

    if (!isValidSAIdNumber(idNumber)) {
        event.preventDefault();

        var errorIdElement = idNumberInput.parentNode.querySelector('.invalid-feedback');
        if (!errorIdElement) {
            errorIdElement = document.createElement('div');
            errorIdElement.className = "invalid-feedback";
            idNumberInput.parentNode.appendChild(errorIdElement);
            errorIdElement.innerText = "You have not entered a valid SA ID Number";
            errorIdElement.style.display = 'block';
        }
    } 
});