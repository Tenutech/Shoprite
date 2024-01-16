/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Form wizard Js File
*/

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