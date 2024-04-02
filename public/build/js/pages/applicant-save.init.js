/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: file-manager Js File
*/

// Save Opportunity
$(document).on('click', '.save-applicant', function(e) {
    e.preventDefault();

    let btn = $(this);
    let applicantID = btn.data('bs-id');

    $.ajax({
        url: route('applicant.save', {id: applicantID}),
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success:function(data){                
            if (data.success == true) {
                // Find the applicant in the allcandidateList
                let applicant = allcandidateList.find(applicant => applicant.encrypted_id === applicantID);
                if (applicant) {
                    // Toggle the saved state based on the server's response
                    if (data.message === 'Applicant Saved!') {
                        // Assuming you want to add the user's ID to the saved_by array
                        applicant.saved_by.push({ id: data.userID });
                    } else if (data.message === 'Applicant Unsaved!') {
                        // Remove the user's ID from the saved_by array
                        const index = applicant.saved_by.findIndex(user => user.id === data.userID);
                        if (index > -1) {
                            applicant.saved_by.splice(index, 1);
                        }
                    }
                }

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