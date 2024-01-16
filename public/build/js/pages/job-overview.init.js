/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: file-manager Js File
*/

$(document).ready(function() {

	/*
	|--------------------------------------------------------------------------
	| Delete Vacancy
	|--------------------------------------------------------------------------
	*/

	$('#vacancyDeleteModal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var vacancyID = button.data('bs-id'); // Extract info from data-bs-* attributes

		$('#vacancy-delete').click(function() {
			$.ajax({
				url: route('vacancy.destroy', {id: vacancyID}),
				method: 'DELETE',
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
							toast: true,
							showCloseButton: true
						});

						window.location.href = route('vacancies.index');
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
	});

	/*
	|--------------------------------------------------------------------------
	| Upload File
	|--------------------------------------------------------------------------
	*/

	$("#formFile").submit(function(e) {
        e.preventDefault();

        var formData = new FormData($('#formFile')[0]);

        $.ajax({
            url: route('file.store'),
            type: 'POST',
            data: formData,
            async: false,
            processData: false,
            contentType: false,
			headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
				if (data.success == true) {			
					let fileIconClass = '';
					let fileIconColorClass = '';

					switch (data.file.type) {
						case 'png':
						case 'jpg':
						case 'jpeg':
							fileIconClass = 'ri-gallery-fill';
							fileIconColorClass = 'text-success';
							break;
						case 'pdf':
							fileIconClass = 'ri-file-pdf-fill';
							fileIconColorClass = 'text-danger';
							break;
						case 'docx':
							fileIconClass = 'ri-file-word-2-fill';
							fileIconColorClass = 'text-primary';
							break;
						case 'xls':
						case 'xlsx':
							fileIconClass = 'ri-file-excel-2-fill';
							fileIconColorClass = 'text-success';
							break;
						case 'csv':
							fileIconClass = 'ri-file-excel-fill';
							fileIconColorClass = 'text-success';
							break;
						default:
							fileIconClass = 'ri-file-text-fill';
							fileIconColorClass = 'text-secondary';
					}

					// Then, when constructing your HTML, use these classes:
					let fileIconHTML = `<i class="${fileIconClass} align-bottom ${fileIconColorClass}"></i>`;

					// Strip the timestamp from the filename
					let fileNameWithoutTimestamp = data.file.name.substring(0, data.file.name.lastIndexOf('-'));

					// Format the filesize
					let formattedFileSize = data.file.size;
					if (formattedFileSize < 0.1) {
						formattedFileSize = (formattedFileSize * 1024).toFixed(1) + ' KB';
					} else {
						formattedFileSize = formattedFileSize.toFixed(1) + ' MB';
					}
			
					// Construct the new row HTML
					let newRow = `
						<tr data-file-id="${data.file.id}">
							<td>
								<a href="${route('file.view', { id: data.encrypted_id })}" target="_blank">
									<div class="d-flex align-items-center">
										<div class="flex-shrink-0 fs-17 me-2 filelist-icon">${fileIconHTML}</div>
										<div class="flex-grow-1 filelist-name">${fileNameWithoutTimestamp}</div>
									</div>
								</a>
							</td>
							<td>${data.file.type}</td>
							<td>${formattedFileSize}</td>
							<td>${data.upload_date}</td>
							<td>
								<div class="d-flex gap-3 justify-content-center">
									<div class="dropdown">
										<button class="btn btn-light btn-icon btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
											<i class="ri-more-fill align-bottom"></i>
										</button>
										<ul class="dropdown-menu dropdown-menu-end">
											<li>
												<a class="dropdown-item viewfile-list" href="${route('file.view', { id: data.encrypted_id })}" target="_blank">View</a>
											</li>
											<li>
												<a class="dropdown-item downloadfile-list" href="${route('file.download', { id: data.encrypted_id })}">Download</a>
											</li>
											<li class="dropdown-divider"></li>
											<li>
												<button class="dropdown-item downloadfile-list" href="#fileDeleteModal" data-bs-toggle="modal" data-bs-id="${data.file.id}">Delete</button>
											</li>
										</ul>
									</div>
								</div>
							</td>
						</tr>
					`;
			
					// Append the new row to the table
					$('#fileTable tbody').append(newRow);			
					
					Swal.fire({
						position: 'top-end',
						icon: 'success',
						title: data.message,
						showConfirmButton: false,
						timer: 2000,
						showCloseButton: true,
						toast: true
					});

					// Close the modal and show a success message
					$('#fileUploadModal').modal('toggle');
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
        })
    });

	/*
	|--------------------------------------------------------------------------
	| Delete File
	|--------------------------------------------------------------------------
	*/

	$('#fileDeleteModal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var fileID = button.data('bs-id'); // Extract info from data-bs-* attributes

		$('#delete-file').off('click').on('click', function() {
			$.ajax({
				url: route('file.destroy', {id: fileID}),
				method: 'DELETE',
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function (data) {
					if (data.success === true) {	
						// Remove the row from the table
						$('#file-list').find('tr[data-file-id="' + fileID + '"]').remove();
	
						//Sweet Alert
						Swal.fire({
							position: 'top-end',
							icon: 'success',
							title: data.message,
							showConfirmButton: false,
							timer: 2000,
							toast: true,
							showCloseButton: true
						});
	
						// Close the modal
						$('#fileDeleteModal').modal('hide');
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
	});
});