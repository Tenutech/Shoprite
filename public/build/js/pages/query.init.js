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

var subject = document.getElementById("subject"),
    intro = document.getElementById("body"),
    addBtn = document.getElementById("add-btn");

document.getElementById("queryModal").addEventListener("hidden.bs.modal", function (e) {
    clearFields();
});

/*
|--------------------------------------------------------------------------
| Add Query
|--------------------------------------------------------------------------
*/

addBtn.addEventListener("click", function (e) {
    e.preventDefault();
    var form = document.getElementById("formQuery");
    if (form.checkValidity()) {
        var formData = new FormData($('#formQuery')[0]);
        var body = $("#body .ql-editor").html();
        formData.set('body', body);

        $.ajax({
            url: route('query.store'),
            type: 'POST',
            data: formData,
            async: false,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data) {
                if(data.success == true) {
                    // queryList.add({
                    //     id: data.encID,
                    //     subject: subject.value,
                    //     body: $("#body .ql-editor").html()              
                    // });

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
                    // refreshCallbacks();
                    count++;                    
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
                console.log(message);
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
        })
    } else {
        form.reportValidity();
    }
});

function clearFields() {
    emailName.value = "";

    subject.value = "";

    $("#body .ql-editor").html('');
}