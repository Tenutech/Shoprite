/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: file-manager Js File
*/

// Save Vacancy
$(document).on('click', '.vacancy-save', function(e) {
    e.preventDefault();

    let btn = $(this);
    let vacancyID = btn.data('bs-id');

    $.ajax({
        url: route('vacancy.save', {id: vacancyID}),
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success:function(data){                
            if(data.success == true){
                if($('#savedVacancies').length) {
                    if (data.message === 'Vacancy Unsaved!') {
                        $('#vacancy-saved-' + data.vacancy.id).remove();
                    } else {
                        var currentCards = $('#savedVacancies').children('.card').length;
                        if (currentCards < 6) {
                            $('#savedVacancies').append(
                                `<div class="card card-height-100" id="vacancy-saved-${data.vacancy.id}">
                                    <div class="card-body">
                                        <div class="dropdown float-end">
                                            <button type="button" class="btn btn-icon btn-sm btn-ghost-primary fs-7 custom-toggle active vacancy-save" data-bs-toggle="button" aria-pressed="true" data-bs-id="${data.id}">
                                                <span class="icon-on"><i class="ri-bookmark-line"></i></span>
                                                <span class="icon-off"><i class="ri-bookmark-fill"></i></span>
                                            </button>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="mb-1 pb-2 me-3">
                                                <i class="${data.vacancy.position.icon} text-${data.vacancy.position.color} fs-1"></i>
                                            </div>
                                            <div>
                                                <a href="${route('job-overview.index', {id: data.id})}">
                                                    <h6 class="fs-15 fw-bold mb-0">${data.vacancy.position.name}
                                                        <span class="text-muted fs-13">
                                                            ${data.vacancy.type.name}
                                                        </span>
                                                    </h6>
                                                </a>
                                                <p class="text-muted mb-0">
                                                    <i class="ri-bubble-chart-line align-bottom"></i> 
                                                    ${data.vacancy.store.brand.name}
                                                    <span class="ms-2">
                                                        <i class="ri-map-pin-2-line align-bottom"></i> 
                                                        ${data.vacancy.store.town.name}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>`
                            );
                        }
                    }
                }

                // Now, find the respective data in the datas variable
                if (typeof allJobList !== "undefined") {
                    const vacancyData = allJobList.find(vacancy => vacancy.encrypted_id === vacancyID);
                            
                    if (vacancyData) {
                        if (data.message === 'Vacancy Unsaved!') {
                            // Remove the user from the saved_by array
                            vacancyData.saved_by = vacancyData.saved_by.filter(user => user.id !== authID);
                        } else {
                            // Add the user to the saved_by array
                            if (!vacancyData.saved_by.some(user => user.id === authID)) {
                                vacancyData.saved_by.push({
                                    id: authID
                                });
                            }
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