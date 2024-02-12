/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Profile-setting init js
*/

/*
|--------------------------------------------------------------------------
| Tab Open
|--------------------------------------------------------------------------
*/

$(document).ready(function () {    
    $("#profileSettingsTab.nav-tabs > li > a").on("shown.bs.tab", function(e) {
        localStorage.setItem('profileSettingsTab', $(e.target).attr('href'));
    });

    var profileSettingsTab = localStorage.getItem('profileSettingsTab');
    if(profileSettingsTab){
        $('#profileSettingsTab.nav-tabs > li > a[href="' + profileSettingsTab + '"]').tab('show');
    }
});

/*
|--------------------------------------------------------------------------
| Profile Picture
|--------------------------------------------------------------------------
*/

// Profile Foreground Img
if (document.querySelector("#profile-foreground-img-file-input")) {
    document.querySelector("#profile-foreground-img-file-input").addEventListener("change", function () {
        var preview = document.querySelector(".profile-wid-img");
        var file = document.querySelector(".profile-foreground-img-file-input")
            .files[0];
        var reader = new FileReader();
        reader.addEventListener(
            "load",
            function () {
                preview.src = reader.result;
            },
            false
        );
        if (file) {
            reader.readAsDataURL(file);
        }
    });
}

// Profile Foreground Img
if (document.querySelector("#avatar")) {
    document.querySelector("#avatar").addEventListener("change", function () {
        var preview = document.querySelector(".user-profile-image");
        var file = document.querySelector(".profile-img-file-input").files[0];
        var reader = new FileReader();
        reader.addEventListener(
            "load",
            function () {
                preview.src = reader.result;
            },
            false
        );
        if (file) {
            reader.readAsDataURL(file);
        }
    });
}

/*
|--------------------------------------------------------------------------
| Capitalize First Letter Of Each Word
|--------------------------------------------------------------------------
*/

function capitalizeFirstLetterOfEachWord(str) {
    return str.replace(/\w\S*/g, function(txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}

/*
|--------------------------------------------------------------------------
| Compute Completion Percentage
|--------------------------------------------------------------------------
*/

function computeCompletionPercentage() {
    var fields = [
        $('#firstname').val(),
        $('#lastname').val(),
        $('#email').val(),
        $('#phone').val(),
        $('#company').val(),
        $('#position').val(),
        $('#website').val()
    ];

    var filledFieldsCount = 0;

    $.each(fields, function(index, value) {
        if (value && value.trim() !== "") {
            filledFieldsCount++;
        }
    });

    var percentage = (filledFieldsCount / fields.length) * 100;
    return Math.round(percentage);
}

/*
|--------------------------------------------------------------------------
| User Form
|--------------------------------------------------------------------------
*/

$("#formUser").submit(function(e) {
	e.preventDefault();
	var formData = new FormData($(this)[0]);

    if ($(".profile-img-file-input").prop('files')[0]) {
        formData.append('avatar', $(".profile-img-file-input").prop('files')[0]);
    }

    if (this.checkValidity()) {
        $.ajax({
            url: route('profile-settings.update'),
            type: "post",
            data: formData,
            async: false,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data) {
                if (data.success == true) {     
                    if ($(".profile-img-file-input").prop('files')[0]) {
                        var avatar = URL.createObjectURL($(".profile-img-file-input").prop('files')[0]);
                        $("#topbar-avatar").attr("src", avatar);
                    }
                    
                    var firstName = capitalizeFirstLetterOfEachWord($("#firstname").val());
                    var lastName = capitalizeFirstLetterOfEachWord($("#lastname").val());
                    $("#user-name").text(firstName + " " + lastName);

                    var position = capitalizeFirstLetterOfEachWord($("#position").val());
                    $("#user-position").text(position);

                    var completionPercentage = computeCompletionPercentage();

                    // Update the progress bar
                    $('.progress-bar').css('width', completionPercentage + '%').attr('aria-valuenow', completionPercentage);
                    $('.progress-bar .label').text(completionPercentage + '%');

                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true,
                        toast: true
                    })                  
                } else {
                    Swal.fire({
                        html:   '<div class="mt-3">' + 
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
    } else {
        this.reportValidity();
    }
});

/*
|--------------------------------------------------------------------------
| Display Error
|--------------------------------------------------------------------------
*/

// Helper function to display errors
var displayError = function (inputId, message) {
    var $input = $("#" + inputId);
    var $errorElement = $input.next(".invalid-feedback");

    $input.addClass('is-invalid');

    if ($errorElement.length) {
        $errorElement.html('<strong>' + message + '</strong>');
    } else {
        $input.after('<span class="invalid-feedback" role="alert"><strong>' + message + '</strong></span>');
    }
};

/*
|--------------------------------------------------------------------------
| Password Form
|--------------------------------------------------------------------------
*/

$("#formPassword").submit(function(e) {
	e.preventDefault();
    
    $('#oldPassword, #newPassword, #confirmPassword').removeClass('is-invalid');
    $('.invalid-feedback').remove();

	var formData = new FormData($(this)[0]);

    if (this.checkValidity()) {
        $.ajax({
            url: route('profile-settings.updatePassword'),
            type: "post",
            data: formData,
            async: false,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success:function(data){
                if (data.success == true) {
                    // Remove is-invalid class from all password fields
                    $('#oldPassword, #newPassword, #confirmPassword').removeClass('is-invalid').val('');
            
                    Swal.fire({
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true,
                        toast: true
                    });
                } else {        
                    if (data.message == 'The password confirmation does not match.') {
                        if (data.errors.new_password) {
                            displayError("newPassword", data.errors.new_password[0]);
                        }
            
                        if (data.errors.confirm_password) {
                            displayError("confirmPassword", data.errors.confirm_password[0]);
                        }
                    }
            
                    if (data.status === "error" && data.message === "The old password is incorrect.") {
                        displayError("oldPassword", "The old password is incorrect.");
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status === 400) { // Bad Request, old password is incorrect
                    var responseData = jqXHR.responseJSON;
                    if (responseData.message) {
                        displayError("oldPassword", responseData.message);
                    }
                } else if (jqXHR.status === 422) {
                    var responseData = jqXHR.responseJSON;
                    if (responseData.message) {
                        displayError("newPassword", responseData.message);
                    }
                } else if (textStatus === 'timeout') {
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
        });
    } else {
        this.reportValidity();
    }
});

/*
|--------------------------------------------------------------------------
| User Form
|--------------------------------------------------------------------------
*/

$("#formNotifications").submit(function(e) {
	e.preventDefault();
	var formData = new FormData($(this)[0]);

    $.ajax({
        url: route('profile-settings.notifications'),
        type: "post",
        data: formData,
        async: false,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success:function(data) {
            if (data.success == true) {
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true,
                    toast: true
                })                  
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