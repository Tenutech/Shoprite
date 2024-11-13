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
    //Position Choices
    const positionSelect = document.getElementById('position');
    if (positionSelect) {
        new Choices(positionSelect, {
            searchEnabled: true,               // Enable the search feature
            shouldSort: false,                 // Keep original order if desired
            searchFields: ['label'],           // Search within the option label
            searchFloor: 1,                    // Start search after typing 1 character
            allowHTML: true,                   // Enable HTML content within options
            fuseOptions: {                     // Fuzzy search options
                threshold: 0.3,                // Flexibility in search matching
                distance: 100,                 // Allows substring matching
                ignoreLocation: true,          // Matches text anywhere in the string
                findAllMatches: true           // Finds all possible matches
            }
        });
    }

    //Store Choices
    const storeSelect = document.getElementById('store');
    if (storeSelect) {
        new Choices(storeSelect, {
            searchEnabled: true,               // Enable the search feature
            shouldSort: true,                 // Keep original order if desired
            searchFields: ['label'],           // Search within the option label
            searchFloor: 1,                    // Start search after typing 1 character
            allowHTML: true,                   // Enable HTML content within options
            fuseOptions: {                     // Fuzzy search options
                threshold: 0.3,                // Flexibility in search matching
                distance: 100,                 // Allows substring matching
                ignoreLocation: true,          // Matches text anywhere in the string
                findAllMatches: true           // Finds all possible matches
            }
        });
    }
});

/*
|--------------------------------------------------------------------------
| SAP Numbers
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {
    const openPositionsInput = document.getElementById('openPositions');
    const positionSelect = document.getElementById('position');
    const sapNumbersContainer = document.getElementById('sapNumbersContainer');

    function updateSapNumberFields() {
        const numberOfPositions = parseInt(openPositionsInput.value) || 0;
        const positionName = positionSelect.options[positionSelect.selectedIndex].text || 'SAP Number';

        // Store the current values
        const currentValues = {};
        sapNumbersContainer.querySelectorAll('input[name="sap_numbers[]"]').forEach((input, index) => {
            currentValues[index] = input.value;
        });

        // Clear existing fields
        sapNumbersContainer.innerHTML = '';

        // Generate new fields based on the number of positions
        for (let i = 0; i < numberOfPositions; i++) {
            const fieldLabel = `${positionName} ${i + 1}`;
            const inputId = `sapNumber${i}`;

            // Create label
            const label = document.createElement('label');
            label.setAttribute('for', inputId);
            label.className = 'form-label';
            label.textContent = fieldLabel;

            // Create input field
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.id = inputId;
            input.name = 'sap_numbers[]';
            input.placeholder = `Enter 8-digit SAP Number`;
            input.maxLength = 8;
            input.pattern = '\\d{8}';
            input.required = true;

            // Restore the value if it exists
            if (currentValues[i] !== undefined) {
                input.value = currentValues[i];
            }

            // Create invalid feedback
            const invalidFeedback = document.createElement('div');
            invalidFeedback.className = 'invalid-feedback';
            invalidFeedback.textContent = 'Please enter an 8-digit SAP number.';

            // Create mb-3 wrapper div
            const div = document.createElement('div');
            div.className = 'mb-3';

            // Append elements to the wrapper div
            div.appendChild(label);
            div.appendChild(input);
            div.appendChild(invalidFeedback);

            // Append the wrapper div to the container
            sapNumbersContainer.appendChild(div);
        }
    }

    // Listen for changes on the openPositions input field
    openPositionsInput.addEventListener('input', function () {
        updateSapNumberFields();
    });

    // Listen for changes on the position select field
    positionSelect.addEventListener('change', function () {
        updateSapNumberFields();
    });

    // Initialize fields on page load if there are existing values
    updateSapNumberFields();
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
                    let requiredFields = Array.from(form.querySelectorAll(".tab-pane.show .form-control, .tab-pane.show .form-check-input"));

                    // First reset custom validity for all inputs
                    requiredFields.forEach(input => {
                        input.setCustomValidity('');
                    })

                    // Check if all required fields have been filled
                    let valid = requiredFields.every(input => {
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

                    // Validate SAP Number fields only in the current active tab
                    let sapNumberFields = form.querySelectorAll(".tab-pane.show input[name='sap_numbers[]']");
                    sapNumberFields.forEach(input => {
                        // Reset previous validation state
                        input.classList.remove('is-invalid');
                        let feedbackDiv = input.parentElement.querySelector('.invalid-feedback');
                        if (feedbackDiv) {
                            feedbackDiv.style.display = 'none';
                        }

                        // Check if the input value is exactly 8 digits
                        if (!/^\d{8}$/.test(input.value)) {
                            input.classList.add('is-invalid');
                            if (feedbackDiv) {
                                feedbackDiv.style.display = 'block';
                            }
                            valid = false; // Set valid to false if any SAP Number field in the active tab is invalid
                        }
                    });
        
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

$(document).on('submit', '#formVacancy, #formVacancyUpdate', function(e) {
    e.preventDefault();

    var formID = $(this).attr('id');
    var formData = new FormData(this);

    $("#confirm").hide();
    $("#loading").removeClass("d-none");

    if (formID === 'formVacancyUpdate') {
        $("#complete").hide();
    }

    if (this.checkValidity()) {
        var url;
        var method;
        if (formID === 'formVacancy') {
            url = route('vacancy.store');
            method = 'POST';
        } else if (formID === 'formVacancyUpdate') {
            url = route('vacancy.update');
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

                    if (formID === 'formVacancy') {
                        $('#id').val(data.encrypted_id);
                        $('#formVacancy').attr('id', 'formVacancyUpdate');
                        $("#view-vacancy").attr('href', route('job-overview.index', {id: data.encrypted_id}));
                        $("#confirm").remove();
                        $("#complete").removeClass("d-none");

                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            toast: true,
                            showCloseButton: true
                        });
                    } else if (formID === 'formVacancyUpdate') {                        
                        $('#lordicon').attr('src', 'https://cdn.lordicon.com/lupuorrc.json');
                        $('#completeHeading').text('Vacancy Updated !');
                        $('#completeText').text('You have succesfully updated this vacancy.');
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
                if (formID === 'formVacancy') {
                    $("#confirm").show();
                } else if (formID === 'formVacancyUpdate') {
                    $("#complete").show();
                }

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
    } else {
        this.reportValidity();
    }
});

/*
|--------------------------------------------------------------------------
| Form Cancel
|--------------------------------------------------------------------------
*/

$('#cancelBtn').click(function() {
    var positionTab = new bootstrap.Tab(document.getElementById('v-pills-position-tab'));
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
    var positionTab = new bootstrap.Tab($('#v-pills-position-tab')[0]);
    positionTab.show();

    // Step 2: Remove class done from steps
    $('.form-steps .nav-link').removeClass('done');
    
    // Step 3: Change the src attribute of the lord-icon element
    $('#lordicon').attr('src', 'https://cdn.lordicon.com/nocovwne.json');

    // Step 4: Update the heading and paragraph texts
    $('#completeHeading').text('Would you like to update this vacancy ?');
    $('#completeText').text('You are about to update this vacancy with new information.');

    // Step 5: Add the new button if it doesn't already exist
    if ($('#updateBtn').length === 0) {
        $('<button/>', {
            type: 'submit',
            id: 'updateBtn',
            class: 'btn btn-secondary btn-label waves-effect waves-light rounded-pill me-1',
            html: '<i class="ri-check-double-line label-icon align-middle rounded-pill fs-16 me-2"></i> Yes, Update !'
        }).insertBefore('#view-vacancy');
    }
});