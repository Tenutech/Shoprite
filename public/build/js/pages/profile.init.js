/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: Profile init js
*/

/*
|--------------------------------------------------------------------------
| Tab Open
|--------------------------------------------------------------------------
*/

$(document).ready(function() {
    var hash = window.location.hash;
    var activeTab = localStorage.getItem('activeTab');

    // Check if there's a hash in the URL to override the localStorage
    if (hash) {
        var tabElement = new bootstrap.Tab(document.querySelector('.nav-pills a[href="' + hash + '"]'));
        if (tabElement) {
            tabElement.show();
            // Update localStorage with the new tab
            localStorage.setItem('activeTab', hash);
        } else if (activeTab) {
            // Fallback to localStorage if hash is not valid
            tabElement = new bootstrap.Tab(document.querySelector('.nav-pills a[href="' + activeTab + '"]'));
            if (tabElement) {
                tabElement.show();
            }
        }
    } else if (activeTab) {
        // Use localStorage if no hash is present
        var tabElement = new bootstrap.Tab(document.querySelector('.nav-pills a[href="' + activeTab + '"]'));
        if (tabElement) {
            tabElement.show();
        }
    }

    // Save the clicked tab to localStorage
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        var clickedTab = $(e.target).attr('href');
        localStorage.setItem('activeTab', clickedTab);
    });
});

/*
|--------------------------------------------------------------------------
| Swiper
|--------------------------------------------------------------------------
*/

var swiper = new Swiper(".project-swiper", {
    slidesPerView: 1,
    spaceBetween: 24,
    navigation: {
        nextEl: ".slider-button-next",
        prevEl: ".slider-button-prev",
    },
    breakpoints: {
        640: {
            slidesPerView: 1,
            spaceBetween: 15,
        },
        768: {
            slidesPerView: 2,
            spaceBetween: 20,
        },
        1200: {
            slidesPerView: 3,
            spaceBetween: 25,
        },
    },
});

/*
|--------------------------------------------------------------------------
| Colors
|--------------------------------------------------------------------------
*/

// get colors array from the string
function getChartColorsArray(chartId) {
    if (document.getElementById(chartId) !== null) {
        var colors = document.getElementById(chartId).getAttribute("data-colors");
        colors = JSON.parse(colors);
        return colors.map(function (value) {
            var newValue = value.replace(" ", "");
            if (newValue.indexOf(",") === -1) {
                var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                if (color) return color;
                else return newValue;;
            } else {
                var val = value.split(',');
                if (val.length == 2) {
                    var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                    rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                    return rgbaColor;
                } else {
                    return newValue;
                }
            }
        });
    }
}

/*
|--------------------------------------------------------------------------
| Literacy Score
|--------------------------------------------------------------------------
*/

// Calculate the series and labels based on the given data
var literacySeries = [];
var literacyLabels = [];
for (var i = 1; i <= literacyScore; i++) {
    literacySeries.push(i);
    literacyLabels.push(i.toString());
}

var options = {
    series: literacySeries,
    chart: {
        height: 300,
        type: 'donut',
    },
    labels: literacyLabels,
    theme: {
        monochrome: {
            enabled: true,
            color: '#405189',
            shadeTo: 'light',
            shadeIntensity: 0.6
        }
    },
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -5
            }
        }
    },
    dataLabels: {
        formatter: function (val, opts) {
            var name = opts.w.globals.labels[opts.seriesIndex];
            return name; // Only return the number, not the percentage
        },
        dropShadow: {
            enabled: false,
        }
    },
    legend: {
        show: false
    },
    title: {
        text: literacy,
        floating: true,
        offsetY: 125,
        align: 'center',
        style: {
            fontSize: '20px',
            fontWeight: 'bold'
        }
    }
};

if(document.querySelector("#literacy_chart")){
    var chart = new ApexCharts(document.querySelector("#literacy_chart"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| Numeracy Score
|--------------------------------------------------------------------------
*/

// Calculate the series and labels based on the given data
var numeracySeries = [];
var numeracyLabels = [];
for (var i = 1; i <= numeracyScore; i++) {
    numeracySeries.push(i);
    numeracyLabels.push(i.toString());
}

var options = {
    series: numeracySeries,
    chart: {
        height: 300,
        type: 'donut',
    },
    labels: numeracyLabels,
    theme: {
        monochrome: {
            enabled: true,
            color: '#3d78e3',
            shadeTo: 'light',
            shadeIntensity: 0.6
        }
    },
    plotOptions: {
        pie: {
            dataLabels: {
                offset: -5
            }
        }
    },
    dataLabels: {
        formatter: function (val, opts) {
            var name = opts.w.globals.labels[opts.seriesIndex];
            return name; // Only return the number, not the percentage
        },
        dropShadow: {
            enabled: false,
        }
    },
    legend: {
        show: false
    },
    title: {
        text: numeracy,
        floating: true,
        offsetY: 125,
        align: 'center',
        style: {
            fontSize: '20px',
            fontWeight: 'bold'
        }
    }
};

if(document.querySelector("#numeracy_chart")){
    var chart = new ApexCharts(document.querySelector("#numeracy_chart"), options);
    chart.render();
}

/*
|--------------------------------------------------------------------------
| File Form
|--------------------------------------------------------------------------
*/

$("#formFile").submit(function(e) {
    e.preventDefault();

    var formData = new FormData($('#formFile')[0]);

    $.ajax({
        url: route('document.store'),
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
                            <a href="${route('document.view', { id: data.encrypted_id })}" target="_blank">
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
                                            <a class="dropdown-item viewfile-list" href="${route('document.view', { id: data.encrypted_id })}" target="_blank">View</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item downloadfile-list" href="${route('document.download', { id: data.encrypted_id })}">Download</a>
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
| File Delete
|--------------------------------------------------------------------------
*/

$('#fileDeleteModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var fileID = button.data('bs-id'); // Extract info from data-bs-* attributes

    $('#delete-file').off('click').on('click', function() {
        $.ajax({
            url: route('document.destroy', {id: fileID}),
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

/*
|--------------------------------------------------------------------------
| Profile Delete
|--------------------------------------------------------------------------
*/


document.getElementById('profile-delete').addEventListener('click', function() {
    $.ajax({
        url: route("profile.delete"),
        type: 'POST',
        data: {
            "_token": $('meta[name="csrf-token"]').attr('content'),
        },
        success: function(response) {
            if (response.success) {
                window.location.href = response.redirect;
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: 'Failed to delete profile',
                showConfirmButton: false,
                timer: 5000,
                showCloseButton: true,
                toast: true
            });
            console.error(error);
        }
    });
});