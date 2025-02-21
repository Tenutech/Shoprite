/*
Template Name: Orient - Admin & Dashboard Template
Author: OTB Group
Website: https://orient.tenutech.com/
Contact: admin@tenutech.com
File: CRM-contact Js File
*/

// Set the default number of items per page
var perPage = 10;

// Table options configuration
var options = {
    valueNames: [ // List of class names used for sorting and searching
        "id",
        "number",
        "position",
        "type",
        "open",
        "filled",
        "sap",
        "date",
        "status"
    ],
    page: perPage, // Number of items per page
    pagination: true, // Enable pagination
    plugins: [
        // List.js plugin for pagination with left and right buffer
        ListPagination({
            left: 2, // Number of page links to show on the left of the current page
            right: 2 // Number of page links to show on the right of the current page
        })
    ]
};

// Initialize the list and add an event listener for when the list is updated
var vacancyList = new List("vacancyList", options).on("updated", function (list) {
    // Get the total number of matching items after filtering/searching
    const totalItems = list.matchingItems.length;
    
    // Check if there are any results
    const hasResults = totalItems > 0;
    
    // Toggle the display of the "No Result" message based on whether there are results
    document.getElementsByClassName("noresult")[0].style.display = hasResults ? "none" : "block";

    // Pagination controls
    const paginationWrap = document.querySelector(".pagination-wrap");
    const prevButton = document.querySelector(".pagination-prev");
    const nextButton = document.querySelector(".pagination-next");

    // Check if the current page is the first or last
    const isFirst = list.i == 1;
    const isLast = list.i > totalItems - list.page;

    // Show pagination if there are results and more than the perPage limit
    if (hasResults && totalItems > perPage) {
        paginationWrap.style.display = "flex"; // Show pagination
        prevButton.classList.toggle("disabled", isFirst); // Disable "Previous" button if on the first page
        nextButton.classList.toggle("disabled", isLast);  // Disable "Next" button if on the last page
    } else {
        paginationWrap.style.display = "none"; // Hide pagination if there are fewer results than perPage
    }
});

var removeBtns = document.getElementsByClassName("remove-item-btn");
refreshCallbacks();

// Handle changes to the per-page selection
var perPageSelect = document.getElementById("per-page-select");
perPageSelect.addEventListener("change", function() {
    // Update the perPage variable with the selected value
    perPage = parseInt(this.value);
    
    // Update the list to reflect the new perPage value
    vacancyList.page = perPage;
    vacancyList.update();
});

// Prevent default behavior for pagination links created by List.js
document.querySelectorAll(".listjs-pagination a").forEach(function(anchor) {
    anchor.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent the default click action to allow List.js to handle the pagination
    });
});

// Custom pagination logic for "Previous" and "Next" buttons
document.querySelector(".pagination-wrap").addEventListener("click", function(event) {
    
    // Handle "Previous" button click
    if (event.target.classList.contains("pagination-prev") || 
        (event.target.parentElement && event.target.parentElement.classList.contains("pagination-prev"))) {
        
        event.preventDefault(); // Prevent default behavior of the button

        // Get the currently active pagination element
        const activeElement = document.querySelector(".pagination.listjs-pagination")?.querySelector(".active");
        
        // Move to the previous page if the "Previous" button is clicked
        if (activeElement) {
            const previousElement = activeElement.previousElementSibling;
            if (previousElement && previousElement.children[0]) {
                previousElement.children[0].click(); // Trigger the click on the previous page link
            }
        }
    }

    // Handle click on the pagination numbers
    if (event.target.closest(".listjs-pagination")) {
        event.preventDefault(); // Prevent default behavior of pagination numbers
        event.target.click();    // Trigger the click event on the pagination link
    }

    // Handle "Next" button click
    if (event.target.classList.contains("pagination-next") || 
        (event.target.parentElement && event.target.parentElement.classList.contains("pagination-next"))) {

        event.preventDefault(); // Prevent default behavior of the button

        // Get the currently active pagination element
        const activeElement = document.querySelector(".pagination.listjs-pagination")?.querySelector(".active");
        
        // Move to the next page if the "Next" button is clicked
        if (activeElement && activeElement.nextElementSibling && activeElement.nextElementSibling.children[0]) {
            activeElement.nextElementSibling.children[0].click(); // Trigger the click on the next page link
        }
    }
});

/*
|--------------------------------------------------------------------------
| Callbacks
|--------------------------------------------------------------------------
*/

function refreshCallbacks() {
    Array.from(removeBtns).forEach(function (btn) {
        btn.onclick = function (e) {
            e.target.closest("tr").children[0].innerText;
            itemId = e.target.closest("tr").children[0].innerText;
            var itemValues = vacancyList.get({
                id: itemId,
            });

            Array.from(itemValues).forEach(function (x) {
                deleteid = new DOMParser().parseFromString(x._values.id, "text/html");

                var isdeleteid = deleteid.body.innerHTML;

                if (isdeleteid == itemId) {
                    document.getElementById("delete-vacancy").onclick = function () {                        
                        $.ajax({
                            url: route('vacancy.destroy', {id: isdeleteid}),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success:function(data) {            
                                if(data.success === true) {
                                    vacancyList.remove("id", isdeleteid);
                                    document.getElementById("deleteVacancy-close").click();
                                    document.querySelector(".pagination-wrap").style.display = "flex";
                                    Swal.fire({
                                        position: 'top-end',
                                        icon: 'success',
                                        title: data.message,
                                        showConfirmButton: false,
                                        timer: 3000,
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
}