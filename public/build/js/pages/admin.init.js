/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: job-statistics init js
*/

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
| Applicants Province Tree Map
|--------------------------------------------------------------------------
*/

var chartTreemapDistributedColors = getChartColorsArray("applicants_treemap");
if (chartTreemapDistributedColors) {
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
        colors: chartTreemapDistributedColors,
        plotOptions: {
            treemap: {
                distributed: true,
                enableShades: false
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#applicants_treemap"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Applicants Race
|--------------------------------------------------------------------------
*/

var applicantRaceChartsColors = getChartColorsArray("applicant_race");

if (applicantRaceChartsColors) {
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
        colors: applicantRaceChartsColors,
        xaxis: {
            categories: getLast12Months()
        }
    };
    var chart = new ApexCharts(document.querySelector("#applicant_race"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Total Applicants
|--------------------------------------------------------------------------
*/

var linechartZoomColors = getChartColorsArray("total_applicants");

if (linechartZoomColors) {
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
        colors: linechartZoomColors,
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
    var chart = new ApexCharts(document.querySelector("#total_applicants"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Total Messages
|--------------------------------------------------------------------------
*/

var totalCostColors = getChartColorsArray("total_messages");

if (totalCostColors) {
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
        colors: totalCostColors,
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
    var chart = new ApexCharts(document.querySelector("#total_messages"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Applicants Positions
|--------------------------------------------------------------------------
*/

var areachartSalesColors = getChartColorsArray("applicant_positions");

if (areachartSalesColors) {
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
        colors: areachartSalesColors
    };
    var chart = new ApexCharts(document.querySelector("#applicant_positions"), options);
    chart.render();
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
                    strokeWidth: 0.25,
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
| Applicant Device
|--------------------------------------------------------------------------
*/

var dountchartUserDeviceColors = getChartColorsArray("applicant_device");
if (dountchartUserDeviceColors) {
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
        colors: dountchartUserDeviceColors,
    };
    var chart = new ApexCharts(document.querySelector("#applicant_device"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Right Sidebar
|--------------------------------------------------------------------------
*/

var layoutRightSideBtn = document.querySelector('.layout-rightside-btn');
if (layoutRightSideBtn) {
    Array.from(document.querySelectorAll(".layout-rightside-btn")).forEach(function (item) {
        var userProfileSidebar = document.querySelector(".layout-rightside-col");
        item.addEventListener("click", function () {
            if (userProfileSidebar.classList.contains("d-block")) {
                userProfileSidebar.classList.remove("d-block");
                userProfileSidebar.classList.add("d-none");
            } else {
                userProfileSidebar.classList.remove("d-none");
                userProfileSidebar.classList.add("d-block");
            }
        });
    });
    window.addEventListener("resize", function () {
        var userProfileSidebar = document.querySelector(".layout-rightside-col");
        if (userProfileSidebar) {
            Array.from(document.querySelectorAll(".layout-rightside-btn")).forEach(function () {
                if (window.outerWidth < 1699 || window.outerWidth > 3440) {
                    userProfileSidebar.classList.remove("d-block");
                } else if (window.outerWidth > 1699) {
                    userProfileSidebar.classList.add("d-block");
                }
            });
        }

        var htmlAttr = document.documentElement;
        if (htmlAttr.getAttribute("data-layout") == "semibox") {
            userProfileSidebar.classList.remove("d-block");
            userProfileSidebar.classList.add("d-none");
        }
    });
    var overlay = document.querySelector('.overlay');
    if (overlay) {
        document.querySelector(".overlay").addEventListener("click", function () {
            if (document.querySelector(".layout-rightside-col").classList.contains('d-block') == true) {
                document.querySelector(".layout-rightside-col").classList.remove("d-block");
            }
        });
    }
}