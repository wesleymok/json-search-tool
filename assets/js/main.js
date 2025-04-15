/**
 * Award Search Plugin - Main Entry Point
 * Initializes and coordinates all modules
 */
jQuery(document).ready(function($) {
    // Initialize modules in the correct order
    AwardSearchTemplates.init();
    AwardSearchFilters.init();
    AwardSearchPagination.init();
    AwardSearch.init();
});