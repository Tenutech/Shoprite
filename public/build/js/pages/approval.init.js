/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Tasks-list init js
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

var checkAll = document.getElementById("checkAll");
if (checkAll) {
  checkAll.onclick = function () {
    var checkboxes = document.querySelectorAll('.form-check-all input[type="checkbox"]');
    var checkedCount = document.querySelectorAll('.form-check-all input[type="checkbox"]:checked').length;
    for (var i = 0; i < checkboxes.length; i++) {
      checkboxes[i].checked = this.checked;
      if (checkboxes[i].checked) {
          checkboxes[i].closest("tr").classList.add("table-active");
      } else {
          checkboxes[i].closest("tr").classList.remove("table-active");
      }
    }

    (checkedCount > 0) ? document.getElementById("remove-actions").style.display = 'none' : document.getElementById("remove-actions").style.display = 'block';
  };
}
var perPage = 8;
var editlist = false;

//Table
var options = {
    valueNames: [
        "id",
        "name",
        "type",
        "user",
        "location",
        "open",
        "filled",
        "date",
        "status"
    ],
    page: perPage,
    pagination: true,
    plugins: [
        ListPagination({
            left: 2,
            right: 2,
        }),
    ],
};

// Init list
var vacanciesList = new List("vacanciesList", options).on("updated", function (list) {
    list.matchingItems.length == 0 ?
        (document.getElementsByClassName("noresult")[0].style.display = "block") :
        (document.getElementsByClassName("noresult")[0].style.display = "none");
    var isFirst = list.i == 1;
    var isLast = list.i > list.matchingItems.length - list.page;
    // make the Prev and Nex buttons disabled on first and last pages accordingly
    document.querySelector(".pagination-prev.disabled") ?
        document.querySelector(".pagination-prev.disabled").classList.remove("disabled") : "";
    document.querySelector(".pagination-next.disabled") ?
        document.querySelector(".pagination-next.disabled").classList.remove("disabled") : "";
    if (isFirst)
        document.querySelector(".pagination-prev").classList.add("disabled");
    if (isLast)
        document.querySelector(".pagination-next").classList.add("disabled");
    if (list.matchingItems.length <= perPage)
        document.querySelector(".pagination-wrap").style.display = "none";
    else
        document.querySelector(".pagination-wrap").style.display = "flex";
    if (list.matchingItems.length == perPage)
        document.querySelector(".pagination.listjs-pagination").firstElementChild.children[0].click()
    if (list.matchingItems.length > 0)
        document.getElementsByClassName("noresult")[0].style.display = "none";
    else
        document.getElementsByClassName("noresult")[0].style.display = "block";
});

isCount = new DOMParser().parseFromString(
    vacanciesList.items.slice(-1)[0]._values.id, "text/html"
);

var isValue = isCount.body.innerHTML;

var approveBtns = document.getElementsByClassName("approve-item-btn");
var amendBtns = document.getElementsByClassName("amend-item-btn");
var declineBtns = document.getElementsByClassName("decline-item-btn");

refreshCallbacks();

function tooltipElm(){
    var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    var tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
}

document.querySelector("#vacanciesList").addEventListener("click", function () {
    ischeckboxcheck();
});

var table = document.getElementById("vacanciesTable");
// save all tr
var tr = table.getElementsByTagName("tr");
var trlist = table.querySelectorAll(".list tr");

var count = 11;

function SearchData() {
    var searchText = document.querySelector('.search').value.toLowerCase();
    var positionFilter = document.getElementById("positionFilter").value;
    var typeFilter = document.getElementById("typeFilter").value;
    var storeFilter = document.getElementById("storeFilter").value;
    var townFilter = document.getElementById("townFilter").value;

    vacanciesList.filter(function(item) {
        var textMatch = searchText === "" || 
                        item.values().name.toLowerCase().includes(searchText) ||
                        item.values().type.toLowerCase().includes(searchText) || 
                        item.values().user.toLowerCase().includes(searchText) ||
                        item.values().location.toLowerCase().includes(searchText);

        var positionMatch = positionFilter === "" || item.values().name.trim().toLowerCase() === positionFilter.toLowerCase();
        var typeMatch = typeFilter === "" || item.values().type.trim().toLowerCase() === typeFilter.toLowerCase();
        var townMatch = townFilter === "" || item.values().location.trim().toLowerCase() === townFilter.toLowerCase();
        
        return textMatch && positionMatch && typeMatch && townMatch;
    });

    vacanciesList.update();
}

function ischeckboxcheck() {
    Array.from(document.getElementsByName("chk_child")).forEach(function (x) {
        x.addEventListener("change", function (e) {
            if (x.checked == true) {
                e.target.closest("tr").classList.add("table-active");
            } else {
                e.target.closest("tr").classList.remove("table-active");
            }
  
            var checkedCount = document.querySelectorAll('[name="chk_child"]:checked').length;
            if (e.target.closest("tr").classList.contains("table-active")) {
                (checkedCount > 0) ? document.getElementById("remove-actions").style.display = 'block': document.getElementById("remove-actions").style.display = 'none';
            } else {
                (checkedCount > 0) ? document.getElementById("remove-actions").style.display = 'block': document.getElementById("remove-actions").style.display = 'none';
            }
        });
    });
}

function refreshCallbacks() {
    // Attach a single event listener to the table or another persistent parent element
    document.getElementById('vacanciesTable').addEventListener('click', function(e) {
        // Using event.target, determine if an approve, amend, or decline button was clicked.
        var target = e.target;

        // Check if the clicked element or any of its ancestors is an approve button.
        if (target.closest('.approve-item-btn')) {
            var itemId = target.closest('tr').children[1].innerText.trim();
            document.getElementById('approve-vacancy').setAttribute('data-id-to-approve', itemId);
        }
        // Check if the clicked element or any of its ancestors is an amend button.
        else if (target.closest('.amend-item-btn')) {
            var itemId = target.closest('tr').children[1].innerText.trim();
            document.getElementById('amend-vacancy').setAttribute('data-id-to-amend', itemId);
        }
        // Check if the clicked element or any of its ancestors is a decline button.
        else if (target.closest('.decline-item-btn')) {
            var itemId = target.closest('tr').children[1].innerText.trim();
            document.getElementById('decline-vacancy').setAttribute('data-id-to-decline', itemId);
        }
    });

    function handlevacancyAction() {
        currentAction = this.getAttribute('data-action');
        var vacancyID = this.getAttribute('data-id-to-' + currentAction);        
        let description = "";

        let url;
        switch(currentAction) {
            case "approve":
                url = route('vacancy.approve');
                break;
            case "amend":
                url = route('vacancy.amend');
                break;
            case "decline":
                url = route('vacancy.decline');
                break;
        }

        if (currentAction === 'amend') {
            description = $("#amend .ql-editor").html();
            if (!description || description == '<p><br></p>') {
                document.querySelector('#amend + .invalid-feedback').style.display = 'block';
                return;  // Don't proceed further if the description is empty
            } else {
                document.querySelector('#amend + .invalid-feedback').style.display = 'none';
            }
        }

        $('#' + currentAction + '-vacancy').hide();
        $('#loading-' + currentAction).removeClass('d-none');

        $.ajax({
            url: url,
            method: 'PUT',
            data: {
                id: vacancyID,
                description: description
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
                if (data.success === true) {
                    statusID = data.vacancy.status_id;
                    var status = '<span class="badge bg-'+ data.vacancy.status.color +'-subtle text-'+ data.vacancy.status.color +' text-uppercase">\
                                    '+ data.vacancy.status.name +'\
                                </span>';

                    var item = vacanciesList.get('id', vacancyID)[0];

                    item.values({
                        status: status
                    });

                    document.getElementById(currentAction + "Vacancy-close").click();
                    $("#amend .ql-editor").html('');

                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        toast: true,
                        showCloseButton: true
                    });

                    $('#' + currentAction + '-vacancy').show();
                    $('#loading-' + currentAction).hide();
                    refreshCallbacks();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#' + currentAction + '-vacancy').show();
                $('#loading-' + currentAction).hide();

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

    document.getElementById("approve-vacancy").onclick = handlevacancyAction;
    document.getElementById("amend-vacancy").onclick = handlevacancyAction;
    document.getElementById("decline-vacancy").onclick = handlevacancyAction;
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

function deleteMultiple() {
    ids_array = [];
    var items = document.getElementsByName('chk_child');
    for (i = 0; i < items.length; i++) {
        if (items[i].checked == true) {
            var trNode = items[i].parentNode.parentNode.parentNode;
            var id = trNode.querySelector("td").innerHTML.trim();
            ids_array.push(id);
        }
    }
    if (typeof ids_array !== 'undefined' && ids_array.length > 0) {
        Swal.fire({
            html: '<div class="mt-3">' + '<lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>' + '<div class="mt-4 pt-2 fs-15 mx-5">' + '<h4>You are about to delete these vacancies ?</h4>' + '<p class="text-muted mx-4 mb-0">Deleting these vacancies will remove all of the information from the database.</p>' + '</div>' + '</div>',
            showCancelButton: true,
            confirmButtonClass: 'btn btn-danger w-xs me-2 mt-2',
            cancelButtonClass: 'btn btn-ghost-dark w-xs mt-2',
            confirmButtonText: "Yes, delete it!",
            buttonsStyling: false,
            showCloseButton: true
        }).then(function (result) {
            if (result.value) {
                $.ajax({
                    url: route('vacancy.destroyMultiple'),
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: { ids: ids_array },
                    success: function (data) {
                        if (data.success === true) {
                            for (i = 0; i < ids_array.length; i++) {
                                vacanciesList.remove("id", `${ids_array[i]}`);
                            }
                            document.getElementById("remove-actions").style.display = 'none';
                            document.getElementById("checkAll").checked = false;
        
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
            }
        });
        document.getElementById('checkAll').checked = false;
    } else {
        Swal.fire({
            title: 'Please select at least one checkbox',
            confirmButtonClass: 'btn btn-info',
            buttonsStyling: false,
            showCloseButton: true
        });
    }
}