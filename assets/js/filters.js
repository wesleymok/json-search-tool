/**
 * Award Search Plugin - Filters Module
 * Handles all filter-related functionality
 */
const AwardSearchFilters = (function($) {
    // Typing timer for search debounce
    let typingTimer;
    const doneTypingInterval = 500; // ms
    
    /**
     * Initialize filters module
     */
    function init() {
        setupEventListeners();
    }
    
    /**
     * Set up all event listeners
     */
    function setupEventListeners() {
        // Handle search form submission
        $('#award-search-form').on('submit', function(e) {
            e.preventDefault();
            AwardSearch.performSearch(1); // Reset to first page on new search
        });
        
        // Handle live search as user types
        $('#award-search-input').on('keyup', function() {
            clearTimeout(typingTimer);
            if (shouldPerformSearch()) {
                typingTimer = setTimeout(function() {
                    AwardSearch.performSearch(1);
                }, doneTypingInterval);
            }
        });
        
        // Handle campus filter change
        $('#campus-filter').on('change', function() {
            const selectedCampus = $(this).val();
            updateFacultyDropdown(selectedCampus);
        });
        
        // Handle other filter changes
        $('#graduate-filter, #award-type-filter').on('change', function() {
            if (shouldPerformSearch()) {
                AwardSearch.performSearch(1);
            }
        });
        
        // Handle faculty filter changes
        $('#faculty-filter').on('change', function() {
            if (shouldPerformSearch()) {
                AwardSearch.performSearch(1);
            }
        });
        
        // Add click event handler for the reset button
        $('#reset-filters-button').on('click', function() {
            resetFilters();
        });
    }
    
    /**
     * Check if search should be performed based on current filter values
     */
    function shouldPerformSearch() {
        return $('#award-search-input').val().length >= 2 || 
               $('#campus-filter').val() !== 'all' || 
               $('#faculty-filter').val() !== 'all' || 
               $('#graduate-filter').val() !== 'all' ||
               $('#award-type-filter').val() !== 'all';
    }
    
    /**
     * Update faculty dropdown based on selected campus
     */
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
                    AwardSearch.updateSearchParam('faculty_filter', 'all');
                    
                    // If we have search params, perform a new search with updated faculties
                    if (shouldPerformSearch()) {
                        AwardSearch.performSearch(1); // Reset to first page with new faculties
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
    
    /**
     * Reset all filters to default values
     */
    function resetFilters() {
        // Reset all form elements to their default values
        $('#award-search-input').val('');
        $('#campus-filter').val('all');
        $('#faculty-filter').val('all');
        $('#graduate-filter').val('all');
        $('#award-type-filter').val('all');
        
        // Reset pagination
        $('#current-page').val(1);
        AwardSearch.updateSearchParam('page', 1);
        
        // Also refresh the faculty dropdown to show all faculties
        updateFacultyDropdown('all');
        
        // Clear results and show a message
        $('#award-search-results').html('<p>Filters have been reset. Enter search terms or select filters to find awards.</p>');
        $('#award-search-pagination').html('');
    }
    
    // Public API
    return {
        init: init,
        updateFacultyDropdown: updateFacultyDropdown,
        resetFilters: resetFilters,
        shouldPerformSearch: shouldPerformSearch
    };
})(jQuery);