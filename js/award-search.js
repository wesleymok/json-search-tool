jQuery(document).ready(function($) {
    // Current search parameters - to be used for pagination
    let currentSearchParams = {
        search_term: '',
        campus_filter: 'all',
        faculty_filter: 'all',
        graduate_filter: 'all',
        award_type_filter: 'all',
        page: 1,
        per_page: $('#per-page').val() || 10
    };
    
    // Function to perform search
    function performSearch(page = 1) {
        // Update search parameters
        currentSearchParams.search_term = $('#award-search-input').val();
        currentSearchParams.campus_filter = $('#campus-filter').val();
        currentSearchParams.faculty_filter = $('#faculty-filter').val();
        currentSearchParams.graduate_filter = $('#graduate-filter').val();
        currentSearchParams.award_type_filter = $('#award-type-filter').val();
        currentSearchParams.page = page;
        
        // Update current page field
        $('#current-page').val(page);
        
        // Show loading indicator
        $('#award-search-results').html('<p>Searching...</p>');
        
        // Send AJAX request
        $.ajax({
            url: award_search_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'award_search_query',
                nonce: award_search_ajax.nonce,
                search_term: currentSearchParams.search_term,
                campus_filter: currentSearchParams.campus_filter,
                faculty_filter: currentSearchParams.faculty_filter,
                graduate_filter: currentSearchParams.graduate_filter,
                award_type_filter: currentSearchParams.award_type_filter,
                page: currentSearchParams.page,
                per_page: currentSearchParams.per_page
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Pass both results and pagination data to displayResults
                    displayResults(response.data.results, response.data.pagination);
                    displayPagination(response.data.pagination);
                } else {
                    $('#award-search-results').html('<p>No results found</p>');
                    $('#award-search-pagination').html('');
                }
            },
            error: function() {
                $('#award-search-results').html('<p>Error occurred during search</p>');
                $('#award-search-pagination').html('');
            }
        });
    }
    
    // Handle search form submission
    $('#award-search-form').on('submit', function(e) {
        e.preventDefault();
        performSearch(1); // Reset to first page on new search
    });
    
    // Display search results
    function displayResults(results, pagination) {
        if (results.length === 0) {
            $('#award-search-results').html('<p>No results found</p>');
            return;
        }
        let totalCount = pagination ? pagination.total_results : results.length;
        let html = '<div class="search-results-count">' + totalCount + ' awards found</div>';
        html += '<div class="search-results-list">';
        
        results.forEach(function(award) {
            html += '<div class="award-item">';
            html += '<h3>' + award['Award Name'] + '</h3>';
            
            html += '<div class="award-details">';
            html += '<p><strong>Award Number:</strong> ' + award['Award Number'] + '</p>';
            html += '<p><strong>Award Cycle:</strong> ' + award['Award Cycle'] + '</p>';
            if (award['Award Type']) {
                html += '<p><strong>Award Type:</strong> ' + 
                       (award['Award Type'] === 'AWRD'  ? 'Award' :
                        award['Award Type'] === 'SCHL'  ? 'Scholarship' :
                        award['Award Type'] === 'PRIZ'  ? 'Prize' :
                        award['Award Type'] === 'BURS'  ? 'Bursaries' :
                        award['Award Type'] === 'FELL' ? 'Fellowship' : award['Award Type']) + 
                       '</p>';
            }
            if (award['Administering Unit']) {
                let adminUnit = award['Administering Unit'];
                let cleanAdminUnit = adminUnit;
                
                // Look for " - " pattern and take everything after it
                let dashPos = adminUnit.indexOf(' - ');
                if (dashPos !== -1) {
                    cleanAdminUnit = adminUnit.substring(dashPos + 3);
                }
                
                html += '<p><strong>Department:</strong> ' + cleanAdminUnit + '</p>';
            }
            html += '<p><strong>Degree Level:</strong> ' + award['Eligible Learner Level'] + '</p>';
            html += '<p><strong>Application Type:</strong> ' + award['Application Type (Award Profile)'] + '</p>'
            
            // Add campus information if available
            if (award['Campus']) {
                html += '<p><strong>Campus:</strong> ' + 
                       (award['Campus'] === 'V' ? 'Vancouver' : 
                        award['Campus'] === 'O' ? 'Okanagan' : award['Campus']) + 
                       '</p>';
            }
            
            // Add description if available
            if (award['Award Description']) {
                html += '<div class="award-description">';
                html += '<h4>Description</h4>';
                html += '<p>' + award['Award Description'] + '</p>';
                html += '</div>';
            }
            
            html += '</div>'; // Close award-details
            html += '</div>'; // Close award-item
        });
        
        html += '</div>'; // Close search-results-list
        $('#award-search-results').html(html);
    }

    // Display pagination controls
    function displayPagination(pagination) {
        if (!pagination || pagination.total_pages <= 1) {
            $('#award-search-pagination').html('');
            return;
        }
        
        let html = '<div class="pagination-info">';
        html += 'Showing ' + ((pagination.current_page - 1) * pagination.per_page + 1);
        html += ' to ' + Math.min(pagination.current_page * pagination.per_page, pagination.total_results);
        html += ' of ' + pagination.total_results + ' awards';
        html += '</div>';
        
        html += '<div class="pagination-controls">';

        // First page button
        if (pagination.current_page > 1) {
            html += '<button class="pagination-button first-page" data-page="1">First</button>';
        } else {
            html += '<button class="pagination-button first-page disabled">First</button>';
        }
        
        // Previous page button
        if (pagination.current_page > 1) {
            html += '<button class="pagination-button" data-page="' + (pagination.current_page - 1) + '">Previous</button>';
        } else {
            html += '<button class="pagination-button disabled">Previous</button>';
        }
        
        // Page numbers
        const maxPages = 5; // Maximum number of page buttons to show
        let startPage = Math.max(1, pagination.current_page - Math.floor(maxPages / 2));
        let endPage = Math.min(pagination.total_pages, startPage + maxPages - 1);
        
        if (endPage - startPage + 1 < maxPages && startPage > 1) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                html += '<button class="pagination-button current" data-page="' + i + '">' + i + '</button>';
            } else {
                html += '<button class="pagination-button" data-page="' + i + '">' + i + '</button>';
            }
        }
        
        // Next page button
        if (pagination.current_page < pagination.total_pages) {
            html += '<button class="pagination-button" data-page="' + (pagination.current_page + 1) + '">Next</button>';
        } else {
            html += '<button class="pagination-button disabled">Next</button>';
        }

        // Last page button (new)
        if (pagination.current_page < pagination.total_pages) {
            html += '<button class="pagination-button last-page" data-page="' + pagination.total_pages + '">Last</button>';
        } else {
            html += '<button class="pagination-button last-page disabled">Last</button>';
        }
        
        html += '</div>';
        $('#award-search-pagination').html(html);
    }
    
    // Handle pagination button clicks
    $(document).on('click', '.pagination-button:not(.disabled):not(.current)', function() {
        const page = $(this).data('page');
        performSearch(page);
        
        // Scroll back to top of results
        $('html, body').animate({
            scrollTop: $('#award-search-form').offset().top
        }, 500);
    });

    // Add a function to reset all filters
    function resetFilters() {
        // Reset all form elements to their default values
        $('#award-search-input').val('');
        $('#campus-filter').val('all');
        $('#faculty-filter').val('all');
        $('#graduate-filter').val('all');
        $('#award-type-filter').val('all');
        
        // Reset pagination
        $('#current-page').val(1);
        currentSearchParams.page = 1;
        
        // Clear results and show a message
        $('#award-search-results').html('<p>Filters have been reset. Enter search terms or select filters to find awards.</p>');
        $('#award-search-pagination').html('');
    }

    // Add click event handler for the reset button
    $('#reset-filters-button').on('click', function() {
        resetFilters();
    });
    
    // Handle live search as user types
    let typingTimer;
    const doneTypingInterval = 500; // ms
    
    $('#award-search-input').on('keyup', function() {
        clearTimeout(typingTimer);
        // If we have enough search characters OR any filter is active
        if ($('#award-search-input').val().length >= 2 || 
            $('#campus-filter').val() !== 'all' || 
            $('#faculty-filter').val() !== 'all' || 
            $('#graduate-filter').val() !== 'all' ||
            $('#award-type-filter').val() !== 'all') {
            typingTimer = setTimeout(function() {
                performSearch(1); // Reset to first page on new search terms
            }, doneTypingInterval);
        }
    });
    
    // Handle filter changes
    $('#campus-filter, #faculty-filter, #graduate-filter, #award-type-filter').on('change', function() {
        // Trigger search when any filter changes
        if ($('#award-search-input').val().length >= 2 || 
            $('#campus-filter').val() !== 'all' || 
            $('#faculty-filter').val() !== 'all' ||
            $('#graduate-filter').val() !== 'all' ||
            $('#award-type-filter').val() !== 'all') {
            performSearch(1); // Reset to first page on filter change
        }
    });
});