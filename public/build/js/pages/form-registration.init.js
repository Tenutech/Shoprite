/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Form wizard Js File
*/

/*
|--------------------------------------------------------------------------
| Is Valid SA ID Number
|--------------------------------------------------------------------------
*/

/**
 * Function to validate if the provided ID number is a valid South African ID number.
 * @param {string} id - The ID number to validate.
 * @returns {boolean} - Returns true if the ID number is valid, otherwise false.
 */
function isValidSAIdNumber(id) {
    id = id.replace(/\D/g, ''); // Remove all non-digit characters

    if (id.length !== 13) {
        return false; // Return false if the ID number is not exactly 13 digits
    }

    let sum = 0; // Sum for the Luhn algorithm
    const length = id.length;
    
    // Apply Luhn algorithm to validate the ID number
    for (let i = 0; i < length - 1; i++) {
        let number = parseInt(id[i], 10); // Convert the current character to a number
        
        // Multiply every second digit by 2 and subtract 9 if the result is greater than 9
        if ((length - i) % 2 === 0) {
            number *= 2;
            if (number > 9) {
                number -= 9;
            }
        }
        
        sum += number; // Add the result to the sum
    }

    // Calculate the checksum
    const checksum = (10 - (sum % 10)) % 10;

    // The last digit of the ID number should match the calculated checksum
    return parseInt(id[length - 1], 10) === checksum;
}

/*
|--------------------------------------------------------------------------
| Is Under 18
|--------------------------------------------------------------------------
*/

/**
 * Function to check if the user is under 18 years old based on their ID number.
 * @param {string} id - The ID number of the applicant.
 * @returns {boolean} - Returns true if the applicant is under 18, otherwise false.
 */
function isUnder18(id) {
    if (id.length < 6) return false; // Ensure the ID number has at least 6 digits

    const year = parseInt(id.substring(0, 2), 10); // Extract the year of birth from the ID number
    const month = parseInt(id.substring(2, 4), 10); // Extract the month of birth
    const day = parseInt(id.substring(4, 6), 10); // Extract the day of birth

    const currentYear = new Date().getFullYear() % 100; // Get the last two digits of the current year
    const century = year > currentYear ? 1900 : 2000; // Determine the century (19xx or 20xx)
    const birthDate = new Date(century + year, month - 1, day); // Construct the birth date from the year, month, and day

    const ageDifMs = Date.now() - birthDate.getTime(); // Calculate the age difference in milliseconds
    const ageDate = new Date(ageDifMs); // Convert the age difference into a Date object
    const age = Math.abs(ageDate.getUTCFullYear() - 1970); // Calculate the age in years

    return age < 18; // Return true if the age is less than 18
}

/*
|--------------------------------------------------------------------------
| Is Valid Mobile
|--------------------------------------------------------------------------
*/

/**
 * Function to validate if the provided mobile number is valid.
 * @param {string} mobile - The mobile number to validate.
 * @returns {boolean} - Returns true if the mobile number is valid, otherwise false.
 */
function isValidMobile(mobile) {
    const phonePattern = /^[0-9]{10}$/; // Regular expression for a 10-digit phone number
    return phonePattern.test(mobile); // Return true if the mobile number matches the pattern
}

/*
|--------------------------------------------------------------------------
| Register Form Submit
|--------------------------------------------------------------------------
*/

document.getElementById('formRegister').addEventListener('submit', function(event) {
    var formRegister = document.getElementById('formRegister');

    // Get the country code and remove any spaces
    let countryCode = document.querySelector('.country-codeno').innerText.trim().replace(/\s+/g, '');

    // Get the phone input element
    let phoneInput = document.getElementById('phone');

    // Remove the `+` and any spaces from the country code
    let numericCountryCode = countryCode.replace('+', '');

    // Check if the phone number already includes the country code
    if (phoneInput.value.startsWith(numericCountryCode)) {
        phoneInput.value = '+' + phoneInput.value; // Ensure the phone number starts with the full country code
    } else {
        // Add the country code to the phone number
        phoneInput.value = countryCode + phoneInput.value;
    }

    // Password validation: check if the passwords match
    var passwordInput = document.getElementById('password');
    var confirmPasswordInput = document.getElementById('input-password');

    // If the passwords don't match, prevent form submission
    if (passwordInput.value !== confirmPasswordInput.value) {
        event.preventDefault(); // Prevent the form from submitting

        // Remove the 'was-validated' class from the form
        formRegister.classList.remove('was-validated');

        // Add 'is-invalid' class to both password input fields
        passwordInput.classList.add('is-invalid');
        confirmPasswordInput.classList.add('is-invalid');

        // Display a custom error message if passwords don't match
        var errorElement = confirmPasswordInput.parentNode.querySelector('.invalid-feedback');
        if (!errorElement) { // If the error element doesn't already exist, create it
            errorElement = document.createElement('div');
            errorElement.className = "invalid-feedback";
            confirmPasswordInput.parentNode.appendChild(errorElement);
        }
        errorElement.innerText = "Passwords don't match"; // Display the error message
    }

    // Validate the South African ID number
    var idNumberInput = document.getElementById('idNumber');
    var idNumber = idNumberInput.value;

    // If the ID number is invalid, prevent form submission and display an error message
    if (!isValidSAIdNumber(idNumber)) {
        event.preventDefault(); // Prevent the form from submitting

        // Remove the 'was-validated' class from the form with a short delay
        setTimeout(function() {
            formRegister.classList.remove('was-validated');
        }, 10);

        // Add 'is-invalid' class to id number field
        idNumberInput.classList.add('is-invalid');

        var errorIdElement = idNumberInput.parentNode.querySelector('.invalid-feedback');
        if (!errorIdElement) { // If the error message doesn't already exist, create it
            errorIdElement = document.createElement('div');
            errorIdElement.className = "invalid-feedback";
            idNumberInput.parentNode.appendChild(errorIdElement);
            errorIdElement.innerText = "You have not entered a valid SA ID Number"; // Display the error message
            errorIdElement.style.display = 'block';
        }
    }

    // Check if the user is under 18
    if (isUnder18(idNumber)) {
        event.preventDefault(); // Prevent the form from submitting

        // Remove the 'was-validated' class from the form with a short delay
        setTimeout(function() {
            formRegister.classList.remove('was-validated');
        }, 10);

        // Add 'is-invalid' class to id number field
        idNumberInput.classList.add('is-invalid');

        var underageErrorElement = idNumberInput.parentNode.querySelector('.invalid-feedback');
        if (!underageErrorElement) { // If the error message doesn't already exist, create it
            underageErrorElement = document.createElement('div');
            underageErrorElement.className = "invalid-feedback";
            idNumberInput.parentNode.appendChild(underageErrorElement);
        }
        underageErrorElement.innerText = "You are under 18 and not eligible to register on the platform"; // Display the underage error message
        underageErrorElement.style.display = 'block';
    }
});