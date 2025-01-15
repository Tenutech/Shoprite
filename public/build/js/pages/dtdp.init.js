/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job-statistics init js
*/
let allowLazyLoading = true;

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

    // Get today's date
    var endDate = new Date(); // Today's date

    // Calculate the start date as the start of the same month 12 months ago
    var startDate = new Date(endDate.getFullYear() - 1, endDate.getMonth(), 1); // Start of the month 12 months ago

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
| Update Metrics
|--------------------------------------------------------------------------
*/

// Helper function to display 'N/A' for invalid values and the value itself for valid numbers (including 0)
function formatValue(value) {
    return value !== null && value !== undefined ? value : 'N/A';
}

// Function to update the metrics on the page for each type
function updateMetrics(type, data) {
    switch (type) {
        case 'time-metrics':
            document.getElementById('averageTimeToShortlistValue').textContent = formatValue(data.averageTimeToShortlist);
            document.getElementById('averageTimeToHireValue').textContent = formatValue(data.averageTimeToHire);
            document.getElementById('adoptionRateValue').textContent = data.adoptionRate !== null && data.adoptionRate !== undefined ? `${data.adoptionRate}%` : 'N/A';
            break;

        case 'proximity-metrics':
            document.getElementById('averageDistanceTalentPoolApplicantsValue').textContent = data.averageDistanceTalentPoolApplicants !== null && data.averageDistanceTalentPoolApplicants !== undefined ? `${data.averageDistanceTalentPoolApplicants} km` : 'N/A';
            document.getElementById('averageDistanceApplicantsAppointedValue').textContent = data.averageDistanceApplicantsAppointed !== null && data.averageDistanceApplicantsAppointed !== undefined ? `${data.averageDistanceApplicantsAppointed} km` : 'N/A';
            break;

        case 'average-score-metrics':
            document.getElementById('averageScoreTalentPoolApplicants').textContent = formatValue(data.averageScoreTalentPoolApplicants);
            document.getElementById('averageScoreApplicantsAppointedValue').textContent = formatValue(data.averageScoreApplicantsAppointed);
            break;

        case 'assessment-score-metrics':
            updateDonutChart(averageLiteracyScoreChart, data.averageLiteracyScoreTalentPoolApplicants, data.literacyQuestionsCount);
            hideSpinner("literacy_chart_container");
            updateDonutChart(averageNumeracyScoreChart, data.averageNumeracyScoreTalentPoolApplicants, data.numeracyQuestionsCount);
            hideSpinner("numeracy_chart_container");
            updateDonutChart(averageSituationalScoreChart, data.averageSituationalScoreTalentPoolApplicants, data.situationalQuestionsCount);
            hideSpinner("situational_chart_container");
            break;

        case 'vacancies-metrics':
            document.getElementById('totalVacanciesValue').textContent = formatValue(data.totalVacancies);
            document.getElementById('totalVacanciesFilledValue').textContent = formatValue(data.totalVacanciesFilled);
            updateRadialChart(totalVacanciesFilledChart, data.totalVacanciesFilled, data.totalVacancies);
            break;

        case 'interviews-metrics':
            document.getElementById('totalInterviewsScheduledValue').textContent = formatValue(data.totalInterviewsScheduled);
            document.getElementById('totalInterviewsCompletedValue').textContent = formatValue(data.totalInterviewsCompleted);
            updateRadialChart(totalInterviewsCompletedChart, data.totalInterviewsCompleted, data.totalInterviewsScheduled);
            break;

        case 'applicants-metrics':
            document.getElementById('totalApplicantsAppointedValue').textContent = formatValue(data.totalApplicantsAppointed);
            document.getElementById('totalApplicantsRegrettedValue').textContent = formatValue(data.totalApplicantsRegretted);
            updateRadialChart(totalApplicantsAppointedChart, data.totalApplicantsAppointed, data.totalInterviewsScheduled);
            updateRadialChart(totalApplicantsRegrettedChart, data.totalApplicantsRegretted, data.totalInterviewsScheduled);
            break;

        case 'talent-pool-metrics':
            document.getElementById('talentPoolApplicantsValue').textContent = formatValue(data.talentPoolApplicants);
            document.getElementById('applicantsAppointedValue').textContent = formatValue(data.applicantsAppointed);
            updateLineCharts(talentPoolByMonthChart, data.talentPoolApplicantsByMonth, data.applicantsAppointedByMonth);
            break;

        case 'demographic-metrics':
            updateRadialBarChart(talentPoolApplicantsDemographicChart, data.talentPoolApplicantsDemographic);
            hideSpinner("talent_pool_applicants_demographic_container");
            updateRadialBarChart(interviewedApplicantsDemographicChart, data.interviewedApplicantsDemographic);
            hideSpinner("interviewed_applicants_demographic_container");
            updateRadialBarChart(appointedApplicantsDemographicChart, data.appointedApplicantsDemographic);
            hideSpinner("appointed_applicants_demographic_container");
            break;

        default:
            console.error('Unknown metrics type:', type);
    }
}

/*
|--------------------------------------------------------------------------
| Fetch Metrics
|--------------------------------------------------------------------------
*/

// Function to fetch metrics data from the API
function fetchMetrics(type, routeName) {
    const apiUrl = route(routeName); // Use Ziggy to dynamically generate the route URL

    fetch(apiUrl)
        .then((response) => response.json())
        .then((data) => {
            updateMetrics(type, data); // Update the metrics on the page
        })
        .catch((error) => {
            console.error(`Error loading ${type} data:`, error);
        });
}

/*
|--------------------------------------------------------------------------
| Lazy Load Data
|--------------------------------------------------------------------------
*/

// Function to lazy load metrics for a specific row
function lazyLoadMetrics(rowId, type, routeName) {
    const metricsRow = document.getElementById(rowId);

    const observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting && allowLazyLoading) {
            fetchMetrics(type, routeName); // Fetch data when the row is visible
            observer.disconnect(); // Stop observing after data is loaded
        }
    });

    observer.observe(metricsRow);
}

/*
|--------------------------------------------------------------------------
| Initialize Lazy Loading for All Metrics
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', function () {
    lazyLoadMetrics('timeRow', 'time-metrics', 'time.metrics');
    lazyLoadMetrics('proximityRow', 'proximity-metrics', 'proximity.metrics');
    lazyLoadMetrics('averageScoresRow', 'average-score-metrics', 'average-score.metrics');
    lazyLoadMetrics('vacanciesRow', 'vacancies-metrics', 'vacancies.metrics');
    lazyLoadMetrics('interviewsRow', 'interviews-metrics', 'interviews.metrics');
    lazyLoadMetrics('applicantsRow', 'applicants-metrics', 'applicants.metrics');
    lazyLoadMetrics('talentPoolRow', 'talent-pool-metrics', 'talent-pool.metrics');
    lazyLoadMetrics('demographicRow', 'demographic-metrics', 'demographic.metrics');
});

/*
|--------------------------------------------------------------------------
| Show and Hide Spinners
|--------------------------------------------------------------------------
*/

// Show the spinner in the header
function showSpinner(containerId) {
    const spinner = document.querySelector(`#${containerId} .spinner-border`);
    if (spinner) {
        spinner.classList.remove('d-none');
    }
}

// Hide the spinner in the header
function hideSpinner(containerId) {
    const spinner = document.querySelector(`#${containerId} .spinner-border`);
    if (spinner) {
        spinner.classList.add('d-none');
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
var talentPoolByMonthColors = getChartColorsArray("talent_pool_by_month");

// Prepare default months from January to December
var defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Get the months (x-axis categories)
var months = defaultMonths; // Use default months if data is empty

//  Talent Pool By Month Chart
if (talentPoolByMonthColors) {
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
        colors: talentPoolByMonthColors,
        dataLabels: {
            enabled: false,
        },
        stroke: {
            width: [3, 3],
            curve: 'straight'
        },
        series: [{
                name: "Total Talent Pool",
                data: [] // Use dynamic data for Talent Pool
            },
            {
                name: "Total Appointed",
                data: [] // Use dynamic data for Appointed
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
            max: 5000 // Set the max value based on the highest number in your data
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

// Extract percentages and labels dynamically from talentPoolApplicantsDemographic
var talentPoolApplicantsDemographicSeries = [];
var talentPoolApplicantsDemographicLabels = ['African', 'Coloured', 'Indian', 'White'];

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

// Extract percentages and labels dynamically from interviewedApplicantsDemographic
var interviewedApplicantsDemographicSeries = [];
var interviewedApplicantsDemographicLabels = ['African', 'Coloured', 'Indian', 'White'];

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

// Extract percentages and labels dynamically from appointedApplicantsDemographic
var appointedApplicantsDemographicSeries = [];
var appointedApplicantsDemographicLabels = ['African', 'Coloured', 'Indian', 'White'];

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
    $('#averageDistanceTalentPoolApplicantsValue').text(data.divisionAverageDistanceTalentPoolApplicants);

    // Update average distance applicants appointed
    $('#averageDistanceApplicantsAppointedValue').text(data.divisionverageDistanceApplicantsAppointed);

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

function updateLineCharts(chartInstance, talentPoolApplicantsByMonth, applicantsAppointedByMonth) {
    // Get default months from January to December
    var defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Prepare the data for the Talent Pool By Month Chart
    var talentPoolData = talentPoolApplicantsByMonth && Object.keys(talentPoolApplicantsByMonth).length > 0
        ? Object.values(talentPoolApplicantsByMonth) // Extract values if not empty
        : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

    var appointedData = applicantsAppointedByMonth && Object.keys(applicantsAppointedByMonth).length > 0
        ? Object.values(applicantsAppointedByMonth) // Extract values if not empty
        : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

    // Get the months (x-axis categories)
    var months = Object.keys(talentPoolApplicantsByMonth).length > 0
        ? Object.keys(talentPoolApplicantsByMonth)  // Use the months from data if available
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