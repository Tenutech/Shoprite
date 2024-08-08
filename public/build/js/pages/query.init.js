/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: list Js File
*/

/*
|--------------------------------------------------------------------------
| Snow Editor
|--------------------------------------------------------------------------
*/

// Snow Editor
var snowEditor = document.querySelectorAll(".snow-editor");
if (snowEditor) {
    Array.from(snowEditor).forEach(function (item) {
        var snowEditorData = {};
        var issnowEditorVal = item.classList.contains("snow-editor");
        if (issnowEditorVal == true) {
            snowEditorData.theme = 'snow',
                snowEditorData.modules = {
                    'toolbar': [
                        [{
                            'font': []
                        }, {
                            'size': []
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'color': []
                        }, {
                            'background': []
                        }],
                        [{
                            'script': 'super'
                        }, {
                            'script': 'sub'
                        }],
                        [{
                            'header': [false, 1, 2, 3, 4, 5, 6]
                        }, 'blockquote', 'code-block'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }, {
                            'indent': '-1'
                        }, {
                            'indent': '+1'
                        }],
                        ['direction', {
                            'align': []
                        }],
                        ['link', 'image', 'video'],
                        ['clean']
                    ]
                }
        }
        new Quill(item, snowEditorData);
    });
}

/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

var subject = document.getElementById("subject"),
    body = document.getElementById("body"),
    addBtn = document.getElementById("add-btn")

function clearFields() {
    subject.value = "";
    $("#body .ql-editor").html('');
}

$(document).ready(function() {
    /*
    |--------------------------------------------------------------------------
    | Add Message
    |--------------------------------------------------------------------------
    */

    $("#formQuery").submit(function(e){
        e.preventDefault();

        var formData = new FormData($(this)[0]);

        var body = $("#body .ql-editor").html();

        formData.set('body', body);        

        $.ajax({
            url: route('query.store'),
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
                    let query = data.query;

                    // Determine badge class based on status
                    let badgeClass = 'bg-info';
                    if (query.status === 'Pending') {
                        badgeClass = 'bg-info';
                    } else if (query.status === 'In Progress') {
                        badgeClass = 'bg-primary';
                    } else if (query.status === 'Complete') {
                        badgeClass = 'bg-success';
                    }

                    // Create new accordion item HTML
                    let newQueryHtml = `
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="query-heading${query.id}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#query-collapse${query.id}" aria-expanded="false" aria-controls="query-collapse${query.id}">
                                    <div class="container-fluid">
                                        <div class="row w-100">
                                            <div class="col">
                                                <span>${query.subject}</span>
                                            </div>
                                            <div class="col-auto">
                                                <span class="badge ${badgeClass}">${query.status}</span>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="query-collapse${query.id}" class="accordion-collapse collapse" aria-labelledby="query-heading${query.id}" data-bs-parent="#query-accordion">
                                <div class="accordion-body">
                                    <div>
                                        <h6>Body:</h6>
                                        ${query.body}
                                    </div>
                                    ${query.answer ? `
                                    <hr>
                                    <div>
                                        <h6>Answer:</h6>
                                        <p class="text-danger">${query.answer}</p>
                                    </div>` : ''}
                                </div>
                            </div>
                        </div>
                    `;

                    // Append new query to the accordion
                    $('#query-accordion').append(newQueryHtml);

                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true,
                        toast: true
                    })

                    document.getElementById("close-modal").click();
                    clearFields();
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
});
