/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: job-statistics init js
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

var perPage = 8;
var editlist = false;

//Table
var options = {
    valueNames: [
        "id",
        "type",
        "open",
        "filled",
        "applicants",
        "location",
        "tags",
        "date",
        "status",
    ],
    page: perPage,
    pagination: true,
    plugins: [
        ListPagination({
            left: 2,
            right: 2
        })
    ]
};

// Init list
var vacanciesList = new List("vacanciesList", options).on("updated", function (list) {
    list.matchingItems.length == 0 ?
        (document.getElementsByClassName("noresult")[0].style.display = "block") :
        (document.getElementsByClassName("noresult")[0].style.display = "none");
    var isFirst = list.i == 1;
    var isLast = list.i > list.matchingItems.length - list.page;

    // make the Prev and Nex buttons disabled on first and last pages accordingly
    (document.querySelector(".pagination-prev.disabled")) ? document.querySelector(".pagination-prev.disabled").classList.remove("disabled"): '';
    (document.querySelector(".pagination-next.disabled")) ? document.querySelector(".pagination-next.disabled").classList.remove("disabled"): '';
    if (isFirst) {
        document.querySelector(".pagination-prev").classList.add("disabled");
    }
    if (isLast) {
        document.querySelector(".pagination-next").classList.add("disabled");
    }
    if (list.matchingItems.length <= perPage) {
        document.querySelector(".pagination-wrap").style.display = "none";
    } else {
        document.querySelector(".pagination-wrap").style.display = "flex";
    }

    if (list.matchingItems.length > 0) {
        document.getElementsByClassName("noresult")[0].style.display = "none";
    } else {
        document.getElementsByClassName("noresult")[0].style.display = "block";
    }
});

ischeckboxcheck();

document.querySelector("#vacanciesList").addEventListener("click", function () {
    ischeckboxcheck();
});

var table = document.getElementById("vacanciesTable");
// save all tr
var tr = table.getElementsByTagName("tr");
var trlist = table.querySelectorAll(".list tr");

var count = 11;

function ischeckboxcheck() {
    Array.from(document.getElementsByName("chk_child")).forEach(function (x) {
        x.addEventListener("change", function (e) {
            if (x.checked == true) {
                e.target.closest("tr").classList.add("table-active");
            } else {
                e.target.closest("tr").classList.remove("table-active");
            }
  
            var checkedCount = document.querySelectorAll('[name="chk_child"]:checked').length;
            if (e.target.closest("tr").classList.contains("table-active")) {
                (checkedCount > 0) ? document.getElementById("remove-actions").style.display = 'block': document.getElementById("remove-actions").style.display = 'none';
            } else {
                (checkedCount > 0) ? document.getElementById("remove-actions").style.display = 'block': document.getElementById("remove-actions").style.display = 'none';
            }
        });
    });
}

function refreshCallbacks() {
    if(removeBtns){
        Array.from(removeBtns).forEach(function (btn) {
            btn.addEventListener("click", function (e) {
                e.target.closest("tr").children[1].innerText;
                itemId = e.target.closest("tr").children[1].innerText;
                var itemValues = vacanciesList.get({
                    id: itemId,
                });
    
                Array.from(itemValues).forEach(function (x) {
                    deleteid = new DOMParser().parseFromString(x._values.id, "text/html");
    
                    var isElem = deleteid.body.firstElementChild;
                    var isdeleteid = deleteid.body.firstElementChild.innerHTML;
    
                    if (isdeleteid == itemId) {
                        document.getElementById("delete-record").addEventListener("click", function () {
                            vacanciesList.remove("id", isElem.outerHTML);
                            document.getElementById("deleteRecord-close").click();
                        });
                    }
                });
            });
        });
    }
}

function deleteMultiple(){
    ids_array = [];
    var items = document.getElementsByName('chk_child');
    for (i = 0; i < items.length; i++) {
        if (items[i].checked == true) {
            var trNode = items[i].parentNode.parentNode.parentNode;
            var id = trNode.querySelector("td a").innerHTML;
            ids_array.push(id);
        }
    }
    if (typeof ids_array !== 'undefined' && ids_array.length > 0) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
            cancelButtonClass: 'btn btn-danger w-xs mt-2',
            confirmButtonText: "Yes, delete it!",
            buttonsStyling: false,
            showCloseButton: true
        }).then(function (result) {
            if (result.value) {
                for (i = 0; i < ids_array.length; i++) {
                    vacanciesList.remove("id", `<a href="javascript:void(0);" class="fw-medium link-primary">${ids_array[i]}</a>`);
                }
                document.getElementById("remove-actions").style.display = 'none';
                document.getElementById("checkAll").checked = false;
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Your data has been deleted.',
                    icon: 'success',
                    confirmButtonClass: 'btn btn-info w-xs mt-2',
                    buttonsStyling: false
                });
            }
        });
    } else {
        Swal.fire({
            title: 'Please select at least one checkbox',
            confirmButtonClass: 'btn btn-info',
            buttonsStyling: false,
            showCloseButton: true
        });
    }
}

document.querySelector(".pagination-next").addEventListener("click", function () {
    (document.querySelector(".pagination.listjs-pagination")) ? (document.querySelector(".pagination.listjs-pagination").querySelector(".active")) ?
    document.querySelector(".pagination.listjs-pagination").querySelector(".active").nextElementSibling.children[0].click(): '': '';
});

document.querySelector(".pagination-prev").addEventListener("click", function () {
    (document.querySelector(".pagination.listjs-pagination")) ? (document.querySelector(".pagination.listjs-pagination").querySelector(".active")) ?
    document.querySelector(".pagination.listjs-pagination").querySelector(".active").previousSibling.children[0].click(): '': '';
});

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

/*
|--------------------------------------------------------------------------
| Vacancies
|--------------------------------------------------------------------------
*/

// Simple Donut Charts
var chart;
var chartDonutBasicColors = getChartColorsArray("vacancy_chart");
if (chartDonutBasicColors) {
    var options = {
        series: [totalApplications, totalApplicationsApproved, totalApplicationsRejected],
        labels: ["Applications", "Approved", "Rejected"],
        chart: {
            height: 300,
            type: 'donut',
        },
        legend: {
            position: 'bottom'
        },
        dataLabels: {
            dropShadow: {
                enabled: false,
            }
        },
        colors: chartDonutBasicColors
    };

    var chart = new ApexCharts(document.querySelector("#vacancy_chart"), options);
    chart.render();
}

var url = route('home.vacancies');
var allJobList = '';
var editList = false;

var prevButton = document.getElementById('page-prev');
var nextButton = document.getElementById('page-next');

// configuration variables
var currentPage = 1;
var itemsPerPage = 3;

var getJSON = function (jsonurl, isLocal, callback) {
    var xhr = new XMLHttpRequest();
    var fullUrl = isLocal ? "build/json/" + jsonurl : jsonurl;
    xhr.open("GET", fullUrl, true);
    xhr.responseType = "json";
    xhr.onload = function () {
        var status = xhr.status;
        if (status === 200) {
            callback(null, xhr.response);
        } else {
            callback(status, xhr.response);
        }
    };
    xhr.send();
};

// get json
getJSON(url, false, function (err, data) {
    if (err !== null) {
        console.log("Something went wrong: " + err);
    } else {
        authID = data.userID;
        authUser = data.authUser;
        allJobList = data.vacancies;
        loadJobListData(allJobList, currentPage);
        paginationEvents();
    }
});

function loadJobListData(datas, page, authID) {
    var pages = Math.ceil(datas.length / itemsPerPage)
    if (page < 1) page = 1
    if (page > pages) page = pages
    document.querySelector("#job-list").innerHTML = '';

    for (var i = (page - 1) * itemsPerPage; i < (page * itemsPerPage) && i < datas.length; i++) {
        if (datas.length > 0) {
            var vacancyIcon = '';
            var vacancyColor = '';
            
            if (datas[i].position) {
                vacancyIcon = datas[i].position.icon;
                vacancyColor = datas[i].position.color;
            } else {
                vacancyIcon = 'ri-briefcase-line';
                vacancyColor = 'primary';
            }

            var positionTags = '';
            if (datas[i].position.tags && datas[i].position.tags.length > 0) {
                datas[i].position.tags.forEach(function(tag) {
                    if (tag.name) {
                        positionTags += '<span class="badge bg-' + tag.color + '-subtle text-' + tag.color + ' me-1">' + tag.name + '</span>';
                    }
                });
            }
        }

        if (datas[i]) {
            var active = '';
            var pressed = 'false';
            var saveButton = '';
            var applyButton = '';

            if (datas[i].saved_by.length > 0) {
                active = 'active';
                pressed = 'true'
            }

            let buttonStatus = 'Apply';
            let isAuthIDApplied = false;
            let isAppliedApproved = false;
            
            for (let j = 0; j < datas[i].applicants.length; j++) {
                if (datas[i].applicants[j].id === authID) {
                    isAuthIDApplied = true;
                    isAppliedApproved = datas[i].applicants[j].pivot.approved === 'Yes';
                    break;
                }
            }

            // Checking the user ID from the data against authId
            saveButton = '\
                <button type="button" class="btn btn-icon btn-soft-primary position-absolute top-0 end-0 m-2 ' + active + ' vacancy-save" data-bs-toggle="button" aria-pressed="' + pressed + '" data-bs-id="' + datas[i].encrypted_id + '">\
                    <i class="mdi mdi-bookmark fs-16"></i>\
                </button>\
            ';

            if (datas[i].user_id === authID) {
                buttonStatus = '';
            } else {
                if (isAuthIDApplied) {
                    if (isAppliedApproved) {
                        buttonStatus = `<a class="btn btn-success w-100 apply-trigger" href="`+ route('job-overview.index', {id: datas[i].encrypted_id}) +`">Approved</a>`;
                    } else {
                        buttonStatus = '<button class="btn btn-warning w-100 apply-trigger">Application Pending</button>';
                    }
                } else {
                    buttonStatus = `<button class="btn btn-soft-primary w-100 apply-trigger" data-bs-toggle="modal" href="#applyModal" data-bs-id="${datas[i].encrypted_id}">Apply Job</button>`;
                }
            }
        
            applyButton = buttonStatus;

            document.querySelector("#job-list").innerHTML += '<div class="card joblist-card" data-total-applications="'+ datas[i].total_applications +'" data-applications-approved="'+ datas[i].applications_approved +'" data-applications-rejected="'+ datas[i].applications_rejected +'">\
                <div class="card-body">\
                    <div class="d-flex mb-4">\
                        <div class="avatar-sm">\
                            <div class="avatar-title bg-light rounded  opportunity-icon">\
                                <i class="'+ vacancyIcon + ' text-'+ vacancyColor + ' fs-1"></i>\
                            </div>\
                        </div>\
                        <div class="ms-3 flex-grow-1">\
                            <img src="'+ datas[i].position.image + '" alt="" class="d-none cover-img">\
                            <a href="'+ route('job-overview.index', {id: datas[i].encrypted_id}) +'"><h5 class="job-title">'+ datas[i].position.name + '</h5></a>\
                            <p class="company-name text-muted mb-0">'+ datas[i].store.brand.name + '</p>\
                        </div>\
                        <div>\
                            '+ saveButton +'\
                        </div>\
                    </div>\
                    <p class="text-muted job-description truncated-text-2-lines">'+ datas[i].position.description + '</p>\
                    <div>'+ positionTags +'</div>\
                </div>\
                <div class="card-footer border-top-dashed">\
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">\
                        <div><i class="ri-coins-line align-bottom me-1"></i> <span class="job-type">'+ datas[i].type.name + '</span></div>\
                        <div class="d-none"><span class="job-experience">'+ datas[i].store.brand.name + '</span></div>\
                        <div><i class="ri-map-pin-2-line align-bottom me-1"></i> <span class="job-location">'+ datas[i].store.town.name + '</span></div>\
                        <div><i class="ri-service-line align-bottom me-1"></i> <span class="job-connections">'+ datas[i].applicants.length +'</span></div>\
                        <div><i class="ri-time-line align-bottom me-1"></i> <span class="job-postdate">'+ formatDate(datas[i].created_at) + '</span></div>\
                        <div><a class="btn btn-primary viewjob-list" data-vacancy="'+ datas[i].encrypted_id + '">View More <i class="ri-arrow-right-line align-bottom ms-1"></i></a></div>\
                    </div>\
                </div>\
            </div>';
        }
    }
    
    document.getElementById("total-result").innerHTML = datas.length
    selectedPage();
    currentPage == 1 ? prevButton.parentNode.classList.add('disabled') : prevButton.parentNode.classList.remove('disabled');
    currentPage == pages ? nextButton.parentNode.classList.add('disabled') : nextButton.parentNode.classList.remove('disabled');
    jobDetailShow();
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
};

// paginationEvents
function paginationEvents() {
    var numPages = function numPages() {
        return Math.ceil(allJobList.length / itemsPerPage);
    };

    function clickPage() {
        document.addEventListener('click', function (e) {
            if (e.target.nodeName == "A" && e.target.classList.contains("clickPageNumber")) {
                currentPage = e.target.textContent;
                loadJobListData(allJobList, currentPage);
            }
        });
    };

    function pageNumbers() {
        var pageNumber = document.getElementById('page-num');
        pageNumber.innerHTML = "";
        // for each page
        for (var i = 1; i < numPages() + 1; i++) {
            pageNumber.innerHTML += "<div class='page-item'><a class='page-link clickPageNumber' href='javascript:void(0);'>" + i + "</a></div>";
        }
    }

    prevButton.addEventListener('click', function () {
        if (currentPage > 1) {
            currentPage--;
            loadJobListData(allJobList, currentPage);
        }
    });

    nextButton.addEventListener('click', function () {
        if (currentPage < numPages()) {
            currentPage++;
            loadJobListData(allJobList, currentPage);
        }
    });

    pageNumbers();
    clickPage();
    selectedPage();
}

// Function to generate random data for the chart
function generateRandomChartData() {
    return [Math.floor(Math.random() * 100), Math.floor(Math.random() * 100), Math.floor(Math.random() * 100)];
}

// jobDetailShow event
function jobDetailShow() {
    Array.from(document.querySelectorAll("#job-list .joblist-card")).forEach(function (item) {
        item.querySelector(".viewjob-list").addEventListener("click", function () {
            var coverImgVal = item.querySelector(".cover-img").src;
            var companyLogoImgVal = item.querySelector(".opportunity-icon").innerHTML;
            var jobTitleVal = item.querySelector(".job-title").innerHTML;
            var companyNameVal = item.querySelector(".company-name").innerHTML;
            var jobDescVal = item.querySelector(".job-description").innerHTML;
            var jobTypeVal = item.querySelector(".job-type").innerHTML;
            var jobLocationVal = item.querySelector(".job-location").innerHTML;
            var jobPostdateVal = item.querySelector(".job-postdate").innerHTML;
            var jobConnectionsVal = item.querySelector(".job-connections").innerHTML;
            var encryptedID = item.querySelector(".viewjob-list").getAttribute('data-vacancy');
        
            document.querySelector("#cover-img").src = coverImgVal;
            document.querySelector("#job-overview .view-opportunity-icon").innerHTML = companyLogoImgVal;
            document.querySelector("#job-overview .view-title").innerHTML = jobTitleVal;
            document.querySelector("#job-overview .view-companyname").innerHTML = companyNameVal;
            document.querySelector("#job-overview .view-location").innerHTML = jobLocationVal;
            document.querySelector("#job-overview .view-desc").innerHTML = jobDescVal;
            document.querySelector("#job-overview .view-type").innerHTML = jobTypeVal;
            document.querySelector("#job-overview .view-postdate").innerHTML = jobPostdateVal;
            document.querySelector("#job-overview .view-experience").innerHTML = jobConnectionsVal;
            document.querySelector("#job-overview .btn-info").href = route('job-overview.index', {id: encryptedID});

            var totalApplications = parseInt(item.getAttribute('data-total-applications'));
            var applicationsApproved = parseInt(item.getAttribute('data-applications-approved'));
            var applicationsRejected = parseInt(item.getAttribute('data-applications-rejected'));

            // Update the chart with the new random data
            chart.updateSeries([totalApplications, applicationsApproved, applicationsRejected]);
        });
    });
}

// Search list
var searchElementList = document.getElementById("searchJob");
searchElementList.addEventListener("keyup", function () {
    var inputVal = searchElementList.value.toLowerCase();
    
    function filterItems(arr, query) {    
        return arr.filter(function (el) {
            let positionContains = el.position && el.position.name.toLowerCase().indexOf(query) !== -1;
            let descriptionContains = el.position && el.position.description.toLowerCase().indexOf(query) !== -1;
            let typeContains = el.type && el.type.name.toLowerCase().indexOf(query.toLowerCase()) !== -1;
            let storeContains = el.store && el.store.brand.name.toLowerCase().indexOf(query.toLowerCase()) !== -1;
            let townContains = el.store && el.store.town.name.toLowerCase().indexOf(query.toLowerCase()) !== -1;
            let tagsContains = el.position && el.position.tags && el.position.tags.some(tag => tag.name.toLowerCase().indexOf(query.toLowerCase()) !== -1);
    
            return positionContains || descriptionContains || typeContains || storeContains || townContains || tagsContains;
        });
    }    

    var filterData = filterItems(allJobList, inputVal);
    if(inputVal.length > 0){
        document.getElementById("found-job-alert").classList.remove("d-none");
    }else{
        document.getElementById("found-job-alert").classList.add("d-none");
    }

    if(filterData.length == 0){
        document.getElementById("pagination-element").style.display = "none";
    }else{
        document.getElementById("pagination-element").style.display = "flex";
    }

    var pageNumber = document.getElementById('page-num');
    pageNumber.innerHTML = "";
    var dataPageNum = Math.ceil(filterData.length / itemsPerPage)
    // for each page
    for (var i = 1; i < dataPageNum + 1; i++) {
        pageNumber.innerHTML += "<div class='page-item'><a class='page-link clickPageNumber' href='javascript:void(0);'>" + i + "</a></div>";
    }
    loadJobListData(filterData, currentPage);
});