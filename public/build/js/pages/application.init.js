/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Form wizard Js File
*/

/*
|--------------------------------------------------------------------------
| Choices
|--------------------------------------------------------------------------
*/

$(document).ready(function() {
    var education = $('#education')[0]; // Get the first DOM element from the jQuery object
    var educationChoices = new Choices(education, {
        shouldSort: false,     // Disable sorting
        searchEnabled: false   // Disable search
    });

    var duration = $('#duration')[0]; // Get the first DOM element from the jQuery object
    var durationChoices = new Choices(duration, {
        shouldSort: false,     // Disable sorting
        searchEnabled: false   // Disable search
    });
});


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
| Avatar
|--------------------------------------------------------------------------
*/

// avatar image
document.querySelector("#avatar").addEventListener("change", function () {
    var preview = document.querySelector("#preview");
    var file = document.querySelector("#avatar").files[0];
    var reader = new FileReader();
    reader.addEventListener("load",function () {
        preview.src = reader.result;
    },false);
    if (file) {
        reader.readAsDataURL(file);
    }
});

/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

if (document.querySelectorAll(".form-steps")) {
    Array.from(document.querySelectorAll(".form-steps")).forEach(function (form) {
        function updateNavLinks() {
            const activeTabIndex = Array.from(form.querySelectorAll('.nav-link')).findIndex(link => link.classList.contains('active'));
            form.querySelectorAll('.nav-link').forEach((link, index) => {
                if (index <= activeTabIndex) {
                    link.classList.remove('disabled');
                } else {
                    link.classList.add('disabled');
                }
            });
        }

        updateNavLinks();

        // next tab
        if (form.querySelectorAll(".nexttab")) {
            Array.from(form.querySelectorAll(".nexttab")).forEach(function (nextButton) {
                nextButton.addEventListener("click", function () {
                    form.classList.add('was-validated');

                    // Get all required fields within the active tab
                    let requiredFields = Array.from(form.querySelectorAll(".tab-pane.show .form-control:required:not([data-exclude-validation]), .tab-pane.show .form-check-input:required"));

                    // First reset custom validity for all inputs
                    requiredFields.forEach(input => {
                        input.setCustomValidity('');
                    })

                    // Function to check if an element is visible
                    function isVisible(element) {
                        if (element.classList.contains('choices__input')) {
                            // For fields enhanced by choices.js, check if the closest parent with 'd-none' is not present
                            return !element.closest('.d-none');
                        } else {
                            // For other fields, use offsetParent
                            return !!element.offsetParent;
                        }
                    }

                    // Add validation for the Google Places Autocomplete field (location)
                    let addressValid = true;
                    const activeTab = form.querySelector(".tab-pane.show");  // Get the active tab

                    // Only validate the address if the location field exists on the current tab
                    if (activeTab.querySelector('[data-google-autocomplete]')) {
                        const addressInput = activeTab.querySelector('[data-google-autocomplete]');
                        const latInput = document.getElementById('latitude');
                        const lngInput = document.getElementById('longitude');
                        
                        if (addressInput) {
                            // Check if latitude and longitude are filled in (indicating a valid place was selected)
                            if (!latInput.value || !lngInput.value) {
                                addressValid = false;
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
                    } else {
                        // If the location field is not on the current tab, skip address validation
                        addressValid = true;
                    }

                    // Check if the brands field contains '1' (All) along with other selected options
                    const brandsSelect = form.querySelector('select[name="brands[]"]');
                    const selectedBrands = Array.from(brandsSelect.selectedOptions).map(option => option.value);

                    // Custom validation for 'brands' (All should not be selected with others)
                    let brandValid = true;
                    if (selectedBrands.includes('1') && selectedBrands.length > 1) {
                        brandValid = false;
                        brandsSelect.classList.add('is-invalid');
                        let feedbackDiv = brandsSelect.closest('.mb-3').querySelector('.invalid-feedback');
                        feedbackDiv.textContent = "You cannot select specific brands with 'Any'.";
                        feedbackDiv.style.display = 'block';

                        // Get the parent element with the class 'choices'
                        let choicesDiv = brandsSelect.closest('.mb-3');
                        
                        // Add a border to the 'choices' div
                        if(choicesDiv) {
                            let choicesElement = choicesDiv.querySelector('.choices');
                            if (choicesElement) {
                                choicesElement.style.border = '1px solid #f17171';
                            }
                        }
                    } else {
                        brandsSelect.classList.remove('is-invalid');
                        let feedbackDiv = brandsSelect.closest('.mb-3').querySelector('.invalid-feedback');
                        feedbackDiv.style.display = 'none';

                        // Reset border for the 'choices' div
                        let choicesDiv = brandsSelect.closest('.mb-3');
                        if(choicesDiv) {
                            let choicesElement = choicesDiv.querySelector('.choices');
                            if (choicesElement) {
                                choicesElement.style.border = ''; // Reset border
                            }
                        }
                    }

                    // Check if all required fields have been filled
                    let valid = requiredFields.every(input => {
                        // Check visibility
                        if (!isVisible(input)) {
                            return true; // Skip validation for hidden fields
                        }

                        let isValid;
                        switch(input.type) {
                            case "radio":
                                let radioGroup = form.querySelectorAll(`input[name="${input.name}"]`);
                                isValid = [...radioGroup].some(radio => radio.checked);
                                break;
                            case "select-one":
                                isValid = input.selectedOptions.length > 0 && input.value !== "";
                                break;
                            case "select-multiple":
                                isValid = input.selectedOptions.length > 0;
                                break;
                            case "text":
                                isValid = input.value !== "";
                                break;
                            default:
                                isValid = input.checkValidity();
                        }
                        return isValid;
                    });

                    // Combine with custom public holidays , enviroment and brand validation
                    valid = valid && addressValid && brandValid;
        
                    // If validation passed, go to the next tab
                    if (!valid) {
                        requiredFields.forEach(input => {                       
                            if (input.type == "radio" && !input.validity.valid) {
                                let alertDiv = form.querySelector(".tab-pane.show .alert-danger");
                                if (!alertDiv) {
                                    alertDiv = document.createElement("div");
                                    alertDiv.classList.add("alert", "alert-danger", "alert-border-left", "alert-dismissible", "fade", "show", "mb-xl-0", "mt-4");
                                    alertDiv.setAttribute("role", "alert");
                                    alertDiv.innerHTML = `<i class="ri-error-warning-line me-3 align-middle fs-16"></i><strong>Please select at least one option!</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
                                    form.querySelector(".tab-pane.show").appendChild(alertDiv);
                                }                                
                            } 
                            
                            if (input.type == "select-multiple" && input.selectedOptions.length === 0) {                            
                                // Get the parent element with the class 'choices'
                                let choicesDiv = input.closest('.mb-3');
                            
                                // Add a border to the 'choices' div
                                if(choicesDiv) {
                                    choicesDiv.querySelector('.choices').style.border = '1px solid #f17171';
                                }
                            
                                // Get the invalid feedback div which is the sibling of the 'choices' div
                                let feedbackDiv = choicesDiv.querySelector('.invalid-feedback');
                                
                                // Display the invalid feedback message
                                if (feedbackDiv) {
                                    feedbackDiv.style.display = 'block';
                                }
                            } 
                            
                            if (input.tagName === "SELECT" && !input.multiple && input.value === "") {
                                // Get the parent element with the class 'choices'
                                let choicesDiv = input.closest('.mb-3');
                            
                                // Add a border to the 'choices' div
                                if(choicesDiv) {
                                    choicesDiv.querySelector('.choices').style.border = '1px solid #f17171';
                                }
                            
                                // Get the invalid feedback div which is the sibling of the 'choices' div
                                let feedbackDiv = choicesDiv.querySelector('.invalid-feedback');
                                
                                // Display the invalid feedback message
                                if (feedbackDiv) {
                                    feedbackDiv.style.display = 'block';
                                }
                            }

                            // Add validation for the date input
                            if (input.name === 'date' && input.value.trim() === '') {
                                input.classList.add('is-invalid');
                                let feedbackDiv = input.parentElement.querySelector('.invalid-feedback');
                                if (feedbackDiv) {
                                    feedbackDiv.style.display = 'block';
                                }
                            }

                            // Add validation for text inputs
                            if (input.type === "text" && !input.validity.valid) {
                                input.classList.add('is-invalid');
                                let feedbackDiv = input.parentElement.querySelector('.invalid-feedback');
                                if (feedbackDiv) {
                                    feedbackDiv.style.display = 'block';
                                }
                            }                           
                        });
                    } else {
                        // If valid, reset the border and hide the invalid feedback message for all select-multiple inputs
                        form.querySelectorAll('select').forEach(input => {
                            let selectParentDiv = input.closest('.mb-3');
                        
                            if (selectParentDiv) {
                                // Reset border for both single and multiple selects
                                let choicesDiv = selectParentDiv.querySelector('.choices');
                                if (choicesDiv) {
                                    choicesDiv.style.border = '';
                                } else {
                                    // If there's no choices div, reset border on the select element itself
                                    input.style.border = '';
                                }
                        
                                // Hide the invalid feedback message
                                let feedbackDiv = selectParentDiv.querySelector('.invalid-feedback');
                                
                                if (feedbackDiv) {
                                    feedbackDiv.style.display = 'none';
                                }
                            }
                        });
                        
                        // Reset validation styles for text inputs
                        form.querySelectorAll('input[type="text"]').forEach(input => {
                            if (input.name === 'date' && input.value.trim() === '') {
                                input.classList.add('is-invalid');
                                let feedbackDiv = input.parentElement.querySelector('.invalid-feedback');
                                if (feedbackDiv) {
                                    feedbackDiv.style.display = 'block';
                                }
                            } else {
                                input.classList.remove('is-invalid');  // remove 'is-invalid' class to reset the border
                                let feedbackDiv = input.parentElement.querySelector('.invalid-feedback');
                                if (feedbackDiv) {
                                    feedbackDiv.style.display = 'none';  // hide the invalid feedback message
                                }
                            }
                        });

                        // If valid, move to the next tab
                        var currentActiveTabButton = form.querySelector('.nav-link.active');
                
                        var nextTab = nextButton.getAttribute('data-nexttab');
                
                        new bootstrap.Tab(document.getElementById(nextTab)).show();
                
                        if (currentActiveTabButton) {
                            currentActiveTabButton.classList.add('done');
                        }

                        form.classList.remove('was-validated');

                        // Scroll to the top of the page when moving to the next tab
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'  // Adds a smooth scrolling effect
                        });
                    }                                 

                    // Call the updateNavLinks function to update the "disabled" class on the nav links
                    updateNavLinks();
                });
            });
        }

        //Pervies tab
        if (form.querySelectorAll(".previestab")) {
            Array.from(form.querySelectorAll(".previestab")).forEach(function (prevButton) {

                prevButton.addEventListener("click", function () {
                    var prevTab = prevButton.getAttribute('data-previous');
                    var totalDone = prevButton.closest("form").querySelectorAll(".custom-nav .done").length;
                    for (var i = totalDone - 1; i < totalDone; i++) {
                        (prevButton.closest("form").querySelectorAll(".custom-nav .done")[i]) ? prevButton.closest("form").querySelectorAll(".custom-nav .done")[i].classList.remove('done'): '';
                    }
                    document.getElementById(prevTab).click();

                    // Call the updateNavLinks function to update the "disabled" class on the nav links
                    updateNavLinks();
                });
            });
        }

        // Step number click
        var tabButtons = form.querySelectorAll('button[data-bs-toggle="pill"]');
        if (tabButtons) {
            Array.from(tabButtons).forEach(function (button, i) {
                button.setAttribute("data-position", i);
                button.addEventListener("click", function (e) {
                    if (button.classList.contains('disabled')) {
                        e.preventDefault();
                        return;
                    }
                    form.classList.remove('was-validated');
           
                    var getProgressBar = button.getAttribute("data-progressbar");
                    if (getProgressBar) {
                        var totalLength = document.getElementById("custom-progress-bar").querySelectorAll("li").length - 1;
                        var current = i;
                        var percent = (current / totalLength) * 100;
                        document.getElementById("custom-progress-bar").querySelector('.progress-bar').style.width = percent + "%";
                    }
                    (form.querySelectorAll(".custom-nav .done").length > 0) ?
                    Array.from(form.querySelectorAll(".custom-nav .done")).forEach(function (doneTab) {
                        doneTab.classList.remove('done');
                    }): '';
                    for (var j = 0; j <= i; j++) {
                        tabButtons[j].classList.contains('active') ? tabButtons[j].classList.remove('done') : tabButtons[j].classList.add('done');
                    }

                    updateNavLinks();
                });
            });
        }
    });
}

/*
|--------------------------------------------------------------------------
| Form Submit
|--------------------------------------------------------------------------
*/

$('#formApplication, #formApplicationUpdate').on('submit', function(e) {
    e.preventDefault();

    // Get the country code and remove any spaces
    var countryCode = $('.country-codeno').text().trim().replace(/\s+/g, '');

    // Get the phone input element
    var phoneNumber = $('#phone').val().trim();

    // Check if the phone number already starts with the country code
    if (!phoneNumber.startsWith(countryCode)) {
        // If it doesn't start with the country code, add the country code
        phoneNumber = countryCode + phoneNumber;
    }

    var formID = $(this).attr('id');
    var formData = new FormData(this);
    formData.set('phone', phoneNumber);

    $("#confirm").hide();
    $("#loading").removeClass("d-none");

    if (formID === 'formApplicationUpdate') {
        $("#complete").hide();
    }

    var url;
    var method;
    if (formID === 'formApplication') {
        url = route('application.store');
        method = 'POST';
    } else if (formID === 'formApplicationUpdate') {
        url = route('application.update');
        method = 'POST';
    }

    $.ajax({
        url: url,
        type: method,
        data: formData,
        async: true,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success:function(data){                
            if (data.success == true){
                $("#loading").addClass("d-none");

                if (formID === 'formApplication') {
                    $('#id').val(data.encrypted_id);
                    $("#confirm").remove();

                    // Check if applicant is not eligible
                    if (data.applicant.public_holidays === 'No' || data.applicant.environment === 'No' || data.applicant.education_id === 1) {
                        $("#view-application-2").attr('href', route('profile.index', {id: data.encrypted_id}));
                        $("#regret").removeClass("d-none");
                    } else {
                        $('#formApplication').attr('id', 'formApplicationUpdate');
                        $("#view-application").attr('href', route('profile.index', {id: data.encrypted_id}));
                        $("#complete").removeClass("d-none");
                    }
                    
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        toast: true,
                        showCloseButton: true
                    });
                } else if (formID === 'formApplicationUpdate') {                        
                    $('#lordicon').attr('src', 'https://cdn.lordicon.com/lupuorrc.json');
                    $('#completeHeading').text('Application Updated !');
                    $('#completeText').text('You have succesfully updated this application.');
                    $("#complete").show();

                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        toast: true,
                        showCloseButton: true
                    });
                }      
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $("#loading").addClass("d-none");
            if (formID === 'formApplication') {
                $("#confirm").show();
            } else if (formID === 'formApplicationUpdate') {
                $("#complete").show();
            }

            // Strip the country code and revert the phone input to its original format
            var phoneNumberWithoutCode = phoneNumber.replace(countryCode, '').replace(/^0+/, '');
            $('#phone').val(phoneNumberWithoutCode);

            if (jqXHR.status === 400) {
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: jqXHR.responseJSON.message,
                    showConfirmButton: false,
                    timer: 5000,
                    showCloseButton: true,
                    toast: true
                });
            } else if (jqXHR.status == 422) {
                // If there are validation errors, you might want to show them as well
                var errorsHtml = '';
                var errors = $.parseJSON(jqXHR.responseText);
                $.each(errors.errors, function(key, val){
                    $("input[name='" + key + "']").addClass("is-invalid");
                    $("#" + key + "_error").text(val[0]);
                    errorsHtml += val[0] + '<br>';
                });
                // Update the requiredAlert div with the validation errors
                $("#requiredAlert").html(errorsHtml);
                // Show the requiredAlert div
                $("#requiredAlert").addClass("show");
        
                setTimeout(function() {
                    $("#requiredAlert").removeClass("show");
                }, 10000);
            } else {
                if (textStatus === 'timeout') {
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
        }
    });
});

/*
|--------------------------------------------------------------------------
| Form Cancel
|--------------------------------------------------------------------------
*/

$('#cancelBtn').click(function() {
    var positionTab = new bootstrap.Tab(document.getElementById('v-pills-welcome-tab'));
    positionTab.show();

    // Remove 'done' class from all elements with 'nav-link' class
    $('.nav-link').removeClass('done');

    // Add 'active' class to 'v-pills-position-tab' element
    $('#v-pills-position-tab').addClass('active');

    // Add 'disabled' class to 'v-pills-store-tab' and 'v-pills-type-tab' elements
    $('#v-pills-store-tab, #v-pills-type-tab').addClass('disabled');
});

/*
|--------------------------------------------------------------------------
| Edit Button
|--------------------------------------------------------------------------
*/

$('#editBtn').on('click', function () {
    // Step 1: Navigate to the first tab
    var positionTab = new bootstrap.Tab($('#v-pills-welcome-tab')[0]);
    positionTab.show();

    // Step 2: Remove class done from steps
    $('.form-steps .nav-link').removeClass('done');
    
    // Step 3: Change the src attribute of the lord-icon element
    $('#lordicon').attr('src', 'https://cdn.lordicon.com/nocovwne.json');

    // Step 4: Update the heading and paragraph texts
    $('#completeHeading').text('Would you like to update your application ?');
    $('#completeText').text('You are about to update your application with new information.');

    // Step 5: Add the new button if it doesn't already exist
    if ($('#updateBtn').length === 0) {
        $('<button/>', {
            type: 'submit',
            id: 'updateBtn',
            class: 'btn btn-secondary btn-label waves-effect waves-light rounded-pill me-1',
            html: '<i class="ri-check-double-line label-icon align-middle rounded-pill fs-16 me-2"></i> Yes, Update !'
        }).insertBefore('#view-application');
    }
});