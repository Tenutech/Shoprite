/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job-statistics init js
*/

/*
|--------------------------------------------------------------------------
| Show Unactioned Shortlist
|--------------------------------------------------------------------------
*/

$(document).ready(function() {
    // Check if the shortlist exists
    if (typeof shortlist !== 'undefined' && shortlist !== null) {
        // Show the modal when shortlist exists
        $('#unActionedShortlistModal').modal('show');
    }
});

/*
|--------------------------------------------------------------------------
| Format Date
|--------------------------------------------------------------------------
*/

// list js
function formatDate(date) {
    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
    ];
    var d = new Date(date),
        month = '' + monthNames[(d.getMonth())],
        day = '' + d.getDate(),
        year = d.getFullYear();
    if (month.length < 2)
        month = '0' + month;
    if (day.length < 2)
        day = '0' + day;
    return [day + " " + month, year].join(', ');
};

/*
|--------------------------------------------------------------------------
| Check Boxes
|--------------------------------------------------------------------------
*/

var checkAll = document.getElementById("checkAll");
if (checkAll) {
  checkAll.onclick = function () {
    var checkboxes = document.querySelectorAll('.form-check-all input[type="checkbox"]');
    var checkedCount = document.querySelectorAll('.form-check-all input[type="checkbox"]:checked').length;
    for (var i = 0; i < checkboxes.length; i++) {
      checkboxes[i].checked = this.checked;
      if (checkboxes[i].checked) {
          checkboxes[i].closest("tr").classList.add("table-active");
      } else {
          checkboxes[i].closest("tr").classList.remove("table-active");
      }
    }

    (checkedCount > 0) ? document.getElementById("remove-actions").style.display = 'none' : document.getElementById("remove-actions").style.display = 'block';
  };
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

var areachartbitcoinColors = getChartColorsArray("applications_sparkline_chart");
if (areachartbitcoinColors) {
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
        colors: areachartbitcoinColors,
        xaxis: {
            categories: getLast5Months(),
        }
    };
    var chart = new ApexCharts(document.querySelector("#applications_sparkline_chart"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Interviewed Sparkline Chart
|--------------------------------------------------------------------------
*/

var areachartbitcoinColors = getChartColorsArray("interviewed_sparkline_chart");
if (areachartbitcoinColors) {
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
        colors: areachartbitcoinColors,
        xaxis: {
            categories: getLast5Months(),
        }
    };
    var chart = new ApexCharts(document.querySelector("#interviewed_sparkline_chart"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Hired Sparkline Chart
|--------------------------------------------------------------------------
*/

var areachartbitcoinColors = getChartColorsArray("hired_sparkline_chart");
if (areachartbitcoinColors) {
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
        colors: areachartbitcoinColors,
        xaxis: {
            categories: getLast5Months(),
        }
    };
    var chart = new ApexCharts(document.querySelector("#hired_sparkline_chart"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Rejected Sparkline Chart
|--------------------------------------------------------------------------
*/

var areachartbitcoinColors = getChartColorsArray("rejected_sparkline_chart");
if (areachartbitcoinColors) {
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
        colors: areachartbitcoinColors,
        xaxis: {
            categories: getLast5Months(),
        }
    };
    var chart = new ApexCharts(document.querySelector("#rejected_sparkline_chart"), options);
    chart.render();
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
| Jobs Summary
|--------------------------------------------------------------------------
*/

// Job Summary
var revenueExpensesChartsColors = getChartColorsArray("jobs_chart");
if (revenueExpensesChartsColors) {
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
        colors: revenueExpensesChartsColors,
        fill: {
            opacity: 0.06,
            colors: revenueExpensesChartsColors,
            type: 'solid'
        }
    };
    var chart = new ApexCharts(document.querySelector("#jobs_chart"), options);
    chart.render();
}