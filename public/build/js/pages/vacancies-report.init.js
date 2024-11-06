// /*
// Template Name: Orient - Admin & Dashboard Template
// Author: OTB Group
// Website: https://orient.tenutech.com/
// Contact: admin@tenutech.com
// File: job-statistics init js
// */


document.addEventListener("DOMContentLoaded", function () {
    const monthsOfYear = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

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
    const date_range = document.getElementById('date_range').value;

    // Extract start and end dates from the Flatpickr date range
    let [start_date, end_date] = date_range ? date_range.split(' to ') : [defaultStartDate, defaultEndDate];

    $.ajax({
        url: route('vacancies.reports.updateVacancyReport'),
        type: "GET",
        data: {
            position_id,
            store_id,
            user_id,
            type_id,
            start_date,
            end_date,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log(response);
            const chartData = fillMissingMonths(response.chartData);
            // Update the dashboard with the new data
            renderOrUpdateCharts(chartData); // Pass the data to the updateDashboard function
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

// Ensure all months are present in the data
function fillMissingMonths(data) {
    const monthsOfYear = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    // Fill missing months for vacancy types by month
    const vacancyTypesByMonth = monthsOfYear.map(month => ({
        month,
        FullTime: data.vacancyTypesByMonth[month]?.FullTime || 0,
        PartTime: data.vacancyTypesByMonth[month]?.PartTime || 0,
        FixedTerm: data.vacancyTypesByMonth[month]?.FixedTerm || 0,
        PeakSeason: data.vacancyTypesByMonth[month]?.PeakSeason || 0
    }));

    // Fill missing months for total and filled vacancies
    const totalVacancies = monthsOfYear.map(month => data.vacanciesOverTime.total[month] || 0);
    const filledVacancies = monthsOfYear.map(month => data.vacanciesOverTime.filled[month] || 0);

    return {
        vacancyTypesByMonth,
        monthsOfYear,
        totalVacancies,
        filledVacancies
    };
}

function updateTotals(data) {
    console.log(data, data.totalPeakSeason, data.totalPartTime);
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

function renderOrUpdateCharts(data) {
    // Vacancy Types by Month Bar Chart
    const vacancyTypesByMonthData = data.vacancyTypesByMonth;

    if (window.vacancyTypesByMonthChart) {
        // Update the chart with new data
        window.vacancyTypesByMonthChart.updateOptions({
            series: [
                { name: 'Full Time', data: vacancyTypesByMonthData.map(item => item.FullTime) },
                { name: 'Part Time', data: vacancyTypesByMonthData.map(item => item.PartTime) },
                { name: 'Fixed Term', data: vacancyTypesByMonthData.map(item => item.FixedTerm) },
                { name: 'Peak Season', data: vacancyTypesByMonthData.map(item => item.PeakSeason) },
            ],
            xaxis: { categories: data.monthsOfYear },
        });
    } else {
        // Create the chart if it doesn't exist
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
                { name: 'Full Time', data: vacancyTypesByMonthData.map(item => item.FullTime) },
                { name: 'Part Time', data: vacancyTypesByMonthData.map(item => item.PartTime) },
                { name: 'Fixed Term', data: vacancyTypesByMonthData.map(item => item.FixedTerm) },
                { name: 'Peak Season', data: vacancyTypesByMonthData.map(item => item.PeakSeason) },
            ],
            yaxis: {
                title: {
                    text: 'Total Vacancies'
                },
                min: 0, // Adjust min to allow smaller values
                max: Math.max(
                    ...vacancyTypesByMonthData.map(item => item.FullTime),
                    ...vacancyTypesByMonthData.map(item => item.PartTime),
                    ...vacancyTypesByMonthData.map(item => item.FixedTerm),
                    ...vacancyTypesByMonthData.map(item => item.PeakSeason)
                ) + 3 // Set the max value based on the highest number in your data
            },
            xaxis: { categories: data.monthsOfYear },
            title: { text: 'Vacancy Types by Month' }
        });
        window.vacancyTypesByMonthChart.render();
    }

    // Vacancies Over Time Line Chart
    if (window.vacanciesOverTimeChart) {
        // Update the chart with new data
        window.vacanciesOverTimeChart.updateOptions({
            series: [
                { name: 'Total Vacancies', data: data.totalVacancies },
                { name: 'Filled Vacancies', data: data.filledVacancies },
            ],
            xaxis: { categories: data.monthsOfYear },
        });
    } else {
        // Create the chart if it doesn't exist
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
            dataLabels: {
                enabled: false,
            },
            stroke: {
                width: [3, 3],
                curve: 'straight'
            },
            series: [
                { name: 'Total Vacancies', data: data.totalVacancies },
                { name: 'Filled Vacancies', data: data.filledVacancies },
            ],
            yaxis: {
                title: {
                    text: 'Total Vacancies'
                },
                min: 0, // Adjust min to allow smaller values
                max: Math.max(...data.totalVacancies, ...data.filledVacancies) + 3 // Set the max value based on the highest number in your data
            },
            xaxis: { categories: data.monthsOfYear },
            title: { text: 'Total Vacancies vs Filled Vacancies' }
        });
        window.vacanciesOverTimeChart.render();
    }
}

function getFilters() {
    return {
        position_id: document.getElementById('position_id').value,
        store_id: document.getElementById('store_id').value,
        user_id: document.getElementById('user_id').value,
        type_id: document.getElementById('type_id').value,
        date_range: document.getElementById('date_range').value,
    };
}

function exportVacancyTypes() {
    let filters = getFilters();
    let url = `{{ route('vacancies.reports.export-types') }}?` + new URLSearchParams(filters).toString();
    window.open(url, '_blank');
}

function exportVacanciesOverTime() {
    let filters = getFilters();
    let url = `{{ route('vacancies.reports.export-time') }}?` + new URLSearchParams(filters).toString();
    window.open(url, '_blank');
}