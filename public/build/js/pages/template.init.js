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

var snowEditor = document.querySelectorAll(".snow-editor");

if (snowEditor) {
    Array.from(snowEditor).forEach(function (item) {
      var snowEditorData = {};
      var issnowEditorVal = item.classList.contains("snow-editor");
  
      if (issnowEditorVal == true) {
        snowEditorData.theme = 'snow', snowEditorData.modules = {
          'toolbar': [[{
            'font': []
          }, {
            'size': []
          }], ['bold', 'italic', 'underline', 'strike'], [{
            'color': []
          }, {
            'background': []
          }], [{
            'script': 'super'
          }, {
            'script': 'sub'
          }], [{
            'header': [false, 1, 2, 3, 4, 5, 6]
          }, 'blockquote', 'code-block'], [{
            'list': 'ordered'
          }, {
            'list': 'bullet'
          }, {
            'indent': '-1'
          }, {
            'indent': '+1'
          }], ['direction', {
            'align': []
          }], ['link', 'image', 'video'], ['clean']]
        };
      }
  
      new Quill(item, snowEditorData);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var typeSelect = document.getElementById('type');
    typeSelect.disabled = true;

    var typeUpdateSelect = document.getElementById('typeUpdate');
    typeUpdateSelect.disabled = true;
});

/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

var idField = document.getElementById("field-id"),
    question = document.getElementById("question"),
    type = document.getElementById("type"),
    sort = document.getElementById("sort"),
    questionUpdate = document.getElementById("questionUpdate"),
    typeUpdate = document.getElementById("typeUpdate"),
    sortUpdate = document.getElementById("sortUpdate"),
    addBtn = document.getElementById("add-btn"),
    editBtn = document.getElementById("edit-btn"),
    deleteBtn = document.getElementById("delete-btn")

function clearFields() {
    question.value = "";
    type.value = "";
    sort.value = "";
    idField.value = "";
    questionUpdate.value = "";
    typeUpdate.value = "";
    sortUpdate.value = "";
}

$(document).ready(function() {
    /*
    |--------------------------------------------------------------------------
    | Add Question
    |--------------------------------------------------------------------------
    */

    $("#formQuestion").submit(function(e){
        e.preventDefault();

        var formData = new FormData($(this)[0]);

        var question = $("#question .ql-editor").html();

        formData.set('question', question);

        $.ajax({
            url: route('template.question.store'),
            type: "POST",
            data: formData,
            async: false,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success === true) {
                    let answerField;
                    if (data.question.type === 'text') {
                        answerField = `<input type="text" class="form-control" name="answers[${data.question.id}]" value="">`;
                    } else if (data.question.type === 'number') {
                        answerField = `<input type="number" class="form-control" name="answers[${data.question.id}]" value="">`;
                    } else if (data.question.type === 'rating') {
                        answerField = `
                            <div class="form-check">
                                <input class="form-check-input d-none" type="hidden" name="answers[${data.question.id}]" id="rating-${data.question.id}" value="">
                                ${[1, 2, 3, 4, 5].map(i => `
                                    <label class="form-check-label" for="rating-${data.question.id}-${i}" style="cursor: pointer; margin-right:20px;">
                                        <i class="ri-star-line" id="star-${data.question.id}-${i}" style="font-size: 1.5em; color: grey;"></i>
                                    </label>`).join('')}
                                <span class="invalid-feedback" role="alert" style="display:none">
                                    <strong>Please select a rating</strong>
                                </span>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    let stars = document.querySelectorAll('[id^="star-${data.question.id}-"]');
                                    stars.forEach(star => {
                                        star.addEventListener('click', function() {
                                            let rating = parseInt(star.id.split('-').pop());
                                            for (let i = 1; i <= rating; i++) {
                                                document.querySelector('#star-${data.question.id}-' + i).classList.remove('ri-star-line');
                                                document.querySelector('#star-${data.question.id}-' + i).classList.add('ri-star-fill');
                                                document.querySelector('#star-${data.question.id}-' + i).style.color = 'gold';
                                            }
                                            for (let i = rating + 1; i <= 5; i++) {
                                                document.querySelector('#star-${data.question.id}-' + i).classList.remove('ri-star-fill');
                                                document.querySelector('#star-${data.question.id}-' + i).classList.add('ri-star-line');
                                                document.querySelector('#star-${data.question.id}-' + i).style.color = 'grey';
                                            }
                                            document.querySelector('#rating-${data.question.id}').value = rating;
                                        });
                                    });
                                });
                            </script>`;
                    } else if (data.question.type === 'textarea') {
                        answerField = `<textarea class="form-control" name="answers[${data.question.id}]" rows="5"></textarea>`;
                    }
            
                    $('#template-questions').append(`
                        <div class="col-xxl-12 col-lg-12" id="message-${data.question.id}">
                            <div class="card">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <h6 class="card-title mb-0" id="question-${data.question.id}">
                                                    Question ${data.question.sort}
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="col d-flex align-items-center justify-content-end dropdown">
                                            <a href="javascript:void(0);" data-bs-toggle="dropdown" aria-expanded="false" class="">
                                                <i class="ri-more-2-fill fs-17"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item edit-list" data-bs-toggle="modal" data-bs-target="#questionUpdateModal" data-edit-id="${data.encID}">
                                                        <i class="ri-pencil-line me-2 align-bottom text-muted"></i>
                                                        Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item remove-list" data-bs-toggle="modal" data-bs-target="#questionDeleteModal" data-remove-id="${data.encID}">
                                                        <i class="ri-delete-bin-5-line me-2 align-bottom text-muted"></i>
                                                        Remove
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <label class="form-label fs-16" style="width:100%;">
                                        <div class="row" style="width:100%;">
                                            <div class="col-sm-12" id="content-${data.question.id}">
                                                ${data.question.question.replace(/\n/g, '<br/>')}
                                            </div>
                                        </div>
                                    </label>
                                    <div class="col-sm-12" id="answer-container-${data.question.id}">
                                        ${answerField}
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="javascript:void(0);" class="link-success float-end" id="type-${data.encID}">
                                        ${data.question.type}
                                        <i class="ri-arrow-right-s-line align-middle ms-1 lh-1"></i>
                                    </a>
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
    | Update Question
    |--------------------------------------------------------------------------
    */

    $('#questionUpdateModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var questionID = button.data('edit-id');

        $.ajax({
            url: route('template.question.details', {id: questionID}),
            type: 'GET',
            cache: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(data) {
            idField.value = data.encID;
            $("#questionUpdate .ql-editor").html(data.question.question);
            typeUpdate.value = data.question.type;
            sortUpdate.value = data.question.sort;
        });
    })

    $("#formQuestionUpdate").submit(function(e){
        e.preventDefault();

        var formData = new FormData($(this)[0]);

        var question = $("#questionUpdate .ql-editor").html();

        formData.set('question', question);

        $.ajax({
            url: route('template.question.update'),
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
                    // Update question title and content
                    $("#question-" + data.question.id).text('Question ' + data.question.sort);
                    $("#content-" + data.question.id).html(data.question.question);

                    // Update answer field based on question type
                    let answerField;
                    if (data.question.type === 'text') {
                        answerField = `<input type="text" class="form-control" name="answers[${data.question.id}]" value="">`;
                    } else if (data.question.type === 'number') {
                        answerField = `<input type="number" class="form-control" name="answers[${data.question.id}]" value="">`;
                    } else if (data.question.type === 'rating') {
                        answerField = `
                            <div class="form-check">
                                <input class="form-check-input d-none" type="hidden" name="answers[${data.question.id}]" id="rating-${data.question.id}" value="">
                                ${[1, 2, 3, 4, 5].map(i => `
                                    <label class="form-check-label" for="rating-${data.question.id}-${i}" style="cursor: pointer; margin-right:20px;">
                                        <i class="ri-star-line" id="star-${data.question.id}-${i}" style="font-size: 1.5em; color: grey;"></i>
                                    </label>`).join('')}
                                <span class="invalid-feedback" role="alert" style="display:none">
                                    <strong>Please select a rating</strong>
                                </span>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    let stars = document.querySelectorAll('[id^="star-${data.question.id}-"]');
                                    stars.forEach(star => {
                                        star.addEventListener('click', function() {
                                            let rating = parseInt(star.id.split('-').pop());
                                            for (let i = 1; i <= rating; i++) {
                                                document.querySelector('#star-${data.question.id}-' + i).classList.remove('ri-star-line');
                                                document.querySelector('#star-${data.question.id}-' + i).classList.add('ri-star-fill');
                                                document.querySelector('#star-${data.question.id}-' + i).style.color = 'gold';
                                            }
                                            for (let i = rating + 1; i <= 5; i++) {
                                                document.querySelector('#star-${data.question.id}-' + i).classList.remove('ri-star-fill');
                                                document.querySelector('#star-${data.question.id}-' + i).classList.add('ri-star-line');
                                                document.querySelector('#star-${data.question.id}-' + i).style.color = 'grey';
                                            }
                                            document.querySelector('#rating-${data.question.id}').value = rating;
                                        });
                                    });
                                });
                            </script>`;
                    } else if (data.question.type === 'textarea') {
                        answerField = `<textarea class="form-control" name="answers[${data.question.id}]" rows="5"></textarea>`;
                    }

                    // Update the answer field container
                    $("#answer-container-" + data.question.id).html(answerField);

                    // Update the type link
                    $("#type-" + data.question.id).html(`
                        ${data.question.type} 
                        <i class="ri-arrow-right-s-line align-middle ms-1 lh-1"></i>
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

                    document.getElementById("close-update-modal").click();
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

    /*
    |--------------------------------------------------------------------------
    | Delete Question
    |--------------------------------------------------------------------------
    */

    $('#questionDeleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var messageID = button.data('remove-id');

        deleteBtn.onclick = function (e) {
            $.ajax({
                url: route('literacy.destroy', {id: messageID}),
                type: "DELETE",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(data) {
                    if (data.success === true) {
                        $("#message-" + data.question.id).remove(); 
                        
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
    })

    /*
    |--------------------------------------------------------------------------
    | Delete Template
    |--------------------------------------------------------------------------
    */

    $('#delete-template-btn').on('click', function(e) {
        e.preventDefault();

        var templateId = $(this).data('template-id');

        $.ajax({
            url: route('template.destroy', { id: templateId }),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success === true) {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true,
                        toast: true
                    });

                    // Redirect after Swal notification
                    setTimeout(function() {
                        window.location.href = route('guide.index');
                    }, 2000); // 2000ms matches the Swal timer
                } else {
                    Swal.fire({
                        position: 'top-end',
                        icon: 'error',
                        title: 'Failed to delete template!',
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true,
                        toast: true
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
})