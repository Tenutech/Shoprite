/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: list Js File
*/

var idField = document.getElementById("field-id"),
    message = document.getElementById("message"),
    answer = document.getElementById("answer"),
    sort = document.getElementById("sort"),
    messageUpdate = document.getElementById("messageUpdate"),
    answerUpdate = document.getElementById("answerUpdate"),
    sortUpdate = document.getElementById("sortUpdate"),
    addBtn = document.getElementById("add-btn"),
    editBtn = document.getElementById("edit-btn"),
    deleteBtn = document.getElementById("delete-btn")

function clearFields() {
    message.value = "";
    answer.value = "";
    sort.value = "";
    idField.value = "";
    messageUpdate.value = "";
    answerUpdate.value = "";
    sortUpdate.value = "";
}

$(document).ready(function() {
    /*
    |--------------------------------------------------------------------------
    | Add Message
    |--------------------------------------------------------------------------
    */

    $("#formMessage").submit(function(e){
        e.preventDefault();

        var formData = new FormData($(this)[0]);

        $.ajax({
            url: route('situational.store'),
            type: "POST",
            data: formData,
            async: false,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data) {
                if (data.success === true) {
                    $('#situational-awareness-test').append(`
                        <div class="col-xxl-12 col-lg-12" id="message-${data.chat.id}">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <h6 class="card-title mb-0" id="question-${data.chat.id}">
                                                    Question ${data.chat.sort}
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="col d-flex align-items-center justify-content-end dropdown">
                                            <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="">
                                                <i class="ri-more-2-fill fs-17"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item edit-list" data-bs-toggle="modal" data-bs-target="#messageUpdateModal" data-edit-id="${data.encID}">
                                                        <i class="ri-pencil-line me-2 align-bottom text-muted"></i>
                                                        Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item remove-list" data-bs-toggle="modal" data-bs-target="#messageDeleteModal" data-remove-id="${data.encID}">
                                                        <i class="ri-delete-bin-5-line me-2 align-bottom text-muted"></i>
                                                        Remove
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title" id="content-${data.chat.id}">
                                        ${data.chat.message.replace(/\n/g, '<br/>')}
                                    </h6>
                                </div>
                                <div class="card-footer">
                                    <a href="javascript:void(0);" class="link-success float-end" id="state-${data.encID}">
                                        ${data.chat.state.name}
                                        <i class="ri-arrow-right-s-line align-middle ms-1 lh-1"></i>
                                    </a>
                                    <p class="text-muted mb-0" id="answer-${data.chat.id}">
                                        Answer:
                                        <span class="text-primary">
                                            ${data.chat.answer}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    `);

                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true,
                        toast: true
                    })

                    document.getElementById("close-add-modal").click();
                    clearFields();
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

    /*
    |--------------------------------------------------------------------------
    | Update Message
    |--------------------------------------------------------------------------
    */

    $('#messageUpdateModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var messageID = button.data('edit-id');

        $.ajax({
            url: route('situational.details', {id: messageID}),
            type: 'GET',
            cache: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(data) {
            idField.value = data.encID;
            messageUpdate.value = data.chat.message;
            answerUpdate.value = data.chat.answer;
            sortUpdate.value = data.chat.sort;
        });
    })

    $("#formMessageUpdate").submit(function(e){
        e.preventDefault();

        var formData = new FormData($(this)[0]);

        $.ajax({
            url: route('situational.update'),
            type: "post",
            data: formData,
            async: false,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data) {
                if (data.success === true) {
                    $("#question-" + data.chat.id).text('Question ' + data.chat.sort);
                    $("#content-" + data.chat.id).html(data.chat.message.replace(/\n/g, '<br/>'));
                    $("#answer-" + data.chat.id).html('Answer: <span class="text-primary">' + data.chat.answer + '</span>');

                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true,
                        toast: true
                    })

                    document.getElementById("close-update-modal").click();
                    clearFields();
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

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    $('#messageDeleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var messageID = button.data('remove-id');

        deleteBtn.onclick = function (e) {
            $.ajax({
                url: route('situational.destroy', {id: messageID}),
                type: "DELETE",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(data) {
                    if (data.success === true) {
                        $("#message-" + data.chat.id).remove();

                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            showCloseButton: true,
                            toast: true
                        });

                        document.getElementById("deleteRecord-close").click();
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
})