/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: CRM-contact Js File
*/

/*
|--------------------------------------------------------------------------
| Add Template
|--------------------------------------------------------------------------
*/

document.getElementById('template-btn').addEventListener('click', function(e) {
    e.preventDefault();
    var form = document.getElementById('template-form');
    var formData = new FormData(form);

    $.ajax({
        url: route('template.store'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
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
                    window.location.href = data.redirect_url;
                }, 2000); // 2000ms matches the Swal timer
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
| List Js
|--------------------------------------------------------------------------
*/

// list js
var checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.onclick = function () {
        var checkboxes = document.querySelectorAll('.form-check-all input[type="checkbox"]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
            if (checkboxes[i].checked) {
                checkboxes[i].closest("tr").classList.add("table-active");
            } else {
                checkboxes[i].closest("tr").classList.remove("table-active");
            }
        }
    };
}

var perPage = 10;

//Table
var options = {
    valueNames: [
        "id",
        "position",
        "template"
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
// Init list
var guideList = new List("guideList", options).on("updated", function (list) {
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
    guideList.page = perPage;
    guideList.update();
});

isCount = new DOMParser().parseFromString(
    guideList.items.slice(-1)[0]._values.id,
    "text/html"
);

var idField = document.getElementById("field-id"),
    position = document.getElementById("position"),
    template = document.getElementById("template"),
    addBtn = document.getElementById("add-btn"),
    editBtn = document.getElementById("edit-btn"),
    removeBtns = document.getElementsByClassName("remove-item-btn"),
    editBtns = document.getElementsByClassName("edit-item-btn");
refreshCallbacks();

document.getElementById("guideModal").addEventListener("show.bs.modal", function (e) {
    if (e.relatedTarget.classList.contains("edit-item-btn")) {
        document.getElementById("exampleModalLabel").innerHTML = "Edit Guide";
        document.getElementById("guideModal").querySelector(".modal-footer").style.display = "block";
        document.getElementById("add-btn").style.display = "none";
        document.getElementById("edit-btn").style.display = "block";
    } else if (e.relatedTarget.classList.contains("add-btn")) {
        document.getElementById("exampleModalLabel").innerHTML = "Add Guide";
        document.getElementById("guideModal").querySelector(".modal-footer").style.display = "block";
        document.getElementById("edit-btn").style.display = "none";
        document.getElementById("add-btn").style.display = "block";
    } else {
        document.getElementById("exampleModalLabel").innerHTML = "List Guide";
        document.getElementById("guideModal").querySelector(".modal-footer").style.display = "none";
    }
});
ischeckboxcheck();

document.getElementById("guideModal").addEventListener("hidden.bs.modal", function (e) {
    clearFields();
});

document.querySelector("#guideList").addEventListener("click", function () {
    refreshCallbacks();
    ischeckboxcheck();
});

var table = document.getElementById("guideTable");
// save all tr
var tr = table.getElementsByTagName("tr");
var trlist = table.querySelectorAll(".list tr");

var count = 11;

var positionVal = new Choices(position, {
    searchEnabled: true,
    shouldSort: false
});

var templateVal = new Choices(template, {
    searchEnabled: true,
    shouldSort: false
});

/*
|--------------------------------------------------------------------------
| Update Guide
|--------------------------------------------------------------------------
*/

editBtn.addEventListener("click", function (e) {
    document.getElementById("exampleModalLabel").innerHTML = "Edit Guide";
    var editValues = guideList.get({
        id: idField.value,
    });
    var form = document.getElementById("fromGuide");
    if (form.checkValidity()) {
        var formData = new FormData($('#fromGuide')[0]);

        $.ajax({
            url: route('guide.update'),
            type: 'POST',
            data: formData,
            async: false,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data) {
                if(data.success === true) {
                    Array.from(editValues).forEach(function (x) {
                        isid = new DOMParser().parseFromString(x._values.id, "text/html");
                        var selectedid = isid.body.innerHTML;
                        if (selectedid == itemId) {
                            if (position.value) {
                                positionValue = position.options[position.selectedIndex].text;
                            } else {
                                positionValue = '';
                            }
        
                            if (data.templateID) {
                                templateValue = `
                                    <a href="${route('template.index', { id: data.templateID })}">
                                        Template ${data.position.template_id}
                                    </a>`;
                            } else {
                                templateValue = `<span class="text-danger">None</span>`;
                            }

                            x.values({
                                id: idField.value,
                                position: positionValue,
                                template: templateValue
                            });
                        }
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

function ischeckboxcheck() {
    Array.from(document.getElementsByName("checkAll")).forEach(function (x) {
        x.addEventListener("click", function (e) {
            if (e.target.checked) {
                e.target.closest("tr").classList.add("table-active");
            } else {
                e.target.closest("tr").classList.remove("table-active");
            }
        });
    });
}

/*
|--------------------------------------------------------------------------
| Callbacks
|--------------------------------------------------------------------------
*/

function refreshCallbacks() {
    Array.from(editBtns).forEach(function (btn) {
        btn.onclick = function (e) {
            e.target.closest("tr").children[1].innerText;
            itemId = e.target.closest("tr").children[1].innerText;
           
            $.ajax({
                url: route('guide.details', {id: itemId}),
                type: 'GET',
                data: {
                    "id": itemId
                },
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).done(function(data) {
                idField.value = data.encID;

                if (data.position.id) {
                    positionVal.setChoiceByValue(data.position.id.toString());
                }

                if (data.position.template_id) {
                    templateVal.setChoiceByValue(data.position.template_id.toString());
                }
            });
        }
    });
}

function clearFields() {
    positionVal.removeActiveItems();
    positionVal.setChoiceByValue("");

    templateVal.removeActiveItems();
    templateVal.setChoiceByValue("");
}

// Prevent default behavior for all pagination links created by List.js
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