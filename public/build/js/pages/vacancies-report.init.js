// /*
// Template Name: Orient - Admin & Dashboard Template
// Author: OTB Group
// Website: https://orient.tenutech.com/
// Contact: admin@tenutech.com
// File: job-statistics init js
// */

document.addEventListener("DOMContentLoaded", function () {
    // Initialize charts as null initially
    window.vacancyTypesByMonthChart = null;
    window.vacanciesOverTimeChart = null;

    // Event listener for the filter button
    document.getElementById('filter-button').addEventListener('click', function (event) {
        event.preventDefault();

        selectedFilters.position_id = document.getElementById('positionFilter').value;
        selectedFilters.store_id = document.getElementById('storeFilter').value;
        selectedFilters.user_id = document.getElementById('userFilter').value;

        // Close the off-canvas
        const filterCanvas = bootstrap.Offcanvas.getInstance(document.getElementById('filters-canvas'));
        filterCanvas.hide();

        fetchData();
    });

    // Fetch initial data
    fetchData();
});

let position = document.getElementById('positionFilter');
let positionVal = new Choices(position, {
    searchEnabled: false,
    shouldSort: false
});

let store = document.getElementById('storeFilter');
let storeVal = new Choices(store, {
    searchEnabled: false,
    shouldSort: false
});

let user = document.getElementById('userFilter');
let userVal = new Choices(user, {
    searchEnabled: false,
    shouldSort: false
});

let filledStatus = document.getElementById('filled_positions');
let filledStatusVal = new Choices(filledStatus, {
    searchEnabled: false,
    shouldSort: false
});

// Retain selected filters globally
const selectedFilters = {
    position_id: '',
    store_id: '',
    user_id: '',
};

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