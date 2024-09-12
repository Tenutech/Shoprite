/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job candidate list init js
*/

$(document).ready(function() {
    // When the vacancy select changes
    $('#vacancy').on('change', function() {
        // Get the selected vacancy ID
        var selectedVacancyID = $(this).val();

        // Set the hidden input field's value to the selected vacancy ID
        $('#vacancyID').val(selectedVacancyID);

        // Redirect to the shortlist route with the selected vacancy ID
        const url = route('shortlist.index', { id: selectedVacancyID });
        window.location.href = url;
    });

    fetchShortlistedApplicants();
});

var selectTown = document.getElementById("selectTown");
const filterTown = new Choices(selectTown, {
    searchEnabled: true
});

var slider = document.getElementById('rangeSlider');
noUiSlider.create(slider, {
    start: maxDistanceFromStore,
    step: 1,
    connect: 'lower',
    range: {
        'min': 0,
        'max': maxDistanceFromStore
    },
});

slider.noUiSlider.on('update', function(values, handle) {
    document.getElementById('rangeValue').innerText = 'Selected Range: ' + values[handle] + 'km';
});

var applicantChoices = new Choices('#applicants', {
    removeItemButton: true,
    searchEnabled: true,
    itemSelectText: '',
});

var vacancyChoices = new Choices('#applicantsVacancy', {
    removeItemButton: true,
    searchEnabled: true,
    itemSelectText: '',
});

var url = "manager/shortlist-data";
var allcandidateList = '';

var prevButton = document.getElementById('page-prev');
var nextButton = document.getElementById('page-next');

// configuration variables
var currentPage = 1;
var itemsPerPage = 8;

document.getElementById('generate-btn').addEventListener('click', function() {
    fetchData();
});

/*
|--------------------------------------------------------------------------
| Fetch Shortlisted Applicants
|--------------------------------------------------------------------------
*/

function fetchShortlistedApplicants() {
    if (vacancyID && shortlistedApplicants.length > 0) {
        $.ajax({
            url: 'manager/shortlist-applicants',
            type: 'GET',
            data: { vacancy_id: vacancyID },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success == true) {
                    allcandidateList = data.applicants;
    
                    if (allcandidateList.length === 0) {
                        // Hide the candidate list container and show the no result message
                        document.querySelector("#candidate-list").style.display = 'none';
                        document.querySelector(".noresult").style.display = 'block';
                    } else {
                        // Show the candidate list container and hide the no result message
                        document.querySelector("#candidate-list").style.display = 'block';
                        document.querySelector(".noresult").style.display = 'none';
                        loadCandidateListData(allcandidateList, currentPage);
                        paginationEvents();
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
            },
        });
    }
}

/*
|--------------------------------------------------------------------------
| Fetch Data
|--------------------------------------------------------------------------
*/

function fetchData() {
    // Get the input elements and their values
    var vacancySelect = document.getElementById('vacancy');
    var vacancy = vacancySelect.value;
    var numberInput = document.getElementById('number');
    var number = numberInput.value;
    var shortlistTypeSelect = document.getElementById('shortlistType');
    var shortlistType = shortlistTypeSelect.value;
    var applicantTypeSelect = document.getElementById('applicantType');
    var applicantType = applicantTypeSelect.value;

    // Validate the vacancy select
    if (vacancy === '') {
        let choicesDiv = vacancySelect.closest('.mb-3');
        if(choicesDiv) {
            let choicesContainer = choicesDiv.querySelector('.choices');
            if (choicesContainer) {
                choicesContainer.style.border = '1px solid #f17171';
            }
        }
        let feedbackDiv = choicesDiv.querySelector('.invalid-feedback');
        if (feedbackDiv) {
            feedbackDiv.style.display = 'block';
        }
        return; // Stop the function if validation fails
    } else {
        let choicesDiv = vacancySelect.closest('.mb-3');
        if(choicesDiv) {
            let choicesContainer = choicesDiv.querySelector('.choices');
            if (choicesContainer) {
                choicesContainer.style.border = '';
            }
        }
        let feedbackDiv = choicesDiv.querySelector('.invalid-feedback');
        if (feedbackDiv) {
            feedbackDiv.style.display = 'none';
        }
    }

    // Validate the number input
    if (number === '' || isNaN(number) || parseInt(number) < parseInt(minShortlistNumber)) {
        numberInput.classList.add('is-invalid');
        return; 
    } else if (number === '' || isNaN(number) || parseInt(number) > parseInt(maxShortlistNumber)) {
        numberInput.classList.add('is-invalid');
        return; 
    } else {
        numberInput.classList.remove('is-invalid');
    }
    
    // Validate the applicant type select
    /*
    if (applicantType === '') {
        let choicesDiv = applicantTypeSelect.closest('.mb-3');
        if(choicesDiv) {
            let choicesContainer = choicesDiv.querySelector('.choices');
            if (choicesContainer) {
                choicesContainer.style.border = '1px solid #f17171';
            }
        }
        let feedbackDiv = choicesDiv.querySelector('.invalid-feedback');
        if (feedbackDiv) {
            feedbackDiv.style.display = 'block';
        }
        return; // Stop the function if validation fails
    } else {
        let choicesDiv = applicantTypeSelect.closest('.mb-3');
        if(choicesDiv) {
            let choicesContainer = choicesDiv.querySelector('.choices');
            if (choicesContainer) {
                choicesContainer.style.border = '';
            }
        }
        let feedbackDiv = choicesDiv.querySelector('.invalid-feedback');
        if (feedbackDiv) {
            feedbackDiv.style.display = 'none';
        }
    }
    */

    // Get the generate button and disable it to prevent multiple clicks
    var generateBtn = document.getElementById('generate-btn');
    generateBtn.innerHTML = '<div class="spinner-border text-light" role="status"><span class="sr-only">Loading...</span></div>';
    generateBtn.disabled = true;    

    // Collect filter parameters
    var filters = {        
        applicant_type_id: document.getElementById('applicantType').value,
        ...activeFilters
    };

    var checks = {
        ...activeChecks
    }

    // Combine filters and checks into a single data object
    var requestData = {
        vacancy_id: vacancy,
        number: number,
        vacancy_id: vacancy,
        shortlist_type_id: shortlistType,
        filters: filters,
        checks: checks
    };

    // Use jQuery's ajax method to fetch the data
    $.ajax({
        url: url, // Your data source or endpoint
        type: 'GET',
        data: requestData,
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success == true) {
                allcandidateList = data.applicants;

                if (allcandidateList.length === 0) {
                    // Hide the candidate list container and show the no result message
                    document.querySelector("#candidate-list").style.display = 'none';
                    document.querySelector(".noresult").style.display = 'block';
                } else {
                    // Show the candidate list container and hide the no result message
                    document.querySelector("#candidate-list").style.display = 'block';
                    document.querySelector(".noresult").style.display = 'none';
                    loadCandidateListData(allcandidateList, currentPage);
                    paginationEvents();
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
        },
        complete: function() {
            // Re-enable the button and restore its original text after the operation is complete
            generateBtn.disabled = false;
            generateBtn.innerHTML = 'Generate Shortlist'; // Replace with your original button text
        }
    });
}

/*
|--------------------------------------------------------------------------
| Load Candidate List Data
|--------------------------------------------------------------------------
*/

function loadCandidateListData(datas, page) {
    var pages = Math.ceil(datas.length / itemsPerPage)
    if (page < 1) page = 1
    if (page > pages) page = pages
    document.querySelector("#candidate-list").innerHTML = '';

    for (var i = (page - 1) * itemsPerPage; i < (page * itemsPerPage) && i < datas.length; i++) {
        if (datas[i]) {
            var bookmark = datas[i].saved_by.length > 0 ? "active" : "";
            var interviewScore = '';

            var isUserProfile = datas[i].avatar ? '<img src="' + datas[i].avatar + '" alt="" class="member-img img-fluid d-block rounded" />'
                : '<img src="/images/avatar.jpg" alt="" class="member-img img-fluid d-block rounded" />';

                var checksHtml = '<div class="card-footer"><div class="d-flex flex-wrap gap-2">';
                for (var j = 0; j < datas[i].latest_checks.length; j++) {
                    var check = datas[i].latest_checks[j];
                    var checkID = check.id;
                    var checkName = check.name; // Get the name of the check
                    var checkIcon = check.icon; // Get the icon from the check data
                    var statusResult = check.pivot.result; // Get the result of the check to determine the status class
                
                    var status;
                    // Convert the result into a status class
                    switch (statusResult) {
                        case 'Passed':
                            status = 'success';
                            break;
                        case 'Discrepancy':
                            status = 'warning';
                            break;
                        case 'Failed':
                            status = 'danger';
                            break;
                        default:
                            status = 'danger';
                            break;
                    }
                
                    // Append each check as a column in the footer row
                    checksHtml += '<a href="'+ route('applicant-profile.index', {id: datas[i].encrypted_id}) + '#checks-tab" class="avatar-sm flex-shrink-0" id="check-' + checkID + '" data-bs-toggle="tooltip" data-bs-placement="top" title="' + checkName + '">' +
                                    '<span class="avatar-title bg-' + status + '-subtle text-' + status + ' rounded-circle fs-4">' +
                                        '<i class="' + checkIcon + '"></i>' +
                                    '</span>' +
                                  '</a>';
                }

                // Initialize interviewAlert as an empty string
                var interviewAlert = '';

                // Check if there are interviews and set the alert based on the status
                if (datas[i].interviews && datas[i].interviews.length > 0) {
                    //Covert Time Function
                    function formatTimeTo24Hour(dateTimeString) {
                        const dateTimeParts = dateTimeString.split(" ");
                        const timePart = dateTimeParts[1] ? dateTimeParts[1] : dateTimeParts[0];
                        const date = new Date(dateTimeString);
                    
                        // Assuming the server's time zone is consistent with South Africa (UTC+2)
                        const offsetInHours = 2;
                        date.setUTCHours(date.getUTCHours() + offsetInHours);
                    
                        const hours = ("0" + date.getHours()).slice(-2); // Ensure two digits
                        const minutes = ("0" + date.getMinutes()).slice(-2); // Ensure two digits
                    
                        return `${hours}:${minutes}`;
                    }
                    
                    // A function to format full date-time strings for the reschedule scenario
                    function formatFullDateTime(dateTimeString) {
                        const date = new Date(dateTimeString);

                        // Adjust for the time zone, similar to the formatTimeTo24Hour function
                        const offsetInHours = 2; // Adjust for South Africa's time zone
                        date.setUTCHours(date.getUTCHours() + offsetInHours);

                        const day = ("0" + date.getDate()).slice(-2); // Ensure two digits
                        const month = date.toLocaleString('en-US', { month: 'short' }); // Get abbreviated month name
                        const year = date.getFullYear();
                        const hours = ("0" + date.getHours()).slice(-2); // Ensure two digits
                        const minutes = ("0" + date.getMinutes()).slice(-2); // Ensure two digits

                        return `${day} ${month} at ${hours}:${minutes}`;
                    }

                    var interview = datas[i].interviews[datas[i].interviews.length - 1]; // Assuming we're only interested in the last interview

                    var interviewDate = new Date(interview.scheduled_date);
                    var day = ("0" + interviewDate.getDate()).slice(-2); // Ensure two digits
                    var month = interviewDate.toLocaleString('en-US', { month: 'short' }); // Get abbreviated month name
                    var formattedDate = `${day} ${month}`;
                    var formattedTime = formatTimeTo24Hour(interview.start_time);

                    const statusMapping = {
                        'Scheduled': {
                            class: 'alert-warning',
                            icon: 'ri-calendar-todo-fill',
                            text: 'Scheduled'
                        },
                        'Confirmed': {
                            class: 'alert-success',
                            icon: 'ri-calendar-check-fill',
                            text: 'Confirmed'
                        },
                        'Declined': {
                            class: 'alert-danger',
                            icon: 'ri-calendar-2-fill',
                            text: 'Declined'
                        },
                        'Reschedule': {
                            class: 'alert-info',
                            icon: 'ri-calendar-event-fill',
                            text: 'Reschedule'
                        },
                        'Completed': {
                            class: 'alert-success',
                            icon: 'ri-calendar-check-fill',
                            text: 'Completed'
                        },
                        'Cancelled': {
                            class: 'alert-dark',
                            icon: 'ri-calendar-2-fill',
                            text: 'Cancelled'
                        },
                        'No Show': {
                            class: 'alert-danger',
                            icon: 'ri-user-unfollow-fill',
                            text: 'No Show'
                        }
                    };

                    // Build the interviewAlert based on the interview status
                    if (statusMapping[interview.status]) {
                        const statusInfo = statusMapping[interview.status];
                        let additionalText = '';
                    
                        if (interview.status === 'Reschedule' && interview.reschedule_date) {
                            const rescheduledDateTime = formatFullDateTime(interview.reschedule_date);
                            additionalText = `<br><strong>Suggested:</strong> ${rescheduledDateTime}`;
                        }
                    
                        interviewAlert = `<div class="alert ${statusInfo.class} alert-dismissible alert-label-icon rounded-label fade show mb-0" role="alert">
                                            <i class="${statusInfo.icon} label-icon"></i><strong>${statusInfo.text}: </strong>${formattedDate} at ${formattedTime}${additionalText}
                                          </div>`;
                    } else {
                        interviewAlert = `<div class="alert alert-warning alert-dismissible alert-label-icon rounded-label fade show mb-0" role="alert">
                                            <i class="ri-calendar-todo-fill label-icon"></i><strong>Scheduled: </strong>${formattedDate} at ${formattedTime}
                                          </div>`;
                    }

                    if (interview.score) {
                        var interviewScore = '<div class="badge text-bg-primary">\
                                                <i class="mdi mdi-star me-1"></i>\
                                                '+ (interview.score ? interview.score : 'N/A') + '\
                                            </div>';         
                    }
                }

                // Append the interview alert after the checksHtml if it exists
                if (interviewAlert) {
                    checksHtml += interviewAlert;
                }

                // Initialize contractAlert as an empty string
                var contractAlert = '';

                // Check if there are contract and set the alert based on the status
                if (datas[i].contracts && datas[i].contracts.length > 0) {
                    contractAlert = '<div class="alert alert-success alert-dismissible alert-label-icon rounded-label fade show mb-0 alert-contract" role="alert">' +
                                        '<i class="ri-article-fill label-icon"></i><strong>Contract Sent</strong>' + 
                                    '</div>';
                }

                // Append the contract alert after the checksHtml if it exists
                if (interviewAlert) {
                    checksHtml += contractAlert;
                }

                checksHtml += '</div></div>';

                var cardBorder = '';

                if (datas[i].vacancies_filled && datas[i].vacancies_filled.length > 0) {
                    cardBorder = 'border card-border-success';
                }

            document.querySelector("#candidate-list").innerHTML += 
                '<div class="col-md-12 col-lg-12 candidate-card" data-candidate-id="' + datas[i].id + '">\
                    <div class="card ' + cardBorder + ' mb-0">\
                        <div class="card-body">\
                            <div class="d-lg-flex align-items-center">\
                                <div class="form-check">\
                                    <input class="form-check-input" type="checkbox" name="chk_child" value="'+ datas[i].encrypted_id + '" data-bs-id="'+ datas[i].id + '" data-bs-name="'+ datas[i].firstname + ' '+ datas[i].lastname + '">\
                                </div>\
                                <div class="flex-shrink-0 col-auto">\
                                    <div class="avatar-sm rounded overflow-hidden">\
                                        '+ isUserProfile + '\
                                    </div>\
                                </div>\
                                <div class="ms-lg-3 my-3 my-lg-0 col-3 text-start">\
                                    <a href="'+ route('applicant-profile.index', {id: datas[i].encrypted_id}) +'">\
                                        <h5 class="fs-16 mb-2">\
                                            '+ datas[i].firstname + ' '+ datas[i].lastname + '\
                                        </h5>\
                                    </a>\
                                    <p class="text-muted mb-0">\
                                        '+ (datas[i].position ? datas[i].position.name : 'N/A') + '\
                                    </p>\
                                </div>\
                                <div class="d-flex gap-4 mt-0 text-muted mx-auto col-2">\
                                    <div><i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i>\
                                        '+ (datas[i].town ? datas[i].town.name : 'N/A') + '\
                                    </div>\
                                </div>\
                                <div class="col-2">\
                                    <i class="ri-time-line text-primary me-1 align-bottom"></i>'+ 
                                    (datas[i].type ? '<span class="badge bg-' + datas[i].type.color + '-subtle text-' + datas[i].type.color + '">' + datas[i].type.name + '</span>' : 'N/A') +
                                '</div>\
                                <div class="d-flex flex-wrap gap-2 align-items-center mx-auto my-3 my-lg-0 col-1">\
                                    <div class="badge text-bg-success">\
                                        <i class="mdi mdi-star me-1"></i>\
                                        '+ (datas[i].score ? datas[i].score : 'N/A') + '\
                                    </div>\
                                    '+ (interviewScore ? interviewScore : '') + '\
                                </div>\
                                <div class="col-2 text-end">\
                                    <a href="'+ route('applicant-profile.index', {id: datas[i].encrypted_id}) +'" class="btn btn-soft-primary">\
                                        View Details\
                                    </a>\
                                    <a href="#!" class="btn btn-ghost-danger btn-icon custom-toggle '+ bookmark + ' save-applicant" data-bs-toggle="button" data-bs-id='+ datas[i].encrypted_id + '>\
                                        <span class="icon-on">\
                                            <i class="ri-bookmark-line align-bottom"></i>\
                                        </span>\
                                        <span class="icon-off">\
                                            <i class="ri-bookmark-3-fill align-bottom"></i>\
                                        </span>\
                                    </a>\
                                    <button class="btn btn-soft-dark candidate-close-btn" data-candidate-id="' + datas[i].id + '">\
                                        <i class="ri-close-circle-line align-bottom fs-16"></i>\
                                    </button>\
                                </div>\
                            </div>\
                        </div>\
                        ' + checksHtml + '\
                    </div>\
                </div>'
        }

        // Add event listeners to the close buttons
        document.querySelectorAll('.candidate-close-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                var candidateId = this.getAttribute('data-candidate-id');
                removeCandidate(candidateId);
            });
        });
    }

    selectedPage();
    currentPage == 1 ? prevButton.parentNode.classList.add('disabled') : prevButton.parentNode.classList.remove('disabled');
    currentPage == pages ? nextButton.parentNode.classList.add('disabled') : nextButton.parentNode.classList.remove('disabled');
}

/*
|--------------------------------------------------------------------------
| Get Status Class
|--------------------------------------------------------------------------
*/

function getStatusClass(statusResult) {
    switch (statusResult) {
        case 'Passed':
            return 'success';
        case 'Discrepancy':
            return 'warning';
        case 'Failed':
            return 'danger';
        default:
            return 'danger';
    }
}

var checkAll = document.getElementById("checkAll");
if (checkAll) {
  checkAll.addEventListener('change', function () {
    // Select all checkboxes with the name 'chk_child'
    var checkboxes = document.querySelectorAll('input[type="checkbox"][name="chk_child"]');
    // Loop over them
    for (var i = 0; i < checkboxes.length; i++) {
      // Change each checkbox state to match the "Check All" state
      checkboxes[i].checked = this.checked;
      // Add or remove the "table-active" class based on the "Check All" state
      if (this.checked) {
        checkboxes[i].closest(".candidate-card").classList.add("table-active");
      } else {
        checkboxes[i].closest(".candidate-card").classList.remove("table-active");
      }
    }
  });
}

/*
|--------------------------------------------------------------------------
| Choices
|--------------------------------------------------------------------------
*/

var sapNumber = document.getElementById("sapNumber");

var sapNumberChoices = new Choices(sapNumber, {
    searchEnabled: true,
    shouldSort: false
});

/*
|--------------------------------------------------------------------------
| Interview
|--------------------------------------------------------------------------
*/

// This array will hold the selected applicants' data
var selectedApplicants = [];

// Select the Interview button
var interviewButton = document.querySelector('#interviewBtn');

// Add click event listener to the Interview button
if (interviewButton) {
    interviewButton.addEventListener('click', function(event) {
        // Find all checked checkboxes for the candidates
        var checkedCheckboxes = document.querySelectorAll('input[type="checkbox"][name="chk_child"]:checked');
        
        // Reset the selectedApplicants array
        selectedApplicants = [];

        // Check if there are any checked checkboxes
        if (checkedCheckboxes.length === 0) {
            // Show SweetAlert notification
            Swal.fire({
                title: 'Please select at least one applicant',
                confirmButtonClass: 'btn btn-info',
                buttonsStyling: false,
                showCloseButton: true
            });
        } else if (checkedCheckboxes.length > 2) {
                Swal.fire({
                    title: 'Please only select two applicants at a time',
                    confirmButtonClass: 'btn btn-info',
                    buttonsStyling: false,
                    showCloseButton: true
                });
        } else {
            // Push the selected applicants' data into the selectedApplicants array
            checkedCheckboxes.forEach(function(checkbox) {
                var id = checkbox.getAttribute('data-bs-id');
                var name = checkbox.getAttribute('data-bs-name');
                selectedApplicants.push({ id: id, name: name });
            });

            // Manually open the modal using jQuery
            $('#interviewModal').modal('show');
        }
    });
}

// Event listener for when the modal is shown
$('#interviewModal').on('shown.bs.modal', function () {
    // Clear current choices
    applicantChoices.clearChoices();
  
    // Add the selected applicants as choices
    selectedApplicants.forEach(function(applicant) {
        applicantChoices.setChoices([
            { value: applicant.id, label: applicant.name, selected: true },
        ], 'value', 'label', false);
    });
});

$('#interviewModal').on('hidden.bs.modal', function () {
    // Clear all selected choices when the modal is closed
    applicantChoices.clearStore();
    clearFields();
});

/*
|--------------------------------------------------------------------------
| Fill Vacancy
|--------------------------------------------------------------------------
*/

// Select the Vacancy button
var vacancyBtn = document.querySelector('#vacancyBtn');

// Add click event listener to the Vacancy button
if (vacancyBtn) {
    vacancyBtn.addEventListener('click', function(event) {
        // Find all checked checkboxes for the candidates
        var checkedCheckboxes = document.querySelectorAll('input[type="checkbox"][name="chk_child"]:checked');
        
        // Reset the selectedApplicants array
        selectedApplicants = [];

        // Check if more than one checkbox is checked
        if (checkedCheckboxes.length > 1) {
            // Show SweetAlert notification
            Swal.fire({
                title: 'Please select only one applicant at a time',
                confirmButtonClass: 'btn btn-info',
                buttonsStyling: false,
                showCloseButton: true
            });
        } 
        // Check if no checkboxes are checked
        else if (checkedCheckboxes.length === 0) {
            // Show SweetAlert notification
            Swal.fire({
                title: 'Please select at least one applicant',
                confirmButtonClass: 'btn btn-info',
                buttonsStyling: false,
                showCloseButton: true
            });
        } else {
            // Push the selected applicant's data into the selectedApplicants array
            checkedCheckboxes.forEach(function(checkbox) {
                var id = checkbox.getAttribute('data-bs-id');
                var name = checkbox.getAttribute('data-bs-name');
                selectedApplicants.push({ id: id, name: name });
            });

            // Manually open the modal using jQuery
            $('#vacancyModal').modal('show');
        }
    });
}

// Event listener for when the modal is shown
$('#vacancyModal').on('shown.bs.modal', function () {
    // Clear current choices
    vacancyChoices.clearChoices();
  
    // Add the selected applicants as choices
    selectedApplicants.forEach(function(applicant) {
        vacancyChoices.setChoices([
            { value: applicant.id, label: applicant.name, selected: true },
        ], 'value', 'label', false);
    });
});

$('#vacancyModal').on('hidden.bs.modal', function () {
    // Clear all selected choices when the modal is closed
    vacancyChoices.clearStore();
    clearFields();
});

/*
|--------------------------------------------------------------------------
| Remove Candidate
|--------------------------------------------------------------------------
*/

function removeCandidate(candidateId) {
    $.ajax({
        url: 'manager/shortlist-update',
        type: 'POST',
        data: {
            vacancy_id: vacancyID,
            applicant_id: candidateId
        },
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success == true) {
                // Remove the candidate from the allcandidateList array
                allcandidateList = allcandidateList.filter(function(candidate) {
                    return candidate.id.toString() !== candidateId.toString();
                });

                // Remove the candidate card from the DOM
                var candidateCard = document.querySelector('.candidate-card[data-candidate-id="' + candidateId + '"]');
                if (candidateCard) {
                    candidateCard.remove();
                }

                // Optionally, you can refresh the candidate list or show a message if allcandidateList is empty
                if (allcandidateList.length === 0) {
                    // Hide the candidate list container and show the no result message
                    document.querySelector("#candidate-list").style.display = 'none';
                    document.querySelector(".noresult").style.display = 'block';
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
        },
    });
}

/*
|--------------------------------------------------------------------------
| Selected Page
|--------------------------------------------------------------------------
*/

function selectedPage() {
    var pagenumLink = document.getElementById('page-num').getElementsByClassName('clickPageNumber');
    for (var i = 0; i < pagenumLink.length; i++) {
        if (i == currentPage - 1) {
            pagenumLink[i].parentNode.classList.add("active");
        } else {
            pagenumLink[i].parentNode.classList.remove("active");
        }
    }
};

/*
|--------------------------------------------------------------------------
| Pagination Events
|--------------------------------------------------------------------------
*/

// paginationEvents
function paginationEvents() {
    var numPages = function numPages() {
        return Math.ceil(allcandidateList.length / itemsPerPage);
    };

    function clickPage() {
        document.addEventListener('click', function (e) {
            if (e.target.nodeName == "A" && e.target.classList.contains("clickPageNumber")) {
                currentPage = e.target.textContent;
                loadCandidateListData(allcandidateList, currentPage);
            }
        });
    };

    function pageNumbers() {
        var pageNumber = document.getElementById('page-num');
        pageNumber.innerHTML = "";
        // for each page
        for (var i = 1; i < numPages() + 1; i++) {
            pageNumber.innerHTML += "<div class='page-item'><a class='page-link clickPageNumber' href='javascript:void(0);'>" + i + "</a></div>";
        }
    }

    prevButton.addEventListener('click', function () {
        if (currentPage > 1) {
            currentPage--;
            loadCandidateListData(allcandidateList, currentPage);
        }
    });

    nextButton.addEventListener('click', function () {
        if (currentPage < numPages()) {
            currentPage++;
            loadCandidateListData(allcandidateList, currentPage);
        }
    });

    pageNumbers();
    clickPage();
    selectedPage();
}

/*
|--------------------------------------------------------------------------
| Google Map
|--------------------------------------------------------------------------
*/

var map;
var selectedLocation;
var center = {lat: -30.5595, lng: 22.9375};
var marker;

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 7,
        center: center
    });

    // Add a double-click event listener to the map
    map.addListener('dblclick', function(event) {
        selectedLocation = {
            lat: event.latLng.lat(),
            lng: event.latLng.lng()
        };

        // Place a marker on the map at the clicked location
        if (marker) {
            marker.setMap(null);  // Remove the existing marker
        }
        marker = new google.maps.Marker({
            position: selectedLocation,
            map: map
        });

        // Add a removable badge for this location
        addLocationBadge(selectedLocation, true);
        applyLocationFilter(selectedLocation);
    });

    // Add location badge on load if coordinates and maxDistanceFromStore are set
    if (coordinates) {
        // Split the coordinates string into latitude and longitude
        var coordArray = coordinates.split(', ');
        
        // Check if we successfully split the coordinates
        if (coordArray.length === 2) {
            coordinates = {
                lat: parseFloat(coordArray[0]),  // Convert latitude to a float
                lng: parseFloat(coordArray[1])   // Convert longitude to a float
            };

            // Ensure both lat and lng are valid and maxDistanceFromStore is set
            if (!isNaN(coordinates.lat) && !isNaN(coordinates.lng) && maxDistanceFromStore) {
                // Add a non-removable location badge with the maxDistanceFromStore
                addLocationBadge(coordinates, false, maxDistanceFromStore);
                applyLocationFilter(coordinates);
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Calculate Distance
|--------------------------------------------------------------------------
*/

function calculateDistance(lat1, lon1, lat2, lon2) {
    var R = 6371; // Radius of the earth in km
    var dLat = (lat2 - lat1) * Math.PI / 180;  
    var dLon = (lon2 - lon1) * Math.PI / 180;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c; // Distance in km
    return d;
}

// Load the map once the modal is fully shown
$('#mapModal').on('shown.bs.modal', function() {
    google.maps.event.trigger(map, 'resize');
});

// Listen to changes on the dropdown
selectTown.addEventListener('change', function() {
    const selectedValue = this.value.split(';');
    const key = selectedValue[0];
    const value = selectedValue[1];
    
    // Get the label as the option text
    const label = this.options[this.selectedIndex].textContent;

    if (!badgeExists(label, 'filterBadges')) {
        addBadge(key, value, label);
        applyFilter(key, value); 
    }
});

let currentSort = { key: null, order: 'asc' };

document.querySelectorAll('.filter-button').forEach(button => {
    button.addEventListener('click', function() {
        const filter = this.getAttribute('data-bs-filter').split(';');
        const label = this.innerText;
        const value = filter[1];

        if (!badgeExists(label, 'filterBadges')) {
            addBadge(filter[0], value, label);

            if (value === 'literacy' || value === 'numeracy') {
                currentSort.key = filter[0]; // set the sorting key
                currentSort.value = value;
                currentSort.order = 'desc'; // for descending order
            } else {
                applyFilter(filter[0], value);
            }
        }
    });
});

document.querySelectorAll('.check-button').forEach(button => {
    button.addEventListener('click', function() {
        const check = this.getAttribute('data-bs-check').split(';');
        const key = check[0];
        const value = check[1] || 'Yes';
        const label = this.innerText;

        if (!badgeExists(label, 'checkBadges')) {
            addBadge(key, value, label, true);
            applyCheck(key, value);
        }
    });
});

/*
|--------------------------------------------------------------------------
| Badge Exists
|--------------------------------------------------------------------------
*/

function badgeExists(value, containerId) {
    const badges = document.querySelectorAll(`#${containerId} .badge`);
    for (let badge of badges) {
        // Get only the text content directly contained in the badge, excluding child elements
        let badgeText = "";
        for (let node of badge.childNodes) {
            if (node.nodeType === Node.TEXT_NODE) {
                badgeText += node.textContent.trim();
            }
        }
        // Now badgeText will contain only the text, which can be compared to the value
        if (badgeText === value.trim()) {
            return true;
        }
    }
    return false;
}

/*
|--------------------------------------------------------------------------
| Add Location Badge
|--------------------------------------------------------------------------
*/

function addLocationBadge(location, removable = true, radius = null) {
    const badgeContainer = document.getElementById('filterBadges');
    
    // Use the provided radius if passed, otherwise fall back to the slider's value
    const selectedRadius = radius ? radius : parseFloat(slider.noUiSlider.get());

    // Format coordinates to higher precision for more accuracy
    const badgeLabel = `${selectedRadius}km from: (${location.lat}, ${location.lng})`;

    // Check if the badge already exists
    if (!badgeExists(badgeLabel, 'filterBadges')) {
        const badge = document.createElement('span');
        badge.className = "badge bg-primary d-flex align-items-center";
        badge.setAttribute('data-key', "coordinates");
        badge.setAttribute('data-value', badgeLabel);
        badge.innerHTML = `
            ${badgeLabel}
            ${removable ? `
            <span class="border-start border-light mx-1" style="height: 16px;"></span>
            <button class="btn-close btn-close-white" type="button" aria-label="Close"></button>
            ` : ''}
        `;

        if (removable) {
            badge.querySelector('.btn-close').addEventListener('click', function() {
                badgeContainer.removeChild(badge);
            });
        }

        badgeContainer.appendChild(badge);
    }
}

/*
|--------------------------------------------------------------------------
| Add Badge
|--------------------------------------------------------------------------
*/

function addBadge(key, value, label, isCheck = false) {
    const badgeContainer = isCheck ? document.getElementById('checkBadges') : document.getElementById('filterBadges');

    const badge = document.createElement('span');
    badge.className = "badge bg-primary d-flex align-items-center";
    badge.setAttribute('data-key', key);
    badge.setAttribute('data-value', value);
    badge.innerHTML = `
        ${label}
        <span class="border-start border-light mx-1" style="height: 16px;"></span>
        <button class="btn-close btn-close-white" type="button" aria-label="Close"></button>
    `;

    badge.querySelector('.btn-close').addEventListener('click', function() {
        if (badge.getAttribute('data-value') === currentSort.value) {
            currentSort = { key: null, value: null, order: 'asc' }; // reset sorting
        }

        if (isCheck) {
            removeCheck(key, value);
        } else {
            removeFilter(key, value);
        }
        
        badgeContainer.removeChild(badge);
    });

    badgeContainer.appendChild(badge);
}

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const activeFilters = {};

function applyLocationFilter(location) {
    const selectedRadius = parseFloat(slider.noUiSlider.get());
    const formattedLocation = `${selectedRadius}km from: (${location.lat}, ${location.lng})`;
    activeFilters["coordinates"] = formattedLocation;
}

function applyFilter(key, value) {
    if (!activeFilters[key]) {
        activeFilters[key] = [];
    }
    
    // Check if the value already exists for this key
    if (activeFilters[key].indexOf(value) === -1) {
        activeFilters[key].push(value);
    }
}

/*
|--------------------------------------------------------------------------
| Checks
|--------------------------------------------------------------------------
*/

// Define activeChecks to store the checks
const activeChecks = {};

// Function to apply a check
function applyCheck(key, value) {
    // If the key doesn't exist, create an array for it
    if (!activeChecks[key]) {
        activeChecks[key] = [];
    }
    
    // Add the value to the array for the key if it doesn't already exist
    if (activeChecks[key].indexOf(value) === -1) {
        activeChecks[key].push(value);
    }
}

function removeFilter(key, value) {
    if (activeFilters[key]) {
        const index = activeFilters[key].indexOf(value);
        if (index !== -1) {
            activeFilters[key].splice(index, 1);
        }

        // If no more values for this key, delete the key from activeFilters
        if (activeFilters[key].length === 0) {
            delete activeFilters[key];
        }

        if (key == "coordinates") {
            delete activeFilters[key];
            selectedLocation = null;
        }
    }
}

// Function to remove a check
function removeCheck(key, value) {
    // Check if the key exists
    if (activeChecks[key]) {
        // Find the index of the value
        const index = activeChecks[key].indexOf(value);
        // If the value exists, remove it
        if (index !== -1) {
            activeChecks[key].splice(index, 1);
        }

        // If there are no more values for the key, delete the key from activeChecks
        if (activeChecks[key].length === 0) {
            delete activeChecks[key];
        }
    }
}

/*
|--------------------------------------------------------------------------
| Clear Fields
|--------------------------------------------------------------------------
*/

function clearFields() {
    // Reset text inputs
    //$('#date').val('');
    //$('#startTime').val('');
    //$('#endTime').val('');
    //$('#location').val('');
    //$('#notes').val('');

    // Reset the Choices.js multi-select
    var applicantsSelect = $('#applicants')[0];
    var choicesInstance = new Choices(applicantsSelect);
    choicesInstance.removeActiveItems();

    sapNumberChoices.removeActiveItems();
    sapNumberChoices.setChoiceByValue("");
}

/*
|--------------------------------------------------------------------------
| Form Interview
|--------------------------------------------------------------------------
*/

$('#formInterview').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var isValid = true; // Flag to determine if the form is valid

    // Clear previous invalid feedback
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').hide();
    $('.choices').css('border', '');

    // Validate each field
    $('#formInterview').find('input, select, textarea').each(function() {
        var element = $(this); // Current element
        var value = element.val(); // Value of the element
        var isRequired = element.prop('required'); // Is it required?
        var elementType = element.attr('type'); // Type of element

        // Check if the field is required and empty
        if (isRequired && !value) {
            isValid = false;
            element.addClass('is-invalid');
            element.siblings('.invalid-feedback').show();
        }

        // Additional validation for specific types
        if (elementType === 'email' && value && !validateEmail(value)) {
            isValid = false;
            element.addClass('is-invalid');
            element.siblings('.invalid-feedback').show();
        }

        // Custom validation for Choices.js select
        if (element.hasClass('choices-select') && !value) {
            isValid = false;
            var choicesDiv = element.closest('.mb-3');
            choicesDiv.find('.choices').css('border', '1px solid #f17171');
            choicesDiv.find('.invalid-feedback').show();
        }

        // Custom validation for time comparison
        if (element.attr('id') === 'endTime') {
            var startTime = $('#startTime').val();
            var endTime = element.val();
            // Assuming time is in HH:mm format
            if (startTime && endTime && startTime >= endTime) {
                isValid = false;
                element.addClass('is-invalid');
                element.siblings('.invalid-feedback').show().text('End time must be after start time.');
            }
        }
    });

    // If the form is not valid, stop here
    if (!isValid) {
        return;
    }

    var selectedChoices = applicantChoices.getValue(true);

    if (this.checkValidity()) {
        $.ajax({
            url: route('interview.store'),
            type: 'POST',
            data: formData,
            async: true,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){                
                if (data.success == true) {
                    // For each selected applicant, add the alert if it doesn't exist
                    selectedChoices.forEach(function(applicantID) {
                        var candidateCard = document.querySelector('.candidate-card[data-candidate-id="' + applicantID + '"]');
                        if (candidateCard) {
                            // Check if the alert div already exists
                            var existingAlert = candidateCard.querySelector('.alert');

                            if (existingAlert) {
                                // Remove existing status classes
                                existingAlert.classList.remove('alert-danger', 'alert-success', 'alert-info');
                                // Add new status class
                                existingAlert.classList.add('alert-warning');

                                // Update existing alert with new information
                                existingAlert.innerHTML = '<i class="ri-calendar-todo-fill label-icon"></i><strong>Scheduled: </strong>' + data.date + ' at ' + data.time;
                            } else {
                                // Create and append a new alert div
                                var alertHtml = '<div class="alert alert-warning alert-dismissible alert-label-icon rounded-label fade show mb-0" role="alert">' +
                                                '<i class="ri-calendar-todo-fill label-icon"></i><strong>Scheduled: </strong>' + data.date + ' at ' + data.time + 
                                                '</div>';
                                // Append the alertHtml to the card footer
                                var cardFooter = candidateCard.querySelector('.card-footer .d-flex');
                                if (cardFooter) {
                                    cardFooter.insertAdjacentHTML('beforeend', alertHtml);
                                }
                            }
                        }
                    });
            
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        toast: true,
                        showCloseButton: true
                    });
            
                    $('#interviewModal').modal('hide');
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
    } else {
        this.reportValidity();
    }
});

/*
|--------------------------------------------------------------------------
| Vacancy Fill
|--------------------------------------------------------------------------
*/

$('#formVacancy').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var isValid = true; // Flag to determine if the form is valid

    // Clear previous invalid feedback
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').hide();
    $('.choices').css('border', '');

    // Validate each field
    $('#formVacancy').find('input, select, textarea').each(function() {
        var element = $(this); // Current element
        var value = element.val(); // Value of the element
        var isRequired = element.prop('required'); // Is it required?
        var elementType = element.attr('type'); // Type of element

        // Check if the field is required and empty
        if (isRequired && (!value || value.length === 0)) {
            isValid = false;
            // Custom validation for Choices.js select
            if (element.hasClass('choices__input')) {
                var choicesDiv = element.closest('.mb-3');
                choicesDiv.find('.choices').css('border', '1px solid #f17171');
                choicesDiv.find('.invalid-feedback').show();
            } else {
                element.addClass('is-invalid');
                element.siblings('.invalid-feedback').show();
            }
        }
    });

    // If the form is not valid, stop here
    if (!isValid) {
        return;
    }

    var selectedChoices = vacancyChoices.getValue(true);

    // Show loader and hide the submit button
    $('#loading-vacancy').removeClass('d-none');
    $('#vacancy-fill').hide();

    if (this.checkValidity()) {
        $.ajax({
            url: route('vacancy.fill'),
            type: 'POST',
            data: formData,
            async: true,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){                
                if (data.success == true) {

                    // For each selected applicant, add the alert if it doesn't exist
                    selectedChoices.forEach(function(applicantID) {
                        var candidateCard = document.querySelector('.candidate-card[data-candidate-id="' + applicantID + '"]');

                        if (candidateCard) {
                            // Add card-border-success class to the card div
                            var cardDiv = candidateCard.querySelector('.card');
                            if (cardDiv && !cardDiv.classList.contains('card-border-success')) {
                                cardDiv.classList.add('border');
                                cardDiv.classList.add('card-border-success');
                            }
                        }
                    });

                    // Reload SAP Number options
                    sapNumberChoices.clearChoices(); // Clear existing choices
                    if (data.vacancy.available_sap_numbers.length > 0) {
                        sapNumberChoices.setChoices(
                            data.vacancy.available_sap_numbers.map(function(sap) {
                                return {
                                    value: sap.id, // Use ID or value field as needed
                                    label: sap.sap_number,
                                    selected: sapNumberChoices.getValue(true) == sap.id // Optional: set selected if needed
                                };
                            }),
                            'value', 'label', true
                        );
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

                    $('#loading-vacancy').addClass('d-none');
                    $('#vacancy-fill').show();
            
                    $('#vacancyModal').modal('hide');
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

                $('#loading-vacancy').addClass('d-none');
                $('#vacancy-fill').show();
            }
        });
    } else {
        this.reportValidity();
    }
});