/**
 * Award Search Plugin - Core Search Module
 * Handles search functionality and state management
 */
const AwardSearch = (function($) {
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
    
    /**
     * Initialize search module
     */
    function init() {
        // No initialization needed - other modules call this module's methods
    }
    
    /**
     * Update a specific search parameter
     */
    function updateSearchParam(param, value) {
        if (currentSearchParams.hasOwnProperty(param)) {
            currentSearchParams[param] = value;
        }
    }
    
    /**
     * Get all current search parameters
     */
    function getSearchParams() {
        return {...currentSearchParams};
    }
    
    /**
     * Perform search with current filters
     */
    function performSearch(page = 1) {
        // Make sure templates are available
        if (!AwardSearchTemplates.hasTemplates()) {
            console.error('Templates not available. Cannot perform search.');
            $('#award-search-results').html('<p>Error: Templates not loaded. Please refresh the page.</p>');
            return;
        }
        
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
                    // Render results and pagination using the template module
                    AwardSearchTemplates.renderResults(response.data.results, response.data.pagination);
                    AwardSearchTemplates.renderPagination(response.data.pagination);
                } else {
                    $('#award-search-results').html('<p>No results found</p>');
                    $('#award-search-pagination').html('');
                }
            },
            error: function(xhr, status, error) {
                $('#award-search-results').html('<p>Error occurred during search: ' + error + '</p>');
                $('#award-search-pagination').html('');
                console.error('AJAX error:', error);
            }
        });
    }
    
    // Public API
    return {
        init: init,
        performSearch: performSearch,
        updateSearchParam: updateSearchParam,
        getSearchParams: getSearchParams
    };
})(jQuery);