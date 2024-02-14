/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Notifications init js
*/

// Notifications
$(document).ready(function() {

    function syncCheckboxes(checkBox) {
        // Extract the ID from the checkbox's ID attribute
        const idMatch = checkBox.id.match(/\d+$/);  // This regex extracts the numbers at the end of the ID
        if (!idMatch) return;  // Return if no ID found
        const id = idMatch[0];
    
        // Checkboxes you want to sync
        const targetIds = [
            `all-notification-check-${id}`,
            `message-notification-check-${id}`,
            `alert-notification-check-${id}`
        ];
    
        targetIds.forEach(targetId => {
            const targetCheckbox = document.getElementById(targetId);
            if (targetCheckbox) {
                targetCheckbox.checked = checkBox.checked;
            }
        });
    }

    $('.notification-check input[type="checkbox"]').on('change', function() {
        syncCheckboxes(this);
    });

    /*
    |--------------------------------------------------------------------------
    | Application Approve / Decline
    |--------------------------------------------------------------------------
    */

    $('.applicationApprove, .applicationDecline').on('click', function() {
        const applicationID = $(this).attr('data-bs-application');
        var btn = $(this);

        let url;

        if ($(this).hasClass('applicationApprove')) {
            url = route('application.approve');
        } else if ($(this).hasClass('applicationDecline')) {
            url = route('application.decline');
        }

        $.ajax({
            url: url,
            type: 'PUT',
            data: {
                id: applicationID
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success == true) {
                    // Reference to the container
                    const $container = btn.closest('.btn-container');

                    // Clear the container
                    $container.empty();

                    // Add the 'Send a message' button if approved, or the 'Declined!' text if declined
                    if (data.message === 'Application approved!') {
                        $container.append('<a href="' + route('messages.index', {id: data.encryptedID}) + '" class="btn btn-sm rounded-pill btn-success waves-effect waves-light">Send message</a>');
                    } else {
                        $container.append('<span class="text-danger">Declined!</span>');
                    }

                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        toast: true,
                        showCloseButton: true
                    });
                }
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
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Intreview Confirm / Decline
    |--------------------------------------------------------------------------
    */

    $('.interviewConfirm, .interviewDecline, .interviewReschedule').on('click', function() {
        const interviewID = $(this).attr('data-bs-interview');
        var btn = $(this);

        let url;

        if ($(this).hasClass('interviewReschedule')) {
            const $container = btn.closest('.btn-container');
            $container.empty(); // Clear previous buttons or inputs
            $container.append('<input type="datetime-local" class="form-control datetime-input" name="reschedule_time" />\
                               <span class="invalid-feedback d-none" role="alert"><strong>Please select a date and time.</strong></span>\
                               <button type="button" class="btn btn-sm rounded-pill btn-primary waves-effect waves-light mt-2 submitReschedule">Submit</button>');

            // Event listener for the submit button
            $container.find('.submitReschedule').on('click', function() {
                const rescheduleTime = $container.find('.datetime-input').val();

                if (!rescheduleTime) {
                    $container.find('.datetime-input').addClass('is-invalid'); // Mark the input as invalid
                    $container.find('.invalid-feedback').removeClass('d-none'); // Show the error message
                    return;
                } else {
                    $container.find('.datetime-input').removeClass('is-invalid'); // Remove the invalid mark if the date and time are valid
                    $container.find('.invalid-feedback').addClass('d-none'); // Hide the error message
                }

                // Proceed to submit the reschedule time along with the interview ID
                $.ajax({
                    url: route('interview.reschedule'),
                    type: 'PUT',
                    data: {
                        id: interviewID,
                        reschedule_time: rescheduleTime
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        // Clear the container
                        $container.empty();

                        // Parse the rescheduleTime as a Date object
                        var rescheduleDate = new Date(rescheduleTime);

                        // Create an array of month abbreviations
                        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

                        // Manually format the date
                        var formattedDate = rescheduleDate.getDate() + ' ' 
                                            + months[rescheduleDate.getMonth()] + ' ' 
                                            + rescheduleDate.getHours().toString().padStart(2, '0') + ':' 
                                            + rescheduleDate.getMinutes().toString().padStart(2, '0');

                        // Add new content
                        $container.append('<span class="text-info">Reschedule for ' + formattedDate + '</span><br>\
                                                <button type="button" data-bs-interview="' + data.encryptedID +'" class="btn btn-sm rounded-pill btn-danger waves-effect waves-light mt-2 interviewDecline">\
                                                    Decline\
                                                </button>'
                        );

                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            toast: true,
                            showCloseButton: true
                        });
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
                    }
                });
            });
        } else {

            if ($(this).hasClass('interviewConfirm')) {
                url = route('interview.approve');
            } else if ($(this).hasClass('interviewDecline')) {
                url = route('interview.decline');
            }

            $.ajax({
                url: url,
                type: 'PUT',
                data: {
                    id: interviewID
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success == true) {
                        // Reference to the container
                        const $container = btn.closest('.btn-container');

                        // Clear the container
                        $container.empty();

                        // Add the 'Send a message' button if approved, or the 'Declined!' text if declined
                        if (data.message === 'Interview confirmed!') {
                            $container.append('<span class="text-success">Confirmed!</span>\
                                                <button type="button" data-bs-interview="' + data.encryptedID +'" class="btn btn-sm rounded-pill btn-danger waves-effect waves-light interviewDecline">\
                                                    Decline\
                                                </button>'
                            );
                        } else {
                            $container.append('<span class="text-danger">Declined!</span>');
                        }

                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            toast: true,
                            showCloseButton: true
                        });
                    }
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
                }
            });
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Notification Read / Remove
    |--------------------------------------------------------------------------
    */

    if (document.getElementsByClassName("notification-check")) {
        function emptyNotification() {
            Array.from(document.querySelectorAll("#notificationItemsTabContent .tab-pane")).forEach(function (elem) {
                if (elem.querySelectorAll(".notification-item").length > 0) {
                    if (elem.querySelector(".view-all")) {
                        elem.querySelector(".view-all").style.display = "block";
                    }
                } else {
                    if (elem.querySelector(".view-all")) {
                        elem.querySelector(".view-all").style.display = "none";
                    }
                    var emptyNotificationElem = elem.querySelector(".empty-notification-elem")
                    if (!emptyNotificationElem) {
                        elem.innerHTML += '<div class="empty-notification-elem">\
                        <div class="w-25 w-sm-50 pt-3 mx-auto">\
                            <img src="build/images/svg/bell.svg" class="img-fluid" alt="user-pic">\
                        </div>\
                        <div class="text-center pb-5 mt-2">\
                            <h6 class="fs-18 fw-semibold lh-base">Hey! You have no <br> notifications </h6>\
                        </div>\
                    </div>'
                    }
                }
            });
        }
        emptyNotification();


        Array.from(document.querySelectorAll("#all-noti-tab .notification-check input")).forEach(function (element) {
            element.addEventListener("change", function (el) {
                el.target.closest("#all-noti-tab .notification-item").classList.toggle("active");
    
                var checkedCount = document.querySelectorAll('#all-noti-tab .notification-check input:checked').length;
    
                document.getElementById("notification-actions").style.display = checkedCount > 0 ? 'block' : 'none';
                document.getElementById("select-content").innerHTML = checkedCount;
            });
    
            var notificationDropdown = document.getElementById('notificationDropdown')
            notificationDropdown.addEventListener('hide.bs.dropdown', function (event) {
                element.checked = false;
                document.querySelectorAll('.notification-item').forEach(function (item) {
                    item.classList.remove("active");
                })
                document.getElementById('notification-actions').style.display = '';
            });
        });

        // Handling the mark as read and remove button
        $("#markAsReadBtn, #removeNotificationsBtn").on("click", function(event) {
            var selectedNotifications = document.querySelectorAll('#all-noti-tab .notification-check input:checked');
            var notificationIds = [];

            selectedNotifications.forEach(function(notificationInput) {
                notificationInput.closest(".notification-item").classList.add("read");
                notificationIds.push(notificationInput.value);
            });

            var url;
            var dataPayload = {
                notifications: notificationIds // Send the selected notification IDs as well
            };

            if (event.target.id === 'markAsReadBtn') {
                url = route('notification.read');
            } else if (event.target.id === 'removeNotificationsBtn') {
                url = route('notification.remove');
            }

            // Send the IDs to your server, for example, via an AJAX POST request
            $.ajax({
                url: url,
                type: 'PUT',
                data: dataPayload,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success == true) {
                        // Count notifications that are already read among the selected notifications
                        let alreadyReadCount = 0;
                        selectedNotifications.forEach(function(notificationInput) {
                            if (!notificationInput.closest(".notification-item").querySelector('.newNotification')) {
                                alreadyReadCount++;
                            }
                        });

                        let affectedCount = selectedNotifications.length - alreadyReadCount;

                        if (event.target.id === 'markAsReadBtn') {
                            selectedNotifications.forEach(function(notificationInput) {
                                const unreadNotificationSpan = notificationInput.closest(".notification-item").querySelector('.newNotification');
                                if (unreadNotificationSpan) {
                                    unreadNotificationSpan.remove();
                                }
                            });
                        } else if (event.target.id === 'removeNotificationsBtn') {
                            selectedNotifications.forEach(function(notificationInput) {
                                notificationInput.closest(".notification-item").remove();
                            });
                        }

                        // Decrease the unread notifications count
                        let currentCount = parseInt($(".topbarNotificationBadge").text()) - affectedCount;

                        if (currentCount <= 0 || isNaN(currentCount)) {
                            $(".topbarNotificationBadge").remove();
                            $(".notificationNewBadge").text("0 New");
                            $(".notificationAllCount").text("All (0)");
                        } else {
                            // Update the count in all places
                            $(".topbarNotificationBadge").text(currentCount);
                            $(".notificationNewBadge").text(currentCount + " New");
                            $(".notificationAllCount").text("All (" + currentCount + ")");
                        }

                        emptyNotification();

                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            toast: true,
                            showCloseButton: true
                        });
                    }
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
                }
            });
        });
    }
});