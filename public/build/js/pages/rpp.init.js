/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job-statistics init js
*/

/*
|--------------------------------------------------------------------------
| Date Filter Default
|--------------------------------------------------------------------------
*/

// Calculate the current date
const currentDate = new Date();

// Calculate the past date: one year ago
const pastDate = new Date(currentDate);
pastDate.setFullYear(currentDate.getFullYear() - 1);

// Adjust the month to the next month
let newMonth = currentDate.getMonth() + 1;
if (newMonth > 11) {
    pastDate.setFullYear(pastDate.getFullYear() + 1); // Move to next year
    newMonth = 0; // Set to January
}
pastDate.setMonth(newMonth);
pastDate.setDate(1); // Set to the first day of the month

// Format the dates as "d M, Y"
const formatDate = (date) => {
    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    return date.toLocaleDateString('en-GB', options);
};

const defaultDateRange = `${formatDate(pastDate)} to ${formatDate(currentDate)}`;

// Initialize Flatpickr with the default date range
flatpickr("#dateFilter", {
    mode: "range",
    dateFormat: "d M, Y",
    defaultDate: [pastDate, currentDate],
    onChange: function(selectedDates, dateStr, instance) {
        // Check if both start and end dates have been selected
        if (selectedDates.length === 2) {
            // Fetch new data when both dates in the range have been selected
            fetchDataAndUpdate(selectedDates);
        }
    }
});

/*
|--------------------------------------------------------------------------
| Fetch Updated Date
|--------------------------------------------------------------------------
*/

function fetchDataAndUpdate(selectedDates) {
    // Get the start and end dates from the selected date range
    const [startDate, endDate] = selectedDates;

    // Format the dates as dd/mm/yyyy
    const formatDate = (date) => {
        const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
        return date.toLocaleDateString('en-GB', options);
    };

    const formattedStartDate = formatDate(startDate);
    const formattedEndDate = formatDate(endDate);

    // Store the original icon HTML
    const originalIconHTML = '<i class="ri-calendar-2-line"></i>';
    const spinnerHTML = '<div class="spinner-border text-light" role="status" style="width:1.5rem; height:1.5rem"><span class="sr-only">Loading...</span></div>';

    // Make an AJAX request to fetch new data based on the selected date range
    $.ajax({
        url: route('rpp.updateData'), // Replace with your actual endpoint
        method: 'GET',
        data: {
            start_date: formattedStartDate,
            end_date: formattedEndDate,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            // Replace the icon with the spinner
            $('#dateFilterIcon').html(spinnerHTML);
        },
        success: function(response) {
            // Update charts with the new data
            updateCharts(response.data);

            // Replace the spinner with the icon
            $('#dateFilterIcon').html(originalIconHTML);
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

            // Replace the spinner with the icon
            $('#dateFilterIcon').html(originalIconHTML);
        }
    });
}

/*
|--------------------------------------------------------------------------
| Update Charts
|--------------------------------------------------------------------------
*/

function updateCharts(data) {
    // Remove timeToHirePreviousYearColumn and absorptionRatePreviousYearColumn
    document.getElementById('timeToHirePreviousYearColumn').remove();
    document.getElementById('absorptionRatePreviousYearColumn').remove();

    // Set timeToHireCurrentYearColumn and absorptionRateCurrentYearColumn to col-md-12
    document.getElementById('timeToHireCurrentYearColumn').className = 'col-md-12';
    document.getElementById('absorptionRateCurrentYearColumn').className = 'col-md-12';

    // Update timeToHireCurrentYearValue with data.totalTimeToHire
    document.getElementById('timeToHireCurrentYearValue').textContent = formatTime(data.averageTimeToHire);

    // Update absorptionRateCurrentYearValue with data.totalAbsorptionRate
    document.getElementById('absorptionRateCurrentYearValue').textContent = data.totalAbsorptionRate;

    // Set timeToHireCurrentYear and absorptionRateCurrentYear to #dateFilter.value
    var dateFilterValue = document.getElementById('dateFilter').value;
    document.getElementById('timeToHireCurrentYear').textContent = dateFilterValue;
    document.getElementById('absorptionRateCurrentYear').textContent = dateFilterValue;

    // Update applicationsSparklineChart
    updateSparklineChart(
        applicationsSparklineChart,
        data.applicationsPerMonth,
        data.percentMovementApplicationsPerMonth,
        "percentMovementApplicationsPerMonthBadge"
    );

    // Update interviewedSparklineChart
    updateSparklineChart(
        interviewedSparklineChart,
        data.interviewedPerMonth,
        data.percentMovementInterviewedPerMonth,
        "percentMovementInterviewedPerMonthBadge"
    );

    // Update hiredSparklineChart
    updateSparklineChart(
        hiredSparklineChart,
        data.appointedPerMonth,
        data.percentMovementAppointedPerMonth,
        "percentMovementHiredPerMonthBadge"
    );

    // Update rejectedSparklineChart
    updateSparklineChart(
        rejectedSparklineChart,
        data.rejectedPerMonth,
        data.percentMovementRejectedPerMonth,
        "percentMovementRejectedPerMonthBadge"
    );

    // Update the applicantsTreemap chart
    applicantsTreemap.updateSeries([{
        data: data.applicantsPerProvince
    }]);

    // Update the applicantsTreemap chart
    applicantRaceChart.updateSeries(data.applicantsByRace.map(raceData => ({
        name: raceData.name,
        data: raceData.data.map(entry => parseInt(entry.split(': ')[1], 10))
    })));
    
    applicantRaceChart.updateOptions({
        xaxis: {
            categories: data.applicantsByRace[0].data.map(entry => entry.split(': ')[0]) // Extracting the months (Jul, Aug, Sep)
        }
    });

    // Update the totalApplicantsChart
    totalApplicantsChart.updateSeries([{
        name: 'Number',
        data: data.totalApplicantsPerMonth.map(entry => parseInt(entry.split(': ')[1], 10))
    }]);

    totalApplicantsChart.updateOptions({
        xaxis: {
            categories: data.totalApplicantsPerMonth.map(entry => entry.split(': ')[0]) // Extracting the months (Jul '23, Aug '23, Sep '23)
        }
    });

    // Update the totalMessagesChart
    totalMessagesChart.updateSeries([{
        name: "Incoming",
        data: data.incomingMessages.map(entry => parseInt(entry.split(': ')[1], 10))
    }, {
        name: "Outgoing",
        data: data.outgoingMessages.map(entry => parseInt(entry.split(': ')[1], 10))
    }]);

    totalMessagesChart.updateOptions({
        xaxis: {
            categories: data.incomingMessages.map(entry => entry.split(': ')[0]) // Extracting the months (Jul '23, Aug '23, Sep '23)
        }
    });

    // Update the counters
    updateCounter("totalIncomingCounter", data.totalIncomingMessages);
    updateCounter("totalOutgoingCounter", data.totalOutgoingMessages);

    // Update the jobsChart
    jobsChart.updateSeries([{
        name: 'Applications',
        data: data.applicationsPerMonth.map(entry => parseInt(entry.split(': ')[1], 10))
    }, {
        name: 'Interviews',
        data: data.interviewedPerMonth.map(entry => parseInt(entry.split(': ')[1], 10))
    }, {
        name: 'Hired',
        data: data.appointedPerMonth.map(entry => parseInt(entry.split(': ')[1], 10))
    }, {
        name: 'Rejected',
        data: data.rejectedPerMonth.map(entry => parseInt(entry.split(': ')[1], 10))
    }]);

    jobsChart.updateOptions({
        xaxis: {
            categories: data.applicationsPerMonth.map(entry => entry.split(': ')[0]) // Extracting the months (Jul '23, Aug '23, Sep '23)
        }
    });

    // Update the applicant data for the vector map
    data.applicantsPerProvince.forEach(province => {
        applicantData[province.x] = province.y;
    });

    // Update the province progress
    updateProvinceProgress(data.applicantsPerProvince);

    // Update the applicantPositionsChart
    applicantPositionsChart.updateSeries(
        data.applicantsByPosition.map(entry => ({
            name: entry.x,
            data: [entry.y]
        }))
    );
}

/*
|--------------------------------------------------------------------------
| Format Time
|--------------------------------------------------------------------------
*/

function formatTime(minutes) {
    const days = Math.floor(minutes / (24 * 60));
    minutes %= (24 * 60);
    const hours = Math.floor(minutes / 60);
    minutes %= 60;
    return `${days}D ${hours}H ${minutes}M`;
}

/*
|--------------------------------------------------------------------------
| Update Sparkline Charts
|--------------------------------------------------------------------------
*/

function updateSparklineChart(chart, data, percentMovement, badgeId) {
    // Get the last 5 records
    const last5Records = data.slice(-5);

    // Split the data into categories (months) and series data (values)
    const last5Categories = last5Records.map(entry => entry.split(': ')[0]);
    const last5Values = last5Records.map(entry => parseInt(entry.split(': ')[1], 10));

    // Determine the color based on percentMovement
    const chartColor = percentMovement >= 0 ? 'rgb(103, 177, 115)' : 'rgb(241, 113, 113)';

    // Update the chart series and color
    chart.updateSeries([{
        name: "Data",
        data: last5Values,
    }]);

    chart.updateOptions({
        xaxis: {
            categories: last5Categories,
        },
        colors: [chartColor]
    });

    // Update the percentage badge
    const absPercentMovement = Math.abs(percentMovement);
    const badgeElement = document.getElementById(badgeId);

    badgeElement.className = `badge bg-light text-${percentMovement >= 0 ? 'success' : 'danger'} mb-0`;
    badgeElement.innerHTML = `
        <i class="ri-arrow-${percentMovement >= 0 ? 'up' : 'down'}-line align-middle"></i> 
        ${absPercentMovement} %
    `;
}

/*
|--------------------------------------------------------------------------
| Update Counter
|--------------------------------------------------------------------------
*/

function updateCounter(counterId, value) {
    const counterElement = document.getElementById(counterId);
    counterElement.setAttribute('data-target', value);
    counterElement.textContent = value;
}

/*
|--------------------------------------------------------------------------
| Update Province Progress
|--------------------------------------------------------------------------
*/

function updateProvinceProgress(data) {
    const provinceProgressElement = document.getElementById('provinceProgress');
    provinceProgressElement.innerHTML = ''; // Clear existing content

    // Sort the provinces by the number of applicants in descending order
    const sortedProvinces = data.sort((a, b) => b.y - a.y);

    // Get the top 3 provinces
    const top3Provinces = sortedProvinces.slice(0, 3);

    // Calculate the total applicants
    const totalApplicants = data.reduce((total, province) => total + province.y, 0);

    // Generate HTML for the top 3 provinces
    top3Provinces.forEach(province => {
        const percentage = ((province.y / totalApplicants) * 100).toFixed(2);

        const provinceHTML = `
            <p class="mb-1">
                ${province.x}
                <span class="float-end">${percentage}%</span>
            </p>
            <div class="progress mt-1 mb-3" style="height: 6px;">
                <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
                    style="width: ${percentage}%" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        `;

        provinceProgressElement.insertAdjacentHTML('beforeend', provinceHTML);
    });
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
| Get Last 5 Months
|--------------------------------------------------------------------------
*/

function getLast5Months() {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const currentDate = new Date();
    const currentMonth = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();
    
    let months = [];
    for (let i = 4; i >= 0; i--) {
        let monthIndex = (currentMonth - i + 12) % 12;
        let year = currentYear - (currentMonth < i ? 1 : 0);
        months.push(`${monthNames[monthIndex]} '${year.toString().slice(-2)}`);
    }    
    return months;
}

/*
|--------------------------------------------------------------------------
| Applications Sparkline Chart
|--------------------------------------------------------------------------
*/

var applicationsSparklineChartColors = getChartColorsArray("applications_sparkline_chart");
if (applicationsSparklineChartColors) {
    var options = {
        series: [{
            name: "Applications",
            data: applicationsPerMonth.slice(-5),
        },],
        chart: {
            width: 140,
            type: "area",
            sparkline: {
                enabled: true,
            },
            toolbar: {
                show: false,
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: "smooth",
            width: 1.5,
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [50, 100, 100, 100],
            },
        },
        colors: applicationsSparklineChartColors,
        xaxis: {
            categories: getLast5Months(),
        }
    };
    var applicationsSparklineChart = new ApexCharts(document.querySelector("#applications_sparkline_chart"), options);
    applicationsSparklineChart.render();
}

/*
|--------------------------------------------------------------------------
| Interviewed Sparkline Chart
|--------------------------------------------------------------------------
*/

var interviewedSparklineChartColors = getChartColorsArray("interviewed_sparkline_chart");
if (interviewedSparklineChartColors) {
    var options = {
        series: [{
            name: "Interviewed",
            data: interviewedPerMonth.slice(-5),
        },],
        chart: {
            width: 140,
            type: "area",
            sparkline: {
                enabled: true,
            },
            toolbar: {
                show: false,
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: "smooth",
            width: 1.5,
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [50, 100, 100, 100],
            },
        },
        colors: interviewedSparklineChartColors,
        xaxis: {
            categories: getLast5Months(),
        }
    };
    var interviewedSparklineChart = new ApexCharts(document.querySelector("#interviewed_sparkline_chart"), options);
    interviewedSparklineChart.render();
}

/*
|--------------------------------------------------------------------------
| Hired Sparkline Chart
|--------------------------------------------------------------------------
*/

var hiredSparklineChartColors = getChartColorsArray("hired_sparkline_chart");
if (hiredSparklineChartColors) {
    var options = {
        series: [{
            name: "Appointed",
            data: appointedPerMonth.slice(-5),
        },],
        chart: {
            width: 140,
            type: "area",
            sparkline: {
                enabled: true,
            },
            toolbar: {
                show: false,
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: "smooth",
            width: 1.5,
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [50, 100, 100, 100],
            },
        },
        colors: hiredSparklineChartColors,
        xaxis: {
            categories: getLast5Months(),
        }
    };
    var hiredSparklineChart = new ApexCharts(document.querySelector("#hired_sparkline_chart"), options);
    hiredSparklineChart.render();
}

/*
|--------------------------------------------------------------------------
| Rejected Sparkline Chart
|--------------------------------------------------------------------------
*/

var rejectedSparklineChartColors = getChartColorsArray("rejected_sparkline_chart");
if (rejectedSparklineChartColors) {
    var options = {
        series: [{
            name: "Rejected",
            data: rejectedPerMonth.slice(-5),
        },],
        chart: {
            width: 140,
            type: "area",
            sparkline: {
                enabled: true,
            },
            toolbar: {
                show: false,
            },
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: "smooth",
            width: 1.5,
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [50, 100, 100, 100],
            },
        },
        colors: rejectedSparklineChartColors,
        xaxis: {
            categories: getLast5Months(),
        }
    };
    var rejectedSparklineChart = new ApexCharts(document.querySelector("#rejected_sparkline_chart"), options);
    rejectedSparklineChart.render();
}

/*
|--------------------------------------------------------------------------
| Generate Data
|--------------------------------------------------------------------------
*/

function getLast12Months() {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const currentDate = new Date();
    const currentMonth = currentDate.getMonth();
    const currentYear = currentDate.getFullYear();
    
    let months = [];
    for (let i = 11; i >= 0; i--) {
        let monthIndex = (currentMonth - i + 12) % 12;
        let year = currentYear - (currentMonth < i ? 1 : 0);
        months.push(`${monthNames[monthIndex]} '${year.toString().slice(-2)}`);
    }    
    return months;
}

function generateRandomData(length, min, max) {
    const data = [];
    for (let i = 0; i < length; i++) {
        data.push(Math.floor(Math.random() * (max - min + 1)) + min);
    }
    return data;
}

/*
|--------------------------------------------------------------------------
| Applicants Province Tree Map
|--------------------------------------------------------------------------
*/

var applicantsTreemapColors = getChartColorsArray("applicants_treemap");
if (applicantsTreemapColors) {
    var options = {
        series: [{
            data: applicantsPerProvince
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
        colors: applicantsTreemapColors,
        plotOptions: {
            treemap: {
                distributed: true,
                enableShades: false
            }
        }
    };

    var applicantsTreemap = new ApexCharts(document.querySelector("#applicants_treemap"), options);
    applicantsTreemap.render();
}

/*
|--------------------------------------------------------------------------
| Applicants Race
|--------------------------------------------------------------------------
*/

var applicantRaceChartColors = getChartColorsArray("applicant_race");

if (applicantRaceChartColors) {
    var options = {
        series: applicantsByRace,
        chart: {
            height: 341,
            type: 'radar',
            dropShadow: {
                enabled: true,
                blur: 1,
                left: 1,
                top: 1
            },
            toolbar: {
                show: false
            }
        },
        stroke: {
            width: 2
        },
        fill: {
            opacity: 0.2
        },
        legend: {
            show: true,
            fontWeight: 500,
            offsetX: 0,
            offsetY: -8,
            markers: {
                width: 8,
                height: 8,
                radius: 6
            },
            itemMargin: {
                horizontal: 10,
                vertical: 0
            }
        },
        markers: {
            size: 0
        },
        colors: applicantRaceChartColors,
        xaxis: {
            categories: getLast12Months()
        }
    };
    var applicantRaceChart = new ApexCharts(document.querySelector("#applicant_race"), options);
    applicantRaceChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Applicants
|--------------------------------------------------------------------------
*/

var totalApplicantsChartColors = getChartColorsArray("total_applicants");

if (totalApplicantsChartColors) {
    var options = {
        series: [{
            name: 'Number',
            data: totalApplicantsPerMonth
        }],
        chart: {
            type: 'area',
            stacked: false,
            height: 500,
            zoom: {
                type: 'x',
                enabled: true,
                autoScaleYaxis: true
            },
            toolbar: {
                autoSelected: 'zoom'
            }
        },
        colors: totalApplicantsChartColors,
        dataLabels: {
            enabled: false
        },
        markers: {
            size: 0
        },
        dataLabels: {
            enabled: false
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0,
                stops: [0, 90, 100]
            }
        },
        xaxis: {
            categories: getLast12Months(),
            title: {
                text: 'Month'
            }
        },
        yaxis: {
            title: {
                text: 'Number'
            },
            min: 0
        },
        tooltip: {
            shared: false,
        }
    };
    var totalApplicantsChart = new ApexCharts(document.querySelector("#total_applicants"), options);
    totalApplicantsChart.render();
}

/*
|--------------------------------------------------------------------------
| Total Messages
|--------------------------------------------------------------------------
*/

var totalMessagesChartColors = getChartColorsArray("total_messages");

if (totalMessagesChartColors) {
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
        colors: totalMessagesChartColors,
        dataLabels: {
            enabled: false
        },
        stroke: {
            width: [3, 3],
            curve: 'straight'
        },
        series: [{
            name: "Incoming",
            data: incomingMessages
        }, {
            name: "Outgoing",
            data: outgoingMessages
        }],
        title: {
            text: 'Messaging Traffic',
            align: 'left',
            style: {
                fontWeight: 500
            }
        },
        grid: {
            row: {
                colors: ['transparent', 'transparent'],
                // takes an array which will be repeated on columns
                opacity: 0.2
            },
            borderColor: '#f1f1f1'
        },
        markers: {
            style: 'inverted',
            size: 6
        },
        xaxis: {
            categories: getLast12Months(),
            title: {
                text: 'Month'
            }
        },
        yaxis: {
            title: {
                text: 'Number'
            },
            min: 0,
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
                }
            }
        }]
    };
    var totalMessagesChart = new ApexCharts(document.querySelector("#total_messages"), options);
    totalMessagesChart.render();
}

/*
|--------------------------------------------------------------------------
| Jobs Summary
|--------------------------------------------------------------------------
*/

// Job Summary
var jobsChartColors = getChartColorsArray("jobs_chart");
if (jobsChartColors) {
    var options = {
        series: [{
            name: 'Applications',
            data: applicationsPerMonth
        }, {
            name: 'Interviews',
            data: interviewedPerMonth
        },
        {
            name: 'Hired',
            data: appointedPerMonth
        },
        {
            name: 'Rejected',
            data: rejectedPerMonth
        }],
        chart: {
            height: 320,
            type: 'area',
            toolbar: 'false',
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2,
        },
        xaxis: {
            categories: getLast12Months(),
        },
        colors: jobsChartColors,
        fill: {
            opacity: 0.06,
            colors: jobsChartColors,
            type: 'solid'
        }
    };
    var jobsChart = new ApexCharts(document.querySelector("#jobs_chart"), options);
    jobsChart.render();
}

/*
|--------------------------------------------------------------------------
| Applicant Map
|--------------------------------------------------------------------------
*/

function adjustSVGViewBox() {
    var svgElement = document.querySelector("#applicants-by-locations svg");
    if (svgElement) {
        svgElement.style.marginLeft = "45px";
    }
}

function loadCharts() {
    // South Africa map with markers
    var vectorMapSAMarkersColors = getChartColorsArray("applicants-by-locations");
    if (vectorMapSAMarkersColors) {
        document.getElementById("applicants-by-locations").innerHTML = "";

        // Define markers array outside of the map configuration
        var markersArray = [
            { name: "Eastern Cape", coords: [-32.9611, 25.6022] },
            { name: "Free State", coords: [-29.0852, 26.1596] },
            { name: "Gauteng", coords: [-26.2041, 28.0473] },
            { name: "KwaZulu-Natal", coords: [-29.8587, 31.0218] },
            { name: "Limpopo", coords: [-23.9045, 29.4685] },
            { name: "Mpumalanga", coords: [-25.4751, 30.9692] },
            { name: "Northern Cape", coords: [-27.7323, 20.7623] },
            { name: "Western Cape", coords: [-33.5249, 18.9241] },
        ];

        const worldemapmarkers = new jsVectorMap({
            map: "za_mill",  // Hypothetical map data for South Africa
            selector: "#applicants-by-locations",
            zoomOnScroll: false,
            zoomButtons: false,
            selectedMarkers: [], // hypothetical markers
            regionStyle: {
                initial: {
                    stroke: "#9599ad",
                    strokeWidth: 0.75,
                    fill: vectorMapSAMarkersColors[0],
                    fillOpacity: 1,
                },
            },
            markersSelectable: true,
            markers: markersArray,
            markerStyle: {
                initial: {
                    fill: vectorMapSAMarkersColors[1],
                },
                selected: {
                    fill: vectorMapSAMarkersColors[2],
                },
            },
            labels: {
                markers: {
                    render: function (marker) {
                        return marker.name;
                    },
                },
            },
            onMarkerClick: function(event, markerIndex) {
                var marker = markersArray[markerIndex]; 
                if (marker && marker.name) {
                    window.location.href = route('applicants.index', {location: marker.name});
                }
            },
            onRegionTooltipShow: function(event, tooltip, code) {
                var regionName = tooltip.text();

                var count = applicantData[regionName] || 0;
                
                tooltip.text(
                    `<p class="fs-6 p-0 m-0">${regionName}</p>` +
                    `<p class="text-xs p-0 m-0">Applicants: ${count}</p>`,
                    true
                );
            },            
        });
    }
    setTimeout(adjustSVGViewBox, 100);
}

window.onresize = function () {
    setTimeout(() => {
        loadCharts();
        adjustSVGViewBox();
    }, 100);
};

loadCharts();

/*
|--------------------------------------------------------------------------
| Applicants Positions
|--------------------------------------------------------------------------
*/

var applicantPositionsChartColors = getChartColorsArray("applicant_positions");

if (applicantPositionsChartColors) {
    var options = {
        series: applicantsByPosition,
        chart: {
            type: 'bar',
            height: 341,
            toolbar: {
                show: false
            }
        },
        dataLabels: {
            enabled: false
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '100%'
            }
        },
        stroke: {
            show: true,
            width: 5,
            colors: ['transparent']
        },
        xaxis: {
            categories: [''],
            axisTicks: {
                show: false,
                borderType: 'solid',
                color: '#78909C',
                height: 6,
                offsetX: 0,
                offsetY: 0
            },
            title: {
                text: 'Applicant Positions',
                offsetX: 0,
                offsetY: -30,
                style: {
                    color: '#78909C',
                    fontSize: '12px',
                    fontWeight: 400
                }
            }
        },
        yaxis: {
            tickAmount: 5,
            min: 0
        },
        fill: {
            opacity: 1
        },
        legend: {
            show: true,
            position: 'bottom',
            horizontalAlign: 'center',
            fontWeight: 500,
            offsetX: 0,
            offsetY: -14,
            itemMargin: {
                horizontal: 8,
                vertical: 0
            },
            markers: {
                width: 10,
                height: 10
            }
        },
        colors: applicantPositionsChartColors
    };
    var applicantPositionsChart = new ApexCharts(document.querySelector("#applicant_positions"), options);
    applicantPositionsChart.render();
}

/*
|--------------------------------------------------------------------------
| Applicant Device
|--------------------------------------------------------------------------
*/

var applicantDeviceChartColors = getChartColorsArray("applicant_device");
if (applicantDeviceChartColors) {
    var options = {
        series: [78.56, 105.02, 42.89],
        labels: ["Desktop", "Mobile", "Tablet"],
        chart: {
            type: "donut",
            height: 219,
        },
        plotOptions: {
            pie: {
                size: 100,
                donut: {
                    size: "76%",
                },
            },
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
            position: 'bottom',
            horizontalAlign: 'center',
            offsetX: 0,
            offsetY: 0,
            markers: {
                width: 20,
                height: 6,
                radius: 2,
            },
            itemMargin: {
                horizontal: 12,
                vertical: 0
            },
        },
        stroke: {
            width: 0
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return value + "k" + " Users";
                }
            },
            tickAmount: 4,
            min: 0
        },
        colors: applicantDeviceChartColors,
    };
    var applicantDeviceChart = new ApexCharts(document.querySelector("#applicant_device"), options);
    applicantDeviceChart.render();
}