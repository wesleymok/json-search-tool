/**
 * Award Search Plugin - Pagination Module
 * Handles pagination interaction
 */
const AwardSearchPagination = (function($) {
    /**
     * Initialize pagination module
     */
    function init() {
        setupEventListeners();
    }
    
    /**
     * Set up pagination event listeners
     */
    function setupEventListeners() {
        // Handle pagination button clicks
        $(document).on('click', '.pagination-button:not(.disabled):not(.current)', function() {
            const page = $(this).data('page');
            AwardSearch.performSearch(page);
            
            // Scroll back to top of results
            $('html, body').animate({
                scrollTop: $('#award-search-form').offset().top
            }, 500);
        });
    }
    
    // Public API
    return {
        init: init
    };
})(jQuery);