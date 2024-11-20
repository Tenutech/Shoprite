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

    // Cache the original town options
    const townOptionsCache = [...document.getElementById('town').options].map(option => ({
        value: option.value,
        label: option.textContent,
        provinceId: option.getAttribute('province-id'),
    }));

    // Cache the original store options
    const storeOptionsCache = [...document.getElementById('store').options].map(option => ({
        value: option.value,
        label: option.textContent,
        brandId: option.getAttribute('brand-id'),
        provinceId: option.getAttribute('province-id'),
        townId: option.getAttribute('town-id'),
        divisionId: option.getAttribute('division-id'),
        regionId: option.getAttribute('region-id'),
    }));

    var brandChoice = new Choices('#brand', { searchEnabled: true, shouldSort: true });
    var provinceChoice = new Choices('#province', { searchEnabled: true, shouldSort: true });
    var townChoice = new Choices('#town', { searchEnabled: true, shouldSort: true });
    var divisionChoice = new Choices('#division', { searchEnabled: true, shouldSort: true });
    var regionChoice = new Choices('#region', { searchEnabled: true, shouldSort: true });
    var storeChoice = new Choices('#store', { searchEnabled: true, shouldSort: true });

    // Filtered options cache
    let filteredStoreOptions = [...storeOptionsCache]; // Start with all store options

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
        brandChoice.removeActiveItems();
        brandChoice.setChoiceByValue("");

        provinceChoice.removeActiveItems();
        provinceChoice.setChoiceByValue("");
        
        townChoice.removeActiveItems();
        townChoice.setChoiceByValue("");
        
        divisionChoice.removeActiveItems();
        divisionChoice.setChoiceByValue("");

        regionChoice.removeActiveItems();
        regionChoice.setChoiceByValue("");

        storeChoice.removeActiveItems();
        storeChoice.setChoiceByValue("");
        
        // Optionally reset any validation states or styling
        $('.is-invalid').removeClass('is-invalid'); // Remove validation error classes
        $('.invalid-feedback').hide(); // Hide error messages
    }

    /*
    |--------------------------------------------------------------------------
    | Change Choices Options
    |--------------------------------------------------------------------------
    */

    // Set town options based on province
    document.getElementById('province').addEventListener('change', function () {
        // Get the selected province ID
        const selectedProvinceId = this.value;

        // Reset the town options
        townChoice.clearStore(); // Clears the current Choices options

        if (selectedProvinceId === "") {
            // If no province is selected, reset to all towns
            townChoice.setChoices(
                [
                    {
                        value: '',
                        label: 'Select town',
                        selected: true, // Make this the selected option
                        disabled: false,
                    },
                    ...townOptionsCache.map(option => ({
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
            // Filter the cached town options based on the selected province
            const filteredOptions = townOptionsCache.filter(option => option.provinceId == selectedProvinceId);

            if (filteredOptions.length > 0) {
                // Add the "Select town" option followed by the filtered town options
                townChoice.setChoices(
                    [
                        {
                            value: '',
                            label: 'Select town',
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
                // Add "No towns available" option if no matches
                townChoice.setChoices(
                    [
                        {
                            value: '',
                            label: 'No towns available',
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

    // Update store options based on the current filter selections
    function updateStoreOptions() {
        // Get selected filter values
        const selectedBrandId = document.getElementById('brand').value;
        const selectedProvinceId = document.getElementById('province').value;
        const selectedTownId = document.getElementById('town').value;
        const selectedDivisionId = document.getElementById('division').value;
        const selectedRegionId = document.getElementById('region').value;

        // Filter the store options based on the selected values
        filteredStoreOptions = storeOptionsCache.filter(option => {
            return (
                (!selectedBrandId || option.brandId == selectedBrandId) &&
                (!selectedProvinceId || option.provinceId == selectedProvinceId) &&
                (!selectedTownId || option.townId == selectedTownId) &&
                (!selectedDivisionId || option.divisionId == selectedDivisionId) &&
                (!selectedRegionId || option.regionId == selectedRegionId)
            );
        });

        // Update the store options in the Choices instance
        storeChoice.clearStore(); // Clears the current Choices options
        if (filteredStoreOptions.length > 0) {
            storeChoice.setChoices(
                [
                    {
                        value: '',
                        label: 'Select store',
                        selected: true, // Make this the selected option
                        disabled: false,
                    },
                    ...filteredStoreOptions.map(option => ({
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

    // Attach change events for each filter
    ['brand', 'province', 'town', 'division', 'region'].forEach(filterId => {
        document.getElementById(filterId).addEventListener('change', updateStoreOptions);
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
            url: route("stores.reports.export"),
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
                link.download = "Stores Report.xlsx"; // File name
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

        // Reference the filter button and update it to show a centered loading spinner
        var filterBtn = $('#filter');
        filterBtn.removeClass('btn-label').addClass('d-flex justify-content-center');
        filterBtn.html('<div class="spinner-border text-light" style="width: 1.2rem; height: 1.2rem;" role="status"><span class="sr-only">Loading...</span></div>');
        filterBtn.prop('disabled', true); // Disable the button

        // Serialize the form data
        var formData = new FormData(this);

        $.ajax({
            url: route("stores.reports.update"),
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
| Update Dashboard
|--------------------------------------------------------------------------
*/

// Function to update elements on the dashboard
function updateDashboard(data) {
    // Update total applicants appointed
    $('#totalApplicantsAppointedValue').text(data.totalApplicantsAppointedFiltered);

    // Update total interviews completed
    $('#totalInterviewsCompletedValue').text(data.totalInterviewsCompletedFiltered);

    // Update hire to interview ratio
    $('#hireToInterviewRatioValue').text(data.hireToInterviewRatioDisplay);

    // Update average time to shortlist
    $('#averageTimeToShortlistValue').text(data.averageTimeToShortlistFiltered);

    // Update average time to hire
    $('#averageTimeToHireValue').text(data.averageTimeToHireFiltered);

    // Update average distance applicants appointed
    $('#averageDistanceApplicantsAppointedValue').text(data.averageDistanceApplicantsAppointedFiltered);

    // Update average assessment score applicants appointed
    $('#averageAssessmentScoreApplicantsAppointedValue').text(data.averageAssessmentScoreApplicantsAppointedFiltered);

    // Update radial charts
    updateRadialChart(totalApplicantsAppointedChart, data.totalApplicantsAppointedFiltered, data.totalApplicantsAppointed);
    updateRadialChart(totalInterviewsCompletedChart, data.totalInterviewsCompletedFiltered, data.totalInterviewsCompleted);
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