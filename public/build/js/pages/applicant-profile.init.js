/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Profile init js
*/

/*
|--------------------------------------------------------------------------
| Tab Open
|--------------------------------------------------------------------------
*/

$(document).ready(function() {
    // First priority to the hash in the URL
    var activeTab = window.location.hash;

    // If there's no hash, check if there's a saved tab in local storage
    if (!activeTab) {
        activeTab = localStorage.getItem('activeTab');
    }

    // If an activeTab has been determined, show it
    if (activeTab) {
        $('.applicant-tab[href="' + activeTab + '"]').tab('show');
    } else {
        // Show default tab if no specific tab is required
        $('.applicant-tab:first').tab('show');
    }

    // Save the tab on click to local storage
    $('.applicant-tab').on('click', function() {
        var tabId = $(this).attr('href');
        localStorage.setItem('activeTab', tabId);

        // Update the URL hash when a tab is clicked
        window.location.hash = tabId;
    });
});

/*
|--------------------------------------------------------------------------
| Date Fields
|--------------------------------------------------------------------------
*/

const tomorrow = new Date();
tomorrow.setDate(tomorrow .getDate() + 1); // Set the date to one days from today

// Restrict the date picker to not allow past dates
flatpickr("#date", {
    dateFormat: "d M, Y",
    minDate: "today", // Disables past dates
    defaultDate: tomorrow, // Set the default date to today
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
| Colors
|--------------------------------------------------------------------------
*/

// get colors array from the string
function getChartColorsArray(chartId) {
    if (document.getElementById(chartId) !== null) {
        var colors = document.getElementById(chartId).getAttribute("data-colors");
        colors = JSON.parse(colors);
        return colors.map(function (value) {
            var newValue = value.replace(" ", "");
            if (newValue.indexOf(",") === -1) {
                var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                if (color) return color;
                else return newValue;;
            } else {
                var val = value.split(',');
                if (val.length == 2) {
                    var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                    rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                    return rgbaColor;
                } else {
                    return newValue;
                }
            }
        });
    }
}

/*
|--------------------------------------------------------------------------
| Literacy Score
|--------------------------------------------------------------------------
*/

// Calculate the series and labels based on the given data
var literacySeries = [];
var literacyLabels = [];
for (var i = 1; i <= literacyScore; i++) {
    literacySeries.push(i);
    literacyLabels.push(i.toString());
}

var options = {
    series: literacySeries,
    chart: {
        height: 300,
        type: 'donut',
    },
    labels: literacyLabels,
    theme: {
        monochrome: {
            enabled: true,
            color: '#405189',
            shadeTo: 'light',
            shadeIntensity: 0.6
        }
    },
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -5
            }
        }
    },
    dataLabels: {
        formatter: function (val, opts) {
            var name = opts.w.globals.labels[opts.seriesIndex];
            return name; // Only return the number, not the percentage
        },
        dropShadow: {
            enabled: false,
        }
    },
    legend: {
        show: false
    },
    title: {
        text: literacy,
        floating: true,
        offsetY: 125,
        align: 'center',
        style: {
            fontSize: '20px',
            fontWeight: 'bold'
        }
    }
};

if(document.querySelector("#literacy_chart")){
    var chart = new ApexCharts(document.querySelector("#literacy_chart"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Numeracy Score
|--------------------------------------------------------------------------
*/

// Calculate the series and labels based on the given data
var numeracySeries = [];
var numeracyLabels = [];
for (var i = 1; i <= numeracyScore; i++) {
    numeracySeries.push(i);
    numeracyLabels.push(i.toString());
}

var options = {
    series: numeracySeries,
    chart: {
        height: 300,
        type: 'donut',
    },
    labels: numeracyLabels,
    theme: {
        monochrome: {
            enabled: true,
            color: '#3d78e3',
            shadeTo: 'light',
            shadeIntensity: 0.6
        }
    },
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -5
            }
        }
    },
    dataLabels: {
        formatter: function (val, opts) {
            var name = opts.w.globals.labels[opts.seriesIndex];
            return name; // Only return the number, not the percentage
        },
        dropShadow: {
            enabled: false,
        }
    },
    legend: {
        show: false
    },
    title: {
        text: numeracy,
        floating: true,
        offsetY: 125,
        align: 'center',
        style: {
            fontSize: '20px',
            fontWeight: 'bold'
        }
    }
};

if(document.querySelector("#numeracy_chart")){
    var chart = new ApexCharts(document.querySelector("#numeracy_chart"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Situational Score
|--------------------------------------------------------------------------
*/

// Calculate the series and labels based on the given data
var situationalSeries = [];
var situationalLabels = [];
for (var i = 1; i <= situationalScore; i++) {
    situationalSeries.push(i);
    situationalLabels.push(i.toString());
}

var options = {
    series: situationalSeries,
    chart: {
        height: 300,
        type: 'donut',
    },
    labels: situationalLabels,
    theme: {
        monochrome: {
            enabled: true,
            color: '#d9aa40',
            shadeTo: 'light',
            shadeIntensity: 0.6
        }
    },
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -5
            }
        }
    },
    dataLabels: {
        formatter: function (val, opts) {
            var name = opts.w.globals.labels[opts.seriesIndex];
            return name; // Only return the number, not the percentage
        },
        dropShadow: {
            enabled: false,
        }
    },
    legend: {
        show: false
    },
    title: {
        text: situational,
        floating: true,
        offsetY: 125,
        align: 'center',
        style: {
            fontSize: '20px',
            fontWeight: 'bold'
        }
    }
};

if(document.querySelector("#situational_chart")){
    var chart = new ApexCharts(document.querySelector("#situational_chart"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Form Interview
|--------------------------------------------------------------------------
*/

$(document).on('submit', '#formInterview', function(e) { 
    e.preventDefault();

    if (!validateRatings()) {
        // Show an error message if validation fails
        $('.invalid-feedback').show();
        return;
    }

    var formData = new FormData(this);

    if (this.checkValidity()) {
        $.ajax({
            url: route('interview.score'),
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
                    // Hide the form
                    $('#formInterview').hide();

                    // Hide the interview and no-show buttons
                    $('#interviewBtn').hide();
                    $('#noShowBtn').hide();

                    $('#scoreDisplay').text(data.score).show();
                    
                    // Update the alert to 'Completed' status
                    var alertElement = $('#interviewAlert .alert'); // Select the existing alert
                    var iconElement = $('#interviewAlert .label-icon'); // Select the icon element
                    var statusTextElement = $('#interviewAlert strong'); // Select the status text element

                    // Update the alert's class to 'alert-success'
                    alertElement.removeClass('alert-warning alert-danger alert-info alert-dark').addClass('alert-success');

                    // Update the icon to 'ri-calendar-check-fill'
                    iconElement.removeClass().addClass('ri-calendar-check-fill label-icon');

                    // Update the status text to 'Completed:'
                    statusTextElement.text('Completed:');

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
            }
        });
    } else {
        this.reportValidity();
    }
});

/*
|--------------------------------------------------------------------------
| Form Schedule Interview
|--------------------------------------------------------------------------
*/

var interviewButton = document.querySelector('#interviewBtn');

// Add click event listener to the Interview button
if (interviewButton) {
    interviewButton.addEventListener('click', function(event) {
        // Manually open the modal using jQuery
        $('#interviewModal').modal('show');
    });
}


$('#interviewModal').on('hidden.bs.modal', function () {
    clearFields();
});

$('#formInterviewSchedule').on('submit', function(e) {
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
    $('#formInterviewSchedule').find('input, select, textarea').each(function() {
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

    if (this.checkValidity()) {
        $.ajax({
            url: route('applicant-interview.store'),
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
                    // Reschedule status - update to alert-info, different icon, and additional reschedule info
                    var scheduledDate = data.interview.scheduled_date.split('T')[0]; // Extract only the date part (YYYY-MM-DD)
                    var startTime = data.interview.start_time.split('T')[1].split('.')[0]; // Extract time from full date-time format (HH:mm:ss)

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
                    // Replace the interview alert
                    if (data.interview.status === 'Reschedule') {
                        $('#interviewAlert').html(`
                            <div class="alert alert-info alert-dismissible alert-label-icon rounded-label fade show mb-0" role="alert">
                                <i class="ri-calendar-event-fill label-icon"></i>
                                <strong>Reschedule:</strong> ${formattedScheduledDate}
                                <br>
                                <strong>Suggested:</strong> ${data.date} at ${data.time}
                            </div>
                        `);
                    } else {
                        $('#interviewAlert').html(`
                            <div class="alert alert-warning alert-dismissible alert-label-icon rounded-label fade show mb-0" role="alert">
                                <i class="ri-calendar-todo-fill label-icon"></i>
                                <strong>Scheduled:</strong> ${data.date} at ${data.time}
                            </div>
                        `);
                    }
                    
                    // Replace the interview form
                    if (data.questions.length === 0) {
                        // If no questions, show the error alert
                        $('#interviewFormContainer').html(`
                            <div class="alert alert-danger mb-xl-0 text-center" role="alert">
                                <strong>Sorry, no interview template has been loaded</strong> for this position. Please <b>contact your administrator</b>
                            </div>
                        `);
                    } else {
                        // If questions exist, generate the form
                        $('#interviewFormContainer').html(`
                            <form class="mt-3" id="formInterview" enctype="multipart/form-data">
                                <input type="hidden" id="interviewID" name="interview_id" value="${data.interviewId}"/>
                                ${data.questions.map(question => `
                                    <div class="form-group mb-4">
                                        <label class="form-label fs-16" style="width:100%;">
                                            <div class="row" style="width:100%;">
                                                <div class="col-sm-1">
                                                    ${question.id}.) 
                                                </div>
                                                <div class="col-sm-11">
                                                    ${question.question}
                                                </div>
                                            </div>
                                        </label>
                                        <div class="col-sm-11 offset-sm-1">
                                            <div class="d-flex">
                                                ${question.type === 'text' ? `
                                                    <input type="text" class="form-control" name="answers[${question.id}]" required>
                                                ` : question.type === 'number' ? `
                                                    <input type="number" class="form-control" name="answers[${question.id}]" required>
                                                ` : question.type === 'rating' ? `
                                                    <div class="form-check">
                                                        <input class="form-check-input d-none" type="hidden" name="answers[${question.id}]" id="rating-${question.id}" required>
                                                        ${[1, 2, 3, 4, 5].map(i => `
                                                            <label class="form-check-label" for="rating-${question.id}-${i}" style="cursor: pointer; margin-right:20px;">
                                                                <i class="ri-star-line" id="star-${question.id}-${i}" style="font-size: 1.5em; color: grey;"></i>
                                                            </label>
                                                        `).join('')}
                                                    </div>
                                                ` : `
                                                    <textarea class="form-control" name="answers[${question.id}]" rows="5" required></textarea>
                                                `}
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success" type="submit">
                                        Submit
                                    </button>
                                </div>
                            </form>
                            <h1 class="display-2 coming-soon-text text-center" id="scoreDisplay" style="display: none;">
                                <!-- The score will be injected here -->
                            </h1>
                        `);
            
                        // Initialize the rating functionality after the form is added to the DOM
                        data.questions.forEach(question => {
                            if (question.type === 'rating') {
                                let stars = document.querySelectorAll(`#formInterview [id^="star-${question.id}-"]`);
                                stars.forEach(star => {
                                    star.addEventListener('click', function() {
                                        let rating = parseInt(star.id.split('-').pop());
                                        for (let i = 1; i <= rating; i++) {
                                            document.querySelector(`#star-${question.id}-${i}`).classList.remove('ri-star-line');
                                            document.querySelector(`#star-${question.id}-${i}`).classList.add('ri-star-fill');
                                            document.querySelector(`#star-${question.id}-${i}`).style.color = 'gold';
                                        }
                                        for (let i = rating + 1; i <= 5; i++) {
                                            document.querySelector(`#star-${question.id}-${i}`).classList.remove('ri-star-fill');
                                            document.querySelector(`#star-${question.id}-${i}`).classList.add('ri-star-line');
                                            document.querySelector(`#star-${question.id}-${i}`).style.color = 'grey';
                                        }
                                        document.querySelector(`#rating-${question.id}`).value = rating;
                                    });
                                });
                            }
                        });
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
| No Show Interviiew
|--------------------------------------------------------------------------
*/

$('#noShow-interview').on('click', function() {
    var interviewId = $(this).data('id');
    var url = route('interview.noShow');
    
    $.ajax({
        url: url,
        type: 'POST',
        data: {
            id: interviewId
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success) {
                // Get the data from the alert element
                var alertBox = $('.alert');
                var statusColor = alertBox.data('status-color');
                var statusIcon = alertBox.data('status-icon');

                // Update the alert box with new status
                alertBox.removeClass('alert-' + statusColor).addClass('alert-danger');
                alertBox.find('strong').text('No Show:');
                alertBox.find('i').removeClass(statusIcon).addClass('ri-user-unfollow-fill');

                // Hide the interview form container
                $('#interviewFormContainer').hide();

                // Close the modal
                $('#interviewNoShowModal').modal('hide');

                // Optionally, you can also show a success message or notification
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
        }
    });
});

/*
|--------------------------------------------------------------------------
| Validate Ratings
|--------------------------------------------------------------------------
*/

function validateRatings() {
    let allRated = true;
    $('input[type="hidden"][name^="answers["]').each(function() {
        if (this.value == '' || this.value < 1 || this.value > 5) {
            allRated = false;
            // Find the closest invalid feedback span and show it
            $(this).siblings('.invalid-feedback').show();
        }
    });
    return allRated;
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
}