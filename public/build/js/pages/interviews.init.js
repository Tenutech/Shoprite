/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: CRM-contact Js File
*/

/*
|--------------------------------------------------------------------------
| Date Fields
|--------------------------------------------------------------------------
*/

const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1); // Set the date to one day from today

// Get the current hour and set the minutes to 00
const now = new Date();
const currentHour = now.getHours();

// Restrict the date picker to allow time selection with the default date as tomorrow and the current hour
flatpickr("#rescheduleTime", {
    enableTime: true, // Enables time selection
    dateFormat: "d M, Y H:i", // Set the format to show date and time
    minDate: "today", // Disables past dates
    defaultDate: tomorrow, // Set the default date to tomorrow
    defaultHour: currentHour, // Default time is set to the current hour
    defaultMinute: 0, // Set minutes to 00
    time_24hr: true, // Use 24-hour time format
});

/*
|--------------------------------------------------------------------------
| Interviews List
|--------------------------------------------------------------------------
*/

// list js
var checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.onclick = function () {
        var checkboxes = document.querySelectorAll('.form-check-all input[type="checkbox"]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
            if (checkboxes[i].checked) {
                checkboxes[i].closest("tr").classList.add("table-active");
            } else {
                checkboxes[i].closest("tr").classList.remove("table-active");
            }
        }
    };
}

var perPage = 10;

//Table
var options = {
    valueNames: [
        "id",
        "interview",
        "scheduled_date",
        "location",
        "notes",
        "reschedule_date",
        "status"
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
var interviewList = new List("interviewList", options).on("updated", function (list) {
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

var perPageSelect = document.getElementById("per-page-select");
perPageSelect.addEventListener("change", function() {
    perPage = parseInt(this.value);
    interviewList.page = perPage;
    interviewList.update();
});

isCount = new DOMParser().parseFromString(
    interviewList.items.slice(-1)[0]._values.id,
    "text/html"
);

var confirmBtn = document.getElementById("confirm-interview"),
    declineBtn = document.getElementById("decline-interview"),
    rescheduleBtn = document.getElementById("reschedule-interview"),
    completeBtn = document.getElementById("complete-interview"),
    cancelBtn = document.getElementById("cancel-interview"),
    noShowBtn = document.getElementById("noShow-interview"),
    confrimBtns = document.getElementsByClassName("confirm-item-btn"),
    declineBtns = document.getElementsByClassName("decline-item-btn"),
    rescheduleBtns = document.getElementsByClassName("reschedule-item-btn"),
    completeBtns = document.getElementsByClassName("complete-item-btn"),
    cancelBtns = document.getElementsByClassName("cancel-item-btn"),
    noShowBtns = document.getElementsByClassName("noShow-item-btn");
refreshCallbacks();

ischeckboxcheck();

document.querySelector("#interviewList").addEventListener("click", function () {
    refreshCallbacks();
    ischeckboxcheck();
});

var table = document.getElementById("interviewTable");
// save all tr
var tr = table.getElementsByTagName("tr");
var trlist = table.querySelectorAll(".list tr");

var count = 11;

function ischeckboxcheck() {
    Array.from(document.getElementsByName("checkAll")).forEach(function (x) {
        x.addEventListener("click", function (e) {
            if (e.target.checked) {
                e.target.closest("tr").classList.add("table-active");
            } else {
                e.target.closest("tr").classList.remove("table-active");
            }
        });
    });
}

/*
|--------------------------------------------------------------------------
| Callbacks
|--------------------------------------------------------------------------
*/

// Helper function to determine the route based on the action
function getRoute(action, id) {
    switch (action) {
        case 'confirm':
            return route('interview.confirm', {id: id});
        case 'decline':
            return route('interview.decline', {id: id});
        case 'reschedule':
            return route('interview.reschedule', {id: id});
        case 'complete':
            return route('interview.complete', {id: id});
        case 'cancel':
            return route('interview.cancel', {id: id});
        case 'noShow':
            return route('interview.noShow', {id: id});
    }
}

// Common function for setting up onclick callbacks
function setupActionButtons(buttons, action) {
    Array.from(buttons).forEach(function(btn) {
        btn.onclick = function(e) {
            const itemId = e.target.closest("tr").children[1].innerText;
            const itemValues = interviewList.get({ id: itemId });
            const actionBtn = document.getElementById(`${action}-interview`);
            const rescheduleTimeInput = document.getElementById('rescheduleTime');

            Array.from(itemValues).forEach(function(x) {
                const interviewId = new DOMParser().parseFromString(x._values.id, "text/html").body.innerHTML;

                if (interviewId === itemId) {
                    actionBtn.onclick = function() {
                        // If rescheduling, remove validation error classes if previously added
                        if (action === 'reschedule') {
                            if (!rescheduleTimeInput.value) {
                                $(rescheduleTimeInput).addClass('is-invalid'); // Mark the input as invalid
                                $('.invalid-feedback').removeClass('d-none'); // Show the error message
                                return;
                            }
                        }

                        $(rescheduleTimeInput).removeClass('is-invalid');
                        $('.invalid-feedback').addClass('d-none'); 

                        const url = getRoute(action, interviewId);
                        let formData = new FormData();

                        formData.append('id', interviewId);

                        // Add reschedule_time to formData if action is reschedule
                        if (action === 'reschedule') {
                            formData.append('reschedule_time', rescheduleTimeInput.value);
                        }

                        // AJAX call with the determined URL
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: formData,
                            processData: false,  // Important: tells jQuery not to process the data
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(data) {
                                if (data.success == true) {
                                    Array.from(itemValues).forEach(function (x) {
                                        let isInterviewId = new DOMParser().parseFromString(x._values.id, "text/html").body.innerHTML;
                                        if (isInterviewId == itemId) {
                                            let newStatus = data.interview.status;
                                            let newScheduledDate = data.interview.scheduled_date.split('T')[0]; // Extract only the date part (YYYY-MM-DD)
                                            let newStartTime = data.interview.start_time.split('T')[1].split('.')[0]; // Extract time from full date-time format (HH:mm:ss)
                                            let newRescheduleDate = data.interview.reschedule_date;

                                            let formattedScheduledDate = '';
                                            let formattedRescheduleDate = '';

                                            // Combine scheduled date and start time
                                            if (newScheduledDate && newStartTime) {
                                                // Combine date and time into a single string
                                                let combinedDateTime = new Date(newScheduledDate + 'T' + newStartTime);

                                                // Adjust for South Africa's time zone (UTC+2)
                                                combinedDateTime.setHours(combinedDateTime.getHours() + 2);

                                                // Add one day to the combined date
                                                combinedDateTime.setDate(combinedDateTime.getDate() + 1);

                                                let day = combinedDateTime.getDate().toString().padStart(2, '0'); // Ensure two digits for the day
                                                let month = combinedDateTime.toLocaleString('default', { month: 'short' }); // 'short' for abbreviated month name
                                                let year = combinedDateTime.getFullYear();

                                                // Format the scheduled date and time for display
                                                formattedScheduledDate = `${day} ${month} ${year}` +
                                                                        '<small class="text-muted ms-1">' +
                                                                        combinedDateTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) +
                                                                        '</small>';
                                            }

                                            // Format the reschedule date if it exists
                                            if (newRescheduleDate) {
                                                let date = new Date(newRescheduleDate); // Parse the full reschedule date-time string
                                                let day = date.getDate().toString().padStart(2, '0'); // Ensure two digits for the day
                                                let month = date.toLocaleString('default', { month: 'short' }); // 'short' for abbreviated month name
                                                let year = date.getFullYear();

                                                // Format the reschedule date and time
                                                formattedRescheduleDate = `${day} ${month} ${year}` +
                                                                        '<small class="text-muted ms-1">' +
                                                                        date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) +
                                                                        '</small>';
                                            }

                                            let badgeClass;
                                            switch (newStatus) {
                                                case 'Scheduled':
                                                    badgeClass = 'bg-warning-subtle text-warning';
                                                    break;
                                                case 'Confirmed':
                                                    badgeClass = 'bg-success-subtle text-success';
                                                    break;
                                                case 'Declined':
                                                    badgeClass = 'bg-danger-subtle text-danger';
                                                    break;
                                                case 'Reschedule':
                                                    badgeClass = 'bg-info-subtle text-info';
                                                    break;
                                                case 'Completed':
                                                    badgeClass = 'bg-success-subtle text-success';
                                                    break;
                                                case 'Cancelled':
                                                    badgeClass = 'bg-dark-subtle text-dark';
                                                    break;
                                                case 'No Show':
                                                    badgeClass = 'bg-danger-subtle text-danger';
                                                    break;
                                                default:
                                                    badgeClass = 'bg-primary-subtle text-primary';
                                            }

                                            // Update the status field of the item
                                            x.values({
                                                status: '<span class="badge ' + badgeClass + ' text-uppercase">' + newStatus + '</span>',
                                                scheduled_date: '<span>' + formattedScheduledDate + '</span>',
                                                reschedule_date: '<span>' + formattedRescheduleDate + '</span>'
                                            });

                                            // Update the dropdown menu based only on the status
                                            let dropdownMenu = '';

                                            if (!['Completed', 'Appointed', 'Regretted', 'Cancelled', 'No Show'].includes(newStatus)) {
                                                dropdownMenu += '<div class="dropdown">' +
                                                                '<button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">' +
                                                                '<i class="ri-more-fill align-middle"></i>' +
                                                                '</button>' +
                                                                '<ul class="dropdown-menu dropdown-menu-end">';

                                                if (['Scheduled', 'Reschedule'].includes(newStatus)) {
                                                    dropdownMenu += '<li><a class="dropdown-item confirm-item-btn" data-bs-toggle="modal" href="#interviewConfirmModal">' +
                                                                    '<i class="ri-checkbox-circle-fill align-bottom me-2 text-success"></i>' +
                                                                    'Confirm' +
                                                                    '</a></li>';
                                                }

                                                if (['Scheduled', 'Reschedule', 'Confirmed'].includes(newStatus)) {
                                                    dropdownMenu += '<li><a class="dropdown-item decline-item-btn" data-bs-toggle="modal" href="#interviewDeclineModal">' +
                                                                    '<i class="ri-close-circle-fill align-bottom me-2 text-danger"></i>' +
                                                                    'Decline' +
                                                                    '</a></li>';
                                                }

                                                if (['Scheduled', 'Reschedule', 'Confirmed'].includes(newStatus)) {
                                                    dropdownMenu += '<li><a class="dropdown-item reschedule-item-btn" data-bs-toggle="modal" href="#interviewRescheduleModal">' +
                                                                    '<i class="ri-calendar-event-fill align-bottom me-2 text-info"></i>' +
                                                                    'Reschedule' +
                                                                    '</a></li>';
                                                }

                                                dropdownMenu += '<li><a class="dropdown-item cancel-item-btn" data-bs-toggle="modal" href="#interviewCancelModal">' +
                                                                '<i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i>' +
                                                                'Cancel' +
                                                                '</a></li>';

                                                dropdownMenu += '<li><a class="dropdown-item noShow-item-btn" data-bs-toggle="modal" href="#interviewNoShowModal">' +
                                                                '<i class="ri-user-unfollow-fill align-bottom me-2 text-danger"></i>' +
                                                                'No Show' +
                                                                '</a></li>';

                                                dropdownMenu += '</ul></div>';
                                            }

                                            // Find the current row and update the dropdown menu
                                            let dropdownCell = e.target.closest("tr").children[8];
                                            dropdownCell.innerHTML = dropdownMenu;

                                            // Re-initialize callbacks for the buttons
                                            refreshCallbacks();
                                        }
                                    });

                                    Swal.fire({
                                        position: 'top-end',
                                        icon: 'success',
                                        title: data.message,
                                        showConfirmButton: false,
                                        timer: 2000,
                                        showCloseButton: true,
                                        toast: true
                                    });

                                    function capitalizeFirstLetter(string) {
                                        return string.charAt(0).toUpperCase() + string.slice(1);
                                    }

                                    // Dynamically determine the modal ID based on the action
                                    var modalId = '#interview' + capitalizeFirstLetter(action) + 'Modal';
                                    $(modalId).modal('hide');
                                    
                                    // Clear the datetime-local input
                                    document.getElementById('rescheduleTime').value = '';
                                }                            },
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
                            }
                        });
                    };
                }
            });
        };
    });
}

// Initialize callbacks for all buttons
function refreshCallbacks() {
    setupActionButtons(confrimBtns, 'confirm');
    setupActionButtons(declineBtns, 'decline');
    setupActionButtons(rescheduleBtns, 'reschedule');
    setupActionButtons(completeBtns, 'complete');
    setupActionButtons(cancelBtns, 'cancel');
    setupActionButtons(noShowBtns, 'noShow');
}

// Prevent default behavior for all pagination links created by List.js
document.querySelectorAll(".listjs-pagination a").forEach(function(anchor) {
    anchor.addEventListener("click", function(event) {
        event.preventDefault();
    });
});

document.querySelector(".pagination-wrap").addEventListener("click", function(event) {
    // If the clicked element or its parent has the class .pagination-prev
    if (event.target.classList.contains("pagination-prev") || (event.target.parentElement && event.target.parentElement.classList.contains("pagination-prev"))) {
        event.preventDefault();
        const activeElement = document.querySelector(".pagination.listjs-pagination")?.querySelector(".active");
        if (activeElement) {
            const previousElement = activeElement.previousElementSibling;
            if (previousElement && previousElement.children[0]) {
                previousElement.children[0].click();
            }
        }
    }
    
    // If the clicked element or its parent is in the .listjs-pagination
    if (event.target.closest(".listjs-pagination")) {
        event.preventDefault();
        event.target.click();
    }
    
    // If the clicked element or its parent has the class .pagination-next
    if (event.target.classList.contains("pagination-next") || (event.target.parentElement && event.target.parentElement.classList.contains("pagination-next"))) {
        event.preventDefault();
        const activeElement = document.querySelector(".pagination.listjs-pagination")?.querySelector(".active");
        if (activeElement && activeElement.nextElementSibling && activeElement.nextElementSibling.children[0]) {
            activeElement.nextElementSibling.children[0].click();
        }
    }
});