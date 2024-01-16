/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
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
        $('.nav-link[href="' + activeTab + '"]').tab('show');
    } else {
        // Show default tab if no specific tab is required
        $('.nav-link:first').tab('show');
    }

    // Save the tab on click to local storage
    $('.nav-link').on('click', function() {
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