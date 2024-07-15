/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job Dashboard init js
*/

/*
|--------------------------------------------------------------------------
| Charts
|--------------------------------------------------------------------------
*/

// get colors array from the string
function getChartColorsArray(chartId) {
    if (document.getElementById(chartId) !== null) {
        var colors = document.getElementById(chartId).getAttribute("data-colors");
        if (colors) {
            colors = JSON.parse(colors);
            return colors.map(function (value) {
                var newValue = value.replace(" ", "");
                if (newValue.indexOf(",") === -1) {
                    var color = getComputedStyle(document.documentElement).getPropertyValue(
                        newValue
                    );
                    if (color) return color;
                    else return newValue;
                } else {
                    var val = value.split(",");
                    if (val.length == 2) {
                        var rgbaColor = getComputedStyle(
                            document.documentElement
                        ).getPropertyValue(val[0]);
                        rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                        return rgbaColor;
                    } else {
                        return newValue;
                    }
                }
            });
        } else {
            console.warn('data-colors atributes not found on', chartId);
        }
    }
}

//  Opportunities Approved Chart
var opportunitiesApprovedElement = document.querySelector("#opportunities_approved");
var opportunitiesApprovedValue = opportunitiesApprovedElement.getAttribute('data-chart');
var opportunitiesApprovedValueInt = parseFloat(opportunitiesApprovedValue);

var chartRadialbarBasicColors = getChartColorsArray("opportunities_approved");
if (chartRadialbarBasicColors) {
    var options = {
        series: [opportunitiesApprovedValueInt],
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
                    }
                }
            }
        },
        colors: chartRadialbarBasicColors
    };

    var chart = new ApexCharts(document.querySelector("#opportunities_approved"), options);
    chart.render();
}

//  Connections Approved Chart
var connectionsApprovedElement = document.querySelector("#connections_approved");
var connectionsApprovedValue = connectionsApprovedElement.getAttribute('data-chart');
var connectionsApprovedValueInt = parseFloat(connectionsApprovedValue);

var chartRadialbarBasicColors = getChartColorsArray("connections_approved");
if (chartRadialbarBasicColors) {
    var options = {
        series: [connectionsApprovedValueInt],
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
                    }
                }
            },
        },
        colors: chartRadialbarBasicColors
    };

    var chart = new ApexCharts(document.querySelector("#connections_approved"), options);
    chart.render();
}

//  Dashed line chart
var linechartDashedColors = getChartColorsArray("line_chart_dashed");
if (linechartDashedColors) {
    var options = {
        chart: {
            height: 345,
            type: 'line',
            zoom: {
                enabled: false
            },
            toolbar: {
                show: false,
            }
        },
        colors: linechartDashedColors,
        dataLabels: {
            enabled: false
        },
        stroke: {
            width: [3, 4, 3],
            curve: 'straight',
            dashArray: [0, 8, 5]
        },
        series: [{
            name: 'New Connections',
            data: newConnectionsCount
        },
        {
            name: "Approved",
            data: approvedConnectionsCount
        },
        {
            name: "Opportunities Posted",
            data: opportunitiesPostedCount
        }
        ],
        markers: {
            size: 0,

            hover: {
                sizeOffset: 6
            }
        },
        xaxis: {
            categories: months,
        },
        grid: {
            borderColor: '#f1f1f1',
        }
    }

    var chart = new ApexCharts(
        document.querySelector("#line_chart_dashed"),
        options
    );

    chart.render();
}

function adjustSVGViewBox() {
    var svgElement = document.querySelector("#opportunities-by-locations svg");
    if (svgElement) {
        svgElement.style.marginLeft = "45px";
    }
}

function loadCharts() {
    // South Africa map with markers
    var vectorMapSAMarkersColors = getChartColorsArray("opportunities-by-locations");
    if (vectorMapSAMarkersColors) {
        document.getElementById("opportunities-by-locations").innerHTML = "";

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
            selector: "#opportunities-by-locations",
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
                    window.location.href = route('marketplace.index', {location: marker.name});
                }
            },
            onRegionTooltipShow: function(event, tooltip, code) {
                var regionName = tooltip.text();

                var count = opportunityData[regionName] || 0;
                
                tooltip.text(
                    `<p class="fs-6 p-0 m-0">${regionName}</p>` +
                    `<p class="text-xs p-0 m-0">Opportunities: ${count}</p>`,
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
| Saved Opportunities
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', (event) => {
    var savedOpportunitiesList = document.querySelectorAll('#savedOpportunitiesTable tbody tr');
    var itemsPerPage = 6;
    var currentPage = 1;

    var prevButton = document.getElementById('page-prev');
    var nextButton = document.getElementById('page-next');

    function updateShowingNumbers() {
        var showingFrom = document.getElementById('showingFrom');
        var showingTo = document.getElementById('showingTo');
        var showingTotal = document.getElementById('showingTotal');
    
        var startIdx = (currentPage - 1) * itemsPerPage;
        var endIdx = Math.min(startIdx + itemsPerPage, savedOpportunitiesList.length);
    
        showingFrom.textContent = startIdx + 1;
        showingTo.textContent = endIdx;
        showingTotal.textContent = savedOpportunitiesList.length;
    }

    function loadSavedOpportunitiesData() {
        savedOpportunitiesList.forEach(row => row.style.display = 'none');
        var startIdx = (currentPage - 1) * itemsPerPage;
        var endIdx = startIdx + itemsPerPage;
        for (var i = startIdx; i < endIdx && i < savedOpportunitiesList.length; i++) {
            savedOpportunitiesList[i].style.display = '';
        }
        selectedPage();
        updateShowingNumbers();
    }

    function selectedPage() {
        var pagenumLink = document.getElementById('page-num').getElementsByClassName('clickPageNumber');
        for (var i = 0; i < pagenumLink.length; i++) {
            if (i == currentPage - 1) {
                pagenumLink[i].parentNode.classList.add("active");
            } else {
                pagenumLink[i].parentNode.classList.remove("active");
            }
        }
    }

    function paginationEvents() {
        function numPages() {
            return Math.ceil(savedOpportunitiesList.length / itemsPerPage);
        }

        function clickPage() {
            document.addEventListener('click', function (e) {
                if (e.target.nodeName == "A" && e.target.classList.contains("clickPageNumber")) {
                    currentPage = parseInt(e.target.textContent);
                    loadSavedOpportunitiesData();
                }
            });
        }

        function pageNumbers() {
            var pageNumber = document.getElementById('page-num');
            pageNumber.innerHTML = "";
            for (var i = 1; i < numPages() + 1; i++) {
                pageNumber.innerHTML += "<div class='page-item'><a class='page-link clickPageNumber' href='javascript:void(0);'>" + i + "</a></div>";
            }
        }

        prevButton.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadSavedOpportunitiesData();
            }
        });

        nextButton.addEventListener('click', function () {
            if (currentPage < numPages()) {
                currentPage++;
                loadSavedOpportunitiesData();
            }
        });

        pageNumbers();
        clickPage();
        selectedPage();
    }

    loadSavedOpportunitiesData();
    paginationEvents();
});

// candidate-list

Array.from(document.querySelectorAll("#candidate-list li")).forEach(function (item) {
    item.querySelector("a").addEventListener("click", function () {
        var candidateName = item.querySelector(".candidate-name").innerHTML;
        var candidatePosition = item.querySelector(".candidate-position").innerHTML;
        var candidateEmail = item.querySelector(".candidate-email").innerHTML;
        var candidateOverview = item.querySelector(".candidate-overview").innerHTML;
        var candidateMessage = item.querySelector(".candidate-message").innerHTML;
        var candidateImg = item.querySelector(".candidate-img").src

        document.getElementById("candidate-name").innerHTML = candidateName;
        document.getElementById("candidate-position").innerHTML = candidatePosition;
        document.getElementById("candidate-email-btn").href = 'mailto:' + candidateEmail;
        document.getElementById("candidate-overview-btn").href = route('opportunity-overview.index', {id: candidateOverview});
        document.getElementById("candidate-message-btn").href = route('messages.index', {id: candidateMessage});
        document.getElementById("candidate-img").src = candidateImg;

        // Update the title attribute of the email button
        var emailBtn = document.getElementById("candidate-email-btn");
        emailBtn.setAttribute('aria-label', candidateEmail);
        emailBtn.setAttribute('data-bs-original-title', candidateEmail);
    })
});


window.addEventListener("load", () => {
    var searchInput = document.getElementById("searchList"), // search box
        candidateList = document.querySelectorAll("#candidate-list li"); // all list items

    searchInput.onkeyup = () => {
        let search = searchInput.value.toLowerCase();

        for (let i of candidateList) {
            let item = i.querySelector(".candidate-name").innerHTML.toLowerCase();
            if (item.indexOf(search) == -1) { i.classList.add("d-none"); }
            else { i.classList.remove("d-none"); }
        }
    };
}); 