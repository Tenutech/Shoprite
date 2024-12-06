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
var totalVacanciesFilledColors = getChartColorsArray("total_vacancies_filled");

// Calculate percentage of filled vacancies
var totalVacancies = totalVacancies || 0;
var totalVacanciesFilled = totalVacanciesFilled || 0;
var percentageFilled = 0;

// Check for divide by zero and calculate percentage
if (totalVacancies > 0) {
    percentageFilled = Math.round((totalVacanciesFilled / totalVacancies) * 100);
}

// Total Vacancies Filled Chart
if (totalVacanciesFilledColors) {
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

// Calculate percentage of completed interviews
var totalInterviewsScheduled = totalInterviewsScheduled || 0;
var totalInterviewsCompleted = totalInterviewsCompleted || 0;
var percentageCompleted = 0;

// Check for divide by zero and calculate percentage
if (totalInterviewsScheduled > 0) {
    percentageCompleted = Math.round((totalInterviewsCompleted / totalInterviewsScheduled) * 100);
}

// Total Interviews Completed Chart
if (totalInterviewsCompletedColors) {
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

// Calculate percentage of appointed applicants
var totalApplicantsAppointed = totalApplicantsAppointed || 0;
var percentageAppointed = 0;

// Check for divide by zero and calculate percentage for appointed applicants
if (totalInterviewsScheduled > 0) {
    percentageAppointed = Math.round((totalApplicantsAppointed / totalInterviewsScheduled) * 100);
}

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
| Total Applicants Regretted
|--------------------------------------------------------------------------
*/

// Total Applicants Regretted
var totalApplicantsRegrettedColors = getChartColorsArray("total_applicants_regretted");

// Calculate percentage of regretted applicants
var totalApplicantsRegretted = totalApplicantsRegretted || 0;
var percentageRegretted = 0;

// Check for divide by zero and calculate percentage for regretted applicants
if (totalInterviewsScheduled > 0) {
    percentageRegretted = Math.round((totalApplicantsRegretted / totalInterviewsScheduled) * 100);
}

// Total Applicants Regretted Chart
if (totalApplicantsRegrettedColors) {
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

// Prepare the data for the chart
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
| Average Literacy Score
|--------------------------------------------------------------------------
*/

// Average Literacy Score
var averageLiteracyScoreColors = getChartColorsArray("literacy_chart");

// Calculate the number of correct answers (rounded value)
var averageLiteracyScore = Math.round(averageLiteracyScoreTalentPoolApplicants);

// Generate the series and labels dynamically based on the number of questions and average score
var literacySeries = [];
var literacyLabels = [];

// Populate the series and labels (e.g., from 1 to literacyQuestionsCount)
for (var i = 1; i <= averageLiteracyScore; i++) {
    literacySeries.push(i);
    literacyLabels.push(i.toString()); // Convert the number to string for labels
}

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
            text: averageLiteracyScore + '/' + literacyQuestionsCount, // Display the score out of total
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

// Calculate the number of correct answers (rounded value)
var averageNumeracyScore = Math.round(averageNumeracyScoreTalentPoolApplicants);

// Generate the series and labels dynamically based on the number of questions and average score
var numeracySeries = [];
var numeracyLabels = [];

// Populate the series and labels (e.g., from 1 to numeracyQuestionsCount)
for (var i = 1; i <= averageNumeracyScore; i++) {
    numeracySeries.push(i);
    numeracyLabels.push(i.toString()); // Convert the number to string for labels
}

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
            text: averageNumeracyScore + '/' + numeracyQuestionsCount, // Display the score out of total
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

// Calculate the number of correct answers (rounded value)
var averageSituationalScore = Math.round(averageSituationalScoreTalentPoolApplicants);

// Generate the series and labels dynamically based on the number of questions and average score
var situationalSeries = [];
var situationalLabels = [];

// Populate the series and labels (e.g., from 1 to situationalQuestionsCount)
for (var i = 1; i <= averageSituationalScore; i++) {
    situationalSeries.push(i);
    situationalLabels.push(i.toString()); // Convert the number to string for labels
}

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
            text: averageSituationalScore + '/' + situationalQuestionsCount, // Display the score out of total
            floating: true,
            offsetY: 125,
            align: 'center',
            style: {
                fontSize: '20px',
                fontWeight: 'bold'
            }
        }
    };

    var averageSituationalScoreChart = new ApexCharts(document.querySelector("#situational_chart"), options);
    averageSituationalScoreChart.render();
}

/*
|--------------------------------------------------------------------------
| Total WhatsApp Applicants
|--------------------------------------------------------------------------
*/

// Total WhatsApp Applicants
var totalWhatsAppApplicantsColors = getChartColorsArray("total_whatsapp_applicants");

// Calculate percentage of WhatsApp applicants
var totalApplicants = talentPoolApplicants || 0;
var totalWhatsAppApplicants = totalWhatsAppApplicants || 0;
var percentageWhatsApp = 0;

// Check for divide by zero and calculate percentage
if (totalApplicants > 0) {
    percentageWhatsApp = Math.round((totalWhatsAppApplicants / totalApplicants) * 100);
}

// Total WhatsApp Applicants Chart
if (totalWhatsAppApplicantsColors) {
    var options = {
        series: [percentageWhatsApp], // Use the calculated percentage
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

// Calculate percentage of Website applicants
var totalApplicants = talentPoolApplicants || 0;
var totalWebsiteApplicants = totalWebsiteApplicants || 0;
var percentageWebsite = 0;

// Check for divide by zero and calculate percentage
if (totalApplicants > 0) {
    percentageWebsite = Math.round((totalWebsiteApplicants / totalApplicants) * 100);
}

// Total Website Applicants Chart
if (totalWebsiteApplicantsColors) {
    var options = {
        series: [percentageWebsite], // Use the calculated percentage
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

// Calculate percentage of stores using solution
var totalStores = totalStores || 0;
var totalStoresUsingSolution = totalStoresUsingSolution || 0;
var percentageStoresUsingSolution = 0;

// Check for divide by zero and calculate percentage
if (totalStores > 0) {
    percentageStoresUsingSolution = Math.round((totalStoresUsingSolution / totalStores) * 100);
}

// Total Website Applicants Chart
if (totalStoresUsingSolutionColors) {
    var options = {
        series: [percentageStoresUsingSolution], // Use the calculated percentage
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

// Calculate percentage of re-employed applicants
var totalAppointedApplicants = totalAppointedApplicants || 0;
var totalReEmployedApplicants = totalReEmployedApplicants || 0;
var percentageReEmployedApplicants = 0;

// Check for divide by zero and calculate percentage
if (totalAppointedApplicants > 0) {
    percentageReEmployedApplicants = Math.round((totalReEmployedApplicants / totalAppointedApplicants) * 100);
}

// Total Website Applicants Chart
if (totalReEmployedApplicantsColors) {
    var options = {
        series: [percentageReEmployedApplicants], // Use the calculated percentage
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
var talentPoolApplicantsDemographicLabels = [];

talentPoolApplicantsDemographic.forEach(function (item) {
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

// Extract percentages and labels dynamically from interviewedApplicantsDemographic
var interviewedApplicantsDemographicSeries = [];
var interviewedApplicantsDemographicLabels = [];

interviewedApplicantsDemographic.forEach(function (item) {
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

// Extract percentages and labels dynamically from appointedApplicantsDemographic
var appointedApplicantsDemographicSeries = [];
var appointedApplicantsDemographicLabels = [];

appointedApplicantsDemographic.forEach(function (item) {
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
| Talent Pool Applicants Gender
|--------------------------------------------------------------------------
*/

// Talent Pool Applicants Gender
var talentPoolApplicantsGenderColors = getChartColorsArray("talent_pool_applicants_gender");

// Extract percentages and labels dynamically from talentPoolApplicantsGender
var talentPoolApplicantsGenderSeries = [];
var talentPoolApplicantsGenderLabels = [];

talentPoolApplicantsGender.forEach(function (item) {
    talentPoolApplicantsGenderSeries.push(item.percentage); // Extract the percentage value
    talentPoolApplicantsGenderLabels.push(item.name);       // Extract the race name
});

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
var interviewedApplicantsGenderLabels = [];

interviewedApplicantsGender.forEach(function (item) {
    interviewedApplicantsGenderSeries.push(item.percentage); // Extract the percentage value
    interviewedApplicantsGenderLabels.push(item.name);       // Extract the race name
});

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
var appointedApplicantsGenderLabels = [];

appointedApplicantsGender.forEach(function (item) {
    appointedApplicantsGenderSeries.push(item.percentage); // Extract the percentage value
    appointedApplicantsGenderLabels.push(item.name);       // Extract the race name
});

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

Object.keys(talentPoolApplicantsProvince).forEach(function (key) {
    talentPoolApplicantsProvinceSeries.push({
        x: key, // Province name
        y: talentPoolApplicantsProvince[key] // Applicant count
    });
});

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

    // Update radial charts
    updateRadialChart(totalVacanciesFilledChart, data.totalVacanciesFilled, data.totalVacancies);
    updateRadialChart(totalInterviewsCompletedChart, data.totalInterviewsCompleted, data.totalInterviewsScheduled);
    updateRadialChart(totalApplicantsAppointedChart, data.totalApplicantsAppointed, data.totalInterviewsScheduled);
    updateRadialChart(totalApplicantsRegrettedChart, data.totalApplicantsRegretted, data.totalInterviewsScheduled);
    updateRadialChart(totalWhatsAppApplicantsChart, data.totalWhatsAppApplicants, data.talentPoolApplicants);
    updateRadialChart(totalWebsiteApplicantsChart, data.totalWebsiteApplicants, data.talentPoolApplicants);

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
