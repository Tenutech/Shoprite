/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Form wizard Js File
*/

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
                    $("#requiredAlert").addClass("show");
                    var errors = $.parseJSON(jqXHR.responseText);
                    $.each(errors.errors, function(key, val){
                        $("input[name='" + key + "']").addClass("is-invalid");
                        $("#" + key + "_error").text(val[0]);
                    });

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