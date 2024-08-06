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

var perPage = 10;

var options = {
    valueNames: [
        "id",
        "subject",
        "body",
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

var queryList = new List("queryList", options).on("updated", function (list) {
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
    queryList.page = perPage;
    queryList.update();
});

isCount = new DOMParser().parseFromString(
    queryList.items.slice(-1)[0]._values.id,
    "text/html"
);

var subject = document.getElementById("subject"),
    body = document.getElementById("body"),
    addBtn = document.getElementById("add-btn");

document.getElementById("queryModal").addEventListener("hidden.bs.modal", function (e) {
    clearFields();
});

document.querySelector("#queryList").addEventListener("click", function () {
    refreshCallbacks();
    // ischeckboxcheck();
});

var table = document.getElementById("queryTable");
// save all tr
var tr = table.getElementsByTagName("tr");
var trlist = table.querySelectorAll(".list tr");

var count = 11;

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
                    queryList.add({
                        id: data.encID,
                        subject: subject.value,
                        body: $("#body .ql-editor").html()              
                    });

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
                    refreshCallbacks();
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

function refreshCallbacks() {

}

function clearFields() {
    subject.value = "";

    $("#body .ql-editor").html('');
}

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