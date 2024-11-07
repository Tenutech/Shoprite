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

    // Fetch initial data
    fetchData();

    // Event listener for the filter button
    document.getElementById('filter-button').addEventListener('click', function () {
        fetchData();
    });
});

function fetchData() {
    const position_id = document.getElementById('position_id').value;
    const store_id = document.getElementById('store_id').value;
    const user_id = document.getElementById('user_id').value;
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
                    enabled: false
                },
                toolbar: {
                    show: false
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
                    enabled: false
                },
                toolbar: {
                    show: false
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
        position_id: document.getElementById('position_id').value,
        store_id: document.getElementById('store_id').value,
        user_id: document.getElementById('user_id').value,
        type_id: document.getElementById('type_id').value,
        filled_positions: document.getElementById('filled_positions').value,
        date_range: document.getElementById('date_range').value,
    };
}

function exportVacancyTypes() {
    let filters = getFilters();
    let url = route('vacancies.reports.export-types') + '?' + new URLSearchParams(filters).toString();
    window.open(url, '_blank');
}

function exportVacanciesOverTime() {
    let filters = getFilters();
    let url = route('vacancies.reports.export-time') + '?' + new URLSearchParams(filters).toString();
    window.open(url, '_blank');
}