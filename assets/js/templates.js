/**
 * Award Search Plugin - Templates Module
 * Handles Handlebars template compilation and rendering
 */
const AwardSearchTemplates = (function($) {
    // Template variables
    let resultsTemplate;
    let paginationTemplate;
    
    /**
     * Initialize templates and register helpers
     */
    function init() {
        // Register Handlebars helpers
        registerHandlebarsHelpers();
        
        // Compile templates
        compileTemplates();
    }
    
    /**
     * Register all Handlebars helpers
     */
    function registerHandlebarsHelpers() {
        // Equal comparison helper
        Handlebars.registerHelper('ifeq', function(a, b, options) {
            if (a === b) {
                return options.fn(this);
            }
            return options.inverse(this);
        });
        
        // Not equal comparison helper
        Handlebars.registerHelper('ifneq', function(a, b, options) {
            if (a !== b) {
                return options.fn(this);
            }
            return options.inverse(this);
        });
        
        // Admin unit formatter helper
        Handlebars.registerHelper('formatAdminUnit', function(adminUnit) {
            // If adminUnit contains multiple entries (separated by semicolons)
            if (adminUnit.indexOf(';') !== -1) {
                // Split by semicolon, format each part, and rejoin with semicolons
                return adminUnit.split(';').map(function(part) {
                    return formatSingleAdminUnit(part.trim());
                }).join('; ');
            } else {
                // Format a single admin unit
                return formatSingleAdminUnit(adminUnit);
            }
            
            // Helper function to format a single admin unit
            function formatSingleAdminUnit(unit) {
                let cleanUnit = unit;
                
                // Look for " - " pattern and take everything after it
                let dashPos = unit.indexOf(' - ');
                if (dashPos !== -1) {
                    cleanUnit = unit.substring(dashPos + 3);
                }
                
                // Remove campus info in parentheses if present
                let parenthesisPos = cleanUnit.indexOf(' (');
                if (parenthesisPos !== -1) {
                    cleanUnit = cleanUnit.substring(0, parenthesisPos);
                }
                
                return cleanUnit.trim();
            }
        });
    }
    
    /**
     * Compile Handlebars templates
     */
    function compileTemplates() {
        // Make sure templates exist in the DOM
        if ($('#results-template').length > 0 && $('#pagination-template').length > 0) {
            // Compile templates
            resultsTemplate = Handlebars.compile($('#results-template').html());
            paginationTemplate = Handlebars.compile($('#pagination-template').html());
            console.log('Templates compiled successfully');
        } else {
            console.error('Templates not found. Results will not display.');
            setTimeout(compileTemplates, 500); // Try again after a delay
        }
    }
    
    /**
     * Render search results
     */
    function renderResults(results, pagination) {
        if (!resultsTemplate) {
            console.error('Results template not available');
            return;
        }
        
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
    
    /**
     * Render pagination controls
     */
    function renderPagination(pagination) {
        if (!paginationTemplate) {
            console.error('Pagination template not available');
            return;
        }
        
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
    
    // Public API
    return {
        init: init,
        renderResults: renderResults,
        renderPagination: renderPagination,
        hasTemplates: function() {
            return !!resultsTemplate && !!paginationTemplate;
        }
    };
})(jQuery);