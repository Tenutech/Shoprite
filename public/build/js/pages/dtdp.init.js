/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job-statistics init js
*/

$(document).ready(function() {
    /*
    |--------------------------------------------------------------------------
    | Show Unactioned Shortlist
    |--------------------------------------------------------------------------
    */

    // Check if the shortlist exists
    if (typeof shortlist !== 'undefined' && shortlist !== null) {
        // Show the modal when shortlist exists
        $('#unActionedShortlistModal').modal('show');
    }

    /*
    |--------------------------------------------------------------------------
    | Date Range
    |--------------------------------------------------------------------------
    */

    // Get the first day of the current year and today's date
    var startDate = new Date(new Date().getFullYear(), 0, 1); // Start of the year
    var endDate = new Date(); // Today's date

    // Initialize Flatpickr with the #dateFilter selector
    flatpickr("#dateFilter", {
        mode: "range",
        dateFormat: "d M Y",
        defaultDate: [formatDate(startDate), formatDate(endDate)], // Set default date range
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                var startDate = selectedDates[0];
                var endDate = selectedDates[1];
    
                // Send the date range via AJAX to update the dashboard
                $.ajax({
                    url: route('dtdp.updateDashboard'),
                    type: "GET",
                    data: {
                        startDate: formatDateBeforeSend(startDate), // Format date for the request
                        endDate: formatDateBeforeSend(endDate), // Format date for the request
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Update the dashboard with the new data
                        updateDashboard(response.data); // Pass the data to the updateDashboard function

                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: response.message,
                            showConfirmButton: false,
                            timer: 2000,
                            showCloseButton: true,
                            toast: true
                        })    
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
            }
        }
    });
});


/*
|--------------------------------------------------------------------------
| Format Date
|--------------------------------------------------------------------------
*/

// Format dates as 'd M Y'
function formatDate(date) {
    var day = String(date.getDate()).padStart(2, '0');
    var month = date.toLocaleString('default', { month: 'short' });
    var year = date.getFullYear();
    return day + " " + month + " " + year;
}

// Format the date as 'Y-m-d'
function formatDateBeforeSend(date) {
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based, so add 1
    var year = date.getFullYear();
    return year + '-' + month + '-' + day; // Format as 'Y-m-d'
}

/*
|--------------------------------------------------------------------------
| Colors Array
|--------------------------------------------------------------------------
*/

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
| Total Vacancies Filled
|--------------------------------------------------------------------------
*/

// Total Vacancies Filled
var totalVacanciesFilled = getChartColorsArray("total_vacancies_filled");

// Calculate percentage of filled vacancies
var divisionTotalVacancies = divisionTotalVacancies || 0;
var divisionTotalVacanciesFilled = divisionTotalVacanciesFilled || 0;
var percentageFilled = 0;

// Check for divide by zero and calculate percentage
if (divisionTotalVacancies > 0) {
    percentageFilled = Math.round((divisionTotalVacanciesFilled / divisionTotalVacancies) * 100);
}

// Total Vacancies Filled Chart
if (totalVacanciesFilled) {
    var options = {
        series: [percentageFilled], // Use the calculated percentage
        chart: {
            type: 'radialBar',
            width: 105,
            sparkline: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    margin: 0,
                    size: '70%'
                },
                track: {
                    margin: 1
                },
                dataLabels: {
                    show: true,
                    name: {
                        show: false
                    },
                    value: {
                        show: true,
                        fontSize: '16px',
                        fontWeight: 600,
                        offsetY: 8,
                        // Show percentage inside the radial chart
                        formatter: function(val) {
                            return Math.round(val) + "%";
                        }
                    }
                }
            }
        },
        colors: totalVacanciesFilled
    };

    var totalVacanciesFilledChart = new ApexCharts(document.querySelector("#total_vacancies_filled"), options);
    totalVacanciesFilledChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Interviews Completed
|--------------------------------------------------------------------------
*/

// Total Interviews Completed
var totalInterviewsCompleted = getChartColorsArray("total_interviews_completed");

// Calculate percentage of completed interviews
var divisionTotalInterviewsScheduled = divisionTotalInterviewsScheduled || 0;
var divisionTotalInterviewsCompleted = divisionTotalInterviewsCompleted || 0;
var percentageCompleted = 0;

// Check for divide by zero and calculate percentage
if (divisionTotalInterviewsScheduled > 0) {
    percentageCompleted = Math.round((divisionTotalInterviewsCompleted / divisionTotalInterviewsScheduled) * 100);
}

// Total Interviews Completed Chart
if (totalInterviewsCompleted) {
    var options = {
        series: [percentageCompleted], // Use the calculated percentage
        chart: {
            type: 'radialBar',
            width: 105,
            sparkline: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    margin: 0,
                    size: '70%'
                },
                track: {
                    margin: 1
                },
                dataLabels: {
                    show: true,
                    name: {
                        show: false
                    },
                    value: {
                        show: true,
                        fontSize: '16px',
                        fontWeight: 600,
                        offsetY: 8,
                        // Show percentage inside the radial chart
                        formatter: function(val) {
                            return Math.round(val) + "%";
                        }
                    }
                }
            }
        },
        colors: totalInterviewsCompleted
    };

    var totalInterviewsCompletedChart = new ApexCharts(document.querySelector("#total_interviews_completed"), options);
    totalInterviewsCompletedChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Applicants Appointed
|--------------------------------------------------------------------------
*/

// Total Applicants Appointed
var totalApplicantsAppointed = getChartColorsArray("total_applicants_appointed");

// Calculate percentage of appointed applicants
var divisionTotalApplicantsAppointed = divisionTotalApplicantsAppointed || 0;
var percentageAppointed = 0;

// Check for divide by zero and calculate percentage for appointed applicants
if (divisionTotalInterviewsScheduled > 0) {
    percentageAppointed = Math.round((divisionTotalApplicantsAppointed / divisionTotalInterviewsScheduled) * 100);
}

// Total Applicants Appointed Chart
if (totalApplicantsAppointed) {
    var options = {
        series: [percentageAppointed], // Use the calculated percentage
        chart: {
            type: 'radialBar',
            width: 105,
            sparkline: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    margin: 0,
                    size: '70%'
                },
                track: {
                    margin: 1
                },
                dataLabels: {
                    show: true,
                    name: {
                        show: false
                    },
                    value: {
                        show: true,
                        fontSize: '16px',
                        fontWeight: 600,
                        offsetY: 8,
                        // Show percentage inside the radial chart
                        formatter: function(val) {
                            return Math.round(val) + "%";
                        }
                    }
                }
            }
        },
        colors: totalApplicantsAppointed
    };

    var totalApplicantsAppointedChart = new ApexCharts(document.querySelector("#total_applicants_appointed"), options);
    totalApplicantsAppointedChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Applicants Regretted
|--------------------------------------------------------------------------
*/

// Total Applicants Regretted
var totalApplicantsRegretted = getChartColorsArray("total_applicants_regretted");

// Calculate percentage of regretted applicants
var divisionTotalApplicantsRegretted = divisionTotalApplicantsRegretted || 0;
var percentageRegretted = 0;

// Check for divide by zero and calculate percentage for regretted applicants
if (divisionTotalInterviewsScheduled > 0) {
    percentageRegretted = Math.round((divisionTotalApplicantsRegretted / divisionTotalInterviewsScheduled) * 100);
}

// Total Applicants Regretted Chart
if (totalApplicantsRegretted) {
    var options = {
        series: [percentageRegretted], // Use the calculated percentage
        chart: {
            type: 'radialBar',
            width: 105,
            sparkline: {
                enabled: true
            }
        },
        dataLabels: {
            enabled: false
        },
        plotOptions: {
            radialBar: {
                hollow: {
                    margin: 0,
                    size: '70%'
                },
                track: {
                    margin: 1
                },
                dataLabels: {
                    show: true,
                    name: {
                        show: false
                    },
                    value: {
                        show: true,
                        fontSize: '16px',
                        fontWeight: 600,
                        offsetY: 8,
                        // Show percentage inside the radial chart
                        formatter: function(val) {
                            return Math.round(val) + "%";
                        }
                    }
                }
            }
        },
        colors: totalApplicantsRegretted
    };

    var totalApplicantsRegrettedChart = new ApexCharts(document.querySelector("#total_applicants_regretted"), options);
    totalApplicantsRegrettedChart.render();
}

/*
|--------------------------------------------------------------------------
| Talent Pool
|--------------------------------------------------------------------------
*/

//  Talent Pool By Month Chart
var talentPoolByMonth = getChartColorsArray("talent_pool_by_month");

// Prepare default months from January to December
var defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Prepare the data for the chart
var talentPoolData = divisionTalentPoolApplicantsByMonth && Object.keys(divisionTalentPoolApplicantsByMonth).length > 0
    ? Object.values(divisionTalentPoolApplicantsByMonth) // Extract values if not empty
    : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

var appointedData = divisionApplicantsAppointedByMonth && Object.keys(divisionApplicantsAppointedByMonth).length > 0
    ? Object.values(divisionApplicantsAppointedByMonth) // Extract values if not empty
    : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

// Get the months (x-axis categories)
var months = Object.keys(divisionApplicantsAppointedByMonth).length > 0 
    ? Object.keys(divisionApplicantsAppointedByMonth)  // Use the months from data if available
    : defaultMonths; // Use default months if data is empty

//  Talent Pool By Month Chart
if (talentPoolByMonth) {
    var options = {
        chart: {
            height: 380,
            type: 'line',
            zoom: {
                enabled: false
            },
            toolbar: {
                show: false
            }
        },
        colors: talentPoolByMonth,
        dataLabels: {
            enabled: false,
        },
        stroke: {
            width: [3, 3],
            curve: 'straight'
        },
        series: [{
                name: "Total Talent Pool",
                data: talentPoolData // Use dynamic data for Talent Pool
            },
            {
                name: "Total Appointed",
                data: appointedData // Use dynamic data for Appointed
            }
        ],
        title: {
            text: 'Total Talent Pool vs Total Appointed',
            align: 'left',
            style: {
                fontWeight: 500,
            },
        },
        grid: {
            row: {
                colors: ['transparent', 'transparent'],
                opacity: 0.2
            },
            borderColor: '#f1f1f1'
        },
        markers: {
            style: 'inverted',
            size: 6
        },
        xaxis: {
            categories:  months, // Use dynamic months from the view
            title: {
                text: 'Month'
            }
        },
        yaxis: {
            title: {
                text: 'Total Applicants'
            },
            min: 0, // Adjust min to allow smaller values
            max: Math.max(...talentPoolData, ...appointedData) + 5 // Set the max value based on the highest number in your data
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            floating: true,
            offsetY: -25,
            offsetX: -5
        },
        responsive: [{
            breakpoint: 600,
            options: {
                chart: {
                    toolbar: {
                        show: false
                    }
                },
                legend: {
                    show: false
                },
            }
        }]
    }

    var talentPoolByMonthChart = new ApexCharts(document.querySelector("#talent_pool_by_month"), options);
    talentPoolByMonthChart.render();
}

/*
|--------------------------------------------------------------------------
| Talent Pool Applicants Demographic
|--------------------------------------------------------------------------
*/

// Talent Pool Applicants Demographic
var talentPoolApplicantsDemographicColors = getChartColorsArray("talent_pool_applicants_demographic");

// Extract percentages and labels dynamically from divisionTalentPoolApplicantsDemographic
var talentPoolApplicantsDemographicSeries = [];
var talentPoolApplicantsDemographicLabels = [];

divisionTalentPoolApplicantsDemographic.forEach(function (item) {
    talentPoolApplicantsDemographicSeries.push(item.percentage); // Extract the percentage value
    talentPoolApplicantsDemographicLabels.push(item.name);       // Extract the race name
});

// Talent Pool Applicants Demographic Chart
if(talentPoolApplicantsDemographicColors){
    var options = {
        series: talentPoolApplicantsDemographicSeries,
        chart: {
            height: 350,
            type: 'radialBar',
        },
        plotOptions: {
            radialBar: {
                offsetY: 0,
                startAngle: 0,
                endAngle: 270,
                hollow: {
                    margin: 5,
                    size: '30%',
                    background: 'transparent',
                    image: undefined,
                },
                dataLabels: {
                    name: {
                        show: false,
                    },
                    value: {
                        show: false,
                    }
                }
            }
        },
        colors: talentPoolApplicantsDemographicColors,
        labels: talentPoolApplicantsDemographicLabels,
        legend: {
            show: true,
            floating: true,
            fontSize: '16px',
            position: 'left',
            offsetX: 80,
            offsetY: 15,
            labels: {
                useSeriesColors: true,
            },
            markers: {
                size: 0
            },
            formatter: function (seriesName, opts) {
                return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex] + "%"
            },
            itemMargin: {
                vertical: 3
            }
        },
        responsive: [
            {
                // For screens smaller than 1700px
                breakpoint: 1700,
                options: {
                    legend: {
                        offsetX: 60 // Set offsetX to 60 for screen sizes under 1700px
                    }
                }
            },
            {
                // For screens smaller than 1700px
                breakpoint: 1600,
                options: {
                    legend: {
                        offsetX: 30 // Set offsetX to 30 for screen sizes under 1600px
                    }
                }
            },
            {
                // For screens smaller than 1700px
                breakpoint: 1550,
                options: {
                    legend: {
                        offsetX: 30 // Set offsetX to 30 for screen sizes under 1550px
                    }
                }
            },
            {
                // For screens smaller than 1700px
                breakpoint: 1500,
                options: {
                    legend: {
                        offsetX: 10 // Set offsetX to 60 for screen sizes under 1700px
                    }
                }
            },
            {
                // For screens smaller than 1700px
                breakpoint: 1250,
                options: {
                    legend: {
                        offsetX: -20 // Set offsetX to 60 for screen sizes under 1700px
                    }
                }
            },
            {
                // For screens smaller than 768px (tablet and mobile)
                breakpoint: 768,
                options: {
                    legend: {
                        offsetX: 180, // Adjust the horizontal offset for medium screens
                    }
                }
            },
            {
                // For screens smaller than 480px (mobile)
                breakpoint: 480,
                options: {
                    legend: {
                        offsetX: -30 // Adjust the horizontal offset for small screens
                    }
                }
            }
        ]
    };

    var talentPoolApplicantsDemographicChart = new ApexCharts(document.querySelector("#talent_pool_applicants_demographic"), options);
    talentPoolApplicantsDemographicChart.render();
}

/*
|--------------------------------------------------------------------------
| Interviewed Applicants Demographic
|--------------------------------------------------------------------------
*/

// Interviewed Applicants Demographic
var interviewedApplicantsDemographicColors = getChartColorsArray("interviewed_applicants_demographic");

// Extract percentages and labels dynamically from divisionInterviewedApplicantsDemographic
var interviewedApplicantsDemographicSeries = [];
var interviewedApplicantsDemographicLabels = [];

divisionInterviewedApplicantsDemographic.forEach(function (item) {
    interviewedApplicantsDemographicSeries.push(item.percentage); // Extract the percentage value
    interviewedApplicantsDemographicLabels.push(item.name);       // Extract the race name
});

// Interviewed Applicants Demographic Chart
if(interviewedApplicantsDemographicColors){
    var options = {
        series: interviewedApplicantsDemographicSeries,
        chart: {
            height: 350,
            type: 'radialBar',
        },
        plotOptions: {
            radialBar: {
                offsetY: 0,
                startAngle: 0,
                endAngle: 270,
                hollow: {
                    margin: 5,
                    size: '30%',
                    background: 'transparent',
                    image: undefined,
                },
                dataLabels: {
                    name: {
                        show: false,
                    },
                    value: {
                        show: false,
                    }
                }
            }
        },
        colors: interviewedApplicantsDemographicColors,
        labels: interviewedApplicantsDemographicLabels,
        legend: {
            show: true,
            floating: true,
            fontSize: '16px',
            position: 'left',
            offsetX: 80,
            offsetY: 15,
            labels: {
                useSeriesColors: true,
            },
            markers: {
                size: 0
            },
            formatter: function (seriesName, opts) {
                return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex] + "%"
            },
            itemMargin: {
                vertical: 3
            }
        },
        responsive: [
            {
                // For screens smaller than 1700px
                breakpoint: 1700,
                options: {
                    legend: {
                        offsetX: 60 // Set offsetX to 60 for screen sizes under 1700px
                    }
                }
            },
            {
                // For screens smaller than 1700px
                breakpoint: 1600,
                options: {
                    legend: {
                        offsetX: 30 // Set offsetX to 30 for screen sizes under 1600px
                    }
                }
            },
            {
                // For screens smaller than 1700px
                breakpoint: 1550,
                options: {
                    legend: {
                        offsetX: 30 // Set offsetX to 30 for screen sizes under 1550px
                    }
                }
            },
            {
                breakpoint: 1500,
                options: {
                    legend: {
                        offsetX: 10
                    }
                }
            },
            {
                breakpoint: 1250,
                options: {
                    legend: {
                        offsetX: -20
                    }
                }
            },
            {
                breakpoint: 768,
                options: {
                    legend: {
                        offsetX: 180
                    }
                }
            },
            {
                breakpoint: 480,
                options: {
                    legend: {
                        offsetX: -30
                    }
                }
            }
        ]
    };

    var interviewedApplicantsDemographicChart = new ApexCharts(document.querySelector("#interviewed_applicants_demographic"), options);
    interviewedApplicantsDemographicChart.render();
}

/*
|--------------------------------------------------------------------------
| Appointed Applicants Demographic
|--------------------------------------------------------------------------
*/

// Appointed Applicants Demographic
var appointedApplicantsDemographicColors = getChartColorsArray("appointed_applicants_demographic");

// Extract percentages and labels dynamically from divisionAppointedApplicantsDemographic
var appointedApplicantsDemographicSeries = [];
var appointedApplicantsDemographicLabels = [];

divisionAppointedApplicantsDemographic.forEach(function (item) {
    appointedApplicantsDemographicSeries.push(item.percentage); // Extract the percentage value
    appointedApplicantsDemographicLabels.push(item.name);       // Extract the race name
});

// Appointed Applicants Demographic Chart
if(appointedApplicantsDemographicColors){
    var options = {
        series: appointedApplicantsDemographicSeries,
        chart: {
            height: 350,
            type: 'radialBar',
        },
        plotOptions: {
            radialBar: {
                offsetY: 0,
                startAngle: 0,
                endAngle: 270,
                hollow: {
                    margin: 5,
                    size: '30%',
                    background: 'transparent',
                    image: undefined,
                },
                dataLabels: {
                    name: {
                        show: false,
                    },
                    value: {
                        show: false,
                    }
                }
            }
        },
        colors: appointedApplicantsDemographicColors,
        labels: appointedApplicantsDemographicLabels,
        legend: {
            show: true,
            floating: true,
            fontSize: '16px',
            position: 'left',
            offsetX: 80,
            offsetY: 15,
            labels: {
                useSeriesColors: true,
            },
            markers: {
                size: 0
            },
            formatter: function (seriesName, opts) {
                return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex] + "%"
            },
            itemMargin: {
                vertical: 3
            }
        },
        responsive: [
            {
                // For screens smaller than 1700px
                breakpoint: 1700,
                options: {
                    legend: {
                        offsetX: 60 // Set offsetX to 60 for screen sizes under 1700px
                    }
                }
            },
            {
                // For screens smaller than 1700px
                breakpoint: 1600,
                options: {
                    legend: {
                        offsetX: 30 // Set offsetX to 30 for screen sizes under 1600px
                    }
                }
            },
            {
                // For screens smaller than 1700px
                breakpoint: 1550,
                options: {
                    legend: {
                        offsetX: 30 // Set offsetX to 30 for screen sizes under 1550px
                    }
                }
            },
            {
                breakpoint: 1500,
                options: {
                    legend: {
                        offsetX: 10
                    }
                }
            },
            {
                breakpoint: 1250,
                options: {
                    legend: {
                        offsetX: -20
                    }
                }
            },
            {
                breakpoint: 768,
                options: {
                    legend: {
                        offsetX: 180
                    }
                }
            },
            {
                breakpoint: 480,
                options: {
                    legend: {
                        offsetX: -30
                    }
                }
            }
        ]
    };

    var appointedApplicantsDemographicChart = new ApexCharts(document.querySelector("#appointed_applicants_demographic"), options);
    appointedApplicantsDemographicChart.render();
}

/*
|--------------------------------------------------------------------------
| Update Dashboard
|--------------------------------------------------------------------------
*/

// Function to update elements on the dashboard
function updateDashboard(data) {
    // Update total vacancies
    $('#totalVacanciesValue').text(data.divisionTotalVacancies);

    // Update total vacancies filled
    $('#totalVacanciesFilledValue').text(data.divisionTotalVacanciesFilled);

    // Update total interviews scheduled
    $('#totalInterviewsScheduledValue').text(data.divisionTotalInterviewsScheduled);

    // Update total interviews completed
    $('#totalInterviewsCompletedValue').text(data.divisionTotalInterviewsCompleted);

    // Update total applicants appointed
    $('#totalApplicantsAppointedValue').text(data.divisionTotalApplicantsAppointed);

    // Update total applicants regretted
    $('#totalApplicantsRegrettedValue').text(data.divisionTotalApplicantsRegretted);

    // Update average time to shortlist
    $('#averageTimeToShortlistValue').text(data.divisionAverageTimeToShortlist);

    // Update average time to hire
    $('#averageTimeToHireValue').text(data.divisionAverageTimeToHire);

    // Update division adoption rate
    $('#adoptionRateValue').text(data.divisionAdoptionRate + '%');

    // Update average distance talent pool applicants
    $('#averageDistanceTalentPoolApplicantsValue').text(data.divisionAverageDistanceTalentPoolApplicants + ' km');

    // Update average distance applicants appointed
    $('#averageDistanceApplicantsAppointedValue').text(data.divisionverageDistanceApplicantsAppointed + ' km');

    // Update average score of appointed applicants
    $('#averageScoreApplicantsAppointedValue').text(data.divisionAverageScoreApplicantsAppointed);

    // Update average assessmet score of appointed applicants
    $('#averageAssessmentScoreApplicantsAppointedValue').text(data.divisionAverageAssessmentScoreApplicantsAppointed);

    // Update talent pool applicants
    $('#talentPoolApplicantsValue').text(data.divisionTalentPoolApplicants);

    // Update appointed applicants
    $('#applicantsAppointedValue').text(data.divisionApplicantsAppointed);

    // Update radial charts
    updateRadialChart(totalVacanciesFilledChart, data.divisionTotalVacanciesFilled, data.divisionTotalVacancies);
    updateRadialChart(totalInterviewsCompletedChart, data.divisionTotalInterviewsCompleted, data.divisionTotalInterviewsScheduled);
    updateRadialChart(totalApplicantsAppointedChart, data.divisionTotalApplicantsAppointed, data.divisionTotalInterviewsScheduled);
    updateRadialChart(totalApplicantsRegrettedChart, data.divisionTotalApplicantsRegretted, data.divisionTotalInterviewsScheduled);

    // Update the "Talent Pool By Month" chart
    updateLineCharts(talentPoolByMonthChart, data.divisionTalentPoolApplicantsByMonth, data.divisionApplicantsAppointedByMonth);

    // Update demographic charts
    updateRadialBarChart(talentPoolApplicantsDemographicChart, data.divisionTalentPoolApplicantsDemographic);
    updateRadialBarChart(interviewedApplicantsDemographicChart, data.divisionInterviewedApplicantsDemographic);
    updateRadialBarChart(appointedApplicantsDemographicChart, data.divisionAppointedApplicantsDemographic);
}

/*
|--------------------------------------------------------------------------
| Update Radial Charts
|--------------------------------------------------------------------------
*/

// Function to update the radial chars
function updateRadialChart(chartInstance, filledValue, totalValue) {
    // Calculate percentage (check for divide by zero)
    var percentage = 0;
    if (totalValue > 0) {
        percentage = Math.round((filledValue / totalValue) * 100);
    }

    // Update the series of the passed chart instance
    chartInstance.updateSeries([percentage]);
}

/*
|--------------------------------------------------------------------------
| Update Line Charts
|--------------------------------------------------------------------------
*/

function updateLineCharts(chartInstance, divisionTalentPoolApplicantsByMonth, divisionpplicantsAppointedByMonth) {
    // Get default months from January to December
    var defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Prepare the data for the Talent Pool By Month Chart
    var talentPoolData = divisionTalentPoolApplicantsByMonth && Object.keys(divisionTalentPoolApplicantsByMonth).length > 0
        ? Object.values(divisionTalentPoolApplicantsByMonth) // Extract values if not empty
        : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

    var appointedData = divisionApplicantsAppointedByMonth && Object.keys(divisionApplicantsAppointedByMonth).length > 0
        ? Object.values(divisionApplicantsAppointedByMonth) // Extract values if not empty
        : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

    // Get the months (x-axis categories)
    var months = Object.keys(divisionTalentPoolApplicantsByMonth).length > 0 
        ? Object.keys(divisionTalentPoolApplicantsByMonth)  // Use the months from data if available
        : defaultMonths; // Use default months if data is empty

    // Calculate max value for the y-axis dynamically
    var maxYValue = Math.max(...talentPoolData, ...appointedData) + 5; // Add buffer to the maximum value

    // Update chart options
    chartInstance.updateOptions({
        xaxis: {
            categories: months // Update x-axis with dynamic months
        },
        yaxis: {
            max: maxYValue // Dynamically update the y-axis maximum
        }
    });

    // Update chart series data
    chartInstance.updateSeries([
        {
            name: "Total Talent Pool",
            data: talentPoolData // Use dynamic data for Talent Pool
        },
        {
            name: "Total Appointed",
            data: appointedData // Use dynamic data for Appointed
        }
    ]);
}

/*
|--------------------------------------------------------------------------
| Update Radial Bar Charts
|--------------------------------------------------------------------------
*/

function updateRadialBarChart(chart, demographicData) {
    // Initialize the series and labels with default values for all races
    var demographicSeries = {
        'African': 0,
        'Coloured': 0,
        'Indian': 0,
        'White': 0
    };

    // Populate the series from demographicData (ensure all races have counts)
    demographicData.forEach(function (item) {
        // Update the corresponding race count from demographicData
        if (demographicSeries.hasOwnProperty(item.name)) {
            demographicSeries[item.name] = item.percentage;
        }
    });

    // Convert the object into arrays for chart update
    var seriesArray = Object.values(demographicSeries);

    // Update the chart
    chart.updateOptions({
        series: seriesArray,  // Updated series with all races
    });
}