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
    
    // Register Handlebars helpers
    Handlebars.registerHelper('ifeq', function(a, b, options) {
        if (a === b) {
            return options.fn(this);
        }
        return options.inverse(this);
    });
    
    Handlebars.registerHelper('ifneq', function(a, b, options) {
        if (a !== b) {
            return options.fn(this);
        }
        return options.inverse(this);
    });
    
    Handlebars.registerHelper('formatAdminUnit', function(adminUnit) {
        let cleanAdminUnit = adminUnit;
        
        // Look for " - " pattern and take everything after it
        let dashPos = adminUnit.indexOf(' - ');
        if (dashPos !== -1) {
            cleanAdminUnit = adminUnit.substring(dashPos + 3);
        }
        
        return cleanAdminUnit;
    });
    
    // Compile the templates
    const resultsTemplate = Handlebars.compile($('#results-template').html());
    const paginationTemplate = Handlebars.compile($('#pagination-template').html());
    
    // Function to update faculty dropdown based on campus selection
    function updateFacultyDropdown(campus) {
        // Show loading indicator in the faculty dropdown
        $('#faculty-filter').html('<option value="all">Loading...</option>');
        
        // Send AJAX request to get faculties for the selected campus
        $.ajax({
            url: award_search_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_faculties_by_campus',
                nonce: award_search_ajax.nonce,
                campus: campus
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Clear and rebuild faculty dropdown
                    const facultyDropdown = $('#faculty-filter');
                    facultyDropdown.empty();
                    
                    // Add default "Select one" option
                    facultyDropdown.append('<option value="all">Select one</option>');
                    
                    // Add faculty options from response
                    if (response.data.faculties && response.data.faculties.length > 0) {
                        response.data.faculties.forEach(function(faculty) {
                            facultyDropdown.append('<option value="' + faculty + '">' + faculty + '</option>');
                        });
                    } else {
                        // No faculties found for this campus
                        facultyDropdown.append('<option value="" disabled>No faculties found</option>');
                    }
                    
                    // Reset faculty selection to "all"
                    facultyDropdown.val('all');
                    currentSearchParams.faculty_filter = 'all';
                    
                    // If we have search params, perform a new search with updated faculties
                    if ($('#award-search-input').val().length >= 2 || 
                        $('#campus-filter').val() !== 'all' || 
                        $('#graduate-filter').val() !== 'all' ||
                        $('#award-type-filter').val() !== 'all') {
                        performSearch(1); // Reset to first page with new faculties
                    }
                } else {
                    // Error handling
                    $('#faculty-filter').html('<option value="all">Select one</option>');
                }
            },
            error: function() {
                // Error handling
                $('#faculty-filter').html('<option value="all">Select one</option>');
            }
        });
    }
    
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
    
    // Display search results using Handlebars template
    function displayResults(results, pagination) {
        if (results.length === 0) {
            $('#award-search-results').html('<p>No results found</p>');
            return;
        }
        
        // Prepare the data for the template
        const templateData = {
            results: results,
            total_results: pagination ? pagination.total_results : results.length
        };
        
        // Render the template with the data
        const html = resultsTemplate(templateData);
        $('#award-search-results').html(html);
    }

    // Display pagination controls using Handlebars template
    function displayPagination(pagination) {
        if (!pagination || pagination.total_pages <= 1) {
            $('#award-search-pagination').html('');
            return;
        }
        
        // Calculate page numbers to show
        const maxPages = 5; // Maximum number of page buttons to show
        let startPage = Math.max(1, pagination.current_page - Math.floor(maxPages / 2));
        let endPage = Math.min(pagination.total_pages, startPage + maxPages - 1);
        
        if (endPage - startPage + 1 < maxPages && startPage > 1) {
            startPage = Math.max(1, endPage - maxPages + 1);
        }
        
        // Generate page numbers array
        let pageNumbers = [];
        for (let i = startPage; i <= endPage; i++) {
            pageNumbers.push({
                number: i,
                current: i === pagination.current_page
            });
        }
        
        // Prepare the data for the template
        const templateData = {
            show_pagination: true,
            current_page: pagination.current_page,
            total_pages: pagination.total_pages,
            total_results: pagination.total_results,
            start_result: (pagination.current_page - 1) * pagination.per_page + 1,
            end_result: Math.min(pagination.current_page * pagination.per_page, pagination.total_results),
            has_prev: pagination.current_page > 1,
            has_next: pagination.current_page < pagination.total_pages,
            prev_page: pagination.current_page - 1,
            next_page: pagination.current_page + 1,
            page_numbers: pageNumbers
        };
        
        // Render the template with the data
        const html = paginationTemplate(templateData);
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
        
        // Also refresh the faculty dropdown to show all faculties
        updateFacultyDropdown('all');
        
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
    
    // Handle campus filter change - update faculty dropdown
    $('#campus-filter').on('change', function() {
        const selectedCampus = $(this).val();
        updateFacultyDropdown(selectedCampus);
    });
    
    // Handle other filter changes
    $('#graduate-filter, #award-type-filter').on('change', function() {
        // Trigger search when any filter changes
        if ($('#award-search-input').val().length >= 2 || 
            $('#campus-filter').val() !== 'all' || 
            $('#faculty-filter').val() !== 'all' ||
            $('#graduate-filter').val() !== 'all' ||
            $('#award-type-filter').val() !== 'all') {
            performSearch(1); // Reset to first page on filter change
        }
    });
    
    // Also handle faculty filter changes separately
    $('#faculty-filter').on('change', function() {
        // Trigger search when faculty filter changes
        if ($('#award-search-input').val().length >= 2 || 
            $('#campus-filter').val() !== 'all' || 
            $('#faculty-filter').val() !== 'all' ||
            $('#graduate-filter').val() !== 'all' ||
            $('#award-type-filter').val() !== 'all') {
            performSearch(1); // Reset to first page on filter change
        }
    });
});