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
    var datePicker = flatpickr("#date", {
        mode: "range",
        dateFormat: "d M Y",
        defaultDate: [formatDate(startDate), formatDate(endDate)], // Set default date range
    });

    /*
    |--------------------------------------------------------------------------
    | Initialize Choices for Select Fields
    |--------------------------------------------------------------------------
    */

    // Cache the original store options
    const storeOptionsCache = [...document.getElementById('store').options].map(option => ({
        value: option.value,
        label: option.textContent,
        divisionId: option.getAttribute('division-id'),
        regionId: option.getAttribute('region-id'),
    }));

    var genderChoice = new Choices('#gender', { searchEnabled: false, shouldSort: true });
    var raceChoice = new Choices('#race', { searchEnabled: false, shouldSort: true });
    var educationChoice = new Choices('#education', { searchEnabled: false, shouldSort: true });
    var experienceChoice = new Choices('#experience', { searchEnabled: false, shouldSort: true });
    var employmentChoice = new Choices('#employment', { searchEnabled: false, shouldSort: true });
    var completedChoice = new Choices('#completed', { searchEnabled: false, shouldSort: true });
    var shortlistedChoice = new Choices('#shortlisted', { searchEnabled: false, shouldSort: true });
    var interviewedChoice = new Choices('#interviewed', { searchEnabled: false, shouldSort: true });
    var appointedChoice = new Choices('#appointed', { searchEnabled: false, shouldSort: true });
    var divisionChoice = new Choices('#division', { searchEnabled: true, shouldSort: true });
    var regionChoice = new Choices('#region', { searchEnabled: true, shouldSort: true });
    var storeChoice = new Choices('#store', { searchEnabled: true, shouldSort: true });

    /*
    |--------------------------------------------------------------------------
    | Clear Filters Button
    |--------------------------------------------------------------------------
    */

    // Call clearFilters on Clear Filters button click
    $('#clearFilters').on('click', function (event) {
        event.preventDefault(); // Prevent default action if within a form
        clearFilters(); // Call the function to clear all fields
    });

    /*
    |--------------------------------------------------------------------------
    | Clear Filters
    |--------------------------------------------------------------------------
    */

    // Define the clearFilters function
    function clearFilters() {
        // Reset each input and select field individually
        datePicker.setDate([formatDate(startDate), formatDate(endDate)]); // Reset date field

        // Reset each Choices instance to default (empty)
        genderChoice.removeActiveItems();
        genderChoice.setChoiceByValue("");
        
        raceChoice.removeActiveItems();
        raceChoice.setChoiceByValue("");
        
        educationChoice.removeActiveItems();
        educationChoice.setChoiceByValue("");
        
        experienceChoice.removeActiveItems();
        experienceChoice.setChoiceByValue("");
        
        employmentChoice.removeActiveItems();
        employmentChoice.setChoiceByValue("");
        
        completedChoice.removeActiveItems();
        completedChoice.setChoiceByValue("");
        
        shortlistedChoice.removeActiveItems();
        shortlistedChoice.setChoiceByValue("");
        
        interviewedChoice.removeActiveItems();
        interviewedChoice.setChoiceByValue("");
        
        appointedChoice.removeActiveItems();
        appointedChoice.setChoiceByValue("");

        divisionChoice.removeActiveItems();
        divisionChoice.setChoiceByValue("");

        regionChoice.removeActiveItems();
        regionChoice.setChoiceByValue("");
        
        storeChoice.removeActiveItems();
        storeChoice.setChoiceByValue("");

        // Clear all number input fields
        $('#minAge').val('');
        $('#maxAge').val('');
        $('#minLiteracy').val('');
        $('#maxLiteracy').val('');
        $('#minNumeracy').val('');
        $('#maxNumeracy').val('');
        $('#minSituational').val('');
        $('#maxSituational').val('');
        $('#minOverall').val('');
        $('#maxOverall').val('');

        // Optionally reset any validation states or styling
        $('.is-invalid').removeClass('is-invalid'); // Remove validation error classes
        $('.invalid-feedback').hide(); // Hide error messages
    }

    /*
    |--------------------------------------------------------------------------
    | Change Choices Options
    |--------------------------------------------------------------------------
    */

    // Set store options based on division
    document.getElementById('division').addEventListener('change', function () {
        // Get the selected division ID
        const selectedDivisionId = this.value;

        // Reset the store options
        storeChoice.clearStore(); // Clears the current Choices options

        if (selectedDivisionId === "") {
            // If no division is selected, reset to all stores
            storeChoice.setChoices(
                [
                    {
                        value: '',
                        label: 'Select store',
                        selected: true, // Make this the selected option
                        disabled: false,
                    },
                    ...storeOptionsCache.map(option => ({
                        value: option.value,
                        label: option.label,
                        selected: false,
                    })),
                ],
                'value',
                'label',
                true
            );
        } else {
            // Filter the cached store options based on the selected division
            const filteredOptions = storeOptionsCache.filter(option => option.divisionId == selectedDivisionId);

            if (filteredOptions.length > 0) {
                // Add the "Select store" option followed by the filtered store options
                storeChoice.setChoices(
                    [
                        {
                            value: '',
                            label: 'Select store',
                            selected: true, // Make this the selected option
                            disabled: false,
                        },
                        ...filteredOptions.map(option => ({
                            value: option.value,
                            label: option.label,
                            selected: false,
                        })),
                    ],
                    'value',
                    'label',
                    true
                );
            } else {
                // Add "No stores available" option if no matches
                storeChoice.setChoices(
                    [
                        {
                            value: '',
                            label: 'No stores available',
                            selected: true,
                            disabled: true, // Make this option disabled
                        },
                    ],
                    'value',
                    'label',
                    true
                );
            }
        }
    });
    
    // Set store options based on region
    document.getElementById('region').addEventListener('change', function () {
        // Get the selected region ID
        const selectedRegionId = this.value;

        // Reset the store options
        storeChoice.clearStore(); // Clears the current Choices options

        if (selectedRegionId === "") {
            // If no region is selected, reset to all stores
            storeChoice.setChoices(
                [
                    {
                        value: '',
                        label: 'Select store',
                        selected: true, // Make this the selected option
                        disabled: false,
                    },
                    ...storeOptionsCache.map(option => ({
                        value: option.value,
                        label: option.label,
                        selected: false,
                    })),
                ],
                'value',
                'label',
                true
            );
        } else {
            // Filter the cached store options based on the selected region
            const filteredOptions = storeOptionsCache.filter(option => option.regionId == selectedRegionId);

            if (filteredOptions.length > 0) {
                // Add the "Select store" option followed by the filtered store options
                storeChoice.setChoices(
                    [
                        {
                            value: '',
                            label: 'Select store',
                            selected: true, // Make this the selected option
                            disabled: false,
                        },
                        ...filteredOptions.map(option => ({
                            value: option.value,
                            label: option.label,
                            selected: false,
                        })),
                    ],
                    'value',
                    'label',
                    true
                );
            } else {
                // Add "No stores available" option if no matches
                storeChoice.setChoices(
                    [
                        {
                            value: '',
                            label: 'No stores available',
                            selected: true,
                            disabled: true, // Make this option disabled
                        },
                    ],
                    'value',
                    'label',
                    true
                );
            }
        }
    });
});

/*
|--------------------------------------------------------------------------
| Export Report
|--------------------------------------------------------------------------
*/

$(document).ready(function() {
    $('#exportReport').on('click', function(event) {
        event.preventDefault(); // Prevent default action

        // Reference the export button and save its initial width
        var exportBtn = $('#exportReport');
        var initialWidth = exportBtn.outerWidth(); // Get the initial width

        // Set the button to fixed width and show the spinner
        exportBtn.css('width', initialWidth + 'px');
        exportBtn.removeClass('btn-label').addClass('d-flex justify-content-center');
        exportBtn.html('<div class="spinner-border text-light" style="width: 1.2rem; height: 1.2rem;" role="status"><span class="sr-only">Loading...</span></div>');
        exportBtn.prop('disabled', true); // Disable the button

        // Get the form data from #formFilters
        var formData = new FormData($('#formFilters')[0]);

        $.ajax({
            url: route("applicants.reports.export"),
            method: 'POST',
            data: formData,
            processData: false,  // Required for FormData
            contentType: false,  // Required for FormData
            xhrFields: {
                responseType: 'blob' // Important to handle binary data from server response
            },
            success: function(response) {
                // Create a link element to download the file
                var downloadUrl = window.URL.createObjectURL(response);
                var link = document.createElement('a');
                link.href = downloadUrl;
                link.download = "Applicants Report.xlsx"; // File name
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Display success notification
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Report exported successfully!',
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

                // Display error notification
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
                exportBtn.prop('disabled', false);
                exportBtn.html('<i class="ri-file-excel-2-fill label-icon align-middle fs-16 me-2"></i> Export Report'); // Original button text
                exportBtn.removeClass('d-flex justify-content-center').addClass('btn-label'); // Restore original class
                exportBtn.css('width', ''); // Remove the fixed width
            }
        });
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

        // Clear previous validation feedback
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();

        // Define min-max field pairs
        const validationPairs = [
            { min: '#minAge', max: '#maxAge', message: 'Max age must be greater than or equal to min age.' },
            { min: '#minLiteracy', max: '#maxLiteracy', message: 'Max literacy score must be greater than or equal to min literacy score.' },
            { min: '#minNumeracy', max: '#maxNumeracy', message: 'Max numeracy score must be greater than or equal to min numeracy score.' },
            { min: '#minSituational', max: '#maxSituational', message: 'Max situational score must be greater than or equal to min situational score.' },
            { min: '#minOverall', max: '#maxOverall', message: 'Max overall score must be greater than or equal to min overall score.' }
        ];

        let isValid = true;

        // Perform validation
        validationPairs.forEach(pair => {
            const minVal = parseFloat($(pair.min).val());
            const maxVal = parseFloat($(pair.max).val());

            // If both fields have values, check if max is less than min
            if (!isNaN(minVal) && !isNaN(maxVal) && maxVal < minVal) {
                isValid = false;
                // Mark fields as invalid
                $(pair.min).addClass('is-invalid');
                $(pair.max).addClass('is-invalid');
                // Display custom error message
                $(pair.max).next('.invalid-feedback').text(pair.message).show();
            }
        });

        if (!isValid) {
            // If validation fails, do not proceed with AJAX
            return;
        }

        // Reference the filter button and update it to show a centered loading spinner
        var filterBtn = $('#filter');
        filterBtn.removeClass('btn-label').addClass('d-flex justify-content-center');
        filterBtn.html('<div class="spinner-border text-light" style="width: 1.2rem; height: 1.2rem;" role="status"><span class="sr-only">Loading...</span></div>');
        filterBtn.prop('disabled', true); // Disable the button

        // Serialize the form data
        var formData = new FormData(this);

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

    var applicantsByMonthChart = new ApexCharts(document.querySelector("#applicants_by_month"), options);
    applicantsByMonthChart.render();
}

/*
|--------------------------------------------------------------------------
| Update Dashboard
|--------------------------------------------------------------------------
*/

// Function to update elements on the dashboard
function updateDashboard(data) {
    // Update total applicants
    $('#totalApplicantsValue').text(data.totalApplicantsFiltered);

    // Update total appointed applicants
    $('#totalAppointedApplicantsValue').text(data.totalAppointedApplicantsFiltered);

    // Update radial charts
    updateRadialChart(totalApplicantsChart, data.totalApplicantsFiltered, data.totalApplicants);
    updateRadialChart(totalAppointedApplicantsChart, data.totalAppointedApplicantsFiltered, data.totalAppointedApplicants);

    // Remove 'd-none' class to show the charts after updating them
    $('#totalApplicantsChart').removeClass('d-none');
    $('#totalApplicantsAppointedChart').removeClass('d-none');

    // Update the "Applicants By Month" chart
    updateLineCharts(applicantsByMonthChart, data.totalApplicantsByMonthFiltered);
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

function updateLineCharts(chartInstance, totalApplicantsByMonth) {
    // Get default months from January to December
    var defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Prepare the data for the Talent Pool By Month Chart
    var totalApplicantsData = totalApplicantsByMonth && Object.keys(totalApplicantsByMonth).length > 0
        ? Object.values(totalApplicantsByMonth) // Extract values if not empty
        : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

    // Get the months (x-axis categories)
    var months = Object.keys(totalApplicantsByMonth).length > 0 
        ? Object.keys(totalApplicantsByMonth)  // Use the months from data if available
        : defaultMonths; // Use default months if data is empty

    // Calculate max value for the y-axis dynamically
    var maxYValue = Math.max(...totalApplicantsData) + 5; // Add buffer to the maximum value

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
            name: "Total",
            data: totalApplicantsData // Use dynamic data for total applicants
        },
    ]);
}