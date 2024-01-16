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

    // Connection Approve / Decline
    $('.applicationApprove, .applicationDecline').on('click', function() {
        const connectionID = $(this).attr('data-bs-application');
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
                id: connectionID
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
                if (jqXHR.status === 400 || jqXHR.status === 422) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: jqXHR.responseJSON.message,
                        showConfirmButton: false,
                        timer: 5000,
                        showCloseButton: true,
                        toast: true
                    });
                } else {
                    if(textStatus === 'timeout') {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: 'The request timed out. Please try again later.',
                            showConfirmButton: false,
                            timer: 5000,
                            showCloseButton: true,
                            toast: true
                        });
                    } else {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: 'An error occurred while processing your request. Please try again later.',
                            showConfirmButton: false,
                            timer: 5000,
                            showCloseButton: true,
                            toast: true
                        });
                    }
                }
            }
        });
    });

    // Notification Read / Remove
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
                    if (jqXHR.status === 400 || jqXHR.status === 422) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: jqXHR.responseJSON.message,
                            showConfirmButton: false,
                            timer: 5000,
                            showCloseButton: true,
                            toast: true
                        });
                    } else {
                        if(textStatus === 'timeout') {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: 'The request timed out. Please try again later.',
                                showConfirmButton: false,
                                timer: 5000,
                                showCloseButton: true,
                                toast: true
                            });
                        } else {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: 'An error occurred while processing your request. Please try again later.',
                                showConfirmButton: false,
                                timer: 5000,
                                showCloseButton: true,
                                toast: true
                            });
                        }
                    }
                }
            });
        });
    }
});