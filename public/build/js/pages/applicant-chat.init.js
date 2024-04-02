/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Chat init js
*/

(function () {
    //User current Id
    var currentChatId = "users-chat";
    var currentSelectedChat = "users";
    var usersList = "";
    var userChatId = 1;

    scrollToBottom(currentChatId);

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
    function getNextMsgs(chatsData, i, from_id, isContinue) {
        var msgs = "";
        while (chatsData[i]) {
            if (chatsData[i + 1] && chatsData[i + 1]["from_id"] == from_id) {
                msgs += getMsg(chatsData[i + 1].id, chatsData[i + 1].msg, chatsData[i + 1].has_images, chatsData[i + 1].has_files, chatsData[i + 1].has_dropDown, false);
                i++;
            } else {
                break;
            }
        }
        return msgs;
    }

    // getMeg
    function getMsg(id, msg, has_images, has_files, has_dropDown, includeWrapper = true) {
        var msgHTML = includeWrapper ? '<div class="ctext-wrap">' : '';
        if (msg != null) {
            msgHTML += '<div class="ctext-wrap-content" id=' + id + '><p class="mb-0 ctext-content">' + msg + '</p></div>\
                        <div class="dropdown align-self-start message-box-drop">\
                            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\
                                <i class="ri-more-2-fill"></i>\
                            </a>\
                            <div class="dropdown-menu">\
                                <a class="dropdown-item copy-message"><i class="ri-file-copy-line me-2 text-muted align-bottom"></i>Copy</a>\
                            </div>\
                        </div>';
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
                                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\
                                    <i class="ri-more-fill"></i>\
                                </a>\
                                <div class="dropdown-menu">\
                                    <a class="dropdown-item" href="' + has_images[i] + '" download=""><i class="ri-download-2-line me-2 text-muted align-bottom"></i>Download</a>\
                                    <a class="dropdown-item" href="#"><i class="ri-reply-line me-2 text-muted align-bottom"></i>Reply</a>\
                                    <a class="dropdown-item" href="#"><i class="ri-share-line me-2 text-muted align-bottom"></i>Forward</a>\
                                    <a class="dropdown-item" href="#"><i class="ri-bookmark-line me-2 text-muted align-bottom"></i>Bookmark</a>\
                                    <a class="dropdown-item delete-image" href="#"><i class="ri-delete-bin-5-line me-2 text-muted align-bottom"></i>Delete</a>\
                                </div>\
                            </li>\
                        </ul>\
                        </div>\
                    </div>';
            }
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
                                    <a href="#" class="text-muted">\
                                        <i class="bx bxs-download"></i>\
                                    </a>\
                                </div>\
                            </div>\
                        </div>\
                    </div>\
                    </div>\
                </div>';
        }
        msgHTML += includeWrapper ? '</div>' : '';
        return msgHTML;
    }

    function updateSelectedChat() {
        if (currentSelectedChat == "users") {
            document.getElementById("users-chat").style.display = "block";
            getChatMessages();
        } else {
            document.getElementById("users-chat").style.display = "none";
            getChatMessages();
        }
    }
    updateSelectedChat();


    //Chat Message
    function getChatMessages() {
        document.getElementById(currentSelectedChat + "-conversation").innerHTML = "";
        
        chatsData.forEach(function (isChat, index) {

            var isAlighn = isChat.type_id == 1 ? " left" : " right";

            var msgHTML = '<li class="chat-list' + isAlighn + '" id="message-' + isChat.id + '">\
                                <div class="conversation-list">';

                                    if (isChat.type_id == 1) {
                                        msgHTML += '<div class="chat-avatar"><img src="' + isChat.applicant.avatar + '" alt=""></div>';
                                    } else if (isChat.type_id == 2) {
                                        msgHTML += '<div class="chat-avatar"><img src="images/shoops.png" alt="shoops"></div>';
                                    }

                                    msgHTML += '<div class="user-chat-content">';
                                    msgHTML += getMsg(isChat.id, isChat.message, isChat.has_images, isChat.has_files, isChat.has_dropDown);

                                    if (chatsData[index + 1] && isChat.from_id == chatsData[index + 1]["from_id"]) {
                                        isContinue = getNextMsgCounts(chatsData, index, isChat.from_id);
                                        msgHTML += getNextMsgs(chatsData, index, isChat.from_id, isContinue);
                                    }

                                    msgHTML += '<div class="conversation-name"><span class="d-none name">' + (isChat.type_id == 1 ? 'Shoops' : isChat.applicant.firstname) + '</span><small class="text-muted time">' + formatDate(isChat.created_at) + '</small> <span class="text-success check-message-icon"><i class="bx bx-check-double"></i></span></div>';  // Note: I changed isChat.datetime to isChat.created_at based on your data structure
                                    msgHTML += "</div>\
                                </div>\
                            </li>";

            document.getElementById(currentSelectedChat + "-conversation").innerHTML += msgHTML;
        });

        var copyMessageElements = document.querySelectorAll(".copy-message");
        copyMessageElements.forEach(attachCopyMessageEventListener);
        scrollToBottom("users-chat");
        updateLightbox();
    }

    // Format Date
    function formatDate(dateString) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const date = new Date(dateString);
    
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        let hour = date.getHours();
        const minute = date.getMinutes();
        const ampm = hour >= 12 ? 'pm' : 'am';
    
        hour = hour % 12;
        hour = hour ? hour : 12; // the hour '0' should be '12'
        const minuteStr = minute < 10 ? '0' + minute : minute;
    
        return `${day} ${month} ${year} ${hour}:${minuteStr}${ampm}`;
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
            var chatContainer = document.getElementById('chat-conversation');
            if (chatContainer) {
                var simpleBarContentWrapper = chatContainer.querySelector(".simplebar-content-wrapper");
                if (simpleBarContentWrapper) {
                    var scrollHeight = simpleBarContentWrapper.scrollHeight;
                    simpleBarContentWrapper.scrollTop = scrollHeight;
                }
            }
        }, 1000); // Increased delay for testing
    }

    //chat form
    var chatForm = document.querySelector("#chatinput-form");
    var chatInput = document.querySelector("#chat-input");
    var chatInputfeedback = document.querySelector(".chat-input-feedback");

    function currentTime() {
        var ampm = new Date().getHours() >= 12 ? "pm" : "am";
        var hour =
            new Date().getHours() > 12 ?
                new Date().getHours() % 12 :
                new Date().getHours();
        var minute =
            new Date().getMinutes() < 10 ?
                "0" + new Date().getMinutes() :
                new Date().getMinutes();
        if (hour < 10) {
            return "0" + hour + ":" + minute + " " + ampm;
        } else {
            return hour + ":" + minute + " " + ampm;
        }
    }
    setInterval(currentTime, 1000);

    var messageIds = 0;

    if (chatForm) {
        //add an item to the List, including to local storage
        chatForm.addEventListener("submit", function (e) {
            e.preventDefault();

            var chatId = currentChatId;
    
            var chatInputValue = chatInput.value

            if (chatInputValue.length === 0) {
                chatInputfeedback.classList.add("show");
                setTimeout(function () {
                    chatInputfeedback.classList.remove("show");
                }, 2000);
            } else {
                $.ajax({
                    url: route('message.store'),
                    method: 'POST',
                    data: {
                        applicant_id: chatsData[0].applicant_id,
                        message: chatInputValue,                        
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success:function(data){                
                        if (data.success == true) {
                            getChatList(chatId, chatInputValue, data.chat.id);
                            scrollToBottom(chatId);

                            chatInput.value = "";

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
            }
        })
    }

    function attachCopyMessageEventListener(element) {
        element.addEventListener("click", function () {
            var isText = element.closest(".ctext-wrap").children[0]
                ? element.closest(".ctext-wrap").children[0].children[0].innerText
                : "";
            navigator.clipboard.writeText(isText);
    
            // Display the clipboard alert
            document.getElementById("copyClipBoard").style.display = "block";
            document.getElementById("copyClipBoardChannel").style.display = "block";
            setTimeout(function() {
                document.getElementById("copyClipBoard").style.display = "none";
                document.getElementById("copyClipBoardChannel").style.display = "none";
            }, 1000);
        });
    }

    //Append New Message
    var getChatList = function (chatid, chatItems, chatMessageId) {
        messageIds++;
        var chatConList = document.getElementById(chatid);
        var itemList = chatConList.querySelector(".chat-conversation-list");
    
        if (chatItems != null) {
            itemList.insertAdjacentHTML(
                "beforeend",
                '<li class="chat-list right" id="message-' + chatMessageId + '">\
                <div class="conversation-list">\
                    <div class="chat-avatar"><img src="images/shoops.png" alt="shoops"></div>\
                    <div class="user-chat-content">\
                        <div class="ctext-wrap">\
                            <div class="ctext-wrap-content">\
                                <p class="mb-0 ctext-content">\
                                    ' + chatItems + '\
                                </p>\
                            </div>\
                            <div class="dropdown align-self-start message-box-drop">\
                                <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">\
                                    <i class="ri-more-2-fill"></i>\
                                </a>\
                                <div class="dropdown-menu">\
                                    <a class="dropdown-item reply-message" href="#"><i class="ri-reply-line me-2 text-muted align-bottom"></i>Reply</a>\
                                    <a class="dropdown-item" href="#"><i class="ri-share-line me-2 text-muted align-bottom"></i>Forward</a>\
                                    <a class="dropdown-item copy-message" href="#""><i class="ri-file-copy-line me-2 text-muted align-bottom"></i>Copy</a>\
                                    <a class="dropdown-item" href="#"><i class="ri-bookmark-line me-2 text-muted align-bottom"></i>Bookmark</a>\
                                    <a class="dropdown-item delete-item" href="#"><i class="ri-delete-bin-5-line me-2 text-muted align-bottom"></i>Delete</a>\
                            </div>\
                        </div>\
                    </div>\
                    <div class="conversation-name">\
                        <small class="text-muted time">' + formatDate(new Date().toISOString()) + '</small>\
                        <span class="text-success check-message-icon"><i class="bx bx-check"></i></span>\
                    </div>\
                </div>\
            </div>\
        </li>'
            );
        }
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

                // Add margin-bottom of 50px
                fgEmojiPicker.style.marginBottom = "50px";
            }
        }, 0);
    });

})();