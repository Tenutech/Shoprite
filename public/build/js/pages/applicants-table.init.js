/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: CRM-contact Js File
*/

//Format Date
function formatDate(dateStr) {
    if (!dateStr) return ''; // Check for falsy input to avoid errors

    const date = new Date(dateStr);
    if (isNaN(date)) return ''; // Check if the date is invalid

    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    return new Intl.DateTimeFormat('en', options).format(date);
}

/*
|--------------------------------------------------------------------------
| Google Maps
|--------------------------------------------------------------------------
*/

function initAutocomplete() {
    // Get the input element with data-google-autocomplete attribute
    const addressInput = document.querySelector('[data-google-autocomplete]');

    if (addressInput) {
        // Initialize Google Places Autocomplete
        const autocomplete = new google.maps.places.Autocomplete(addressInput, {
            types: ['geocode'],  // Restrict to geocoding (address types)
            componentRestrictions: { 'country': 'ZA' },  // Restrict to South Africa (optional)
        });

        // Flag to check if a valid place was selected
        let placeSelected = false;

        // When the user selects an address from the suggestions
        autocomplete.addListener('place_changed', function () {
            const place = autocomplete.getPlace();
            placeSelected = true;

            if (!place.geometry) {
                // If no geometry is available, reset the input field
                addressInput.value = '';
                addressInput.classList.add('is-invalid');

                // Show the correct error message right after the address input
                const feedback = addressInput.nextElementSibling;  // This gets the closest sibling
                feedback.textContent = 'Please select a verified address!';
                feedback.style.display = 'block';  // Show the feedback element
                document.getElementById("edit-btn").classList.add("disabled");
            } else {
                // Valid address selected, remove error state
                addressInput.classList.remove('is-invalid');

                // Hide the feedback element
                const feedback = addressInput.nextElementSibling;
                feedback.textContent = '';
                feedback.style.display = 'none';  // Hide the feedback

                // Set lat/lng hidden fields
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                document.getElementById("edit-btn").classList.remove("disabled");
            }
        });

        // Prevent browser autocomplete by disabling autofill
        addressInput.setAttribute('autocomplete', 'off');

        // Validate on field blur that the user selected a valid address
        addressInput.addEventListener('blur', function () {
            if (!placeSelected) {
                // If the user didn't select a valid address, mark the field as invalid
                addressInput.classList.add('is-invalid');
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';

                // Show error message and ensure display is set to block
                const feedback = addressInput.nextElementSibling;  // Get the closest invalid-feedback
                feedback.textContent = 'Please select a verified address!';
                feedback.style.display = 'block';  // Force display to block
            }
        });

        // Reset placeSelected when user starts typing again
        addressInput.addEventListener('input', function () {
            placeSelected = false;
            addressInput.classList.remove('is-invalid');

            // Hide error message
            const feedback = addressInput.nextElementSibling;
            feedback.textContent = '';
            feedback.style.display = 'none';  // Hide feedback
        });
    }
}

// Load the Google Autocomplete on page load
window.addEventListener('load', initAutocomplete);

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
        "name",
        "id_number",
        "phone",
        "employment",
        "state",
        "email",
        "town",
        "age",
        "gender",
        "race",
        "score",
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
var applicantsTableList = new List("applicantsTableList", options).on("updated", function (list) {
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
    applicantsTableList.page = perPage;
    applicantsTableList.update();
});

isCount = new DOMParser().parseFromString(
    applicantsTableList.items.slice(-1)[0]._values.id,
    "text/html"
);

// user image
document.querySelector("#avatar").addEventListener("change", function () {
    var preview = document.querySelector("#profile-img");
    var file = document.querySelector("#avatar").files[0];
    var reader = new FileReader();
    reader.addEventListener("load",function () {
        preview.src = reader.result;
    },false);
    if (file) {
        reader.readAsDataURL(file);
    }
});

var idField = document.getElementById("field-id"),
    profileImg = document.getElementById("profile-img"),
    avatar = document.getElementById("avatar"),
    firstname = document.getElementById("firstname"),
    lastname = document.getElementById("lastname"),
    email = document.getElementById("email"),
    phone = document.getElementById("phone"),
    idNumber = document.getElementById("idNumber"),
    employment = document.getElementById("employment"),
    gender = document.getElementById("gender"),
    race = document.getElementById("race"),
    locationAddress = document.getElementById("location"),
    latitude = document.getElementById("latitude"),
    longitude = document.getElementById("longitude"),
    education = document.getElementById("education"),
    duration = document.getElementById("duration"),
    brands = document.getElementById("brands"),
    publicHolidays = document.getElementById("publicHolidays"),
    environment = document.getElementById("environment"),
    disability = document.getElementById("disability"),
    state = document.getElementById("state"),
    editBtn = document.getElementById("edit-btn"),
    removeBtns = document.getElementsByClassName("remove-item-btn"),
    editBtns = document.getElementsByClassName("edit-item-btn");
    viewBtns = document.getElementsByClassName("view-item-btn");
refreshCallbacks();

ischeckboxcheck();

document.getElementById("applicantsTableModal").addEventListener("hidden.bs.modal", function (e) {
    clearFields();
});

document.querySelector("#applicantsTableList").addEventListener("click", function () {
    refreshCallbacks();
    ischeckboxcheck();
});

var table = document.getElementById("applicantsTable");
// save all tr
var tr = table.getElementsByTagName("tr");
var trlist = table.querySelectorAll(".list tr");

var count = 11;

var employmentVal = new Choices(employment, {
    searchEnabled: false,
    shouldSort: true
});

var genderVal = new Choices(gender, {
    searchEnabled: false,
    shouldSort: true
});

var raceVal = new Choices(race, {
    searchEnabled: false,
    shouldSort: true
});

var educationVal = new Choices(education, {
    searchEnabled: false,
    shouldSort: false
});

var durationVal = new Choices(duration, {
    searchEnabled: false,
    shouldSort: false
});

var brandVal = new Choices(brands, {
    searchEnabled: false,  // Disable search if not needed
    shouldSort: false,     // Disable sorting
    removeItemButton: true,  // Enable item removal by showing a remove button
    duplicateItemsAllowed: false,  // Prevent duplicate selections
    placeholderValue: 'Select brands',  // Optional: Add a placeholder
    removeItems: true,   // Allow items to be removed
    removeItemButton: true,  // Show the "x" button for removable items
    itemSelectText: ''  // Prevent extra text appearing when selecting
});

var publicHolidaysVal = new Choices(publicHolidays, {
    searchEnabled: false,
    shouldSort: false
});

var environmentVal = new Choices(environment, {
    searchEnabled: false,
    shouldSort: false
});

var disabilityVal = new Choices(disability, {
    searchEnabled: false,
    shouldSort: false
});

var stateVal = new Choices(state, {
    searchEnabled: false,
    shouldSort: false
});

/*
|--------------------------------------------------------------------------
| Update Applicant
|--------------------------------------------------------------------------
*/

editBtn.addEventListener("click", function (e) {
    document.getElementById("exampleModalLabel").innerHTML = "Edit Applicant";
    var editValues = applicantsTableList.get({
        id: idField.value,
    });
    var form = document.getElementById("formApplicant");
    if (form.checkValidity()) {
        var formData = new FormData($('#formApplicant')[0]);

        $.ajax({
            url: route('applicants-table.update'),
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
                            // Employment badge logic
                            let employmentValue, employmentBadgeClass;
                            switch (employment.options[employment.selectedIndex].value) {
                                case 'A':
                                    employmentValue = 'Active Employee';
                                    employmentBadgeClass = 'warning';
                                    break;
                                case 'B':
                                    employmentValue = 'Blacklisted';
                                    employmentBadgeClass = 'danger';
                                    break;
                                case 'P':
                                    employmentValue = 'Previously Employed';
                                    employmentBadgeClass = 'info';
                                    break;
                                case 'N':
                                    employmentValue = 'Not an Employee';
                                    employmentBadgeClass = 'success';
                                    break;
                                case 'I':
                                default:
                                    employmentValue = 'Inconclusive';
                                    employmentBadgeClass = 'dark';
                                    break;
                            }

                            if (gender.value) {
                                genderValue = gender.options[gender.selectedIndex].text;
                            } else {
                                genderValue = '';
                            }

                            if (race.value) {
                                raceValue = race.options[race.selectedIndex].text;
                            } else {
                                raceValue = '';
                            }

                            if (education.value) {
                                educationValue = education.options[education.selectedIndex].text;
                            } else {
                                educationValue = '';
                            }

                            if (duration.value) {
                                durationValue = duration.options[duration.selectedIndex].text;
                            } else {
                                durationValue = '';
                            }

                            if (state.value) {
                                stateValue = state.options[state.selectedIndex].text;
                            } else {
                                stateValue = '';
                            }

                            x.values({
                                id: idField.value,
                                name: '<div class="d-flex align-items-center">\
                                        <div class="flex-shrink-0"><img src="'+ profileImg.src + '" alt="" class="avatar-xs rounded-circle object-cover"></div>\
                                            <div class="flex-grow-1 ms-2 name">' + firstname.value + ' ' + lastname.value + '</div>\
                                        </div>',
                                id_number: idNumber.value,
                                phone: phone.value,
                                email: email.value,
                                employment: '<span class="badge bg-' + employmentBadgeClass + '-subtle text-' + employmentBadgeClass + ' text-uppercase">' + employmentValue + '</span>',
                                state: stateValue,
                                gender: genderValue,
                                race: raceValue,
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
    Array.from(removeBtns).forEach(function (btn) {
        btn.onclick = function (e) {
            e.target.closest("tr").children[1].innerText;
            itemId = e.target.closest("tr").children[1].innerText;
            var itemValues = applicantsTableList.get({
                id: itemId,
            });

            Array.from(itemValues).forEach(function (x) {
                deleteid = new DOMParser().parseFromString(x._values.id, "text/html");

                var isdeleteid = deleteid.body.innerHTML;

                if (isdeleteid == itemId) {
                    document.getElementById("delete-user").onclick = function () {
                        $.ajax({
                            url: route('applicants-table.destroy', {id: isdeleteid}),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success:function(data) {
                                if(data.success === true) {
                                    applicantsTableList.remove("id", isdeleteid);
                                    document.getElementById("deleteRecord-close").click();
                                    Swal.fire({
                                        position: 'top-end',
                                        icon: 'success',
                                        title: data.message,
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
                    }
                }
            });
        };
    });

    Array.from(editBtns).forEach(function (btn) {
        btn.onclick = function (e) {
            e.target.closest("tr").children[1].innerText;
            itemId = e.target.closest("tr").children[1].innerText;

            $.ajax({
                url: route('applicants-table.details', {id: itemId}),
                type: 'get',
                data: {
                    "id": itemId
                },
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).done(function(data) {
                idField.value = data.encID;

                profileImg.src = data.applicant.avatar;

                firstname.value = data.applicant.firstname;

                lastname.value = data.applicant.lastname;

                email.value = data.applicant.email;

                phone.value = data.applicant.phone;

                idNumber.value = data.applicant.id_number;

                if(data.applicant.employment) {
                    employmentVal.setChoiceByValue(data.applicant.employment.toString());
                }

                if(data.applicant.gender_id) {
                    genderVal.setChoiceByValue(data.applicant.gender_id.toString());
                }

                if(data.applicant.race_id) {
                    raceVal.setChoiceByValue(data.applicant.race_id.toString());
                }

                locationAddress.value = data.applicant.location;

                latitude.value = data.latitude;

                longitude.value = data.longitude;

                if(data.applicant.education_id) {
                    educationVal.setChoiceByValue(data.applicant.education_id.toString());
                }

                if(data.applicant.duration_id) {
                    durationVal.setChoiceByValue(data.applicant.duration_id.toString());
                }

                var availableBrandIds = [1, 2, 5, 6];

                if (data.applicant.brands) {
                    // Handle brands selection
                    var brandsList = data.applicant.brands;  // Array of applicant's brands

                    // Custom function to check and select brands
                    brandsList.forEach(function(brand) {
                        if (availableBrandIds.includes(brand.id)) {
                            // Select the brand in the Choices.js dropdown
                            brandVal.setChoiceByValue(brand.id.toString());
                        }
                    });
                }

                if(data.applicant.public_holidays) {
                    publicHolidaysVal.setChoiceByValue(data.applicant.public_holidays.toString());
                }

                if(data.applicant.environment) {
                    environmentVal.setChoiceByValue(data.applicant.environment.toString());
                }

                if(data.applicant.disability) {
                    disabilityVal.setChoiceByValue(data.applicant.disability.toString());
                }

                if(data.applicant.state_id) {
                    stateVal.setChoiceByValue(data.applicant.state_id.toString());
                }
            });
        }
    });

    Array.from(viewBtns).forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            e.target.closest("tr").children[1].innerText;
            itemId = e.target.closest("tr").children[1].innerText;
            var itemValues = applicantsTableList.get({
                id: itemId,
            });

            Array.from(itemValues).forEach(function (x) {
                isid = new DOMParser().parseFromString(x._values.id, "text/html");
                var selectedid = isid.body.innerHTML;
                if (selectedid == itemId) {
                    var codeBlock = `
                        <div class="card-body text-center">
                            <div class="position-relative d-inline-block">
                                <img src="${new DOMParser().parseFromString(x._values.name, "text/html").body.querySelector("img").src}" alt=""
                                    class="avatar-lg rounded-circle img-thumbnail object-cover">
                                <span class="contact-active position-absolute rounded-circle bg-success"><span
                                        class="visually-hidden"></span>
                            </div>
                            <h5 class="mt-4 mb-1">${new DOMParser().parseFromString(x._values.name, "text/html").body.querySelector(".name").innerHTML}</h5>
                            <p class="text-muted">${x._values.employment}</p>
                        </div>
                        <div class="card-body">
                            <h6 class="text-muted text-uppercase fw-semibold mb-3">Personal Information</h6>
                            <div class="table-responsive table-card">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="fw-medium" scope="row">Email</td>
                                            <td>${x._values.email}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Phone</td>
                                            <td>${x._values.phone}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">ID Number</td>
                                            <td>${x._values.id_number}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Age</td>
                                            <td>${x._values.age}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Gender</td>
                                            <td>${x._values.gender}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Ethnicity</td>
                                            <td>${x._values.race}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Town</td>
                                            <td>${x._values.town}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Score</td>
                                            <td>${x._values.score}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">State</td>
                                            <td>${x._values.state}</td>
                                        </tr>                                        
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-grid gap-2 mt-4" >
                                <a href="`+ route('applicant-profile.index', {id: x._values.id}) +`" class="btn btn-primary" type="button">View Profile</a>
                            </div>
                        </div>`;
                    document.getElementById('contact-view-detail').innerHTML = codeBlock;
                }
            });
        });
    });
}

function clearFields() {
    profileImg.src = "build/images/users/user-dummy-img.jpg";

    firstname.value = "";

    lastname.value = "";

    email.value = "";

    phone.value = "";

    idNumber.value = "";

    employmentVal.removeActiveItems();
    employmentVal.setChoiceByValue("");

    genderVal.removeActiveItems();
    genderVal.setChoiceByValue("");

    raceVal.removeActiveItems();
    raceVal.setChoiceByValue("");

    locationAddress.value = "";

    educationVal.removeActiveItems();
    educationVal.setChoiceByValue("");

    durationVal.removeActiveItems();
    durationVal.setChoiceByValue("");

    brandVal.removeActiveItems();

    publicHolidaysVal.removeActiveItems();
    publicHolidaysVal.setChoiceByValue("");

    environmentVal.removeActiveItems();
    environmentVal.setChoiceByValue("");

    disabilityVal.removeActiveItems();
    disabilityVal.setChoiceByValue("");

    stateVal.removeActiveItems();
    stateVal.setChoiceByValue("");
}

// Delete All Records
function deleteMultiple(){
    ids_array = [];
    var items = document.getElementsByName('chk_child');
    for (i = 0; i < items.length; i++) {
        if (items[i].checked == true) {
            var trNode = items[i].parentNode.parentNode.parentNode;
            var id = trNode.querySelector("td").innerHTML;
            ids_array.push(id);
        }
    }

    if(typeof ids_array !== 'undefined' && ids_array.length > 0){
        Swal.fire({
            html: '<div class="mt-3">' + '<lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>' + '<div class="mt-4 pt-2 fs-15 mx-5">' + '<h4>You are about to delete these applicants ?</h4>' + '<p class="text-muted mx-4 mb-0">Deleting these applicants will remove all of their information from the database.</p>' + '</div>' + '</div>',
            showCancelButton: true,
            confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
            cancelButtonClass: 'btn btn-danger w-xs mt-2',
            confirmButtonText: "Yes, delete it!",
            buttonsStyling: false,
            showCloseButton: true
        }).then(function (result) {
            if (result.value) {
                for (i = 0; i < ids_array.length; i++) {
                    applicantsTableList.remove("id", `${ids_array[i]}`);
                }

                $.ajax({
                    url: route('applicants-table.destroyMultiple'),
                    type: 'post',
                    data: {
                        ids: ids_array
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success:function(data) {
                        if(data.success === true) {
                            document.getElementById('checkAll').checked = false;

                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: data.message,
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
                })
            }
        });
    }else{
        Swal.fire({
            title: 'Please select at least one applicant',
            confirmButtonClass: 'btn btn-info',
            buttonsStyling: false,
            showCloseButton: true
        });
    }
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