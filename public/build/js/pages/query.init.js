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
                    toolbar: [
                        [{ 'font': [] }, { 'size': [] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'script': 'super' }, { 'script': 'sub' }],
                        [{ 'header': [false, 1, 2, 3, 4, 5, 6] }, 'blockquote', 'code-block'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }],
                        ['direction', { 'align': [] }],
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
    | Add Query
    |--------------------------------------------------------------------------
    */

    $("#formQuery").submit(function(e){
        e.preventDefault();

        // Get the category select field and check if a category is selected
        const categorySelect = document.getElementById("category");
        const categoryValue = categorySelect.value;

        // Reference the container for Choices.js
        let choicesDiv = categorySelect.closest('.mb-3');
        let choicesContainer = choicesDiv.querySelector('.choices');

        // Check if category is selected
        if (!categoryValue) {
            // Apply error styles if category is not selected
            if (choicesContainer) {
                choicesContainer.style.border = '1px solid #f17171';
            }
            
            // Display feedback message
            let feedbackDiv = choicesDiv.querySelector('.invalid-feedback');
            if (!feedbackDiv) {
                // Create feedback div if it doesn't exist
                feedbackDiv = document.createElement('div');
                feedbackDiv.classList.add('invalid-feedback');
                feedbackDiv.textContent = 'Please select a category.';
                choicesDiv.appendChild(feedbackDiv);
            }
            feedbackDiv.style.display = 'block';
            
            // Exit the function early since form is invalid
            return;
        } else {
            // Clear error styles if category is valid
            if (choicesContainer) {
                choicesContainer.style.border = '';
            }
            let feedbackDiv = choicesDiv.querySelector('.invalid-feedback');
            if (feedbackDiv) {
                feedbackDiv.style.display = 'none';
            }
        }

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

                    // Format created_at and updated_at dates
                    let createdAt = new Date(query.created_at).toLocaleString();
                    let updatedAt = new Date(query.updated_at).toLocaleString();
                    let categoryName = data.category ? data.category.name : '';

                    // Process query.body to wrap <img> tags in a container
                    let bodyWithWrappedImages = query.body.replace(
                        /<img([^>]+)>/g,
                        '<div class="image-container"><img$1></div>'
                    );

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
                                        ${bodyWithWrappedImages}
                                    </div>
                                    ${query.answer ? `
                                    <hr>
                                    <div>
                                        <h6>Answer:</h6>
                                        <p class="text-danger">${query.answer}</p>
                                    </div>` : ''}
                                    <hr>
                                    <div class="d-flex justify-content-between text-muted">
                                        <small>
                                            Created at: <b>${createdAt}</b> | 
                                            Updated at: <b>${updatedAt}</b>
                                        </small>
                                        <small class="text-end">
                                            <b>${categoryName}</b>
                                        </small>
                                    </div>
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
