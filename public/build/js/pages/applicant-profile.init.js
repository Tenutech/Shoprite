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
| Form Interview
|--------------------------------------------------------------------------
*/

$('#formInterview').on('submit', function(e) {
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

                    $('#scoreDisplay').text(data.score).show(); 

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
                if (jqXHR.status === 400 || jqXHR.status === 422) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: jqXHR.responseJSON.message,
                        showConfirmButton: false,
                        timer: 5000,
                        showCloseButton: true,
                        toast: true
                    });
                } else {
                    if(textStatus === 'timeout') {
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