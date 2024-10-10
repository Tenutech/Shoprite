/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Profile-setting init js
*/

/*
|--------------------------------------------------------------------------
| Google Maps
|--------------------------------------------------------------------------
*/

function initAutocomplete() {
    // Get the input element with data-google-autocomplete attribute
    const addressInput = document.querySelector('[data-google-autocomplete]');
    
    if (addressInput) {
        // Initialize Google Places Autocomplete
        const autocomplete = new google.maps.places.Autocomplete(addressInput, {
            types: ['geocode'],  // Restrict to geocoding (address types)
            componentRestrictions: { 'country': 'ZA' },  // Restrict to South Africa (optional)
        });

        // Flag to check if a valid place was selected
        let placeSelected = false;

        // When the user selects an address from the suggestions
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            placeSelected = true;

            if (!place.geometry) {
                // If no geometry is available, reset the input field
                addressInput.value = '';
                addressInput.classList.add('is-invalid');
                
                // Show the correct error message right after the address input
                const feedback = addressInput.nextElementSibling;  // This gets the closest sibling
                feedback.textContent = 'Please select a verified address!';
                feedback.style.display = 'block';  // Show the feedback element
            } else {
                // Valid address selected, remove error state
                addressInput.classList.remove('is-invalid');
                
                // Hide the feedback element
                const feedback = addressInput.nextElementSibling;
                feedback.textContent = '';
                feedback.style.display = 'none';  // Hide the feedback

                // Set lat/lng hidden fields
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            }
        });

        // Prevent browser autocomplete by disabling autofill
        addressInput.setAttribute('autocomplete', 'off');

        // Validate on field blur that the user selected a valid address
        addressInput.addEventListener('blur', function () {
            if (!placeSelected) {
                // If the user didn't select a valid address, mark the field as invalid
                addressInput.classList.add('is-invalid');
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                
                // Show error message and ensure display is set to block
                const feedback = addressInput.nextElementSibling;  // Get the closest invalid-feedback
                feedback.textContent = 'Please select a verified address!';
                feedback.style.display = 'block';  // Force display to block
            }
        });

        // Reset placeSelected when user starts typing again
        addressInput.addEventListener('input', function () {
            placeSelected = false;
            addressInput.classList.remove('is-invalid');
            
            // Hide error message
            const feedback = addressInput.nextElementSibling;
            feedback.textContent = '';
            feedback.style.display = 'none';  // Hide feedback
        });
    }
}

// Load the Google Autocomplete on page load
window.addEventListener('load', initAutocomplete);

/*
|--------------------------------------------------------------------------
| Tab Open
|--------------------------------------------------------------------------
*/

$(document).ready(function () {    
    $("#profileSettingsTab.nav-tabs > li > a").on("shown.bs.tab", function(e) {
        localStorage.setItem('profileSettingsTab', $(e.target).attr('href'));
    });

    var profileSettingsTab = localStorage.getItem('profileSettingsTab');
    if(profileSettingsTab){
        $('#profileSettingsTab.nav-tabs > li > a[href="' + profileSettingsTab + '"]').tab('show');
    }
});

/*
|--------------------------------------------------------------------------
| Profile Picture
|--------------------------------------------------------------------------
*/

// Profile Foreground Img
if (document.querySelector("#profile-foreground-img-file-input")) {
    document.querySelector("#profile-foreground-img-file-input").addEventListener("change", function () {
        var preview = document.querySelector(".profile-wid-img");
        var file = document.querySelector(".profile-foreground-img-file-input")
            .files[0];
        var reader = new FileReader();
        reader.addEventListener(
            "load",
            function () {
                preview.src = reader.result;
            },
            false
        );
        if (file) {
            reader.readAsDataURL(file);
        }
    });
}

// Profile Foreground Img
if (document.querySelector("#avatar")) {
    document.querySelector("#avatar").addEventListener("change", function () {
        var preview = document.querySelector(".user-profile-image");
        var file = document.querySelector(".profile-img-file-input").files[0];
        var reader = new FileReader();
        reader.addEventListener(
            "load",
            function () {
                preview.src = reader.result;
            },
            false
        );
        if (file) {
            reader.readAsDataURL(file);
        }
    });
}

/*
|--------------------------------------------------------------------------
| Capitalize First Letter Of Each Word
|--------------------------------------------------------------------------
*/

function capitalizeFirstLetterOfEachWord(str) {
    return str.replace(/\w\S*/g, function(txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}

/*
|--------------------------------------------------------------------------
| Compute Completion Percentage
|--------------------------------------------------------------------------
*/

function computeCompletionPercentage() {
    var fields = [
        $('#firstname').val(),
        $('#lastname').val(),
        $('#email').val(),
        $('#phone').val(),
        $('#address').val(),
    ];

    var filledFieldsCount = 0;

    $.each(fields, function(index, value) {
        if (value && value.trim() !== "") {
            filledFieldsCount++;
        }
    });

    var percentage = (filledFieldsCount / fields.length) * 100;
    return Math.round(percentage);
}

/*
|--------------------------------------------------------------------------
| User Form
|--------------------------------------------------------------------------
*/

$("#formUser").submit(function(e) {
    e.preventDefault();
    var formData = new FormData($(this)[0]);

    if ($(".profile-img-file-input").prop('files')[0]) {
        formData.append('avatar', $(".profile-img-file-input").prop('files')[0]);
    }

    // Get the country code and remove any spaces
    var countryCode = $('.country-codeno').text().trim().replace(/\s+/g, '');

    // Get the phone input element
    var phoneNumber = $('#phone').val().trim();

    // Check if the phone number already starts with the country code
    if (!phoneNumber.startsWith(countryCode)) {
        // If it doesn't start with the country code, add the country code
        phoneNumber = countryCode + phoneNumber;
    }

    formData.set('phone', phoneNumber);

    const addressInput = document.getElementById('location');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    var addressIsValid = true;
    
    if (addressInput) {
        // Check if latitude and longitude are filled in (indicating a valid place was selected)
        if (!latInput.value || !lngInput.value) {
            addressIsValid = false;
            addressInput.classList.add('is-invalid');
            addressInput.classList.remove('was-validated');
            const feedback = addressInput.nextElementSibling;
            feedback.textContent = 'Please select a verified address!';
            feedback.style.display = 'block';

            // Apply custom invalid border color
            addressInput.style.borderColor = 'var(--vz-form-invalid-border-color)';
        } else {
            addressInput.classList.remove('is-invalid');
            addressInput.classList.add('was-validated'); 
            const feedback = addressInput.nextElementSibling;
            feedback.style.display = 'none';

            // Apply custom valid border color
            addressInput.style.borderColor = 'var(--vz-form-valid-border-color)';
        }
    }

    // Validate the brands Choices.js field
    var brandsElement = $('#brands');
    var selectedBrands = brandsElement.val();
    var brandsIsValid = true;

    if (!selectedBrands || selectedBrands.length === 0) {
        brandsIsValid = false;

        // Highlight only the invalid Choices.js field
        brandsElement.closest('.mb-3').find('.choices__inner').css('border', '1px solid #f17171');
        brandsElement.closest('.mb-3').find('.invalid-feedback').show(); // Show validation message
    } else {
        // Remove validation styles if the field is valid
        brandsElement.closest('.mb-3').find('.choices__inner').css('border', ''); // Remove red border for valid input
        brandsElement.closest('.mb-3').find('.invalid-feedback').hide(); // Hide the feedback
    }

    // If validation fails, stop the form submission
    if (!addressIsValid || !brandsIsValid) {
        return;
    }

    // If validation passes, continue with AJAX submission
    $.ajax({
        url: route('profile-settings.update'),
        type: "post",
        data: formData,
        async: false,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success == true) {
                if ($(".profile-img-file-input").prop('files')[0]) {
                    var avatar = URL.createObjectURL($(".profile-img-file-input").prop('files')[0]);
                    $("#topbar-avatar").attr("src", avatar);
                }

                var firstName = capitalizeFirstLetterOfEachWord($("#firstname").val());
                var lastName = capitalizeFirstLetterOfEachWord($("#lastname").val());
                $("#user-name").text(firstName + " " + lastName);

                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true,
                    toast: true
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            let message = '';

            if (jqXHR.status === 400 || jqXHR.status === 422) {
                message = jqXHR.responseJSON.message;
            } else if (textStatus === 'timeout') {
                message = 'The request timed out. Please try again later.';
            } else {
                message = 'An error occurred while processing your request. Please try again later.';
            }

            // Strip the country code and revert the phone input to its original format
            var phoneNumberWithoutCode = phoneNumber.replace(countryCode, '').replace(/^0+/, '');
            $('#phone').val(phoneNumberWithoutCode);

            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: message,
                showConfirmButton: false,
                timer: 5000,
                showCloseButton: true,
                toast: true
            });
        }
    });
});

/*
|--------------------------------------------------------------------------
| Display Error
|--------------------------------------------------------------------------
*/

// Helper function to display errors
var displayError = function (inputId, message) {
    var $input = $("#" + inputId);
    var $errorElement = $input.next(".invalid-feedback");

    $input.addClass('is-invalid');

    if ($errorElement.length) {
        $errorElement.html('<strong>' + message + '</strong>');
    } else {
        $input.after('<span class="invalid-feedback" role="alert"><strong>' + message + '</strong></span>');
    }
};

/*
|--------------------------------------------------------------------------
| Password Form
|--------------------------------------------------------------------------
*/

$("#formPassword").submit(function(e) {
	e.preventDefault();
    
    $('#oldPassword, #newPassword, #confirmPassword').removeClass('is-invalid');
    $('.invalid-feedback').remove();

	var formData = new FormData($(this)[0]);

    if (this.checkValidity()) {
        $.ajax({
            url: route('profile-settings.updatePassword'),
            type: "post",
            data: formData,
            async: false,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
                if (data.success == true) {
                    // Remove is-invalid class from all password fields
                    $('#oldPassword, #newPassword, #confirmPassword').removeClass('is-invalid').val('');
            
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true,
                        toast: true
                    });
                } else {        
                    if (data.message == 'The password confirmation does not match.') {
                        if (data.errors.new_password) {
                            displayError("newPassword", data.errors.new_password[0]);
                        }
            
                        if (data.errors.confirm_password) {
                            displayError("confirmPassword", data.errors.confirm_password[0]);
                        }
                    }
            
                    if (data.status === "error" && data.message === "The old password is incorrect.") {
                        displayError("oldPassword", "The old password is incorrect.");
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 400) { // Bad Request, old password is incorrect
                    var responseData = jqXHR.responseJSON;
                    if (responseData.message) {
                        displayError("oldPassword", responseData.message);
                    }
                } else if (jqXHR.status === 422) {
                    var responseData = jqXHR.responseJSON;
                    if (responseData.message) {
                        displayError("newPassword", responseData.message);
                    }
                } else if (textStatus === 'timeout') {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'The request timed out. Please try again later.',
                        showConfirmButton: false,
                        timer: 5000,
                        showCloseButton: true,
                        toast: true
                    });
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'An error occurred while processing your request. Please try again later.',
                        showConfirmButton: false,
                        timer: 5000,
                        showCloseButton: true,
                        toast: true
                    });
                }
            }
        });
    } else {
        this.reportValidity();
    }
});

/*
|--------------------------------------------------------------------------
| Notifications Form
|--------------------------------------------------------------------------
*/

$("#formNotifications").submit(function(e) {
	e.preventDefault();
	var formData = new FormData($(this)[0]);

    $.ajax({
        url: route('profile-settings.notifications'),
        type: "post",
        data: formData,
        async: false,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success:function(data) {
            if (data.success == true) {
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true,
                    toast: true
                })                  
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            let message = ''; // Initialize the message variable
        
            if (jqXHR.status === 400 || jqXHR.status === 422) {
                message = jqXHR.responseJSON.message;
            } else if (textStatus === 'timeout') {
                message = 'The request timed out. Please try again later.';
            } else {
                message = 'An error occurred while processing your request. Please try again later.';
            }
        
            // Trigger the Swal notification with the dynamic message
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: message,
                showConfirmButton: false,
                timer: 5000,
                showCloseButton: true,
                toast: true
            });
        }
    });
});