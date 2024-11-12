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
    | Date Range
    |--------------------------------------------------------------------------
    */

    // Get the first day of the current year and today's date
    var startDate = new Date(new Date().getFullYear(), 0, 1); // Start of the year
    var endDate = new Date(); // Today's date

    // Initialize Flatpickr with the #dateFilter selector
    flatpickr("#date", {
        mode: "range",
        dateFormat: "d M Y",
        defaultDate: [formatDate(startDate), formatDate(endDate)], // Set default date range
    });
});

/*
|--------------------------------------------------------------------------
| Form Filters
|--------------------------------------------------------------------------
*/

$(document).ready(function() {
    $('#formFilters').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        // Reference the filter button and update it to show a centered loading spinner
        var filterBtn = $('#filter');
        filterBtn.removeClass('btn-label').addClass('d-flex justify-content-center');
        filterBtn.html('<div class="spinner-border text-light" style="width: 1.2rem; height: 1.2rem;" role="status"><span class="sr-only">Loading...</span></div>');
        filterBtn.prop('disabled', true); // Disable the button

        // Serialize the form data
        var formData = new FormData(this);

        console.log(formData);

        $.ajax({
            url: route("applicants.reports.update"),
            method: 'POST',
            data: formData,
            processData: false,  // Required for FormData
            contentType: false,  // Required for FormData
            success: function(response) {
                // Update the dashboard with the new data
                updateDashboard(response.data); // Pass the data to the updateDashboard function

                // Display success notification
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true,
                    toast: true
                });
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
                // Re-enable the button, restore original text, and re-add btn-label class
                filterBtn.prop('disabled', false);
                filterBtn.html('<i class="ri-equalizer-fill label-icon align-middle fs-16 me-2"></i> Filter'); // Original button text
                filterBtn.removeClass('d-flex justify-content-center').addClass('btn-label'); // Restore original class
            }
        });
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
| Total Applicants
|--------------------------------------------------------------------------
*/

// Total Applicants
var totalApplicantsColors = getChartColorsArray("total_applicants");

// Total Applicants Chart
if (totalApplicantsColors) {
    var options = {
        series: [0], // Use the calculated percentage
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
        colors: totalApplicantsColors
    };

    var totalApplicantsChart = new ApexCharts(document.querySelector("#total_applicants"), options);
    totalApplicantsChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Appointed Applicants
|--------------------------------------------------------------------------
*/

// Total Appointed Applicants
var totalAppointedApplicantsColors = getChartColorsArray("total_appointed_applicants");

// Total Appointed Applicants Chart
if (totalAppointedApplicantsColors) {
    var options = {
        series: [0], // Use the calculated percentage
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
        colors: totalAppointedApplicantsColors
    };

    var totalAppointedApplicantsChart = new ApexCharts(document.querySelector("#total_appointed_applicants"), options);
    totalAppointedApplicantsChart.render();
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
        colors: totalApplicantsAppointedColors
    };

    var totalApplicantsAppointedChart = new ApexCharts(document.querySelector("#total_applicants_appointed"), options);
    totalApplicantsAppointedChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Applicants By Month
|--------------------------------------------------------------------------
*/

//  Total Applicants By Month Chart
var applicantsByMonthColors = getChartColorsArray("applicants_by_month");

// Prepare default months from January to December
var defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Prepare the data for the chart
var totalApplicantsData = totalApplicantsByMonth && Object.keys(totalApplicantsByMonth).length > 0
    ? Object.values(totalApplicantsByMonth) // Extract values if not empty
    : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

var totalApplicantsAppointedData = totalApplicantsAppointedByMonth && Object.keys(totalApplicantsAppointedByMonth).length > 0
    ? Object.values(totalApplicantsAppointedByMonth) // Extract values if not empty
    : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

// Get gender-specific data (dashed lines for gender)
var genderSeries = [];
Object.keys(totalApplicantsGenderByMonth).forEach(function(gender) {
    genderSeries.push({
        name: gender,
        data: Object.values(totalApplicantsGenderByMonth[gender])
    });
});

// Get race-specific data (different dashed lines for race)
var raceSeries = [];
Object.keys(totalApplicantsRaceByMonth).forEach(function(race) {
    raceSeries.push({
        name: race,
        data: Object.values(totalApplicantsRaceByMonth[race])
    });
});

// Prepare final series data, including total and appointed (solid lines)
var seriesData = [
    {
        name: 'Total',
        data: totalApplicantsData
    },
    {
        name: 'Appointed',
        data: totalApplicantsAppointedData
    },
    ...genderSeries,
    ...raceSeries
];

// Get the months (x-axis categories)
var months = Object.keys(totalApplicantsByMonth).length > 0 
    ? Object.keys(totalApplicantsByMonth)  // Use the months from data if available
    : defaultMonths; // Use default months if data is empty

// Calculate monthly totals for percentages
var monthlyTotals = totalApplicantsData

// Total Applicants By Month Chart
if (applicantsByMonthColors) {
    var options = {
        chart: {
            height: 380,
            type: 'line',
            zoom: {
                enabled: true
            },
            toolbar: {
                show: true,
            }
        },
        colors: applicantsByMonthColors,
        dataLabels: {
            enabled: false
        },
        stroke: {
            width: [3, 3, 3, 3, 3, 3, 3, 3],
            curve: 'straight',
            dashArray: [0, 0, 8, 8, 5, 5, 5, 5]
        },
        series: seriesData,
        title: {
            text: 'Applicants',
            align: 'left',
            style: {
                fontWeight: 500,
            },
        },
        markers: {
            size: 0,

            hover: {
                sizeOffset: 6
            }
        },
        xaxis: {
            categories: months,
        },
        tooltip: {
            y: {
                formatter: function(val, { seriesIndex, dataPointIndex, w }) {
                    // Skip percentage calculation for "Total" series (index 0)
                    if (seriesIndex === 0) {
                        return val;
                    }
                    // Calculate percentage for other series
                    var totalForMonth = monthlyTotals[dataPointIndex];
                    var percentage = totalForMonth > 0 ? Math.round((val / totalForMonth) * 100) : 0;
                    return val + " (" + percentage + "%)";
                }
            }
        },
        grid: {
            borderColor: '#f1f1f1',
        }
    }

    var applicantsChart = new ApexCharts(document.querySelector("#applicants_by_month"), options);
    applicantsChart.render();
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
    $('#averageDistanceTalentPoolApplicantsValue').text(data.averageDistanceTalentPoolApplicants + ' km');

    // Update average distance applicants appointed
    $('#averageDistanceApplicantsAppointedValue').text(data.averageDistanceApplicantsAppointed + ' km');

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

    // Update radial charts
    updateRadialChart(totalVacanciesFilledChart, data.totalVacanciesFilled, data.totalVacancies);
    updateRadialChart(totalInterviewsCompletedChart, data.totalInterviewsCompleted, data.totalInterviewsScheduled);
    updateRadialChart(totalApplicantsAppointedChart, data.totalApplicantsAppointed, data.totalInterviewsScheduled);
    updateRadialChart(totalApplicantsRegrettedChart, data.totalApplicantsRegretted, data.totalInterviewsScheduled);
    updateRadialChart(totalWhatsAppApplicantsChart, data.totalWhatsAppApplicants, data.talentPoolApplicants);
    updateRadialChart(totalWebsiteApplicantsChart, data.totalWebsiteApplicants, data.talentPoolApplicants);

    // Update the "Talent Pool By Month" chart
    updateLineCharts(talentPoolByMonthChart, data.totalApplicantsByMonth, data.totalApplicantsAppointedByMonth);

    // Update average literacy, numeracy, and situational scores
    updateDonutChart(averageLiteracyScoreChart, data.averageLiteracyScoreTalentPoolApplicants, data.literacyQuestionsCount);
    updateDonutChart(averageNumeracyScoreChart, data.averageNumeracyScoreTalentPoolApplicants, data.numeracyQuestionsCount);
    updateDonutChart(averageSituationalScoreChart, data.averageSituationalScoreTalentPoolApplicants, data.situationalQuestionsCount);

    // Update demographic charts
    updateRadialBarChart(talentPoolApplicantsDemographicChart, data.talentPoolApplicantsDemographic);
    updateRadialBarChart(interviewedApplicantsDemographicChart, data.interviewedApplicantsDemographic);
    updateRadialBarChart(appointedApplicantsDemographicChart, data.appointedApplicantsDemographic);

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

function updateLineCharts(chartInstance, totalApplicantsByMonth, totalApplicantsAppointedByMonth) {
    // Get default months from January to December
    var defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Prepare the data for the Talent Pool By Month Chart
    var totalApplicantsData = totalApplicantsByMonth && Object.keys(totalApplicantsByMonth).length > 0
        ? Object.values(totalApplicantsByMonth) // Extract values if not empty
        : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

    var totalApplicantsAppointedData = totalApplicantsAppointedByMonth && Object.keys(totalApplicantsAppointedByMonth).length > 0
        ? Object.values(totalApplicantsAppointedByMonth) // Extract values if not empty
        : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

    // Get the months (x-axis categories)
    var months = Object.keys(totalApplicantsByMonth).length > 0 
        ? Object.keys(totalApplicantsByMonth)  // Use the months from data if available
        : defaultMonths; // Use default months if data is empty

    // Calculate max value for the y-axis dynamically
    var maxYValue = Math.max(...totalApplicantsData, ...totalApplicantsAppointedData) + 5; // Add buffer to the maximum value

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
            data: totalApplicantsData // Use dynamic data for Talent Pool
        },
        {
            name: "Total Appointed",
            data: totalApplicantsAppointedData // Use dynamic data for Appointed
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
