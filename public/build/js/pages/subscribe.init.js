/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: file-manager Js File
*/
$(document).on('submit', '#formSubscribe', function(e) {
    e.preventDefault();

    $.ajax({
        url: '/subscribe',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            email: $('#email').val()
        },
        success:function(data){                
            if(data.success == true) {
                $('#subscribeModal').modal('hide');

                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    toast: true,
                    showCloseButton: true
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
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
});