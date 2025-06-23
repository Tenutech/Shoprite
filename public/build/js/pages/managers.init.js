/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: CRM-contact Js File
*/

/*
|--------------------------------------------------------------------------
| Password Addon
|--------------------------------------------------------------------------
*/

// Toggle visibility of password inputs
document.querySelector("#password-addon").addEventListener("click", function () {
    var passwordInput = document.querySelector("#password");
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
    } else {
        passwordInput.type = "password";
    }
});

document.querySelector("#password-addon-confirmation").addEventListener("click", function () {
    var passwordInputConfirmation = document.querySelector("#input-password-confirmation");
    if (passwordInputConfirmation.type === "password") {
        passwordInputConfirmation.type = "text";
    } else {
        passwordInputConfirmation.type = "password";
    }
});

//Format Date
function formatDate(dateStr) {
    if (!dateStr) return ''; // Check for falsy input to avoid errors

    const date = new Date(dateStr);
    if (isNaN(date)) return ''; // Check if the date is invalid

    const options = { day: '2-digit', month: 'short', year: 'numeric' };
    return new Intl.DateTimeFormat('en', options).format(date);
}

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
        "email",
        "phone",
        "id_number",
        "id_verified",
        "birth_date",
        "age",
        "gender",
        "role",
        "store",
        "division",
        "region",        
        "brand",
        "status"
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
var userList = new List("userList", options).on("updated", function (list) {
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

document.getElementById("custom-user-search").addEventListener("input", function () {
    customSearch(this.value);
});

function customSearch(query) {
    const cleanedQuery = query.toLowerCase().trim();

    userList.filter(function(item) {
        const values = item.values();

        return (
            values.email?.toLowerCase().includes(cleanedQuery) ||
            values.phone?.toLowerCase().includes(cleanedQuery) ||
            values.name?.toLowerCase().includes(cleanedQuery) ||
            values.store?.toLowerCase().includes(cleanedQuery) ||
            values.id_number?.toLowerCase().includes(cleanedQuery)
        );
    });
}

var perPageSelect = document.getElementById("per-page-select");
perPageSelect.addEventListener("change", function() {
    perPage = parseInt(this.value);
    userList.page = perPage;
    userList.update();
});

isCount = new DOMParser().parseFromString(
    userList.items.slice(-1)[0]._values.id,
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
    idVerified = document.getElementById("idVerified"),
    birthDate = document.getElementById("birthDate"),
    age = document.getElementById("age"),
    gender = document.getElementById("gender"),
    role = document.getElementById("role"),
    store = document.getElementById("store"),
    division = document.getElementById("division"),
    region = document.getElementById("region"),    
    brand = document.getElementById("brand"),
    addBtn = document.getElementById("add-btn"),
    editBtn = document.getElementById("edit-btn"),
    passwordBtn = document.getElementById("password-reset"),
    removeBtns = document.getElementsByClassName("remove-item-btn"),
    editBtns = document.getElementsByClassName("edit-item-btn");
    viewBtns = document.getElementsByClassName("view-item-btn");
    passwordBtns = document.getElementsByClassName("password-item-btn");
refreshCallbacks();

document.getElementById("usersModal").addEventListener("show.bs.modal", function (e) {
    if (e.relatedTarget.classList.contains("edit-item-btn")) {
        document.getElementById("exampleModalLabel").innerHTML = "Edit User";
        document.getElementById("usersModal").querySelector(".modal-footer").style.display = "block";
        document.getElementById("add-btn").style.display = "none";
        document.getElementById("edit-btn").style.display = "block";
    } else if (e.relatedTarget.classList.contains("add-btn")) {
        document.getElementById("exampleModalLabel").innerHTML = "Add User";
        document.getElementById("usersModal").querySelector(".modal-footer").style.display = "block";
        document.getElementById("edit-btn").style.display = "none";
        document.getElementById("add-btn").style.display = "block";
    } else {
        document.getElementById("exampleModalLabel").innerHTML = "List User";
        document.getElementById("usersModal").querySelector(".modal-footer").style.display = "none";
    }
});
ischeckboxcheck();

document.getElementById("usersModal").addEventListener("hidden.bs.modal", function (e) {
    clearFields();
});

document.querySelector("#userList").addEventListener("click", function () {
    refreshCallbacks();
    ischeckboxcheck();
});

var table = document.getElementById("userTable");
// save all tr
var tr = table.getElementsByTagName("tr");
var trlist = table.querySelectorAll(".list tr");

var count = 11;

var idVerifiedVal = new Choices(idVerified, {
    searchEnabled: false,
    shouldSort: false
});

var genderVal = new Choices(gender, {
    searchEnabled: false,
    shouldSort: false
});

var roleVal = new Choices(role, {
    searchEnabled: false
});

var storeVal = new Choices(store, {
    searchEnabled: true
});
var divisionVal = new Choices(division, {
    searchEnabled: true
});

var regionVal = new Choices(region, {
    searchEnabled: true
});

var brandVal = new Choices(brand, {
    searchEnabled: true
});

/*
|--------------------------------------------------------------------------
| Add User
|--------------------------------------------------------------------------
*/

addBtn.addEventListener("click", function (e) {
    e.preventDefault();
    var form = document.getElementById("formUser");
    if (form.checkValidity()) {
        var formData = new FormData($('#formUser')[0]);
        $.ajax({
            url: route('managers.store'),
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
                    if (idVerified.value) {
                        idVerifiedValue = idVerified.options[idVerified.selectedIndex].text;
                    } else {
                        idVerifiedValue = '';
                    }

                    if (gender.value) {
                        genderValue = gender.options[gender.selectedIndex].text;
                    } else {
                        genderValue = '';
                    }

                    if (role.value) {
                        roleValue = role.options[role.selectedIndex].text;
                    } else {
                        roleValue = '';
                    }

                    if (store.value) {
                        storeValue = store.options[store.selectedIndex].text;
                    } else {
                        storeValue = '';
                    }

                    if (division.value) {
                        divisionValue = division.options[division.selectedIndex].text;
                    } else {
                        divisionValue = '';
                    }

                    if (region.value) {
                        regionValue = region.options[region.selectedIndex].text;
                    } else {
                        regionValue = '';
                    }

                    if (brand.value) {
                        brandValue = brand.options[brand.selectedIndex].text;
                    } else {
                        brandValue = '';
                    }

                    userList.add({
                        id: data.encID,
                        name: '<div class="d-flex align-items-center">\
                                <div class="flex-shrink-0"><img src="'+ profileImg.src + '" alt="" class="avatar-xs rounded-circle object-cover"></div>\
                                    <div class="flex-grow-1 ms-2 name">' + firstname.value + ' ' + lastname.value + '</div>\
                                </div>',
                        email: email.value,
                        phone: phone.value,
                        id_number: idNumber.value,
                        id_verified: idVerifiedValue,
                        birth_date: formatDate(birthDate.value),
                        age: age.value,
                        gender: genderValue,
                        role: roleValue,
                        store: storeValue,
                        division: divisionValue,
                        region: regionValue,                        
                        brand: brandValue,
                        status: '<span class="badge bg-danger-subtle text-danger text-uppercase">\
                                    Offline\
                                </span>'                     
                    });
                    userList.sort('name', { order: "asc" });
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

/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
*/

editBtn.addEventListener("click", function (e) {
    document.getElementById("exampleModalLabel").innerHTML = "Edit User";
    var editValues = userList.get({
        id: idField.value,
    });
    var form = document.getElementById("formUser");
    if (form.checkValidity()) {
        var formData = new FormData($('#formUser')[0]);

        $.ajax({
            url: route('managers.update'),
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
                            if (idVerified.value) {
                                idVerifiedValue = idVerified.options[idVerified.selectedIndex].text;
                            } else {
                                idVerifiedValue = '';
                            }
        
                            if (gender.value) {
                                genderValue = gender.options[gender.selectedIndex].text;
                            } else {
                                genderValue = '';
                            }
        
                            if (role.value) {
                                roleValue = role.options[role.selectedIndex].text;
                            } else {
                                roleValue = '';
                            }
        
                            if (store.value) {
                                storeValue = store.options[store.selectedIndex].text;
                            } else {
                                storeValue = '';
                            }

                            if (division.value) {
                                divisionValue = division.options[division.selectedIndex].text;
                            } else {
                                divisionValue = '';
                            }

                            if (region.value) {
                                regionValue = region.options[region.selectedIndex].text;
                            } else {
                                regionValue = '';
                            }

                            if (brand.value) {
                                brandValue = brand.options[brand.selectedIndex].text;
                            } else {
                                brandValue = '';
                            }
        
                            x.values({
                                id: idField.value,
                                name: '<div class="d-flex align-items-center">\
                                        <div class="flex-shrink-0"><img src="'+ profileImg.src + '" alt="" class="avatar-xs rounded-circle object-cover"></div>\
                                            <div class="flex-grow-1 ms-2 name">' + firstname.value + ' ' + lastname.value + '</div>\
                                        </div>',
                                email: email.value,
                                phone: phone.value,
                                id_number: idNumber.value,
                                id_verified: idVerifiedValue,
                                birth_date: formatDate(birthDate.value),
                                age: age.value,
                                gender: genderValue,
                                role: roleValue,
                                store: storeValue,
                                division: divisionValue,
                                region: regionValue,                                
                                brand: brandValue,
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

/*
|--------------------------------------------------------------------------
| Reset Password
|--------------------------------------------------------------------------
*/

passwordBtn.addEventListener("click", function (e) {
    e.preventDefault(); // Prevent the form from submitting naturally
    var form = document.getElementById("formPassword");

    // Password validation: check if the passwords match
    var passwordInput = document.getElementById('password');
    var confirmPasswordInput = document.getElementById('input-password-confirmation');

    if (passwordInput.value !== confirmPasswordInput.value) {
        // Prevent the form from submitting
        event.preventDefault();

        // Add 'is-invalid' class to both password input fields
        passwordInput.classList.add('is-invalid');
        confirmPasswordInput.classList.add('is-invalid');

        // Display a custom error message if passwords don't match
        var errorElement = confirmPasswordInput.parentNode.querySelector('.invalid-feedback');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = "invalid-feedback";
            confirmPasswordInput.parentNode.appendChild(errorElement);
        }
        errorElement.innerText = "Passwords don't match";
    } else {
        // If passwords match, remove the invalid classes
        passwordInput.classList.remove('is-invalid');
        confirmPasswordInput.classList.remove('is-invalid');

        // Proceed with the AJAX submission
        var formData = new FormData(form);

        $.ajax({
            url: route('users.password'),
            type: 'POST',
            data: formData,
            async: false,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
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

                    document.getElementById("resetPassword-close").click(); // Close the modal

                    // Reset the password fields after success
                    passwordInput.value = ''; // Clear the input value
                    confirmPasswordInput.value = ''; // Clear the confirmation input value

                    // Set both input fields back to type "password"
                    passwordInput.type = 'password';
                    confirmPasswordInput.type = 'password';
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                let message = '';

                if (jqXHR.status === 400 || jqXHR.status === 422) {
                    message = jqXHR.responseJSON.message;
                } else if (textStatus === 'timeout') {
                    message = 'The request timed out. Please try again later.';
                } else {
                    message = 'An error occurred while processing your request. Please try again later.';
                }

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
            var itemValues = userList.get({
                id: itemId,
            });

            Array.from(itemValues).forEach(function (x) {
                deleteid = new DOMParser().parseFromString(x._values.id, "text/html");

                var isdeleteid = deleteid.body.innerHTML;

                if (isdeleteid == itemId) {
                    document.getElementById("delete-user").onclick = function () {                        
                        $.ajax({
                            url: route('managers.destroy', {id: isdeleteid}),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success:function(data) {            
                                if(data.success === true) {
                                    userList.remove("id", isdeleteid);
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
                url: route('managers.details', {id: itemId}),
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

                profileImg.src = 'images/' + data.user.avatar;

                firstname.value = data.user.firstname;

                lastname.value = data.user.lastname;

                email.value = data.user.email;

                phone.value = data.user.phone;

                idNumber.value = data.user.id_number;

                if(data.user.id_verified) {
                    idVerifiedVal.setChoiceByValue(data.user.id_verified.toString());
                }

                birthDate.value = data.user.birth_date;

                age.value = data.user.age;

                if(data.user.gender_id) {
                    genderVal.setChoiceByValue(data.user.gender_id.toString());
                }

                if(data.user.role_id) {
                    roleVal.setChoiceByValue(data.user.role_id.toString());
                }

                if(data.user.store_id) {
                    storeVal.setChoiceByValue(data.user.store_id.toString());
                }

                if(data.user.division_id) {
                    divisionVal.setChoiceByValue(data.user.division_id.toString());
                }

                if(data.user.region_id) {
                    regionVal.setChoiceByValue(data.user.region_id.toString());
                }                

                if(data.user.brand_id) {
                    brandVal.setChoiceByValue(data.user.brand_id.toString());
                }
            });
        }
    });

    Array.from(viewBtns).forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            e.target.closest("tr").children[1].innerText;
            itemId = e.target.closest("tr").children[1].innerText;
            var itemValues = userList.get({
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
                            <p class="text-muted">${x._values.role}</p>
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
                                            <td class="fw-medium" scope="row">Store</td>
                                            <td>${x._values.store}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Division</td>
                                            <td>${x._values.division}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Region</td>
                                            <td>${x._values.region}</td>
                                        </tr>                                        
                                        <tr>
                                            <td class="fw-medium" scope="row">Brand</td>
                                            <td>${x._values.brand}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-medium" scope="row">Role</td>
                                            <td>${x._values.role}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-grid gap-2 mt-4" >
                                <a href="`+ route('user-profile.index', {id: x._values.id}) +`" class="btn btn-primary" type="button">View Profile</a>
                            </div>
                        </div>`;
                    document.getElementById('contact-view-detail').innerHTML = codeBlock;
                }
            });
        });
    });

    // Add event listeners for password reset buttons
    Array.from(passwordBtns).forEach(function (btn) {
        btn.onclick = function (e) {
            // Retrieve the itemId from the closest table row
            itemId = e.target.closest("tr").children[1].innerText;
            
            // Set the hidden input field with the user ID
            document.getElementById("password-id").value = itemId;
        };
    });
}

function clearFields() {
    profileImg.src = "build/images/users/user-dummy-img.jpg";

    firstname.value = "";

    lastname.value = "";

    email.value = "";

    phone.value = "";

    idNumber.value = "";

    idVerifiedVal.removeActiveItems();
    idVerifiedVal.setChoiceByValue("");

    birthDate.value = "";

    age.value = "";

    genderVal.removeActiveItems();
    genderVal.setChoiceByValue("");

    roleVal.removeActiveItems();
    roleVal.setChoiceByValue("");

    storeVal.removeActiveItems();
    storeVal.setChoiceByValue("");

    divisionVal.removeActiveItems();
    divisionVal.setChoiceByValue("");

    regionVal.removeActiveItems();
    regionVal.setChoiceByValue("");
    
    brandVal.removeActiveItems();
    brandVal.setChoiceByValue("");
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
            html: '<div class="mt-3">' + '<lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>' + '<div class="mt-4 pt-2 fs-15 mx-5">' + '<h4>You are about to delete these users ?</h4>' + '<p class="text-muted mx-4 mb-0">Deleting these users will remove all of their information from the database.</p>' + '</div>' + '</div>',
            showCancelButton: true,
            confirmButtonClass: 'btn btn-primary w-xs me-2 mt-2',
            cancelButtonClass: 'btn btn-danger w-xs mt-2',
            confirmButtonText: "Yes, delete it!",
            buttonsStyling: false,
            showCloseButton: true
        }).then(function (result) {
            if (result.value) {
                for (i = 0; i < ids_array.length; i++) {
                    userList.remove("id", `${ids_array[i]}`);
                }
    
                $.ajax({
                    url: route('managers.destroyMultiple'),
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
            title: 'Please select at least one user',
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

/*
|--------------------------------------------------------------------------
| Export Users
|--------------------------------------------------------------------------
*/

// When the 'Export Users' button is clicked
$('#exportUsers').on('click', function (event) {
    event.preventDefault(); // Prevent the default form submission or link action

    // Cache the button element and get its current width to preserve layout
    var exportBtn = $('#exportUsers');
    var initialWidth = exportBtn.outerWidth();

    // Lock the button width and show a loading spinner inside the button
    exportBtn.css('width', initialWidth + 'px');
    exportBtn.removeClass('btn-label').addClass('d-flex justify-content-center');
    exportBtn.html('<div class="spinner-border text-light" style="width: 1.2rem; height: 1.2rem;" role="status"><span class="sr-only">Loading...</span></div>');
    exportBtn.prop('disabled', true); // Disable the button to prevent multiple clicks

    // Perform AJAX POST request to the Laravel export route
    $.ajax({
        url: route("users.reports.export"), // Laravel route helper wrapped via Ziggy or similar
        method: 'GET',
        xhrFields: { 
            responseType: 'blob' // Expect a file (blob) in the response
        },
        success: function(response) {
            // Create a link element to download the file
            var downloadUrl = window.URL.createObjectURL(response);
            var link = document.createElement('a');
            link.href = downloadUrl;
            link.download = "Internal Users Report.csv"; // File name
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Display success notification
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: 'Report exported successfully!',
                showConfirmButton: false,
                timer: 5000,
                showCloseButton: true,
                toast: true
            });
        },
        error: function () {
            // Show error notification if export fails
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: 'Error exporting users.',
                showConfirmButton: false,
                timer: 5000,
                showCloseButton: true,
                toast: true
            });
        },
        complete: function () {
            // Re-enable the button and reset it to its original state
            exportBtn.prop('disabled', false);
            exportBtn.html('<i class="ri-file-excel-2-fill label-icon align-middle fs-16 me-2"></i> Export Users');
            exportBtn.removeClass('d-flex justify-content-center').addClass('btn-label');
            exportBtn.css('width', ''); // Remove fixed width so it adapts to content again
        }
    });
});