/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: file-manager Js File
*/

$(document).ready(function() {
    let vacancyID = null;
    let triggeredButton = null;

    $('#applyModal').on('show.bs.modal', function (e) {
        // Get the button that triggered the modal
        triggeredButton = $(e.relatedTarget);

        // Get the data-bs-id attribute value from the triggered button
        vacancyID = triggeredButton.data('bs-id');
    });

    $('#apply').off('click').on('click', function() {
        if (vacancyID) {

            $('#apply').hide();
            $('#loading-apply').removeClass('d-none');

            $.ajax({
                url: route('vacancy.apply', {id: vacancyID}),
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success:function(data){                
                    if(data.success == true){
                        $('#applyModal').modal('hide');
                        
                        triggeredButton.removeClass('btn-secondary')
                            .addClass('btn-warning w-100')
                            .addClass('text-white')
                            .text('Application Pending')
                            .removeAttr('data-bs-toggle')
                            .removeAttr('href')
                            .removeAttr('data-bs-id');

                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: data.message,
                            showConfirmButton: false,
                            timer: 2000,
                            toast: true,
                            showCloseButton: true
                        });

                        $('#loading-apply').hide();
                        $('#apply').show();                        

                        // Now, find the respective data in the datas variable
                        if (typeof allJobList !== "undefined") {
                            const vacancyData = allJobList.find(vacancy => vacancy.encrypted_id === vacancyID);

                            if (vacancyData) {
                                // Update the vacancyData in allJobList based on the data from the success response
                                const applicant = vacancyData.applicants.find(user => user.id === authID);
                                
                                if (applicant) {
                                    applicant.pivot.approved = data.application.approved; // Update the approval status
                                } else {
                                    // If the user isn't found in the connected users list, add them with the new status
                                    vacancyData.applicants.push({
                                        id: authID,
                                        pivot: {
                                            approved: "Pending"
                                        }
                                    });
                                }
                            }
                            if (vacancyData) {
                                // Update the vacancyData in allJobList based on the data from the success response
                                const applicant = vacancyData.applicants.find(user => user.id === authID);
                                
                                if (!applicant) {
                                    // Add the user to the applicants array
                                    vacancyData.applicants.push({
                                        id: authID
                                    });
                                }
                            }
                        }
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
        }
    });
});