/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: job candidate list init js
*/

var selectTown = document.getElementById("selectTown");
const filterTown = new Choices(selectTown, {
    searchEnabled: true
});

var slider = document.getElementById('rangeSlider');
noUiSlider.create(slider, {
    start: 10,
    step: 1,
    connect: 'lower',
    range: {
        'min': 0,
        'max': 100
    },
});

slider.noUiSlider.on('update', function(values, handle) {
    document.getElementById('rangeValue').innerText = 'Selected Range: ' + values[handle] + 'km';
});

var url = route('applicants.data');
var allcandidateList = '';

var prevButton = document.getElementById('page-prev');
var nextButton = document.getElementById('page-next');

// configuration variables
var currentPage = 1;
var itemsPerPage = 8;

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
getJSON("job-candidate-list.json", function (err, data) {
    if (err !== null) {
        Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: 'Something went wrong: ' + err,
            showConfirmButton: false,
            showCloseButton: true,
            toast: true
        });
    } else {
        allcandidateList = data.applicants;
        loadCandidateListData(allcandidateList, currentPage);
        paginationEvents();
    }
});

function loadCandidateListData(datas, page) {
    var pages = Math.ceil(datas.length / itemsPerPage)
    if (page < 1) page = 1
    if (page > pages) page = pages
    document.querySelector("#candidate-list").innerHTML = '';

    for (var i = (page - 1) * itemsPerPage; i < (page * itemsPerPage) && i < datas.length; i++) {
        if (datas[i]) {
            var bookmark = datas[i].saved_by.length > 0 ? "active" : "";

            var isUserProfile = datas[i].avatar ? '<img src="' + datas[i].avatar + '" alt="" class="member-img img-fluid d-block rounded" />'
                : '<img src="/images/avatar.jpg" alt="" class="member-img img-fluid d-block rounded" />';

            document.querySelector("#candidate-list").innerHTML += 
                '<div class="col-md-6 col-lg-12">\
                    <div class="card mb-0">\
                        <div class="card-body">\
                            <div class="d-lg-flex align-items-center">\
                                <div class="flex-shrink-0 col-auto">\
                                    <div class="avatar-sm rounded overflow-hidden">\
                                        '+ isUserProfile + '\
                                    </div>\
                                </div>\
                                <div class="ms-lg-3 my-3 my-lg-0 col-3 text-start">\
                                    <a href="'+ route('applicant-profile.index', {id: datas[i].encrypted_id}) +'">\
                                        <h5 class="fs-16 mb-2">\
                                            '+ datas[i].firstname + ' '+ datas[i].lastname + '\
                                        </h5>\
                                    </a>\
                                    <p class="text-muted mb-0">\
                                        '+ (datas[i].position ? 
                                            (datas[i].position.name == 'Other' ? 
                                                datas[i].position_specify : 
                                                datas[i].position.name) 
                                            : 'N/A') + '\
                                    </p>\
                                </div>\
                                <div class="d-flex gap-4 mt-0 text-muted mx-auto col-2">\
                                    <div><i class="ri-map-pin-2-line text-primary me-1 align-bottom"></i>\
                                        '+ (datas[i].town ? datas[i].town.name : 'N/A') + '\
                                    </div>\
                                </div>\
                                <div class="col-2">\
                                    <i class="ri-briefcase-line text-primary me-1 align-bottom"></i>\
                                    '+ (datas[i].type ? 
                                        (datas[i].type.name == 'Other' ? 
                                            '<span class="badge bg-' + datas[i].type.color + '-subtle text-' + datas[i].type.color + '">' + datas[i].application_reason_specify + '</span>' : 
                                            '<span class="badge bg-' + datas[i].type.color + '-subtle text-' + datas[i].type.color + '">' + datas[i].type.name + '</span>') 
                                        : 'N/A') + '\
                                </div>\
                                <div class="d-flex flex-wrap gap-2 align-items-center mx-auto my-3 my-lg-0 col-1">\
                                    <div class="badge text-bg-success">\
                                        <i class="mdi mdi-star me-1"></i>\
                                        '+ (datas[i].score ? datas[i].score : 'N/A') + '\
                                    </div>\
                                </div>\
                                <div class="col-2 text-end">\
                                    <a href="'+ route('applicant-profile.index', {id: datas[i].encrypted_id}) +'" class="btn btn-soft-primary">\
                                        View Details\
                                    </a>\
                                    <a href="#!" class="btn btn-ghost-danger btn-icon custom-toggle '+ bookmark + ' save-applicant" data-bs-toggle="button" data-bs-id='+ datas[i].encrypted_id + '>\
                                        <span class="icon-on">\
                                            <i class="ri-bookmark-line align-bottom"></i>\
                                        </span>\
                                        <span class="icon-off">\
                                            <i class="ri-bookmark-3-fill align-bottom"></i>\
                                        </span>\
                                    </a>\
                                </div>\
                            </div>\
                        </div>\
                    </div>\
                </div>'
        }
    }

    selectedPage();
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
        return Math.ceil(allcandidateList.length / itemsPerPage);
    };

    function clickPage() {
        document.addEventListener('click', function (e) {
            if (e.target.nodeName == "A" && e.target.classList.contains("clickPageNumber")) {
                currentPage = e.target.textContent;
                loadCandidateListData(allcandidateList, currentPage);
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
            loadCandidateListData(allcandidateList, currentPage);
        }
    });

    nextButton.addEventListener('click', function () {
        if (currentPage < numPages()) {
            currentPage++;
            loadCandidateListData(allcandidateList, currentPage);
        }
    });

    pageNumbers();
    clickPage();
    selectedPage();
}

var map;
var selectedLocation;
var center = {lat: -30.5595, lng: 22.9375};
var marker;

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
      zoom: 7,
      center: center
    });

    // Add a double-click event listener to the map
    map.addListener('dblclick', function(event) {
        selectedLocation = {
            lat: event.latLng.lat(),
            lng: event.latLng.lng()
        };

        // Place a marker on the map at the clicked location
        if (marker) {
            marker.setMap(null);  // Remove the existing marker
        }
        marker = new google.maps.Marker({
            position: selectedLocation,
            map: map
        });

        // Add a badge for this location
        addLocationBadge(selectedLocation);
        applyLocationFilter(selectedLocation);
    });
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    var R = 6371; // Radius of the earth in km
    var dLat = (lat2 - lat1) * Math.PI / 180;  
    var dLon = (lon2 - lon1) * Math.PI / 180;
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var d = R * c; // Distance in km
    return d;
}

// Load the map once the modal is fully shown
$('#mapModal').on('shown.bs.modal', function() {
    google.maps.event.trigger(map, 'resize');
});

// Listen to changes on the dropdown
selectTown.addEventListener('change', function() {
    const selectedValue = this.value.split(';');
    const key = selectedValue[0];
    const value = selectedValue[1];
    
    // Get the label as the option text
    const label = this.options[this.selectedIndex].textContent;

    if (!badgeExists(value)) {
        addBadge(key, value, label);
        applyFilter(key, value); 
    }
});

function filterItems(arr, query, filters) {
    return arr.filter(function (el) {
        const searchTextMatch = (
            (el.firstname && el.firstname.toLowerCase().indexOf(query) !== -1) ||
            (el.lastname && el.lastname.toLowerCase().indexOf(query) !== -1) ||
            (el.phone && el.phone.toLowerCase().indexOf(query) !== -1) ||
            (el.id_number && el.id_number.toLowerCase().indexOf(query) !== -1) ||
            (el.location && el.location.toLowerCase().indexOf(query) !== -1) ||
            (el.contact_number && el.contact_number.toLowerCase().indexOf(query) !== -1) ||
            (el.gender && el.gender.name.toLowerCase().indexOf(query) !== -1) ||
            (el.race && el.race.name.toLowerCase().indexOf(query) !== -1) ||
            (el.email && el.email.toLowerCase().indexOf(query) !== -1) ||
            (el.position && el.position.name.toLowerCase().indexOf(query) !== -1) ||
            (el.position_specify && el.position_specify.toLowerCase().indexOf(query) !== -1) ||
            (el.education && el.education.name.toLowerCase().indexOf(query) !== -1) ||
            (el.type && el.type.name.toLowerCase().indexOf(query) !== -1)
        );

        const filterMatch = Object.entries(filters)
            .filter(([key]) => key !== "coordinates")
            .every(([key, values]) => {
            return values.some(value => el[key] == value);
        });

        const locationMatch = !filters["coordinates"] ? true : (function(){
            if (!el.coordinates) return false;
            
            const selectedLoc = filters["coordinates"][0];
            const coords = el.coordinates.split(',');
            const candidateLat = parseFloat(coords[0].trim());
            const candidateLng = parseFloat(coords[1].trim());
            const distance = calculateDistance(selectedLoc.lat, selectedLoc.lng, candidateLat, candidateLng);
            const selectedRadius = parseFloat(slider.noUiSlider.get());

            return distance <= selectedRadius;
        })();

        return searchTextMatch && filterMatch && locationMatch;
    })
}

// Merge your search and filter actions into this function:
function filterAndSearch() {
    const inputVal = searchElementList.value.toLowerCase();
    const filterData = filterItems(allcandidateList, inputVal, activeFilters);

    if (currentSort.key) {
        filterData.sort((a, b) => {
            if (currentSort.order === 'desc') {
                return b[currentSort.key] - a[currentSort.key];
            } else {
                return a[currentSort.key] - b[currentSort.key];
            }
        });
    }

    // If no results are found
    if (filterData.length == 0) {
        document.getElementById("pagination-element").style.display = "none";
        document.querySelector("#candidate-list").innerHTML = ''; // Clear the candidate list
        document.querySelector(".noresult").style.display = "block"; // Show the "No Result Found" message
    } else {
        document.getElementById("pagination-element").style.display = "flex";
        document.querySelector(".noresult").style.display = "none"; // Hide the "No Result Found" message

        var pageNumber = document.getElementById('page-num');
        pageNumber.innerHTML = "";
        var dataPageNum = Math.ceil(filterData.length / itemsPerPage)
        // for each page
        for (var i = 1; i < dataPageNum + 1; i++) {
            pageNumber.innerHTML += "<div class='page-item'><a class='page-link clickPageNumber' href='javascript:void(0);'>" + i + "</a></div>";
        }
        loadCandidateListData(filterData, currentPage);
    }
}

// Modify your keyup event listener like this:
var searchElementList = document.getElementById("searchApplicant");
searchElementList.addEventListener("keyup", function () {
    filterAndSearch();
});

let currentSort = { key: null, order: 'asc' };

document.querySelectorAll('.filter-button').forEach(button => {
    button.addEventListener('click', function() {
        const filter = this.getAttribute('data-bs-filter').split(';');
        const label = this.innerText;
        const value = filter[1];

        if (!badgeExists(value)) {
            addBadge(filter[0], value, label);

            if (value === 'literacy' || value === 'numeracy') {
                currentSort.key = filter[0]; // set the sorting key
                currentSort.value = value;
                currentSort.order = 'desc'; // for descending order
            } else {
                applyFilter(filter[0], value);
            }
            
            filterAndSearch();
        }
    });
});

function badgeExists(value) {
    const badges = document.querySelectorAll('#filterBadges .badge');
    for (let badge of badges) {
        if (badge.getAttribute('data-value') === value) {
            return true;
        }
    }
    return false;
}

function addLocationBadge(location) {
    const badgeContainer = document.getElementById('filterBadges');
    const selectedRadius = parseFloat(slider.noUiSlider.get());
    const badgeLabel = `${selectedRadius}km from: (${location.lat.toFixed(2)}, ${location.lng.toFixed(2)})`;

    if (!badgeExists(badgeLabel)) {
        addBadge("coordinates", badgeLabel, badgeLabel);
    }
}

function addBadge(key, value, label) {
    const badgeContainer = document.getElementById('filterBadges');

    const badge = document.createElement('span');
    badge.className = "badge bg-primary d-flex align-items-center";
    badge.setAttribute('data-key', key);
    badge.setAttribute('data-value', value);
    badge.innerHTML = `
        ${label}
        <span class="border-start border-light mx-1" style="height: 16px;"></span>
        <button class="btn-close btn-close-white" type="button" aria-label="Close"></button>
    `;

    badge.querySelector('.btn-close').addEventListener('click', function() {
        if (badge.getAttribute('data-value') === currentSort.value) {
            currentSort = { key: null, value: null, order: 'asc' }; // reset sorting
        }
        removeFilter(key, value);
        badgeContainer.removeChild(badge);
        filterAndSearch();
    });

    badgeContainer.appendChild(badge);
}

const activeFilters = {};

function applyLocationFilter(location) {
    activeFilters["coordinates"] = [location];
    filterAndSearch();
}

function applyFilter(key, value) {
    if (!activeFilters[key]) {
        activeFilters[key] = [];
    }
    
    // Check if the value already exists for this key
    if (activeFilters[key].indexOf(value) === -1) {
        activeFilters[key].push(value);
    }

    filterAndSearch();
}

function removeFilter(key, value) {
    if (activeFilters[key]) {
        const index = activeFilters[key].indexOf(value);
        if (index !== -1) {
            activeFilters[key].splice(index, 1);
        }

        // If no more values for this key, delete the key from activeFilters
        if (activeFilters[key].length === 0) {
            delete activeFilters[key];
        }

        if (key == "coordinates") {
            delete activeFilters[key];
            selectedLocation = null;
        }
    }
    filterAndSearch();
}