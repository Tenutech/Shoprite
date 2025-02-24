/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job-statistics init js
*/

// Enable lazy loading by default
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

                // Disable lazy loading when date range changes
                allowLazyLoading = false;

                // Get the button and replace its content with the spinner
                const calendarBtn = document.getElementById('calendarBtn');
                const originalContent = calendarBtn.innerHTML; // Save original content
                calendarBtn.innerHTML = '<div class="spinner-border spinner-border-sm text-white" role="status"></div>';

                // Hide Spinners
                hideSpinner("talent_pool_applicants_demographic_container");
                hideSpinner("interviewed_applicants_demographic_container");
                hideSpinner("appointed_applicants_demographic_container");
                hideSpinner("talent_pool_applicants_gender_container");
                hideSpinner("interviewed_applicants_gender_container");
                hideSpinner("appointed_applicants_gender_container");
                hideSpinner("talent_pool_applicants_province_container");
        
                // Send the date range via AJAX to update the dashboard
                $.ajax({
                    url: route('admin.updateDashboard'),
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
                    },
                    complete: function () {
                        // Revert the button content back to the original icon
                        calendarBtn.innerHTML = originalContent;
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

        case 'proximity-talent-pool':
            document.getElementById('averageDistanceTalentPoolApplicantsValue').textContent = data.averageDistanceTalentPoolApplicants !== null && data.averageDistanceTalentPoolApplicants !== undefined ? `${data.averageDistanceTalentPoolApplicants} km` : 'N/A';
            break;
    
        case 'proximity-applicants-appointed':
            document.getElementById('averageDistanceApplicantsAppointedValue').textContent = data.averageDistanceApplicantsAppointed !== null && data.averageDistanceApplicantsAppointed !== undefined ? `${data.averageDistanceApplicantsAppointed} km` : 'N/A';
            break;

        case 'average-score-metrics':
            document.getElementById('averageScoreTalentPoolApplicantsValue').textContent = formatValue(data.averageScoreTalentPoolApplicants);
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

        case 'application-channels-metrics':
            document.getElementById('totalWhatsAppApplicantsValue').textContent = formatValue(data.totalWhatsAppApplicants);
            document.getElementById('totalWebsiteApplicantsValue').textContent = formatValue(data.totalWebsiteApplicants);
            updateRadialChart(totalWhatsAppApplicantsChart, data.totalWhatsAppApplicants, data.talentPoolApplicants);
            updateRadialChart(totalWebsiteApplicantsChart, data.totalWebsiteApplicants, data.talentPoolApplicants);
            break;

        case 'application-completion-metrics':
            document.getElementById('completionRateValue').textContent = data.completionRate !== null && data.completionRate !== undefined ? `${data.completionRate}%` : 'N/A';
            document.getElementById('dropOffStateValue').textContent = formatValue(data.dropOffState);

             // Initialize and set the tooltip dynamically
            const dropOffStateCard = document.getElementById('dropOffStateCard');
            if (dropOffStateCard) {
                const tooltipContent = data.dropOffChat.message || 'No additional details';

                // Remove any existing tooltip instance
                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                    const existingTooltip = bootstrap.Tooltip.getInstance(dropOffStateCard);
                    if (existingTooltip) {
                        existingTooltip.dispose();
                    }
                }

                // Initialize a new tooltip with the updated content
                new bootstrap.Tooltip(dropOffStateCard, {
                    title: tooltipContent,
                    placement: 'top',
                    html: true,
                });
            }
            break;

        case 'stores-metrics':
            document.getElementById('totalsStoresUsingSolutionValue').textContent = formatValue(data.totalStoresUsingSolution);
            document.getElementById('totalReEmployedApplicantsValue').textContent = formatValue(data.totalReEmployedApplicants);
            updateRadialChart(totalsStoresUsingSolutionChart, data.totalStoresUsingSolution, data.totalStores);
            updateRadialChart(totalReEmployedApplicantsChart, data.totalReEmployedApplicants, data.totalAppointedApplicants);
            break;

        case 'demographic-metrics':
            updateRadialBarChart(talentPoolApplicantsDemographicChart, data.talentPoolApplicantsDemographic);
            hideSpinner("talent_pool_applicants_demographic_container");
            updateRadialBarChart(interviewedApplicantsDemographicChart, data.interviewedApplicantsDemographic);
            hideSpinner("interviewed_applicants_demographic_container");
            updateRadialBarChart(appointedApplicantsDemographicChart, data.appointedApplicantsDemographic);
            hideSpinner("appointed_applicants_demographic_container");

            // Update demographic totals
            updateDemographicTotals(data.talentPoolApplicantsDemographic, "talent_pool_applicants_demographic_totals");
            updateDemographicTotals(data.interviewedApplicantsDemographic, "interviewed_pool_applicants_demographic_totals");
            updateDemographicTotals(data.appointedApplicantsDemographic, "appointed_pool_applicants_demographic_totals");
            break;

        case 'gender-metrics':
            updateRadialBarChartGender(talentPoolApplicantsGenderChart, data.talentPoolApplicantsGender);
            hideSpinner("talent_pool_applicants_gender_container");
            updateRadialBarChartGender(interviewedApplicantsGenderChart, data.interviewedApplicantsGender);
            hideSpinner("interviewed_applicants_gender_container");
            updateRadialBarChartGender(appointedApplicantsGenderChart, data.appointedApplicantsGender);
            hideSpinner("appointed_applicants_gender_container");

            // Update gender totals
            updateGenderTotals(data.talentPoolApplicantsGender, "talent_pool_applicants_gender_totals");
            updateGenderTotals(data.interviewedApplicantsGender, "interviewed_applicants_gender_totals");
            updateGenderTotals(data.appointedApplicantsGender, "appointed_applicants_gender_totals");
            break;

        case 'province-metrics':
            updateTreemapChart(talentPoolApplicantsProvinceChart, data.talentPoolApplicantsProvince);
            hideSpinner("talent_pool_applicants_province_container");
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

// Function to fetch metrics data from the API with abort signal
function fetchMetrics(type, routeName, signal) {
    const apiUrl = route(routeName); // Use Ziggy to dynamically generate the route URL

    return fetch(apiUrl, { signal }) // Pass the signal to the fetch call
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then((data) => {
            updateMetrics(type, data); // Update the metrics on the page
        })
        .catch((error) => {
            if (error.name === 'AbortError') {
                console.log(`Fetch aborted for ${routeName}`);
            } else {
                console.error(`Error fetching metrics for ${routeName}:`, error);
            }
        });
}

/*
|--------------------------------------------------------------------------
| Lazy Load Data
|--------------------------------------------------------------------------
*/

// Store controllers for each fetch call
const abortControllers = new Map();

// Function to lazy load metrics for a specific row with abort support
function lazyLoadMetrics(rowId, type, routeName) {
    const metricsRow = document.getElementById(rowId);

    if (!metricsRow) {
        console.warn(`Metrics row with ID ${rowId} not found`);
        return;
    }

    const observer = new IntersectionObserver((entries, observer) => {
        if (entries[0].isIntersecting) {
            const controller = new AbortController();
            const signal = controller.signal;
            abortControllers.set(rowId, controller);

            fetchMetrics(type, routeName, signal)
                .then(() => {
                    observer.disconnect(); // Stop observing only after data is loaded
                    abortControllers.delete(rowId);
                })
                .catch((error) => {
                    if (error.name === 'AbortError') {
                        console.log(`Fetch aborted for ${rowId}`);
                    } else {
                        console.error(`Error fetching metrics for ${rowId}:`, error);
                    }
                });
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
    // lazyLoadMetrics('proximityRow', 'proximity-metrics', 'proximity.metrics');
    lazyLoadMetrics('proximityRow', 'proximity-talent-pool', 'proximity.metrics.talentpool');
    lazyLoadMetrics('proximityRow', 'proximity-applicants-appointed', 'proximity.metrics.appointed');
    lazyLoadMetrics('averageScoresRow', 'average-score-metrics', 'average-score.metrics');
    lazyLoadMetrics('assessmentScoresRow', 'assessment-score-metrics', 'assessment-scores.metrics');
    lazyLoadMetrics('vacanciesRow', 'vacancies-metrics', 'vacancies.metrics');
    lazyLoadMetrics('interviewsRow', 'interviews-metrics', 'interviews.metrics');
    lazyLoadMetrics('applicantsRow', 'applicants-metrics', 'applicants.metrics');
    lazyLoadMetrics('talentPoolRow', 'talent-pool-metrics', 'talent-pool.metrics');
    lazyLoadMetrics('applicationChannelsRow', 'application-channels-metrics', 'application-channels.metrics');
    lazyLoadMetrics('applicationCompletionRow', 'application-completion-metrics', 'application-completion.metrics');
    lazyLoadMetrics('storesRow', 'stores-metrics', 'stores.metrics');
    lazyLoadMetrics('demographicRow', 'demographic-metrics', 'demographic.metrics');
    lazyLoadMetrics('genderRow', 'gender-metrics', 'gender.metrics');
    lazyLoadMetrics('provinceRow', 'province-metrics', 'province.metrics');
});

/*
|-------------------------------------------------------------------------- 
| Cancel All Pending Requests on Navigation
|-------------------------------------------------------------------------- 
*/

// Function to abort all ongoing requests
function abortAllRequests() {
    abortControllers.forEach((controller) => {
        controller.abort(); // Abort each fetch
    });
    abortControllers.clear(); // Clear the map
}

// Attach event listener to cancel requests on navigation
window.addEventListener('beforeunload', abortAllRequests);

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
| Average Literacy Score
|--------------------------------------------------------------------------
*/

// Average Literacy Score
var averageLiteracyScoreColors = getChartColorsArray("literacy_chart");

// Generate the series and labels dynamically based on the number of questions and average score
var literacySeries = [];
var literacyLabels = [];

// Average Literacy Score Chart
if (averageLiteracyScoreColors) {
    var options = {
        series: literacySeries, // Dynamically generated series
        chart: {
            height: 300,
            type: 'donut',
        },
        labels: literacyLabels, // Dynamically generated labels
        theme: {
            monochrome: {
                enabled: true,
                color: averageLiteracyScoreColors[0],
                shadeTo: 'dark',
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
            text: '',
            floating: true,
            offsetY: 125,
            align: 'center',
            style: {
                fontSize: '20px',
                fontWeight: 'bold'
            }
        }
    };

    var averageLiteracyScoreChart = new ApexCharts(document.querySelector("#literacy_chart"), options);
    averageLiteracyScoreChart.render();
}

/*
|--------------------------------------------------------------------------
| Average Numeracy Score
|--------------------------------------------------------------------------
*/

// Average Numeracy Score
var averageNumeracyScoreColors = getChartColorsArray("numeracy_chart");

// Generate the series and labels dynamically based on the number of questions and average score
var numeracySeries = [];
var numeracyLabels = [];

// Average Numeracy Score Chart
if (averageNumeracyScoreColors) {
    var options = {
        series: numeracySeries, // Dynamically generated series
        chart: {
            height: 300,
            type: 'donut',
        },
        labels: numeracyLabels, // Dynamically generated labels
        theme: {
            monochrome: {
                enabled: true,
                color: averageNumeracyScoreColors[0],
                shadeTo: 'dark',
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
            text: '',
            floating: true,
            offsetY: 125,
            align: 'center',
            style: {
                fontSize: '20px',
                fontWeight: 'bold'
            }
        }
    };

    var averageNumeracyScoreChart = new ApexCharts(document.querySelector("#numeracy_chart"), options);
    averageNumeracyScoreChart.render();
}

/*
|--------------------------------------------------------------------------
| Average Situational Score
|--------------------------------------------------------------------------
*/

// Average Situational Score
var averageSituationalScoreColors = getChartColorsArray("situational_chart");

// Generate the series and labels dynamically based on the number of questions and average score
var situationalSeries = []; // Placeholder for series
var situationalLabels = []; // Placeholder for labels

// Average Situational Score Chart
if (averageSituationalScoreColors) {
    var options = {
        series: situationalSeries, // Dynamically generated series
        chart: {
            height: 300,
            type: 'donut',
        },
        labels: situationalLabels, // Dynamically generated labels
        theme: {
            monochrome: {
                enabled: true,
                color: averageSituationalScoreColors[0],
                shadeTo: 'dark',
                shadeIntensity: 0.6,
            },
        },
        plotOptions: {
            pie: {
                dataLabels: {
                    offset: -5,
                },
            },
        },
        dataLabels: {
            formatter: function (val, opts) {
                var name = opts.w.globals.labels[opts.seriesIndex];
                return name; // Only return the number, not the percentage
            },
            dropShadow: {
                enabled: false,
            },
        },
        legend: {
            show: false,
        },
        title: {
            text: '',
            floating: true,
            offsetY: 125,
            align: 'center',
            style: {
                fontSize: '20px',
                fontWeight: 'bold',
            },
        },
    };

    var averageSituationalScoreChart = new ApexCharts(document.querySelector("#situational_chart"), options);
    averageSituationalScoreChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Vacancies Filled
|--------------------------------------------------------------------------
*/

// Total Vacancies Filled
var totalVacanciesFilledColors = getChartColorsArray("total_vacancies_filled");

// Total Vacancies Filled Chart
if (totalVacanciesFilledColors) {
    var options = {
        series: [], // Use the calculated percentage
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
        colors: totalVacanciesFilledColors
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
var totalInterviewsCompletedColors = getChartColorsArray("total_interviews_completed");

// Total Interviews Completed Chart
if (totalInterviewsCompletedColors) {
    var options = {
        series: [], // Use the calculated percentage
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
        colors: totalInterviewsCompletedColors
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
var totalApplicantsAppointedColors = getChartColorsArray("total_applicants_appointed");

// Total Applicants Appointed Chart
if (totalApplicantsAppointedColors) {
    var options = {
        series: [], // Use the calculated percentage
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
        colors: totalApplicantsAppointedColors
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
var totalApplicantsRegrettedColors = getChartColorsArray("total_applicants_regretted");

// Total Applicants Regretted Chart
if (totalApplicantsRegrettedColors) {
    var options = {
        series: [], // Use the calculated percentage
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
        colors: totalApplicantsRegrettedColors
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
| Total WhatsApp Applicants
|--------------------------------------------------------------------------
*/

// Total WhatsApp Applicants
var totalWhatsAppApplicantsColors = getChartColorsArray("total_whatsapp_applicants");

// Total WhatsApp Applicants Chart
if (totalWhatsAppApplicantsColors) {
    var options = {
        series: [], // Use the calculated percentage
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
        colors: totalWhatsAppApplicantsColors
    };

    var totalWhatsAppApplicantsChart = new ApexCharts(document.querySelector("#total_whatsapp_applicants"), options);
    totalWhatsAppApplicantsChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Website Applicants
|--------------------------------------------------------------------------
*/

// Total Website Applicants
var totalWebsiteApplicantsColors = getChartColorsArray("total_website_applicants");

// Total Website Applicants Chart
if (totalWebsiteApplicantsColors) {
    var options = {
        series: [], // Use the calculated percentage
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
        colors: totalWebsiteApplicantsColors
    };

    var totalWebsiteApplicantsChart = new ApexCharts(document.querySelector("#total_website_applicants"), options);
    totalWebsiteApplicantsChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Stores Using Solution
|--------------------------------------------------------------------------
*/

// Total Stores Using Solution
var totalStoresUsingSolutionColors = getChartColorsArray("total_stores_using_solution");

// Total Website Applicants Chart
if (totalStoresUsingSolutionColors) {
    var options = {
        series: [], // Use the calculated percentage
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
        colors: totalStoresUsingSolutionColors
    };

    var totalsStoresUsingSolutionChart = new ApexCharts(document.querySelector("#total_stores_using_solution"), options);
    totalsStoresUsingSolutionChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Re-Employed Applicants
|--------------------------------------------------------------------------
*/

// Total Re-Employed Applicants
var totalReEmployedApplicantsColors = getChartColorsArray("total_re_employed_applicants");

// Total Website Applicants Chart
if (totalReEmployedApplicantsColors) {
    var options = {
        series: [], // Use the calculated percentage
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
        colors: totalReEmployedApplicantsColors
    };

    var totalReEmployedApplicantsChart = new ApexCharts(document.querySelector("#total_re_employed_applicants"), options);
    totalReEmployedApplicantsChart.render();
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
| Talent Pool Applicants Gender
|--------------------------------------------------------------------------
*/

// Talent Pool Applicants Gender
var talentPoolApplicantsGenderColors = getChartColorsArray("talent_pool_applicants_gender");

// Extract percentages and labels dynamically from talentPoolApplicantsGender
var talentPoolApplicantsGenderSeries = [];
var talentPoolApplicantsGenderLabels = ['Male', 'Female'];

// Talent Pool Applicants Gender Chart
if(talentPoolApplicantsGenderColors){
    var options = {
        series: talentPoolApplicantsGenderSeries,
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
        colors: talentPoolApplicantsGenderColors,
        labels: talentPoolApplicantsGenderLabels,
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

    var talentPoolApplicantsGenderChart = new ApexCharts(document.querySelector("#talent_pool_applicants_gender"), options);
    talentPoolApplicantsGenderChart.render();
}

/*
|--------------------------------------------------------------------------
| Interviewed Applicants Gender
|--------------------------------------------------------------------------
*/

// Interviewed Applicants Gender
var interviewedApplicantsGenderColors = getChartColorsArray("interviewed_applicants_gender");

// Extract percentages and labels dynamically from interviewedApplicantsGender
var interviewedApplicantsGenderSeries = [];
var interviewedApplicantsGenderLabels = ['Male', 'Female'];

// Interviewed Applicants Gender Chart
if(interviewedApplicantsGenderColors){
    var options = {
        series: interviewedApplicantsGenderSeries,
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
        colors: interviewedApplicantsGenderColors,
        labels: interviewedApplicantsGenderLabels,
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

    var interviewedApplicantsGenderChart = new ApexCharts(document.querySelector("#interviewed_applicants_gender"), options);
    interviewedApplicantsGenderChart.render();
}

/*
|--------------------------------------------------------------------------
| Appointed Applicants Gender
|--------------------------------------------------------------------------
*/

// Appointed Applicants Gender
var appointedApplicantsGenderColors = getChartColorsArray("appointed_applicants_gender");

// Extract percentages and labels dynamically from appointedApplicantsGender
var appointedApplicantsGenderSeries = [];
var appointedApplicantsGenderLabels = ['Male', 'Female'];


// Appointed Applicants Gender Chart
if(appointedApplicantsGenderColors){
    var options = {
        series: appointedApplicantsGenderSeries,
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
        colors: appointedApplicantsGenderColors,
        labels: appointedApplicantsGenderLabels,
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

    var appointedApplicantsGenderChart = new ApexCharts(document.querySelector("#appointed_applicants_gender"), options);
    appointedApplicantsGenderChart.render();
}

/*
|-------------------------------------------------------------------------- 
| Talent Pool Applicants Province
|-------------------------------------------------------------------------- 
*/

// Talent Pool Applicants Province
var talentPoolApplicantsProvinceColors = getChartColorsArray("talent_pool_applicants_province");

// Convert the talentPoolApplicantsProvince object into the required format for the series
var talentPoolApplicantsProvinceSeries = [];

// Talent Pool Applicants Province Chart
if(talentPoolApplicantsProvinceColors) {
    var options = {
        series: [{
            data: talentPoolApplicantsProvinceSeries // Use the dynamically generated series
        }],
        legend: {
            show: false
        },
        chart: {
            height: 350,
            type: 'treemap',
            toolbar: {
                show: false
            }
        },
        title: {
            text: 'Talent Pool By Province',
            align: 'center',
            style: {
                fontWeight: 500,
            }
        },
        colors: talentPoolApplicantsProvinceColors,
        plotOptions: {
            treemap: {
                distributed: true,
                enableShades: false
            }
        }
    };

    var talentPoolApplicantsProvinceChart = new ApexCharts(document.querySelector("#talent_pool_applicants_province"), options);
    talentPoolApplicantsProvinceChart.render();
}

/*
|--------------------------------------------------------------------------
| Update Dashboard
|--------------------------------------------------------------------------
*/

// Function to update elements on the dashboard
function updateDashboard(data) {
    // Update total vacancies
    $('#totalVacanciesValue').text(data.totalVacancies);

    // Update total vacancies filled
    $('#totalVacanciesFilledValue').text(data.totalVacanciesFilled);

    // Update total interviews scheduled
    $('#totalInterviewsScheduledValue').text(data.totalInterviewsScheduled);

    // Update total interviews completed
    $('#totalInterviewsCompletedValue').text(data.totalInterviewsCompleted);

    // Update total applicants appointed
    $('#totalApplicantsAppointedValue').text(data.totalApplicantsAppointed);

    // Update total applicants regretted
    $('#totalApplicantsRegrettedValue').text(data.totalApplicantsRegretted);

    // Update average time to shortlist
    $('#averageTimeToShortlistValue').text(data.averageTimeToShortlist);

    // Update average time to hire
    $('#averageTimeToHireValue').text(data.averageTimeToHire);

    // Update dashboard adoption rate
    $('#adoptionRateValue').text(data.adoptionRate + '%');

    // Update average distance talent pool applicants
    $('#averageDistanceTalentPoolApplicantsValue').text(data.averageDistanceTalentPoolApplicants);

    // Update average distance applicants appointed
    $('#averageDistanceApplicantsAppointedValue').text(data.averageDistanceApplicantsAppointed);

    // Update talent pool applicants
    $('#talentPoolApplicantsValue').text(data.talentPoolApplicants);

    // Update appointed applicants
    $('#applicantsAppointedValue').text(data.applicantsAppointed);

    // Update average score of talent pool applicants
    $('#averageScoreTalentPoolApplicantsValue').text(data.averageScoreTalentPoolApplicants);

    // Update average score of appointed applicants
    $('#averageScoreApplicantsAppointedValue').text(data.averageScoreApplicantsAppointed);

    // Update total WhatsApp applicants
    $('#totalWhatsAppApplicantsValue').text(data.totalWhatsAppApplicants);

    // Update total Website applicants
    $('#totalWebsiteApplicantsValue').text(data.totalWebsiteApplicants);

    // Update completion rate
    $('#completionRateValue').text(data.completionRate);

    // Update drop off state
    $('#dropOffStateValue').text(data.dropOffState);

    // Update stores using the solution
    $('#totalsStoresUsingSolutionValue').text(data.totalStoresUsingSolution);
    
    // Update re-employed applicants
    $('#totalReEmployedApplicantsValue').text(data.totalReEmployedApplicants);

    // Update radial charts
    updateRadialChart(totalVacanciesFilledChart, data.totalVacanciesFilled, data.totalVacancies);
    updateRadialChart(totalInterviewsCompletedChart, data.totalInterviewsCompleted, data.totalInterviewsScheduled);
    updateRadialChart(totalApplicantsAppointedChart, data.totalApplicantsAppointed, data.totalInterviewsScheduled);
    updateRadialChart(totalApplicantsRegrettedChart, data.totalApplicantsRegretted, data.totalInterviewsScheduled);
    updateRadialChart(totalWhatsAppApplicantsChart, data.totalWhatsAppApplicants, data.talentPoolApplicants);
    updateRadialChart(totalWebsiteApplicantsChart, data.totalWebsiteApplicants, data.talentPoolApplicants);
    updateRadialChart(totalsStoresUsingSolutionChart, data.totalStoresUsingSolution, data.totalStores);
    updateRadialChart(totalReEmployedApplicantsChart, data.totalReEmployedApplicants, data.totalAppointedApplicants);

    // Update the "Talent Pool By Month" chart
    updateLineCharts(talentPoolByMonthChart, data.talentPoolApplicantsByMonth, data.applicantsAppointedByMonth);

    // Update average literacy, numeracy, and situational scores
    updateDonutChart(averageLiteracyScoreChart, data.averageLiteracyScoreTalentPoolApplicants, data.literacyQuestionsCount);
    updateDonutChart(averageNumeracyScoreChart, data.averageNumeracyScoreTalentPoolApplicants, data.numeracyQuestionsCount);
    updateDonutChart(averageSituationalScoreChart, data.averageSituationalScoreTalentPoolApplicants, data.situationalQuestionsCount);

    // Update demographic charts
    updateRadialBarChart(talentPoolApplicantsDemographicChart, data.talentPoolApplicantsDemographic);
    updateRadialBarChart(interviewedApplicantsDemographicChart, data.interviewedApplicantsDemographic);
    updateRadialBarChart(appointedApplicantsDemographicChart, data.appointedApplicantsDemographic);

    // Update demographic totals
    updateDemographicTotals(data.talentPoolApplicantsDemographic, "talent_pool_applicants_demographic_totals");
    updateDemographicTotals(data.interviewedApplicantsDemographic, "interviewed_pool_applicants_demographic_totals");
    updateDemographicTotals(data.appointedApplicantsDemographic, "appointed_pool_applicants_demographic_totals");

    // Update gender charts
    updateRadialBarChartGender(talentPoolApplicantsGenderChart, data.talentPoolApplicantsGender);
    updateRadialBarChartGender(interviewedApplicantsGenderChart, data.interviewedApplicantsGender);
    updateRadialBarChartGender(appointedApplicantsGenderChart, data.appointedApplicantsGender);

    // Update gender totals
    updateGenderTotals(data.talentPoolApplicantsGender, "talent_pool_applicants_gender_totals");
    updateGenderTotals(data.interviewedApplicantsGender, "interviewed_applicants_gender_totals");
    updateGenderTotals(data.appointedApplicantsGender, "appointed_applicants_gender_totals");

    // Update the treemap chart with the new province data
    updateTreemapChart(talentPoolApplicantsProvinceChart, data.talentPoolApplicantsProvince);
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
| Update Donut Charts
|--------------------------------------------------------------------------
*/

function updateDonutChart(chart, score, totalQuestions) {
    // Dynamically generate series and labels
    var series = [];
    var labels = [];
    for (var i = 1; i <= score; i++) {
        series.push(i);
        labels.push(i.toString());
    }
    
    // Update chart series and title
    chart.updateOptions({
        series: series,
        labels: labels,
        title: {
            text: score + '/' + totalQuestions
        }
    });
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

/*
|--------------------------------------------------------------------------
| Update Demographic Totals
|--------------------------------------------------------------------------
*/

// Function to update demographic totals with row ID
function updateDemographicTotals(demographicData, rowId) {
    const rowElement = document.getElementById(rowId);
    if (rowElement) {
        // Define demographic categories to handle the empty data case
        const categories = ["African", "Coloured", "Indian", "White"];
        
        if (demographicData.length === 0) {
            // If data is empty, set all totals to 0
            categories.forEach(category => {
                const totalElement = rowElement.querySelector(`.${category}`);
                if (totalElement) {
                    totalElement.textContent = `0`;
                }
            });
        } else {
            // Otherwise, update the totals with actual data
            demographicData.forEach(demo => {
                const totalElement = rowElement.querySelector(`.${demo.name}`);
                if (totalElement) {
                    totalElement.textContent = `${demo.total}`;
                }
            });

            // Handle categories not present in the demographicData
            categories.forEach(category => {
                if (!demographicData.some(demo => demo.name === category)) {
                    const totalElement = rowElement.querySelector(`.${category}`);
                    if (totalElement) {
                        totalElement.textContent = `0`;
                    }
                }
            });
        }
    }
}

/*
|--------------------------------------------------------------------------
| Update Radial Bar Charts Gender
|--------------------------------------------------------------------------
*/

function updateRadialBarChartGender(chart, genderData) {
    // Initialize the series and labels with default values for all races
    var genderSeries = {
        'Male': 0,
        'Female': 0
    };

    // Populate the series from genderData (ensure all races have counts)
    genderData.forEach(function (item) {
        // Update the corresponding race count from genderData
        if (genderSeries.hasOwnProperty(item.name)) {
            genderSeries[item.name] = item.percentage;
        }
    });

    // Convert the object into arrays for chart update
    var seriesArray = Object.values(genderSeries);

    // Update the chart
    chart.updateOptions({
        series: seriesArray,  // Updated series with all races
    });
}

/*
|--------------------------------------------------------------------------
| Update Gender Totals
|--------------------------------------------------------------------------
*/

// Function to update gender totals with row ID
function updateGenderTotals(genderData, rowId) {
    const rowElement = document.getElementById(rowId);
    if (rowElement) {
        // Define gender categories to handle the empty data case
        const categories = ["Male", "Female", "Non-Binary", "Other"];

        if (genderData.length === 0) {
            // If data is empty, set all totals to 0
            categories.forEach(category => {
                const totalElement = rowElement.querySelector(`.${category}`);
                if (totalElement) {
                    totalElement.textContent = `0`;
                }
            });
        } else {
            // Otherwise, update the totals with actual data
            genderData.forEach(demo => {
                const totalElement = rowElement.querySelector(`.${demo.name}`);
                if (totalElement) {
                    totalElement.textContent = `${demo.total}`;
                }
            });

            // Handle categories not present in the genderData
            categories.forEach(category => {
                if (!genderData.some(demo => demo.name === category)) {
                    const totalElement = rowElement.querySelector(`.${category}`);
                    if (totalElement) {
                        totalElement.textContent = `0`;
                    }
                }
            });
        }
    }
}

/*
|--------------------------------------------------------------------------
| Update Update Treemap Chart
|--------------------------------------------------------------------------
*/

function updateTreemapChart(chart, provinceData) {
    // Prepare the series data from the updated province data
    var updatedSeries = [];

    Object.keys(provinceData).forEach(function (key) {
        updatedSeries.push({
            x: key, // Province name
            y: provinceData[key] // Applicant count
        });
    });

    // Update the chart
    chart.updateSeries([{
        data: updatedSeries
    }]);
}
