/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job candidate list init js
*/

/*
|--------------------------------------------------------------------------
| Date Fields
|--------------------------------------------------------------------------
*/

const tomorrow = new Date();
tomorrow.setDate(tomorrow .getDate() + 1); // Set the date to one days from today

// Restrict the date picker to not allow past date
flatpickr("#date", {
    dateFormat: "d M, Y",
    minDate: "today", // Disables past dates
    defaultDate: tomorrow, // Set the default date to tomorrow
});

// Set default start time to current hour + 1 and end time to +2 hours
var currentDate = new Date();
var currentHour = currentDate.getHours();
var startTime = (currentHour + 1) % 24; // Start time is current hour + 1
var endTime = (currentHour + 2) % 24; // End time is start time + 1 hour

// Initialize flatpickr for start time
var startTimePicker = flatpickr("#startTime", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    defaultDate: new Date(currentDate.setHours(startTime, 0, 0)), // Set the start time to current hour + 1
    time_24hr: true,
    defaultHour: startTime,
    defaultMinute: 0, // Set default minute to 0
    onChange: function(selectedDates, dateStr, instance) {
        // When start time is updated, adjust the end time accordingly
        var selectedStartTime = new Date(selectedDates[0]);
        var minEndTime = new Date(selectedStartTime.getTime() + 30 * 60000); // Minimum end time is +30 minutes
        var maxEndTime = new Date(selectedStartTime.getTime() + 60 * 60000); // Maximum end time is +1 hour

        // Update the end time picker with the new values
        endTimePicker.setDate(maxEndTime, true); // Set the default end time to 30 minutes after start
        endTimePicker.set({
            minTime: minEndTime.toTimeString().slice(0, 5), // Update minTime dynamically
            maxTime: maxEndTime.toTimeString().slice(0, 5)  // Update maxTime dynamically
        });
    }
});

// Initialize flatpickr for end time with default values
var endTimePicker = flatpickr("#endTime", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    defaultDate: new Date(currentDate.setHours(endTime, 0, 0)), // Set the end time to start time + 1 hour
    time_24hr: true,
    defaultHour: endTime,
    defaultMinute: 0, // Set default minute to 0
});

/*
|--------------------------------------------------------------------------
| Load Data On Vacancy Change
|--------------------------------------------------------------------------
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

/*
|--------------------------------------------------------------------------
| Initialize Fields
|--------------------------------------------------------------------------
*/

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

var generateButton = document.getElementById('generate-btn');

if (generateButton) {
    generateButton.addEventListener('click', function() {
        fetchData();
    });
}

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

                // Set the value of the input field with id="number" to the applicantCount
                if (data.applicantCount) {
                    document.getElementById('number').value = data.applicantCount;
                }

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
            var employment = datas[i].employment || 'I'; // Use 'I' if employment is null
            var checkID = datas[i].encrypted_id; // Assuming you still need the ID
            var status, tooltip;
        
            // Set status and tooltip based on employment
            switch (employment) {
                case 'A':
                    status = 'warning';
                    tooltip = 'Active Employee';
                    break;
                case 'B':
                    status = 'danger';
                    tooltip = 'Blacklisted';
                    break;
                case 'P':
                    status = 'info';
                    tooltip = 'Previously Employed';
                    break;
                case 'N':
                    status = 'success';
                    tooltip = 'Not an Employee';
                    break;
                case 'I':
                default:
                    status = 'dark';
                    tooltip = 'Inconclusive';
                    break;
            }

            // Initialize interviewAlert as an empty string
            var interviewAlert = '';

            // Check if there are interviews and set the alert based on the status
            if (datas[i].interviews && datas[i].interviews.length > 0) {
                var interview = datas[i].interviews[datas[i].interviews.length - 1];
                var formattedDate, formattedTime;

                if (interview.status === 'Appointed' && interview.updated_at) {
                    // Use updated_at for Appointed status
                    var updatedAtDate = new Date(interview.updated_at);
                    formattedDate = updatedAtDate.toLocaleString('en-GB', { day: '2-digit', month: 'short' });
                    formattedTime = formatTimeTo24Hour(interview.updated_at);
                } else {
                    // Use the scheduled date and time for other statuses
                    var interviewDate = new Date(interview.scheduled_date);
                    formattedDate = interviewDate.toLocaleString('en-GB', { day: '2-digit', month: 'short' });
                    formattedTime = formatTimeTo24Hour(interview.start_time);
                }

                const statusMapping = {
                    'Scheduled': { class: 'alert-warning', icon: 'ri-calendar-todo-fill', text: 'Scheduled' },
                    'Confirmed': { class: 'alert-success', icon: 'ri-calendar-check-fill', text: 'Confirmed' },
                    'Declined': { class: 'alert-danger', icon: 'ri-calendar-2-fill', text: 'Declined' },
                    'Reschedule': { class: 'alert-info', icon: 'ri-calendar-event-fill', text: 'Reschedule' },
                    'Completed': { class: 'alert-success', icon: 'ri-calendar-check-fill', text: 'Completed' },
                    'Cancelled': { class: 'alert-dark', icon: 'ri-calendar-2-fill', text: 'Cancelled' },
                    'No Show': { class: 'alert-danger', icon: 'ri-user-unfollow-fill', text: 'No Show' },
                    'Appointed': { class: 'alert-success', icon: 'ri-open-arm-fill', text: 'Appointed' },
                    'Regretted': { class: 'alert-danger', icon: 'ri-user-unfollow-fill', text: 'Regretted' }
                };

                if (statusMapping[interview.status]) {
                    const statusInfo = statusMapping[interview.status];
                    let additionalText = '';

                    if (interview.status === 'Reschedule' && interview.reschedule_date) {
                        var rescheduledDateTime = formatFullDateTime(interview.reschedule_date);
                        additionalText = `<br><strong>Suggested:</strong> ${rescheduledDateTime}`;
                    }

                    interviewAlert = `<div class="alert ${statusInfo.class} alert-dismissible alert-label-icon rounded-label fade show mb-0" role="alert">
                                        <i class="${statusInfo.icon} label-icon"></i><strong>${statusInfo.text}: </strong>${formattedDate} at ${formattedTime}${additionalText}
                                    </div>`;

                    // Add the check icon if the reschedule_by is 'Applicant'
                    if (interview.status === 'Reschedule' && interview.reschedule_by === 'Applicant') {
                        interviewAlert = `<div class="d-flex align-items-center">
                                            ${interviewAlert}
                                            <a class="ms-auto align-middle interviewConfirmBtn" data-bs-toggle="tooltip" title="Confirm interview for ${rescheduledDateTime}" data-interview-id="${interview.encrypted_id}" style="cursor: pointer;">
                                                <i class="ri-check-double-fill text-success" style="font-size: 25px;"></i>
                                            </a>
                                        </div>`;
                    }
                }
            }

            //Get latest interview with score
            var latestInterview = datas[i].latest_interview_with_score;

            if (latestInterview && latestInterview.score) {
                interviewScore = `<div class="badge text-bg-primary">
                                    <i class="mdi mdi-star me-1"></i>
                                    ${latestInterview.score ? latestInterview.score : 'N/A'}
                                </div>`;
            }

            // Append the interview alert after the checksHtml if it exists
            if (interviewAlert) {
                checksHtml += interviewAlert;
            }

            checksHtml += '</div></div>';

            var cardBorder = '';

            if (datas[i].vacancies_filled && datas[i].vacancies_filled.length > 0) {
                cardBorder = 'border card-border-success';
            }

            var closeButton = '';
            if (!datas[i].vacancies_filled || datas[i].vacancies_filled.length === 0) {
                closeButton = '<button class="btn btn-soft-dark candidate-close-btn" data-candidate-id="' + datas[i].id + '">\
                                    <i class="ri-close-circle-line align-bottom fs-16"></i>\
                                </button>';
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
                                        '+ (datas[i].distance ? datas[i].distance : 'N/A') + '\
                                    </p>\
                                </div>\
                                <div class="col-2">\
                                    <i class="ri-shield-user-line text-'+ status + ' me-1 align-bottom"></i>' + 
                                    '<span class="badge bg-'+ status + '-subtle text-'+ status + '">' + tooltip +
                                '</div>\
                                <div class="d-flex gap-4 mt-0 text-muted mx-auto col-2">\
                                    <div><i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i>\
                                        '+ (datas[i].town ? datas[i].town.name : 'N/A') + '\
                                    </div>\
                                </div>\
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
                                    ' + closeButton + '\
                                </div>\
                            </div>\
                        </div>\
                        ' + checksHtml + '\
                    </div>\
                </div>';
        }

        // Add event listeners to the close buttons
        document.querySelectorAll('.candidate-close-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                var candidateId = this.getAttribute('data-candidate-id');
                removeCandidate(candidateId);
            });
        });
    }

    // Call the function to bind the event listener
    interviewConfirm();

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

    // Get current date and time
    var currentDateTime = new Date();

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

        // Custom validation for Choices.js select
        if (element.hasClass('choices-select') && !value) {
            isValid = false;
            var choicesDiv = element.closest('.mb-3');
            choicesDiv.find('.choices').css('border', '1px solid #f17171');
            choicesDiv.find('.invalid-feedback').show();
        }

        // Validate interview date
        if (element.attr('id') === 'date') {
            var interviewDate = new Date(value);
            if (interviewDate < currentDateTime) {
                isValid = false;
                element.addClass('is-invalid');
                element.siblings('.invalid-feedback').show().text('Interview date must be in the future.');
            }
        }

        // Validate start time (should be after the current time if today)
        if (element.attr('id') === 'startTime') {
            var startTime = element.val();
            var interviewDate = $('#date').val();
            var fullStartDateTime = new Date(interviewDate + ' ' + startTime);
            if (fullStartDateTime <= currentDateTime) {
                isValid = false;
                element.addClass('is-invalid');
                element.siblings('.invalid-feedback').show().text('Start time must be in the future.');
            }
        }

        // Custom validation for end time
        if (element.attr('id') === 'endTime') {
            var startTime = $('#startTime').val();
            var endTime = element.val();

            // Convert times to Date objects for comparison
            var interviewDate = $('#date').val();
            var fullStartDateTime = new Date(interviewDate + ' ' + startTime);
            var fullEndDateTime = new Date(interviewDate + ' ' + endTime);

            // Check if end time is at least 30 min and not more than 1 hour after start time
            var diffInMinutes = (fullEndDateTime - fullStartDateTime) / (1000 * 60); // Difference in minutes
            if (diffInMinutes < 30 || diffInMinutes > 60) {
                isValid = false;
                element.addClass('is-invalid');
                element.siblings('.invalid-feedback').show().text('End time must be between 30 minutes and 1 hour after start time.');
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
                            // Find the corresponding interview for the applicant in the response data
                            var interview = data.interviews.find(function(interviewResult) {
                                return interviewResult.applicant == applicantID;
                            });

                            // Check if the alert div already exists
                            var existingAlert = candidateCard.querySelector('.alert');

                            // Check if interview exists and if status = Reschedule
                            if (interview && interview.status === 'Reschedule') {
                                // Reschedule status - update to alert-info, different icon, and additional reschedule info
                                var scheduledDate = interview.interview.scheduled_date.split('T')[0]; // Extract only the date part (YYYY-MM-DD)
                                var startTime = interview.interview.start_time.split('T')[1].split('.')[0]; // Extract time from full date-time format (HH:mm:ss)

                                var formattedScheduledDate = '';

                                // Combine scheduled date and start time
                                if (scheduledDate && startTime) {
                                    // Combine date and time into a single string
                                    var combinedDateTime = new Date(scheduledDate + 'T' + startTime);

                                    // Adjust for South Africa's time zone (UTC+2)
                                    combinedDateTime.setHours(combinedDateTime.getHours() + 2);

                                    // Add one day to the combined date
                                    combinedDateTime.setDate(combinedDateTime.getDate() + 1);

                                    var day = combinedDateTime.getDate().toString().padStart(2, '0'); // Ensure two digits for the day
                                    var month = combinedDateTime.toLocaleString('default', { month: 'short' }); // 'short' for abbreviated month name

                                    // Format the scheduled date and time for display
                                    formattedScheduledDate = `${day} ${month} at ${combinedDateTime.getHours().toString().padStart(2, '0')}:${combinedDateTime.getMinutes().toString().padStart(2, '0')}`;
                                }

                                var rescheduledDateTime = formatFullDateTime(interview.interview.reschedule_date);
                                var additionalText = `<br><strong>Suggested:</strong> ${rescheduledDateTime}`;
                                
                                // Construct the content to be updated
                                var updatedContent = `<i class="ri-calendar-event-fill label-icon"></i><strong>Reschedule: </strong>${formattedScheduledDate}${additionalText}`;
                            
                                if (existingAlert) {
                                    // Remove existing status classes
                                    existingAlert.classList.remove('alert-danger', 'alert-success', 'alert-warning');
                                    // Add new status class
                                    existingAlert.classList.add('alert-info');
                            
                                    // Update only the inner content of the existing alert (not the entire alert div)
                                    existingAlert.innerHTML = updatedContent;

                                    // Check if the interviewConfirmBtn exists and remove it if present
                                    var interviewConfirmBtn = candidateCard.querySelector('.interviewConfirmBtn');
                                    if (interviewConfirmBtn) {
                                        interviewConfirmBtn.remove();
                                    }
                                } else {
                                    // Create and append a new alert div for reschedule
                                    var rescheduleHtml = `<div class="alert alert-info alert-dismissible alert-label-icon rounded-label fade show mb-0" role="alert">
                                                            ${updatedContent}
                                                          </div>`;
                                    
                                    var cardFooter = candidateCard.querySelector('.card-footer .d-flex');
                                    if (cardFooter) {
                                        cardFooter.insertAdjacentHTML('beforeend', rescheduleHtml);
                                    }
                                }
                            } else {
                                // Check if the alert already exists
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

                    // Deselect all checkboxes after successful submission
                    document.querySelectorAll('input[type="checkbox"][name="chk_child"]').forEach(function(checkbox) {
                        checkbox.checked = false;
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
| Interview Confirm
|--------------------------------------------------------------------------
*/

// Function to handle the approval of the interview
function interviewConfirm() {
    // Add click event listener to the interviewConfirmBtn
    document.querySelectorAll('.interviewConfirmBtn').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            // Get the encrypted interview ID from the button's data attribute
            var interviewId = this.getAttribute('data-interview-id');

            //Set Form Data
            let formData = new FormData();
            formData.append('id', interviewId);

            // Send an AJAX request to approve the interview
            $.ajax({
                url: route('interview.confirm'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Use the scheduled date and time for other statuses
                        var interviewDate = new Date(response.interview.scheduled_date);
                        formattedDate = interviewDate.toLocaleString('en-GB', { day: '2-digit', month: 'short' });
                        formattedTime = formatTimeTo24Hour(response.interview.start_time);

                        // On success, update the alert to "Confirmed"
                        var alertElement = button.closest('.d-flex').querySelector('.alert');
                        if (alertElement) {
                            // Update the alert class, icon, and text
                            alertElement.classList.remove('alert-info');
                            alertElement.classList.add('alert-success');
                            alertElement.querySelector('.label-icon').classList.remove('ri-calendar-event-fill');
                            alertElement.querySelector('.label-icon').classList.add('ri-calendar-check-fill');
                            alertElement.innerHTML = '<i class="ri-calendar-check-fill label-icon"></i><strong>Confirmed: </strong>' + formattedDate + ' at ' + formattedTime;
                        }

                        // Hide the confirm button
                        button.style.display = 'none'; // Hide the button

                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
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
                }
            });
        });
    });
}

// Call the function to bind the event listener
interviewConfirm();

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

                            // Remove the close button
                            var closeButton = candidateCard.querySelector('.candidate-close-btn');
                            if (closeButton) {
                                closeButton.remove();
                            }

                            // Check if the alert div already exists
                            var existingAlert = candidateCard.querySelector('.alert');

                            if (existingAlert) {
                                // Remove existing status classes
                                existingAlert.classList.remove('alert-danger', 'alert-warning', 'alert-info');
                                // Add new status class
                                existingAlert.classList.add('alert-success');

                                // Convert time to the desired format
                                var updatedTime = new Date(data.vacancy.vacancy.updated_at);
                                var formattedDateTime = formatDateTime(updatedTime);

                                // Update existing alert with new information
                                existingAlert.innerHTML = '<i class="ri-open-arm-fill label-icon"></i><strong>Appointed: </strong>' + formattedDateTime;
                            } else {
                                // Create and append a new alert div
                                var updatedTime = new Date(data.vacancy.vacancy.updated_at);
                                var formattedDateTime = formatDateTime(updatedTime);

                                var alertHtml = '<div class="alert alert-success alert-dismissible alert-label-icon rounded-label fade show mb-0" role="alert">' +
                                                '<i class="ri-open-arm-fill label-icon"></i><strong>Appointed: </strong>' + formattedDateTime + 
                                                '</div>';
                                // Append the alertHtml to the card footer
                                var cardFooter = candidateCard.querySelector('.card-footer .d-flex');
                                if (cardFooter) {
                                    cardFooter.insertAdjacentHTML('beforeend', alertHtml);
                                }
                            }
                        }
                    });

                    // If no open positions remain, remove all applicants not appointed and without the card-border-success class
                    if (data.vacancy.vacancy.open_positions === 0) {
                        document.querySelectorAll('.candidate-card').forEach(function(candidateCard) {
                            var cardDiv = candidateCard.querySelector('.card');
                            var candidateId = candidateCard.getAttribute('data-candidate-id');

                            // If the card does not have card-border-success and the applicant is not appointed, remove the card
                            if (!cardDiv.classList.contains('card-border-success') && !selectedChoices.includes(candidateId)) {
                                candidateCard.remove(); // Remove the card from the DOM
                            }
                        });

                        // Change the Generate Shortlist button to Vacancy Filled!
                        var generateButton = document.querySelector('#generate-btn');

                        if (generateButton) {
                            // Change the text and the button ID
                            generateButton.textContent = 'Vacancy Filled!';
                            generateButton.id = 'vacancyFilled-btn'; // Change the ID to vacancyFilled-btn
                        }

                        // Hide the column with the Interview and Fill Vacancy buttons
                        var colButtons = document.getElementById('colButtons');
                        if (colButtons) {
                            colButtons.style.display = 'none'; // Hide the column
                        }
                    }

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

                    // Update open positions in the view
                    var openPositionsElement = document.querySelector('#openPositions');
                    var openPositionsText = data.vacancy.vacancy.open_positions + ' open ' + (data.vacancy.vacancy.open_positions === 1 ? 'position' : 'positions') + ' available.';
                    openPositionsElement.textContent = openPositionsText;
            
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        toast: true,
                        showCloseButton: true
                    });

                    // Deselect all checkboxes after successful submission
                    document.querySelectorAll('input[type="checkbox"][name="chk_child"]').forEach(function(checkbox) {
                        checkbox.checked = false;
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

/*
|--------------------------------------------------------------------------
| Format Date Time
|--------------------------------------------------------------------------
*/

function formatDateTime(dateTime) {
    const date = new Date(dateTime);
    
    const day = ("0" + date.getDate()).slice(-2); // Ensure two digits for day
    const month = date.toLocaleString('en-GB', { month: 'short' }); // Get abbreviated month name
    const hours = ("0" + date.getHours()).slice(-2); // Ensure two digits for hours
    const minutes = ("0" + date.getMinutes()).slice(-2); // Ensure two digits for minutes

    return `${day} ${month} at ${hours}:${minutes}`;
}

// A function to format the time part of the interview start_time string and adjust for South Africa time (UTC+2)
function formatTimeTo24Hour(dateTimeString) {
    const date = new Date(dateTimeString);
    
    const localDate = new Date(date.getTime());
    
    const hours = ("0" + localDate.getHours()).slice(-2); // Ensure two digits
    const minutes = ("0" + localDate.getMinutes()).slice(-2); // Ensure two digits
    
    return `${hours}:${minutes}`;
}

// A function to format full date-time strings for the reschedule scenario
function formatFullDateTime(dateTimeString) {
    const date = new Date(dateTimeString);

    // Adjust for the time zone, similar to the formatTimeTo24Hour function
    const offsetInHours = 0; // Adjust for South Africa's time zone
    date.setUTCHours(date.getUTCHours() + offsetInHours);

    const day = ("0" + date.getDate()).slice(-2); // Ensure two digits
    const month = date.toLocaleString('en-GB', { month: 'short' }); // Get abbreviated month name
    const year = date.getFullYear();
    const hours = ("0" + date.getHours()).slice(-2); // Ensure two digits
    const minutes = ("0" + date.getMinutes()).slice(-2); // Ensure two digits

    return `${day} ${month} at ${hours}:${minutes}`;
}