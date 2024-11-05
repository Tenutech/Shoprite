
document.addEventListener("DOMContentLoaded", function () {
    const monthsOfYear = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    // Initialize charts as null initially
    window.vacancyTypesByMonthChart = null;
    window.vacanciesOverTimeChart = null;

    // Fetch initial data
    fetchData();

    // Event listener for the filter button
    document.getElementById('filter-button').addEventListener('click', function () {
        fetchData();
    });
});

function fetchData() {
    const store_id = document.getElementById('store_id').value;
    const brand_id = document.getElementById('brand_id').value;
    const town_id = document.getElementById('town_id').value;
    const province_id = document.getElementById('province_id').value;
    const date_range = document.getElementById('date_range').value;

    // Extract start and end dates from the Flatpickr date range
    let [start_date, end_date] = date_range ? date_range.split(' to ') : [defaultStartDate, defaultEndDate];

    $.ajax({
        url: route('stores.reports.update'),
        type: "POST",
        data: {
            store_id,
            brand_id,
            town_id,
            province_id,
            start_date,
            end_date,
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log(response);
            const data = response.data;
            // Update the dashboard with the new data
            updateTotals(data);

            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: response.message,
                showConfirmButton: false,
                timer: 2000,
                showCloseButton: true,
                toast: true
            })
        },
        error: function (jqXHR, textStatus, errorThrown) {
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

function updateTotals(data) {

    let totalApplicantsPlaced = data.totalApplicantsPlaced;
    let averageTimetoHire = data.averageTimetoHire;
    let averageAssementScore = data.averageAssementScore;
    let averageDistanceApplicantsAppointed = data.averageDistanceApplicantsAppointed;
    let shortlistToHireRatio = data.shortlistToHireRatio;
    let interviewToHireRatio = data.interviewToHireRatio;

    document.getElementById("totalApplicantsPlacedValue").innerHTML = totalApplicantsPlaced;
    document.getElementById("averageTimetoHireValue").innerHTML = averageTimetoHire;
    document.getElementById("averageAssementScoreValue").innerHTML = averageAssementScore;
    document.getElementById("averageDistanceApplicantsAppointedValue").innerHTML = averageDistanceApplicantsAppointed;
    document.getElementById("shortlistToHireRatioValue").innerHTML = shortlistToHireRatio;
    document.getElementById("interviewToHireRatioValue").innerHTML = interviewToHireRatio;
}

function exportStores() {
    let filters = getFilters();
    let url = `{{ route('stores.reports.export') }}?` + new URLSearchParams(filters).toString();
    window.open(url, '_blank');
}

function getFilters() {
    return {
        store_id: document.getElementById('store_id').value,
        brand_id: document.getElementById('brand_id').value,
        town_id: document.getElementById('town_id').value,
        province_id: document.getElementById('province_id').value,
        region_id: document.getElementById('region_id').value,
        divsion_id: document.getElementById('divsion_id').value,
        date_range: document.getElementById('date_range').value,
    };
}

/*
|--------------------------------------------------------------------------
| Export Report
|--------------------------------------------------------------------------
*/

$(document).ready(function() {
    $('#exportReport').on('click', function(event) {
        event.preventDefault(); // Prevent default action

        // Reference the export button and save its initial width
        var exportBtn = $('#exportReport');
        var initialWidth = exportBtn.outerWidth(); // Get the initial width

        // Set the button to fixed width and show the spinner
        exportBtn.css('width', initialWidth + 'px');
        exportBtn.removeClass('btn-label').addClass('d-flex justify-content-center');
        exportBtn.html('<div class="spinner-border text-light" style="width: 1.2rem; height: 1.2rem;" role="status"><span class="sr-only">Loading...</span></div>');
        exportBtn.prop('disabled', true); // Disable the button

        // Get the form data from #formFilters
        var formData = new FormData($('#formFilters')[0]);

        $.ajax({
            url: route("stores.reports.export"),
            method: 'POST',
            data: formData,
            processData: false,  // Required for FormData
            contentType: false,  // Required for FormData
            xhrFields: {
                responseType: 'blob' // Important to handle binary data from server response
            },
            success: function(response) {
                // Create a link element to download the file
                var downloadUrl = window.URL.createObjectURL(response);
                var link = document.createElement('a');
                link.href = downloadUrl;
                link.download = "Applicants Report.xlsx"; // File name
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Display success notification
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: 'Report exported successfully!',
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true,
                    toast: true
                });
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

                // Display error notification
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: message,
                    showConfirmButton: false,
                    timer: 5000,
                    showCloseButton: true,
                    toast: true
                });
            },
            complete: function() {
                // Re-enable the button, restore original text, and re-add btn-label class
                exportBtn.prop('disabled', false);
                exportBtn.html('<i class="ri-file-excel-2-fill label-icon align-middle fs-16 me-2"></i> Export Report'); // Original button text
                exportBtn.removeClass('d-flex justify-content-center').addClass('btn-label'); // Restore original class
                exportBtn.css('width', ''); // Remove the fixed width
            }
        });
    });
});