/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Chat init js
*/

(function () {
    var dummyUserImage = "build/images/users/user-dummy-img.jpg";
    var dummyMultiUserImage = "build/images/users/multi-user.jpg";
    var isreplyMessage = false;

    // favourite btn
    document.querySelectorAll(".favourite-btn").forEach(function (item) {
        item.addEventListener("click", function (event) {
            this.classList.toggle("active");
        });
    });

    // format time
    function formatTime(timestamp) {
        var date = new Date(timestamp);
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0'+minutes : minutes;
        
        var day = date.getDate().toString().padStart(2, '0');
        var month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are zero indexed, so we add 1
        var year = date.getFullYear();
        
        var strTime = day + "/" + month + "/" + year + " - " + hours + ':' + minutes + ' ' + ampm;
        return strTime;
    }

    // toggleSelected
    function toggleSelected() {
        var userChatElement = document.querySelectorAll(".user-chat");
        Array.from(document.querySelectorAll(".chat-user-list li a")).forEach(function (item) {
            item.addEventListener("click", function (event) {
                userChatElement.forEach(function (elm) {
                    elm.classList.add("user-chat-show");
                });

                // chat user list link active
                var chatUserList = document.querySelector(".chat-user-list li.active");
                if (chatUserList) chatUserList.classList.remove("active");
                this.parentNode.classList.add("active");
            });
        });

        // user-chat-remove
        document.querySelectorAll(".user-chat-remove").forEach(function (item) {
            item.addEventListener("click", function (event) {
                userChatElement.forEach(function (elm) {
                    elm.classList.remove("user-chat-show");
                });
            });
        });
    }


    //User current Id
    var currentChatId = "users-chat";
    var currentSelectedChat = "users";
    var url="/user-messages";
    var usersList = "";
    var userChatId = 1;

    scrollToBottom(currentChatId);

    //user list by json
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

    // get User list
    getJSON(url, function (err, data) {
        let isError = false;
        let userList = new Set();
        if (err !== null) {
            console.log("Something went wrong: " + err);
            isError = true;
        } else {            
            let userChatId = data.userID;
            let countRead;
            var notofication = '';

            let maxId = Math.max(...data.messages.map(message => message.id));

            data.messages.forEach(function (message, index) {
                const otherUser = message.from_id === userChatId ? message.to : message.from;
                
                var status;
                var statusClass;
                if (otherUser.status_id == 1) {
                    status = 'Online';
                    statusClass = 'online';
                } else {
                    status = 'Offline';
                    statusClass = 'away';
                }
                
                let userProfilePic = otherUser.avatar ? '<img src="/images/' + otherUser.avatar + '" class="rounded-circle img-fluid userprofile" alt=""><span class="user-status"></span>' : '<div class="avatar-title rounded-circle bg-primary text-white fs-10">' + otherUser.firstname.charAt(0) + '</div><span class="user-status"></span>';

                let activeClass = data.contactUser ? (otherUser.id.toString() === data.contactUser.id.toString() ? "active" : "") : (message.id === maxId ? "active" : "");

                countRead = data.chats.filter(message => message.from_id == otherUser.id && message.to_id == userChatId && message.read == 'No').length;

                if (countRead > 0 && index > 0) {
                    notofication = '<span class="readMsg position-relative topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">\
                                        '+ countRead +'\
                                        <span class="visually-hidden">unread messages</span>\
                                    </span>';
                }
    
                document.getElementById("userList").innerHTML +=
                    '<li id="contact-id-' + otherUser.id + '" data-name="direct-message" data-email="' + otherUser.email + '" data-phone="' + otherUser.phone + '" data-seen="' + otherUser.updated_at_human + '" data-company="' + otherUser.company.name + '" data-position="' + otherUser.position.name + '" class="' + activeClass + '">\
                        <a href="javascript: void(0);">\
                            <div class="d-flex align-items-center">\
                                <div class="flex-shrink-0 chat-user-img ' + statusClass + ' align-self-center me-2 ms-0">\
                                    <div class="avatar-xxs">\
                                    ' + userProfilePic + '\
                                    </div>\
                                </div>\
                                <div class="flex-grow-1 overflow-hidden">\
                                    <p class="text-truncate mb-0">' + otherUser.firstname + ' ' + otherUser.lastname + '</p>\
                                    <p class="text-status mb-0 d-none">' + status + ' </p>\
                                </div>\
                                '+ notofication +'\
                            </div>\
                        </a>\
                    </li>';
    
                userList.add(otherUser.id);  // Adding user to the set to keep track of unique users
            });

            if (data.messages.length > 0) {
                // Make an AJAX POST request to mark the messages as read
                $.ajax({
                    url: route('messages.read'),  // Adjust this if necessary to point to your /message route
                    method: 'POST',
                    data: { userId: data.messages[0].from_id },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            }   

            if (data.contactUser && !document.getElementById("contact-id-" + data.contactUser.id)) {
                // Fetch details of data.contactUser (status, avatar, first name, last name, etc.)
                // You might already have these details available in data.contactUser object
                var status = data.contactUser.status_id === 1 ? 'Online' : 'Offline';
                var statusClass = data.contactUser.status_id === 1 ? 'online' : 'away';
                var userProfilePic = data.contactUser.avatar ? 
                    '<img src="/images/' + data.contactUser.avatar + '" class="rounded-circle img-fluid userprofile" alt=""><span class="user-status"></span>' : 
                    '<div class="avatar-title rounded-circle bg-primary text-white fs-10">' + data.contactUser.firstname.charAt(0) + '</div><span class="user-status"></span>';

                var contactListItemHTML = 
                    '<li id="contact-id-' + data.contactUser.id + '" data-name="direct-message" data-email="' + data.contactUser.email + '" data-phone="' + data.contactUser.phone + '" data-seen="' + data.contactUser.updated_at_human + '" data-company="' + data.contactUser.company.name + '" data-position="' + data.contactUser.position.name + '" class="active">\
                        <a href="javascript: void(0);">\
                            <div class="d-flex align-items-center">\
                                <div class="flex-shrink-0 chat-user-img ' + statusClass + ' align-self-center me-2 ms-0">\
                                    <div class="avatar-xxs">\
                                    ' + userProfilePic + '\
                                    </div>\
                                </div>\
                                <div class="flex-grow-1 overflow-hidden">\
                                    <p class="text-truncate mb-0">' + data.contactUser.firstname + ' ' + data.contactUser.lastname + '</p>\
                                    <p class="text-status mb-0 d-none">' + status + ' </p>\
                                </div>\
                            </div>\
                        </a>\
                    </li>';
                
                // Prepend the new list item to the user list
                document.getElementById("userList").insertAdjacentHTML('afterbegin', contactListItemHTML);

                userList.add(data.contactUser.id);
            }

            // get contacts list
            var usersListContacts = Object.values(data.contacts);
            
            // Create the 'name' attribute and then sort the usersList array
            usersListContacts.forEach(function (user, index) {
                user.name = user.firstname + ' ' + user.lastname;
            });

            usersListContacts.sort(function (a, b) {
                return a.name.localeCompare(b.name); // This will sort the array in alphabetical order
            });

            // set favourite users list
            var msgHTML = "";
            var userNameCharAt = "";

            usersListContacts.forEach(function (user, index) {
                var status;
                var statusClass;
                if (user.status_id == 1) {
                    status = 'Online';
                    statusClass = 'online';
                } else {
                    status = 'Offline';
                    statusClass = 'away';
                }

                var profile = user.avatar
                    ? '<img src="/images/' +
                    user.avatar +
                    '" class="img-fluid rounded-circle" alt="">'
                    : '<span class="avatar-title rounded-circle bg-primary fs-10">' + user.firstname.charAt(0) + '</span>';

                msgHTML =
                    '<li id="user-id-' + user.id + '" data-email="' + user.email + '" data-phone="' + user.phone + '" data-seen="' + user.updated_at_human + '" data-company="' + user.company.name + '" data-position="' + user.position.name + '">\
                        <div class="d-flex align-items-center">\
                            <div class="flex-shrink-0 me-2">\
                                <div class="avatar-xxs">\
                                    ' +
                                profile +
                                '\
                                </div>\
                            </div>\
                            <div class="flex-grow-1">\
                                <p class="text-truncate contactlist-name mb-0">' + user.name + '</p>\
                                <p class="text-status mb-0 d-none">' + status + ' </p>\
                            </div>\
                            <div class="flex-shrink-0">\
                                <div class="dropdown">\
                                    <a href="" class="text-muted" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\
                                        <i class="ri-more-2-fill"></i>\
                                    </a>\
                                    <div class="dropdown-menu dropdown-menu-end">\
                                        <a class="dropdown-item" href=""><i class="ri-pencil-line text-muted me-2 align-bottom"></i>Edit</a>\
                                        <a class="dropdown-item" href=""><i class="ri-forbid-2-line text-muted me-2 align-bottom"></i>Block</a>\
                                        <a class="dropdown-item" href=""><i class="ri-delete-bin-6-line text-muted me-2 align-bottom"></i>Remove</a>\
                                    </div>\
                                </div>\
                            </div>\
                        </div>\
                    </li>';
                var isSortContact =
                    '<div class="mt-3" >\
                        <div class="contact-list-title text-success">' +
                            user.name.charAt(0).toUpperCase() + '\
                        </div>\
                        <ul id="contact-sort-' + user.name.charAt(0) + '" class="list-unstyled contact-list" >';
                        if (userNameCharAt != user.name.charAt(0)) {
                            document.getElementsByClassName("sort-contact")[0].innerHTML +=
                                isSortContact;
                        }
                        document.getElementById(
                            "contact-sort-" + user.name.charAt(0)
                        ).innerHTML =
                            document.getElementById("contact-sort-" + user.name.charAt(0))
                                .innerHTML + msgHTML;
                        userNameCharAt = user.name.charAt(0);
                        + "</ul>" + "</div>";
            });
        }

        let userListArray = Array.from(userList);
        let firstValue;
        if (data.contactUser) {
            firstValue = userListArray[userListArray.length - 1];
        } else {
            firstValue = userListArray[0];
        }        

        // Filter the messages where the userId matches either the from_id or to_id
        var filteredMessages = data.chats
            .filter(message => message.from_id == firstValue || message.to_id == firstValue)
            .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

        updateSelectedChat(filteredMessages, data.userID, isError);
        toggleSelected();
        chatSwap(data.chats, data.userID, isError);
        contactList(data.chats, data.userID, isError);

        if (data.contactID !== null) {
            const contactElement = document.querySelector('#user-id-' + data.contactID);
            if (contactElement) {
                contactElement.click();
            }
        }
    });

    function contactList(messages, userID, isError) {
        document.querySelectorAll(".sort-contact ul li").forEach(function (item) {
            item.addEventListener("click", function (event) {
                var contactId = item.getAttribute("id");
                var userId = contactId.split('-')[2];

                // Filter the messages where the userId matches either the from_id or to_id
                var filteredMessages = messages
                    .filter(message => message.from_id == userId || message.to_id == userId)
                    .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                updateSelectedChat(filteredMessages, userID, isError);
                currentSelectedChat = "users";

                var contactName = item.querySelector("li .contactlist-name").innerHTML;
                var userstatus = item.querySelector(".text-status").innerHTML;
                var statusIconClass = userstatus.trim().toLowerCase() === 'online' ? 'success' : 'warning';
                var statusClass = userstatus.trim().toLowerCase() === 'online' ? 'online' : 'away';
                var email = item.getAttribute('data-email');
                var phone = item.getAttribute('data-phone');
                var lastSeen = item.getAttribute('data-seen');
                var company = item.getAttribute('data-company');
                var position = item.getAttribute('data-position');
                var messageInput = document.getElementById("chat-input");

                messageInput.setAttribute("data-bs-id", userId);

                document.querySelector(".user-chat-topbar .text-truncate .username").innerHTML = contactName;
                document.querySelector(".user-chat-topbar .userStatus").innerHTML = userstatus;
                document.querySelector(".profile-offcanvas .username").innerHTML = contactName;
                document.querySelector(".profile-offcanvas .userStatus i").className = "ri-checkbox-blank-circle-fill me-1 align-bottom text-" + statusIconClass;
                document.querySelector(".profile-offcanvas .userStatus").innerHTML = 
                    `<i class="ri-checkbox-blank-circle-fill me-1 align-bottom text-${statusIconClass}"></i> ${userstatus}`;
                document.querySelector(".profile-offcanvas .userEmail").innerText = email;
                document.querySelector(".profile-offcanvas .userPhone").innerText = phone;
                document.querySelector(".profile-offcanvas .userCompany").innerText = company;
                document.querySelector(".profile-offcanvas .userPosition").innerText = position;

                if (userstatus.trim().toLowerCase() === 'offline') {
                    document.querySelector(".profile-offcanvas .userLastSeen").innerText = 'last seen ' + lastSeen;
                } else {
                    document.querySelector(".profile-offcanvas .userLastSeen").innerText = '';
                }

                // Determine the class based on user status and set it to the appropriate element
                var statusClass = userstatus.trim().toLowerCase() === 'online' ? 'online' : 'away';
                document.querySelector(".user-chat-topbar .chat-user-img").classList.remove('online', 'away');
                document.querySelector(".user-chat-topbar .chat-user-img").classList.add(statusClass);

                var profilePicHTML;
                if (item.querySelector(".avatar-xxs img")) {
                    var profilePicSrc = item.querySelector(".avatar-xxs img").getAttribute('src');
                    profilePicHTML = '<img src="' + profilePicSrc + '" class="rounded-circle img-fluid userprofile" alt=""><span class="user-status"></span>';
                } else {
                    profilePicHTML = '<div class="avatar-title rounded-circle bg-primary text-white fs-10">' + name.charAt(0) + '</div><span class="user-status"></span>';
                }
                var notificationHTML = ''; // Set your notification HTML here based on your logic

                var existingLi = document.querySelector(`#contact-id-${userId}`);
                if (!existingLi) {
                    // Construct the new li HTML string
                    var newLiHTML = `
                        <li id="contact-id-${userId}" data-name="direct-message" data-email="${email}" class="mb-2">
                            <a href="javascript: void(0);">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 chat-user-img ${statusClass} align-self-center me-2 ms-0">
                                        <div class="avatar-xxs">
                                            ${profilePicHTML}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-truncate mb-0">${contactName}</p>
                                        <p class="text-status mb-0 d-none">${userstatus}</p>
                                    </div>
                                    ${notificationHTML}
                                </div>
                            </a>
                        </li>`;
                
                    // Create a temporary div to hold the new li HTML string
                    var tempDiv = document.createElement("div");
                    tempDiv.innerHTML = newLiHTML.trim();
                
                    // Get the newly created li element
                    var newLi = tempDiv.firstChild;
                
                    // Attach the click event listener to the new li element
                    newLi.addEventListener("click", function () {
                        chatSwap(messages, userId, isError);
                    });
                
                    // Add the new li element to userList
                    document.getElementById("userList").appendChild(newLi);
                }

                if (isreplyMessage == true) {
                    isreplyMessage = false;
                    document.querySelector(".replyCard").classList.remove("show");
                }
                
                if (item.querySelector(".align-items-center").querySelector(".avatar-xxs img")) {
                    var contactImg = item.querySelector(".align-items-center").querySelector(".avatar-xxs .rounded-circle").getAttribute("src");
                    document.querySelector(".user-own-img .avatar-xs").setAttribute("src", contactImg);
                    document.querySelector(".profile-offcanvas .profile-img").setAttribute("src", contactImg);
                } else {
                    document.querySelector(".user-own-img .avatar-xs").setAttribute("src", dummyUserImage);
                    document.querySelector(".profile-offcanvas .profile-img").setAttribute("src", dummyUserImage);
                }
                var conversationImg = document.getElementById("users-conversation");
                conversationImg.querySelectorAll(".left .chat-avatar").forEach(function (item3) {
                    if (contactImg) {
                        item3.querySelector("img").setAttribute("src", contactImg);
                    } else {
                        item3.querySelector("img").setAttribute("src", dummyUserImage);
                    }
                });
                window.stop();
            });
        });
    }
    
    // getNextMsgCounts
    function getNextMsgCounts(chatsData, i, from_id) {
        var counts = 0;
        while (chatsData[i]) {
            if (chatsData[i + 1] && chatsData[i + 1]["from_id"] == from_id) {
                counts++;
                i++;
            } else {
                break;
            }
        }
        return counts;
    }

    //getNextMsgs
    function getNextMsgs(chatsData, i, from_id) {
        var msgs = '';
        while (chatsData[i]) {
            if (chatsData[i + 1] && chatsData[i + 1]["from_id"] == from_id) {
                msgs += getMsg(chatsData[i + 1].id, chatsData[i + 1].message, chatsData[i + 1].has_images, chatsData[i + 1].has_files, chatsData[i + 1].has_dropDown);
                i++;
            } else {
                break;
            }
        }
        return {msgs, i};
    }

    // getMesg
    function getMsg(id, message, has_images, has_files, has_dropDown) {
        var msgHTML = '<div class="ctext-wrap">';
        if (message != null) {
            msgHTML += '<div class="ctext-wrap-content" id=' + id + '>\
                            <p class="mb-0 ctext-content">' + message + "</p>\
                        </div>";
        } else if (has_images && has_images.length > 0) {
            msgHTML += '<div class="message-img mb-0">';
            for (i = 0; i < has_images.length; i++) {
                msgHTML +=
                    '<div class="message-img-list">\
                <div>\
                    <a class="popup-img d-inline-block" href="' + has_images[i] + '">\
                        <img src="' + has_images[i] + '" alt="" class="rounded border">\
                    </a>\
                </div>\
                <div class="message-img-link">\
                <ul class="list-inline mb-0">\
                    <li class="list-inline-item dropdown">\
                        <a class="dropdown-toggle" href="" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\
                            <i class="ri-more-fill"></i>\
                        </a>\
                        <div class="dropdown-menu">\
                            <a class="dropdown-item" href="' + has_images[i] + '" download=""><i class="ri-download-2-line me-2 text-muted align-bottom"></i>Download</a>\
                            <a class="dropdown-item" href=""><i class="ri-reply-line me-2 text-muted align-bottom"></i>Reply</a>\
                            <a class="dropdown-item" href=""><i class="ri-share-line me-2 text-muted align-bottom"></i>Forward</a>\
                            <a class="dropdown-item" href=""><i class="ri-bookmark-line me-2 text-muted align-bottom"></i>Bookmark</a>\
                            <a class="dropdown-item delete-image" href=""><i class="ri-delete-bin-5-line me-2 text-muted align-bottom"></i>Delete</a>\
                        </div>\
                    </li>\
                </ul>\
                </div>\
            </div>';
            }
            msgHTML += "</div>";
        } else if (has_files && has_files.length > 0) {
            msgHTML +=
                '<div class="ctext-wrap-content">\
            <div class="p-3 border-primary border rounded-3">\
            <div class="d-flex align-items-center attached-file">\
                <div class="flex-shrink-0 avatar-sm me-3 ms-0 attached-file-avatar">\
                    <div class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-20">\
                        <i class="ri-attachment-2"></i>\
                    </div>\
                </div>\
                <div class="flex-grow-1 overflow-hidden">\
                    <div class="text-start">\
                        <h5 class="font-size-14 mb-1">design-phase-1-approved.pdf</h5>\
                        <p class="text-muted text-truncate font-size-13 mb-0">12.5 MB</p>\
                    </div>\
                </div>\
                <div class="flex-shrink-0 ms-4">\
                    <div class="d-flex gap-2 font-size-20 d-flex align-items-start">\
                        <div>\
                            <a href="" class="text-muted">\
                                <i class="bx bxs-download"></i>\
                            </a>\
                        </div>\
                    </div>\
                </div>\
            </div>\
            </div>\
        </div>';
        }
        if (message != null) {
            msgHTML +=
                '<div class="dropdown align-self-start message-box-drop">\
                <a class="dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">\
                    <i class="ri-more-2-fill"></i>\
                </a>\
                <div class="dropdown-menu">\
                    <a class="dropdown-item copy-message" href="javascript: void(0);"><i class="ri-file-copy-line me-2 text-muted align-bottom"></i>Copy</a>\
                    <a class="dropdown-item delete-item" href="javascript: void(0);"><i class="ri-delete-bin-5-line me-2 text-muted align-bottom"></i>Delete</a>\
                </div>\
            </div>'
        }
        msgHTML += '</div>';
        return msgHTML;
    }

    function updateSelectedChat(filteredMessages, userID, isError) {
        if (currentSelectedChat == "users") {
            document.getElementById("channel-chat").style.display = "none";
            document.getElementById("users-chat").style.display = "block";
            getChatMessages(filteredMessages, userID, isError);
        } else {
            document.getElementById("channel-chat").style.display = "block";
            document.getElementById("users-chat").style.display = "none";
            getChatMessages(filteredMessages, userID, isError);
        }
    }

    //Chat Message
    function getChatMessages(data, userID, isError) {
        if (!isError) {
            document.getElementById("elmLoader").innerHTML = '';
        }
    
        var chatsData = currentSelectedChat == "users" ? data : data;
        document.getElementById(currentSelectedChat + "-conversation").innerHTML = "";
        
        for (let index = 0; index < chatsData.length; index++) {
            let isChat = chatsData[index];
            let userChatId = userID;
            var isAlighn = isChat.from_id == userChatId ? " right" : " left";
    
            var usersList = Object.values(data);
    
            var user = usersList.find(function (list) {
                return list.to_id == isChat.to_id;
            });
    
            var msgHTML = '<li class="chat-list' + isAlighn + '" id=' + isChat.id + '>\
                <div class="conversation-list">';
            if (userChatId != isChat.from_id)
                msgHTML += '<div class="chat-avatar"><img src="/images/' + user.from.avatar + '" alt=""></div>';
    
            msgHTML += '<div class="user-chat-content">';
            msgHTML += getMsg(isChat.id, isChat.message, isChat.has_images, isChat.has_files, isChat.has_dropDown);
            
            while (chatsData[index + 1] && isChat.from_id == chatsData[index + 1]["from_id"]) {
                let result = getNextMsgs(chatsData, index, isChat.from_id);
                msgHTML += result.msgs;
                index = result.i; // update index to skip messages that were added in getNextMsgs
            }
    
            msgHTML +=
                '<div class="conversation-name"><span class="d-none name">'+user.name+'</span><small class="text-muted time">'+ formatTime(isChat.created_at) +
                '</small> <span class="text-success check-message-icon"><i class="bx bx-check-double"></i></span></div>';
            msgHTML += "</div>\
                            </div>\
                        </li>";
    
            document.getElementById(currentSelectedChat + "-conversation").innerHTML += msgHTML;
        }
    
        deleteMessage();
        deleteChannelMessage();
        deleteImage();
        replyMessage();
        replyChannelMessage();
        copyMessage();
        copyChannelMessage();
        copyClipboard();
        scrollToBottom("users-chat");
        updateLightbox();
    }

    // GLightbox Popup
    function updateLightbox() {
        var lightbox = GLightbox({
            selector: ".popup-img",
            title: false,
        });
    }

    // // Scroll to Bottom
    function scrollToBottom(id) {
        setTimeout(function () {
            var simpleBar = (document.getElementById(id).querySelector("#chat-conversation .simplebar-content-wrapper")) ?
                document.getElementById(id).querySelector("#chat-conversation .simplebar-content-wrapper") : ''

            var offsetHeight = document.getElementsByClassName("chat-conversation-list")[0] ?
                document.getElementById(id).getElementsByClassName("chat-conversation-list")[0].scrollHeight - window.innerHeight + 335 : 0;
            if (offsetHeight)
                simpleBar.scrollTo({
                    top: offsetHeight,
                    behavior: "smooth"
                });
        }, 100);
    }

    //chat form
    var chatForm = document.querySelector("#chatinput-form");
    var chatInput = document.querySelector("#chat-input");
    var chatInputfeedback = document.querySelector(".chat-input-feedback");

    function currentTime() {
        let now = new Date();
        let day = now.getDate().toString().padStart(2, '0');
        let month = (now.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-11 in JavaScript
        let year = now.getFullYear();
    
        let hour = now.getHours();
        let ampm = hour >= 12 ? "pm" : "am";
        hour = hour % 12;
        hour = hour ? hour : 12; // the hour '0' should be '12'
    
        let minute = now.getMinutes().toString().padStart(2, '0');
        
        let formattedTime = `${day}/${month}/${year} - ${hour}:${minute} ${ampm}`;
        return formattedTime;
    }
    setInterval(currentTime, 1000);
    

    var messageIds = 0;

    if (chatForm) {
        //add an item to the List, including to local storage
        chatForm.addEventListener("submit", function (e) {
            e.preventDefault();

            var userIDChat = document.getElementById("chat-input").getAttribute('data-bs-id');
            var chatId = currentChatId;
            var chatReplyId = currentChatId;

            var chatInputValue = chatInput.value

            if (chatInputValue.length > 0 && userIDChat > 0) {
                var formData = new FormData();

                formData.append('user_id', userIDChat);
                formData.append('message', chatInputValue);

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: route('message.store'),
                    method: 'POST',
                    data: formData,
                    async: true,
                    processData: false,
                    contentType: false,
                    success:function(data){                
                        if(data.success == true){
                            if (isreplyMessage == true) {
                                getReplyChatList(chatReplyId, chatInputValue);
                                isreplyMessage = false;
                            } else {
                                getChatList(chatId, chatInputValue);
                            }
                            scrollToBottom(chatId || chatReplyId);

                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: data.message,
                                showConfirmButton: false,
                                timer: 2000,
                                toast: true,
                                showCloseButton: true
                            });   
                        } else {
                            Swal.fire({
                                html: '<div class="mt-3">' + 
                                        '<lord-icon src="https://cdn.lordicon.com/tdrtiskw.json" trigger="loop" colors="primary:#f06548,secondary:#f7b84b" style="width:120px;height:120px"></lord-icon>' + 
                                        '<div class="mt-4 pt-2 fs-15">' + 
                                            '<h4>Oops...! Something went Wrong !</h4>' + 
                                            '<div class="accordion" id="default-accordion-example">' +
                                                '<div class="accordion-item">' +
                                                    '<h2 class="accordion-header" id="headingOne">' +
                                                        '<button class="accordion-button d-block text-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne" style="background-color:rgba(169,50,38,0.1); color:#C0392B;">' +
                                                            'Show Error Message' +
                                                        '</button>' +
                                                    '</h2>' +
                                                    '<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#default-accordion-example">' +
                                                        '<div class="accordion-body">' +
                                                            data.error +
                                                        '</div>' +
                                                    '</div>' +
                                                '</div>' +
                                            '</div>' +
                                        '</div>' + 
                                    '</div>',
                                showCancelButton: true,
                                showConfirmButton: false,
                                cancelButtonClass: 'btn btn-primary w-xs mb-1',
                                cancelButtonText: 'Dismiss',
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        if(jqXHR.status == 422) {
                            $("#requiredAlert").addClass("show");
                            var errors = $.parseJSON(jqXHR.responseText);
                            $.each(errors.errors, function(key, val){
                                $("input[name='" + key + "']").addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
            
                            setTimeout(function() {
                                $("#requiredAlert").removeClass("show");
                            }, 10000);
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
            } else {
                chatInputfeedback.classList.add("show");
                setTimeout(function () {
                    chatInputfeedback.classList.remove("show");
                }, 2000);
            }
            chatInput.value = "";

            //reply msg remove textarea
            document.getElementById("close_toggle").click();
        })
    }

    //user Name and user Profile change on click
    function chatSwap(messages, userID, isError) {
        document.querySelectorAll("#userList li").forEach(function (item) {
            item.addEventListener("click", function () {
                currentSelectedChat = "users";
                currentChatId = "users-chat";
                var contactId = item.getAttribute("id");
                var userId = contactId.split('-')[2];

                // Filter the messages where the userId matches either the from_id or to_id
                var filteredMessages = messages
                    .filter(message => message.from_id == userId || message.to_id == userId)
                    .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                updateSelectedChat(filteredMessages, userID, isError);
                var username = item.querySelector(".text-truncate").innerHTML;
                var userstatus = item.querySelector(".text-status").innerHTML;
                var statusIconClass = userstatus.trim().toLowerCase() === 'online' ? 'success' : 'warning';
                var email = item.getAttribute('data-email');
                var phone = item.getAttribute('data-phone');
                var lastSeen = item.getAttribute('data-seen');
                var company = item.getAttribute('data-company');
                var position = item.getAttribute('data-position');
                var messageInput = document.getElementById("chat-input");

                messageInput.setAttribute("data-bs-id", userId);

                if (item.querySelector(".readMsg")) {
                    item.querySelector(".readMsg").remove();
                }

                document.querySelector(".user-chat-topbar .text-truncate .username").innerHTML = username;
                document.querySelector(".user-chat-topbar .userStatus").innerHTML = userstatus;
                document.querySelector(".profile-offcanvas .username").innerHTML = username;
                document.querySelector(".profile-offcanvas .userStatus i").className = "ri-checkbox-blank-circle-fill me-1 align-bottom text-" + statusIconClass;
                document.querySelector(".profile-offcanvas .userStatus").innerHTML = 
                    `<i class="ri-checkbox-blank-circle-fill me-1 align-bottom text-${statusIconClass}"></i> ${userstatus}`;
                document.querySelector(".profile-offcanvas .userEmail").innerText = email;
                document.querySelector(".profile-offcanvas .userPhone").innerText = phone;
                document.querySelector(".profile-offcanvas .userCompany").innerText = company;
                document.querySelector(".profile-offcanvas .userPosition").innerText = position;

                if (userstatus.trim().toLowerCase() === 'offline') {
                    document.querySelector(".profile-offcanvas .userLastSeen").innerText = 'last seen ' + lastSeen;
                } else {
                    document.querySelector(".profile-offcanvas .userLastSeen").innerText = '';
                }

                // Determine the class based on user status and set it to the appropriate element
                var statusClass = userstatus.trim().toLowerCase() === 'online' ? 'online' : 'away';
                document.querySelector(".user-chat-topbar .chat-user-img").classList.remove('online', 'away');
                document.querySelector(".user-chat-topbar .chat-user-img").classList.add(statusClass);

                if (isreplyMessage == true) {
                    isreplyMessage = false;
                    document.querySelector(".replyCard").classList.remove("show");
                }

                if (document.getElementById(contactId).querySelector(".userprofile")) {
                    var userProfile = document.getElementById(contactId).querySelector(".userprofile").getAttribute("src");
                    document.querySelector(".user-chat-topbar .avatar-xs").setAttribute("src", userProfile);
                    document.querySelector(".profile-offcanvas .avatar-lg").setAttribute("src", userProfile);
                } else {
                    document.querySelector(".user-chat-topbar .avatar-xs").setAttribute("src", dummyUserImage);
                    document.querySelector(".profile-offcanvas .avatar-lg").setAttribute("src", dummyUserImage);
                }

                var conversationImg = document.getElementById("users-conversation");
                conversationImg.querySelectorAll(".left .chat-avatar").forEach(function (item) {
                    if (userProfile) {
                        item.querySelector("img").setAttribute("src", userProfile);
                    } else {
                        item.querySelector("img").setAttribute("src", dummyUserImage);
                    }
                });

                // Make an AJAX POST request to mark the messages as read
                $.ajax({
                    url: route('messages.read'),  // Adjust this if necessary to point to your /message route
                    method: 'POST',
                    data: { userId: userId },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                window.stop();
            });
        });
    };

    //Copy Message to clipboard
    var itemList = document.querySelector(".chat-conversation-list");
    function copyMessage() {
        var copyMessage = itemList.querySelectorAll(".copy-message");
        copyMessage.forEach(function (item) {
            item.addEventListener("click", function () {
                var isText = item.closest(".ctext-wrap").children[0]
                    ? item.closest(".ctext-wrap").children[0].children[0].innerText
                    : "";
                navigator.clipboard.writeText(isText);
            });
        });
    }

    function copyChannelMessage() {
        var copyChannelMessage = channelItemList.querySelectorAll(".copy-message");
        copyChannelMessage.forEach(function (item) {
            item.addEventListener("click", function () {
                var isText = item.closest(".ctext-wrap").children[0]
                    ? item.closest(".ctext-wrap").children[0].children[0].innerText
                    : "";
                navigator.clipboard.writeText(isText);
            });
        });
    }


    //Copy Message Alert
    function copyClipboard() {
        var copyClipboardAlert = document.querySelectorAll(".copy-message");
        copyClipboardAlert.forEach(function (item) {
            item.addEventListener("click", function () {
                document.getElementById("copyClipBoard").style.display = "block";
                document.getElementById("copyClipBoardChannel").style.display = "block";
                setTimeout(hideclipboard, 1000);
                function hideclipboard() {
                    document.getElementById("copyClipBoard").style.display = "none";
                    document.getElementById("copyClipBoardChannel").style.display =
                        "none";
                }
            });
        });
    }

    //Delete Message 
    function deleteMessage() {
        var deleteItems = document.querySelectorAll(".delete-item");
        deleteItems.forEach(function (item) {
            item.addEventListener("click", function () {
                var ctextWrap = item.closest(".ctext-wrap");
                var ctextWrapContent = ctextWrap.querySelector(".ctext-wrap-content");
                var messageID = ctextWrapContent.id;

                $.ajax({
                    url: route('message.destroy', {id: messageID}),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        if (data.success === true) {
                            // Removing the element after successful deletion on the server
                            if (item.closest(".user-chat-content").childElementCount == 2) {
                                item.closest(".chat-list").remove();
                            } else {
                                ctextWrap.remove();
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
                        } else {
                            Swal.fire({
                                html: '<div class="mt-3">' + 
                                        '<lord-icon src="https://cdn.lordicon.com/tdrtiskw.json" trigger="loop" colors="primary:#f06548,secondary:#f7b84b" style="width:120px;height:120px"></lord-icon>' + 
                                        '<div class="mt-4 pt-2 fs-15">' + 
                                            '<h4>Oops...! Something went Wrong !</h4>' + 
                                            '<div class="accordion" id="default-accordion-example">' +
                                                '<div class="accordion-item">' +
                                                    '<h2 class="accordion-header" id="headingOne">' +
                                                        '<button class="accordion-button d-block text-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne" style="background-color:rgba(169,50,38,0.1); color:#C0392B;">' +
                                                            'Show Error Message' +
                                                        '</button>' +
                                                    '</h2>' +
                                                    '<div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#default-accordion-example">' +
                                                        '<div class="accordion-body">' +
                                                            data.error +
                                                        '</div>' +
                                                    '</div>' +
                                                '</div>' +
                                            '</div>' +
                                        '</div>' + 
                                    '</div>',
                                showCancelButton: true,
                                showConfirmButton: false,
                                cancelButtonClass: 'btn btn-primary w-xs mb-1',
                                cancelButtonText: 'Dismiss',
                                buttonsStyling: false,
                                showCloseButton: true
                            })
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        if(jqXHR.status == 422) {
                            $("#requiredAlert").addClass("show");
                            var errors = $.parseJSON(jqXHR.responseText);
                            $.each(errors.errors, function(key, val){
                                $("input[name='" + key + "']").addClass("is-invalid");
                                $("#" + key + "_error").text(val[0]);
                            });
            
                            setTimeout(function() {
                                $("#requiredAlert").removeClass("show");
                            }, 10000);
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
        });
    }

    //Delete Image 
    function deleteImage() {
        var deleteImage = itemList.querySelectorAll(".chat-conversation-list .chat-list");
        deleteImage.forEach(function (item) {
            item.querySelectorAll(".delete-image").forEach(function (subitem) {
                subitem.addEventListener("click", function () {
                    subitem.closest(".message-img").childElementCount == 1
                        ? subitem.closest(".chat-list").remove()
                        : subitem.closest(".message-img-list").remove();
                });
            });
        });
    }
    deleteImage();

    //Delete Channel Message
    var channelItemList = document.querySelector("#channel-conversation");
    function deleteChannelMessage() {
        channelChatList = channelItemList.querySelectorAll(".delete-item");
        channelChatList.forEach(function (item) {
            item.addEventListener("click", function () {
                item.closest(".user-chat-content").childElementCount == 2
                    ? item.closest(".chat-list").remove()
                    : item.closest(".ctext-wrap").remove();
            });
        });
    }
    deleteChannelMessage();

    //Reply Message
    function replyMessage() {
        var replyMessage = itemList.querySelectorAll(".reply-message");
        var replyToggleOpen = document.querySelector(".replyCard");
        var replyToggleClose = document.querySelector("#close_toggle");

        replyMessage.forEach(function (item) {
            item.addEventListener("click", function () {
                isreplyMessage = true;
                replyToggleOpen.classList.add("show");
                replyToggleClose.addEventListener("click", function () {
                    replyToggleOpen.classList.remove("show");
                });

                var replyMsg = item.closest(".ctext-wrap").children[0].children[0].innerText;
                document.querySelector(".replyCard .replymessage-block .flex-grow-1 .mb-0").innerText = replyMsg;
                var replyuser = document.querySelector(".user-chat-topbar .text-truncate .username").innerHTML;
                var msgOwnerName = (item.closest(".chat-list")) ? item.closest(".chat-list").classList.contains("left") ? replyuser : 'You' : replyuser;
                document.querySelector(".replyCard .replymessage-block .flex-grow-1 .conversation-name").innerText = msgOwnerName;
            });
        });
    }

    //reply Channelmessage
    function replyChannelMessage() {
        var replyChannelMessage = channelItemList.querySelectorAll(".reply-message");
        var replyChannelToggleOpen = document.querySelector(".replyCard");
        var replyChannelToggleClose = document.querySelector("#close_toggle");

        replyChannelMessage.forEach(function (item) {
            item.addEventListener("click", function () {
                isreplyMessage = true;
                replyChannelToggleOpen.classList.add("show");
                replyChannelToggleClose.addEventListener("click", function () {
                    replyChannelToggleOpen.classList.remove("show");
                });
                var replyChannelMsg = item.closest(".ctext-wrap").children[0].children[0].innerText;
                document.querySelector(".replyCard .replymessage-block .flex-grow-1 .mb-0").innerText = replyChannelMsg;
                var replyChanneluser = item.closest(".user-chat-content").querySelector(".conversation-name .name").innerText;
                var msgOwnerName = (item.closest(".chat-list")) ? item.closest(".chat-list").classList.contains("left") ? replyChanneluser : 'You' : replyChanneluser;
                document.querySelector(".replyCard .replymessage-block .flex-grow-1 .conversation-name").innerText = msgOwnerName;
            });
        });
    }

    //Append New Message
    var getChatList = function (chatid, chatItems) {
        messageIds++;
        var chatConList = document.getElementById(chatid);
        var itemList = chatConList.querySelector(".chat-conversation-list");

        if (chatItems != null) {
            itemList.insertAdjacentHTML(
                "beforeend",
                '<li class="chat-list right" id="chat-list-' +
                messageIds +
                '" >\
                <div class="conversation-list">\
                    <div class="user-chat-content">\
                        <div class="ctext-wrap">\
                            <div class="ctext-wrap-content">\
                                <p class="mb-0 ctext-content">\
                                    ' +
                chatItems + '\
                                </p>\
                            </div>\
                            <div class="dropdown align-self-start message-box-drop">\
                                <a class="dropdown-toggle" href="" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\
                                    <i class="ri-more-2-fill"></i>\
                                </a>\
                                <div class="dropdown-menu">\
                                    <a class="dropdown-item reply-message" href=""><i class="ri-reply-line me-2 text-muted align-bottom"></i>Reply</a>\
                                    <a class="dropdown-item" href=""><i class="ri-share-line me-2 text-muted align-bottom"></i>Forward</a>\
                                    <a class="dropdown-item copy-message" href="""><i class="ri-file-copy-line me-2 text-muted align-bottom"></i>Copy</a>\
                                    <a class="dropdown-item" href=""><i class="ri-bookmark-line me-2 text-muted align-bottom"></i>Bookmark</a>\
                                    <a class="dropdown-item delete-item" href=""><i class="ri-delete-bin-5-line me-2 text-muted align-bottom"></i>Delete</a>\
                            </div>\
                        </div>\
                    </div>\
                    <div class="conversation-name">\
                        <small class="text-muted time">' +
                currentTime() +
                '</small>\
                        <span class="text-success check-message-icon"><i class="bx bx-check"></i></span>\
                    </div>\
                </div>\
            </div>\
        </li>'
            );
        }

        // remove chat list
        var newChatList = document.getElementById("chat-list-" + messageIds);
        newChatList.querySelectorAll(".delete-item").forEach(function (subitem) {
            subitem.addEventListener("click", function () {
                itemList.removeChild(newChatList);
            });
        });

        //Copy Message
        newChatList.querySelectorAll(".copy-message").forEach(function (subitem) {
            subitem.addEventListener("click", function () {
                var currentValue =
                    newChatList.childNodes[1].firstElementChild.firstElementChild
                        .firstElementChild.firstElementChild.innerText;
                navigator.clipboard.writeText(currentValue);
            });
        });

        //Copy Clipboard alert
        newChatList.querySelectorAll(".copy-message").forEach(function (subitem) {
            subitem.addEventListener("click", function () {
                document.getElementById("copyClipBoard").style.display = "block";
                setTimeout(hideclipboardNew, 1000);

                function hideclipboardNew() {
                    document.getElementById("copyClipBoard").style.display = "none";
                }
            });
        });

        //reply Message model    
        newChatList.querySelectorAll(".reply-message").forEach(function (subitem) {
            subitem.addEventListener("click", function () {
                var replyToggleOpenNew = document.querySelector(".replyCard");
                var replyToggleCloseNew = document.querySelector("#close_toggle");
                var replyMessageNew = subitem.closest(".ctext-wrap").children[0].children[0].innerText;
                var replyUserNew = document.querySelector(".replyCard .replymessage-block .flex-grow-1 .conversation-name").innerHTML;
                isreplyMessage = true;
                replyToggleOpenNew.classList.add("show");
                replyToggleCloseNew.addEventListener("click", function () {
                    replyToggleOpenNew.classList.remove("show");
                });
                var msgOwnerName = (subitem.closest(".chat-list")) ? subitem.closest(".chat-list").classList.contains("left") ? replyUserNew : 'You' : replyUserNew;
                document.querySelector(".replyCard .replymessage-block .flex-grow-1 .conversation-name").innerText = msgOwnerName;
                document.querySelector(".replyCard .replymessage-block .flex-grow-1 .mb-0").innerText = replyMessageNew;
            });
        });
    };

    var messageboxcollapse = 0;

    //message with reply
    var getReplyChatList = function (chatReplyId, chatReplyItems) {
        var chatReplyUser = document.querySelector(".replyCard .replymessage-block .flex-grow-1 .conversation-name").innerHTML;
        var chatReplyMessage = document.querySelector(".replyCard .replymessage-block .flex-grow-1 .mb-0").innerText;

        messageIds++;
        var chatreplyConList = document.getElementById(chatReplyId);
        var itemReplyList = chatreplyConList.querySelector(".chat-conversation-list");
        if (chatReplyItems != null) {
            itemReplyList.insertAdjacentHTML(
                "beforeend",
                '<li class="chat-list right" id="chat-list-' + messageIds + '" >\
                <div class="conversation-list">\
                    <div class="user-chat-content">\
                        <div class="ctext-wrap">\
                            <div class="ctext-wrap-content">\
                            <div class="replymessage-block mb-0 d-flex align-items-start">\
                        <div class="flex-grow-1">\
                            <h5 class="conversation-name">' + chatReplyUser + '</h5>\
                            <p class="mb-0">' + chatReplyMessage + '</p>\
                        </div>\
                        <div class="flex-shrink-0">\
                            <button type="button" class="btn btn-sm btn-link mt-n2 me-n3 font-size-18">\
                            </button>\
                        </div>\
                    </div>\
                                <p class="mb-0 ctext-content mt-1">\
                                    ' + chatReplyItems + '\
                                </p>\
                            </div>\
                            <div class="dropdown align-self-start message-box-drop">\
                                <a class="dropdown-toggle" href="" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\
                                    <i class="ri-more-2-fill"></i>\
                                </a>\
                                <div class="dropdown-menu">\
                                    <a class="dropdown-item reply-message" href=""><i class="ri-reply-line me-2 text-muted align-bottom"></i>Reply</a>\
                                    <a class="dropdown-item" href=""><i class="ri-share-line me-2 text-muted align-bottom"></i>Forward</a>\
                                    <a class="dropdown-item copy-message" href=""><i class="ri-file-copy-line me-2 text-muted align-bottom"></i>Copy</a>\
                                    <a class="dropdown-item" href=""><i class="ri-bookmark-line me-2 text-muted align-bottom"></i>Bookmark</a>\
                                    <a class="dropdown-item delete-item" href=""><i class="ri-delete-bin-5-line me-2 text-muted align-bottom"></i>Delete</a>\
                            </div>\
                        </div>\
                    </div>\
                    <div class="conversation-name">\
                        <small class="text-muted time">' + currentTime() + '</small>\
                        <span class="text-success check-message-icon"><i class="bx bx-check"></i></span>\
                    </div>\
                </div>\
            </div>\
        </li>'
            );
            messageboxcollapse++;
        }

        // remove chat list
        var newChatList = document.getElementById("chat-list-" + messageIds);
        newChatList.querySelectorAll(".delete-item").forEach(function (subitem) {
            subitem.addEventListener("click", function () {
                itemList.removeChild(newChatList);
            });
        });

        //Copy Clipboard alert
        newChatList.querySelectorAll(".copy-message").forEach(function (subitem) {
            subitem.addEventListener("click", function () {
                document.getElementById("copyClipBoard").style.display = "block";
                document.getElementById("copyClipBoardChannel").style.display = "block";
                setTimeout(hideclipboardNew, 1000);

                function hideclipboardNew() {
                    document.getElementById("copyClipBoard").style.display = "none";
                    document.getElementById("copyClipBoardChannel").style.display = "none";
                }
            });
        });

        newChatList.querySelectorAll(".reply-message").forEach(function (subitem) {
            subitem.addEventListener("click", function () {
                var replyMessage = subitem.closest(".ctext-wrap").children[0].children[0].innerText;
                var replyuser = document.querySelector(".user-chat-topbar .text-truncate .username").innerHTML;
                document.querySelector(".replyCard .replymessage-block .flex-grow-1 .mb-0").innerText = replyMessage;
                var msgOwnerName = (subitem.closest(".chat-list")) ? subitem.closest(".chat-list").classList.contains("left") ? replyuser : 'You' : replyuser;
                document.querySelector(".replyCard .replymessage-block .flex-grow-1 .conversation-name").innerText = msgOwnerName;
            });
        });

        //Copy Message
        newChatList.querySelectorAll(".copy-message").forEach(function (subitem) {
            subitem.addEventListener("click", function () {
                newChatList.childNodes[1].children[1].firstElementChild.firstElementChild.getAttribute("id");
                isText = newChatList.childNodes[1].children[1].firstElementChild.firstElementChild.innerText;
                navigator.clipboard.writeText(isText);
            });
        });
    };


    var emojiPicker = new FgEmojiPicker({
        trigger: [".emoji-btn"],
        removeOnSelection: false,
        closeButton: true,
        position: ["top", "right"],
        preFetch: true,
        dir: "build/js/pages/plugins/json",
        insertInto: document.querySelector(".chat-input"),
    });

    // emojiPicker position
    var emojiBtn = document.getElementById("emoji-btn");
    emojiBtn.addEventListener("click", function () {
        setTimeout(function () {
            var fgEmojiPicker = document.getElementsByClassName("fg-emoji-picker")[0];
            if (fgEmojiPicker) {
                var leftEmoji = window.getComputedStyle(fgEmojiPicker) ? window.getComputedStyle(fgEmojiPicker).getPropertyValue("left") : "";
                if (leftEmoji) {
                    leftEmoji = leftEmoji.replace("px", "");
                    leftEmoji = leftEmoji - 40 + "px";
                    fgEmojiPicker.style.left = leftEmoji;
                }
            }
        }, 0);
    });

})();
//Search Message
function searchMessages() {
    var searchInput, searchFilter, searchUL, searchLI, a, i, txtValue;
    searchInput = document.getElementById("searchMessage");
    searchFilter = searchInput.value.toUpperCase();
    searchUL = document.getElementById("users-conversation");
    searchLI = searchUL.getElementsByTagName("li");
    searchLI.forEach(function (search) {
        a = search.getElementsByTagName("p")[0] ? search.getElementsByTagName("p")[0] : '';
        txtValue = a.textContent || a.innerText ? a.textContent || a.innerText : '';
        if (txtValue.toUpperCase().indexOf(searchFilter) > -1) {
            search.style.display = "";
        } else {
            search.style.display = "none";
        }
    });
};


// chat-conversation
var scrollEl = new SimpleBar(document.getElementById('chat-conversation'));
scrollEl.getScrollElement().scrollTop = document.getElementById("users-conversation").scrollHeight;