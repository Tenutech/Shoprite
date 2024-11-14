// /*
// Template Name: Orient - Admin & Dashboard Template
// Author: OTB Group
// Website: https://orient.tenutech.com/
// Contact: admin@tenutech.com
// File: job-statistics init js
// */

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

    var positionChoice = new Choices('#position', { searchEnabled: false, shouldSort: true });
    var storeChoice = new Choices('#store', { searchEnabled: true, shouldSort: true });
    var userChoice = new Choices('#user', { searchEnabled: true, shouldSort: true });
    var typeChoice = new Choices('#type', { searchEnabled: false, shouldSort: true });

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
        positionChoice.removeActiveItems();
        positionChoice.setChoiceByValue("");
        
        storeChoice.removeActiveItems();
        storeChoice.setChoiceByValue("");
        
        userChoice.removeActiveItems();
        userChoice.setChoiceByValue("");
        
        typeChoice.removeActiveItems();
        typeChoice.setChoiceByValue("");

        // Clear all number input fields
        $('#openPositions').val('');
        $('#filledPositions').val('');

        // Optionally reset any validation states or styling
        $('.is-invalid').removeClass('is-invalid'); // Remove validation error classes
        $('.invalid-feedback').hide(); // Hide error messages
    }
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
            { min: '#openPositions', max: '#filledPositions', message: 'Open positions must be greater than or equal to filled positions.' }
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
            url: route("vacancies.reports.update"),
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
| Total Vacancies
|--------------------------------------------------------------------------
*/

// Total Vacancies
var totalVacanciesColors = getChartColorsArray("total_vacancies");

// Total Vacancies Chart
if (totalVacanciesColors) {
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
        colors: totalVacanciesColors
    };

    var totalVacanciesChart = new ApexCharts(document.querySelector("#total_vacancies"), options);
    totalVacanciesChart.render();
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
| Total Vacancies By Month
|--------------------------------------------------------------------------
*/

//  Total Vacancies By Month Chart
var vacanciesByMonthColors = getChartColorsArray("vacancies_by_month");

// Prepare default months from January to December
var defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

// Prepare the data for the chart
var totalVacanciesData = totalVacanciesByMonth && Object.keys(totalVacanciesByMonth).length > 0
    ? Object.values(totalVacanciesByMonth) // Extract values if not empty
    : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

var totalVacanciesFilledData = totalVacanciesFilledByMonth && Object.keys(totalVacanciesFilledByMonth).length > 0
    ? Object.values(totalVacanciesFilledByMonth) // Extract values if not empty
    : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

// Get type-specific data (dashed lines for type)
var typeSeries = [];
Object.keys(totalVacanciesTypeByMonth).forEach(function(type) {
    typeSeries.push({
        name: type,
        data: Object.values(totalVacanciesTypeByMonth[type])
    });
});

// Prepare final series data, including total and appointed (solid lines)
var seriesData = [
    {
        name: 'Total',
        data: totalVacanciesData
    },
    {
        name: 'Filled',
        data: totalVacanciesFilledData
    },
    ...typeSeries
];

// Get the months (x-axis categories)
var months = Object.keys(totalVacanciesByMonth).length > 0 
    ? Object.keys(totalVacanciesByMonth)  // Use the months from data if available
    : defaultMonths; // Use default months if data is empty

// Calculate monthly totals for percentages
var monthlyTotals = totalVacanciesData

// Total Vacancies By Month Chart
if (vacanciesByMonthColors) {
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
        colors: vacanciesByMonthColors,
        dataLabels: {
            enabled: false
        },
        stroke: {
            width: [3, 3, 3, 3, 3, 3],
            curve: 'straight',
            dashArray: [0, 0, 8, 8, 8, 8]
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

    var vacanciesByMonthChart = new ApexCharts(document.querySelector("#vacancies_by_month"), options);
    vacanciesByMonthChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Vacancies By Type
|--------------------------------------------------------------------------
*/

// Total Vacancies By Type Chart
var vacanciesByTypeColors = getChartColorsArray("vacancies_by_type");

// Extract categories (keys) and data (values) from the object
var vacncyCategories = Object.keys(totalVacanciesByType);
var vacancyData = Object.values(totalVacanciesByType);

// Total Vacancies By Type Chart
if (vacanciesByTypeColors) {
    var options = {
        series: [{
            name: 'Total',
            data: vacancyData // Use the vacancyData array here
        }],
        chart: {
            type: 'bar',
            height: 350,
            zoom: {
                enabled: true
            },
            toolbar: {
                show: true,
            }
        },
        colors: vacanciesByTypeColors,
        plotOptions: {
            bar: {
                columnWidth: '45%',
                distributed: true,
            }
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            show: false
        },
        xaxis: {
            categories: vacncyCategories, // Use the vacncyCategories array here
            labels: {
                style: {
                    colors: vacanciesByTypeColors,
                    fontSize: '12px'
                }
            }
        }
    };

    var vacanciesByTypeChart = new ApexCharts(document.querySelector("#vacancies_by_type"), options);
    vacanciesByTypeChart.render();
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

    // Update radial charts
    updateRadialChart(totalVacanciesChart, data.totalVacanciesFiltered, data.totalVacancies);
    updateRadialChart(totalVacanciesFilteredChart, data.totalVacanciesFilledFiltered, data.totalVacanciesFiltered);

    // Remove 'd-none' class to show the charts after updating them
    $('#totalVacanciesChart').removeClass('d-none');

    // Update the "Vacancies By Month" chart
    updateLineCharts(vacanciesByMonthChart, data.totalVacanciesByMonthFiltered);
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

function updateLineCharts(chartInstance, totalVacanciesByMonth) {
    // Get default months from January to December
    var defaultMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    // Prepare the data for the Vacancies By Month Chart
    var totalVacanciesData = totalVacanciesByMonth && Object.keys(totalVacanciesByMonth).length > 0
        ? Object.values(totalVacanciesByMonth) // Extract values if not empty
        : new Array(12).fill(0); // If empty, fill the array with 12 zeros (for each month)

    // Get the months (x-axis categories)
    var months = Object.keys(totalVacanciesByMonth).length > 0 
        ? Object.keys(totalVacanciesByMonth)  // Use the months from data if available
        : defaultMonths; // Use default months if data is empty

    // Calculate max value for the y-axis dynamically
    var maxYValue = Math.max(...totalVacanciesData) + 5; // Add buffer to the maximum value

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
            data: totalVacanciesData // Use dynamic data for total vacancies
        },
    ]);
}

function fetchData() {
    const position_id = document.getElementById('positionFilter').value;
    const store_id = document.getElementById('storeFilter').value;
    const user_id = document.getElementById('userFilter').value;
    const type_id = document.getElementById('type_id').value;
    const filled_positions = document.getElementById('filled_positions').value;
    const date_range = document.getElementById('date_range').value;

    let [start_date, end_date] = date_range ? date_range.split(' to ') : [defaultStartDate, defaultEndDate];

    $.ajax({
        url: route('vacancies.reports.updateVacancyReport'),
        type: "GET",
        data: {
            position_id,
            store_id,
            user_id,
            type_id,
            filled_positions,
            start_date,
            end_date,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            updateDropdownOptions(response.filters);
            renderVacancyTypeBarChart(response.chartData.vacancyTypesByMonth);
            renderVacanciesOverTimeLineChart(response.chartData.vacanciesOverTime);
            updateTotals(response.chartData.totals);

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
        error: function (jqXHR, textStatus, errorThrown) {
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

function updateDropdownOptions(filters) {
    setDropdownOptions('position', positionVal, filters.positions, 'Select Position');
    setDropdownOptions('store', storeVal, filters.stores, 'Select Store');
    setDropdownOptions('user', userVal, filters.users, 'Select User');
}

function setDropdownOptions(type, choicesInstance, items, placeholder) {
    choicesInstance.clearChoices();

    // Add new options based on filtered data
    items.forEach(item => {
        if (type === 'position') {
            choicesInstance.setChoices([{ value: item.id, label: item.name, selected: false }], 'value', 'label', false);
        } else if (type === 'store') {
            choicesInstance.setChoices([{ value: item.id, label: item.name, selected: false }], 'value', 'label', false);
        } else if (type === 'user') {
            choicesInstance.setChoices([{ value: item.id, label: item.firstname + ' ' + item.lastname, selected: false }], 'value', 'label', false);
        }
    });
}

// Set the selected filter values when the open filters button is clicked
document.getElementById('open-filters').addEventListener('click', function (event) {
    event.preventDefault();

    positionVal.setChoiceByValue(selectedFilters.position_id.toString());
    storeVal.setChoiceByValue(selectedFilters.store_id.toString());
    userVal.setChoiceByValue(selectedFilters.user_id.toString());
});

// Clear the selected filter values when the clear filters button is clicked
document.getElementById('clearFilters').addEventListener('click', function (event) {

    positionVal.removeActiveItems();
    positionVal.setChoiceByValue('');

    storeVal.removeActiveItems();
    storeVal.setChoiceByValue('');

    userVal.removeActiveItems();
    userVal.setChoiceByValue('');

    filledStatusVal.removeActiveItems();
    filledStatusVal.setChoiceByValue('');

    // Reset date range to default dates
    dateRangePicker.setDate([defaultStartDate, defaultEndDate]);
    window.selectedDateRange = [defaultStartDate.toISOString().split('T')[0], defaultEndDate.toISOString().split('T')[0]];

    selectedFilters.position_id = '';
    selectedFilters.store_id = '';
    selectedFilters.user_id = '';

    document.getElementById('positionFilter').value = '';
    document.getElementById('storeFilter').value = '';
    document.getElementById('userFilter').value = '';
    document.getElementById('type_id').value = '';
    document.getElementById('filled_positions').value = '';

    fetchData();
});

function renderVacancyTypeBarChart(data) {
    const categories = Object.keys(data);
    const seriesData = Object.values(data);

    if (window.vacancyTypesByMonthChart) {
        // Update the chart with new data
        window.vacancyTypesByMonthChart.updateOptions({
            series: [
                { name: 'Full Time', data: seriesData.map(item => item.FullTime) },
                { name: 'Part Time', data: seriesData.map(item => item.PartTime) },
                { name: 'Fixed Term', data: seriesData.map(item => item.FixedTerm) },
                { name: 'Peak Season', data: seriesData.map(item => item.PeakSeason) },
            ],
            xaxis: { categories: categories },
        });
    } else {
        window.vacancyTypesByMonthChart = new ApexCharts(document.querySelector("#vacancyTypesByMonthChart"), {
            chart: {
                type: 'bar',
                height: 300,
                zoom: {
                    enabled: true
                },
                toolbar: {
                    show: true
                }
            },
            series: [
                { name: 'Full Time', data: seriesData.map(item => item.FullTime) },
                { name: 'Part Time', data: seriesData.map(item => item.PartTime) },
                { name: 'Fixed Term', data: seriesData.map(item => item.FixedTerm) },
                { name: 'Peak Season', data: seriesData.map(item => item.PeakSeason) },
            ],
            xaxis: {
                categories: categories,
                title: { text: 'Years' }
            },
            yaxis: {
                title: { text: 'Total Vacancies' },
                min: 0, // Adjust min to allow smaller values
                max: Math.max(
                    ...seriesData.map(item => item.FullTime),
                    ...seriesData.map(item => item.PartTime),
                    ...seriesData.map(item => item.FixedTerm),
                    ...seriesData.map(item => item.PeakSeason)
                ) + 3 // Set the max value based on the highest number in your data
            },
            title: {
                text: 'Total Vacancies by Type'
            }
        });
        window.vacancyTypesByMonthChart.render();
    }
}

function renderVacanciesOverTimeLineChart(data) {
    const categories = Object.keys(data);
    const seriesData = Object.values(data);

    if (window.vacanciesOverTimeChart) {
        // Update the chart with new data
        window.vacanciesOverTimeChart.updateOptions({
            series: [
                { name: 'Total Vacancies', data: seriesData.map(item => item.total) },
                { name: 'Filled Vacancies', data: seriesData.map(item => item.filled) },
            ],
            xaxis: { categories: categories },
        });
    } else {
        window.vacanciesOverTimeChart = new ApexCharts(document.querySelector("#vacanciesOverTimeChart"), {
            chart: {
                type: 'line',
                height: 300,
                zoom: {
                    enabled: true
                },
                toolbar: {
                    show: true
                }
            },
            series: [
                { name: 'Total Vacancies', data: seriesData.map(item => item.total) },
                { name: 'Filled Vacancies', data: seriesData.map(item => item.filled) }
            ],
            xaxis: {
                categories: categories,
                title: { text: 'Years' }
            },
            yaxis: {
                title: { text: 'Vacancies' },
                min: 0,
                max: Math.max(
                    ...seriesData.map(item => item.total),
                    ...seriesData.map(item => item.filled)
                ) + 3
            },
            title: {
                text: 'Total Vacancies vs Filled Vacancies'
            }
        });
        window.vacanciesOverTimeChart.render();
    }
}

function updateTotals(data) {
    let totalFullTime = data.totalFullTime;
    let totalPartTime = data.totalPartTime;
    let totalFixedTerm = data.totalFixedTerm;
    let totalPeakSeason = data.totalPeakSeason;
    let totalVacancies = data.totalVacancies;
    let filledVacancies = data.totalFilledVacancies;

    document.getElementById("totalFullTime").innerHTML = totalFullTime;
    document.getElementById("totalPartTime").innerHTML = totalPartTime;
    document.getElementById("totalFixedTerm").innerHTML = totalFixedTerm;
    document.getElementById("totalPeakSeason").innerHTML = totalPeakSeason;
    document.getElementById("totalVacancies").innerHTML = totalVacancies;
    document.getElementById("filledVacancies").innerHTML = filledVacancies;
}

function getFilters() {
    return {
        position_id: document.getElementById('positionFilter').value,
        store_id: document.getElementById('storeFilter').value,
        user_id: document.getElementById('userFilter').value,
        type_id: document.getElementById('type_id').value,
        filled_positions: document.getElementById('filled_positions').value,
        date_range: document.getElementById('date_range').value,
    };
}

// Export Vacancy Types Report
$(document).ready(function() {
    $('#exportVacancyTypes').on('click', function(event) {
        event.preventDefault(); // Prevent default action

        let filters = getFilters();

        // Reference the export button and save its initial width
        var exportBtn = $('#exportReport');
        var initialWidth = exportBtn.outerWidth(); // Get the initial width

        // Set the button to fixed width and show the spinner
        exportBtn.css('width', initialWidth + 'px');
        exportBtn.removeClass('btn-label').addClass('d-flex justify-content-center');
        exportBtn.html('<div class="spinner-border text-light" style="width: 1.2rem; height: 1.2rem;" role="status"><span class="sr-only">Loading...</span></div>');
        exportBtn.prop('disabled', true); // Disable the button

        // Get the form data from #formFilters
        $.ajax({
            url: route("vacancies.reports.export-types"),
            method: 'GET',
            data: filters,
            processData: false,  // Required for FormData
            contentType: false,  // Required for FormData
            xhrFields: {
                responseType: 'blob' // Important to handle binary data from server response
            },
            success: function(response) {
                console.log(response);
                // Create a link element to download the file
                var downloadUrl = window.URL.createObjectURL(response);
                var link = document.createElement('a');
                link.href = downloadUrl;
                link.download = "Vacancy Types Report.xlsx"; // File name
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

// Export Vacancies Over Time Report
$(document).ready(function() {
    $('#exportVacanciesOverTime').on('click', function(event) {
        event.preventDefault(); // Prevent default action

        let filters = getFilters();

        // Reference the export button and save its initial width
        var exportBtn = $('#exportReport');
        var initialWidth = exportBtn.outerWidth(); // Get the initial width

        // Set the button to fixed width and show the spinner
        exportBtn.css('width', initialWidth + 'px');
        exportBtn.removeClass('btn-label').addClass('d-flex justify-content-center');
        exportBtn.html('<div class="spinner-border text-light" style="width: 1.2rem; height: 1.2rem;" role="status"><span class="sr-only">Loading...</span></div>');
        exportBtn.prop('disabled', true); // Disable the button

        // Get the form data from #formFilters
        $.ajax({
            url: route("vacancies.reports.export-time"),
            method: 'GET',
            data: filters,
            processData: false,  // Required for FormData
            contentType: false,  // Required for FormData
            xhrFields: {
                responseType: 'blob' // Important to handle binary data from server response
            },
            success: function(response) {
                console.log(response);
                // Create a link element to download the file
                var downloadUrl = window.URL.createObjectURL(response);
                var link = document.createElement('a');
                link.href = downloadUrl;
                link.download = "Vacancies Over Time.xlsx"; // File name
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