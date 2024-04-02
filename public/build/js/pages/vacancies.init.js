/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job grid list Js File
*/

// Format Date
function formatDate(inputDate) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const date = new Date(inputDate);
    const day = date.getDate();
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
}

//URL
var url = "/vacancy/jobs";
var allJobList = '';

var prevButton = document.getElementById('page-prev');
var nextButton = document.getElementById('page-next');

// configuration variables
var currentPage = 1;
var itemsPerPage = 8;
var authID;

var getJSON = function (jsonurl, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
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
getJSON(url, function (err, data) {
    if (err !== null) {
        console.log("Something went wrong: " + err);
    } else {
        authID = data.userID;
        authUser = data.authUser;
        allJobList = data.vacancies;
        
        var isPosition = document.getElementById("positionFilter").value;
        if (isPosition !== "all") {
            filterData();
        } else {
            loadJobListData(allJobList, currentPage, authID);
        }
        paginationEvents();
    }
});

// load job list data
function loadJobListData(datas, page, authID) {
    var pages = Math.ceil(datas.length / itemsPerPage)
    if (page < 1) page = 1
    if (page > pages) page = pages
    document.querySelector("#job-list").innerHTML = '';

    if (currentPage == 1) {
        itemsPerPage = 7;
        document.querySelector("#job-list").insertAdjacentHTML('afterbegin', '<div class="col-lg-3 col-md-6" id="job-widget">\
        <div class="card card-height-100 bg-primary bg-job">\
            <div class="card-body p-5">\
                <h2 class="lh-base text-white">Shoprite invites young professionals for an intership!</h2>\
                <p class="text-white text-opacity-75 mb-0 fs-14">Don\'t miss your opportunity to improve your skills!</p>\
                <div class="mt-5 pt-2">\
                    <button type="button" class="btn btn-light w-100">View More <i class="ri-arrow-right-line align-bottom"></i></button>\
                </div>\
            </div>\
        </div>\
    </div>');
    } else {
        itemsPerPage = 8;
    }
    
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

            document.querySelector("#job-list").innerHTML += '\
                <div class="col-lg-3 col-md-6 d-flex mb-3">\
                    <div class="card d-flex flex-column h-100">\
                        <div class="card-body d-flex flex-column flex-grow-1 position-relative">\
                            '+ saveButton +'\
                            <div class="avatar-sm mb-4">\
                                <div class="avatar-title bg-light rounded">\
                                    <i class="'+ vacancyIcon + ' text-'+ vacancyColor + ' fs-1"></i>\
                                </div>\
                            </div>\
                            <a href="'+ route('job-overview.index', {id: datas[i].encrypted_id}) +'"><h5>'+ datas[i].position.name + '</h5></a>\
                            <p class="text-muted">'+ datas[i].type.name + '</p>\
                            <div class="d-flex gap-4 mb-3">\
                                <div><i class="ri-store-3-line text-primary me-1 align-bottom"></i> '+ datas[i].store.brand.name + '</div>\
                                <div><i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i> '+ datas[i].store.town.name + '</div>\
                                <div><i class="ri-time-line text-primary me-1 align-bottom"></i> '+ formatDate(datas[i].created_at) + '</div>\
                            </div>\
                            <div class="text-muted truncated-text-4-lines mb-4">'+ datas[i].position.description +'</div>\
                            <div class="hstack gap-2">'+ positionTags +'</div>\
                            <div class="mt-auto">\
                                <div class="hstack gap-2">\
                                    '+ applyButton +'\
                                    <a href="'+ route('job-overview.index', {id: datas[i].encrypted_id}) +'" class="btn btn-soft-info w-100">Overview</a>\
                                </div>\
                            </div>\
                        </div>\
                    </div>\
                </div>';
        };
    }

    document.getElementById("total-result").innerHTML = datas.length
    selectedPage();
    var searchElementList = document.getElementById("searchJob");
    searchElementList.addEventListener("keyup", function () {
        var inputVal = searchElementList.value.toLowerCase();
        if(inputVal.length > 0){
            document.getElementById("job-widget").style.display = "none";
        }else{
            document.getElementById("job-widget").style.display = "block";
        }
    });

    let jobWidget = document.getElementById("job-widget");
    if (jobWidget) {
        if (datas.length > 0) {
            jobWidget.style.display = "block";
        } else {
            jobWidget.style.display = "none";
        }
    }

    currentPage == 1 ? prevButton.parentNode.classList.add('disabled') : prevButton.parentNode.classList.remove('disabled');
    currentPage == pages ? nextButton.parentNode.classList.add('disabled') : nextButton.parentNode.classList.remove('disabled');
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
                loadJobListData(allJobList, currentPage, authID);
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
            loadJobListData(allJobList, currentPage, authID);
        }
    });

    nextButton.addEventListener('click', function () {
        if (currentPage < numPages()) {
            currentPage++;
            loadJobListData(allJobList, currentPage, authID);
        }
    });

    pageNumbers();
    clickPage();
    selectedPage();
}

var positionChoices = new Choices(document.getElementById("positionFilter"), {
    searchEnabled: true,
    shouldSort: false
});

var typeChoices = new Choices(document.getElementById("typeFilter"), {
    searchEnabled: true,
    shouldSort: false
});

var storeChoices = new Choices(document.getElementById("storeFilter"), {
    searchEnabled: true,
    shouldSort: false
});

var townChoices = new Choices(document.getElementById("townFilter"), {
    searchEnabled: true,
    shouldSort: false
});

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

    var filteredResults = filterItems(allJobList, inputVal);

    if(filteredResults.length == 0){
        document.getElementById("pagination-element").style.display = "none";
    }else{
        document.getElementById("pagination-element").style.display = "flex";
    }

    var pageNumber = document.getElementById('page-num');
    pageNumber.innerHTML = "";
    var dataPageNum = Math.ceil(filteredResults.length / itemsPerPage)
    // for each page
    for (var i = 1; i < dataPageNum + 1; i++) {
        pageNumber.innerHTML += "<div class='page-item'><a class='page-link clickPageNumber' href='javascript:void(0);'>" + i + "</a></div>";
    }

    loadJobListData(filteredResults, currentPage, authID);
});

function filterData() {
    var isPosition = document.getElementById("positionFilter").value;
    var isType = document.getElementById("typeFilter").value;
    var isStore = document.getElementById("storeFilter").value;
    var isTown = document.getElementById("townFilter").value;

    var filterData = allJobList.filter(function (data) {
        var positionFilter = isPosition === "all" || data.position_id == isPosition;
        var typeFilter = isType === "all" || data.type_id == isType;
        var storeFilter = isStore === "all" || data.store_id == isStore;
        var townFilter = isTown === "all" || data.store.town_id == isTown;

        return positionFilter && typeFilter && storeFilter && townFilter;
    });

    var pageNumber = document.getElementById('page-num');
    pageNumber.innerHTML = "";
    var dataPageNum = Math.ceil(filterData.length / itemsPerPage);
    for (var i = 1; i < dataPageNum + 1; i++) {
        pageNumber.innerHTML += "<div class='page-item'><a class='page-link clickPageNumber' href='javascript:void(0);'>" + i + "</a></div>";
    }

    loadJobListData(filterData, currentPage, authID);
}

function resetFilters() {
    // Reset the input fields and select dropdowns
    document.querySelector('.search').value = "";
    positionChoices.setChoiceByValue("all");
    typeChoices.setChoiceByValue("all");
    storeChoices.setChoiceByValue("all");
    townChoices.setChoiceByValue("all");

    filterData();
}