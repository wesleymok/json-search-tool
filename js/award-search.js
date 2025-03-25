jQuery(document).ready(function($) {
    // Function to perform search
    function performSearch() {
        const searchTerm = $('#award-search-input').val();
        const campusFilter = $('#campus-filter').val();
        const facultyFilter = $('#faculty-filter').val();
        const graduateFilter = $('#graduate-filter').val();
        const awardTypeFilter = $('#award-type-filter').val();
        
        // Only require search term if no filters are applied
        // if (searchTerm.length < 2 && campusFilter === 'all' && facultyFilter === 'all') {
        //     $('#award-search-results').html('<p>Please enter at least 2 characters or select a filter</p>');
        //     return;
        // }
        
        // Show loading indicator
        $('#award-search-results').html('<p>Searching...</p>');
        
        // Send AJAX request
        $.ajax({
            url: award_search_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'award_search_query',
                nonce: award_search_ajax.nonce,
                search_term: searchTerm,
                campus_filter: campusFilter,
                faculty_filter: facultyFilter,
                graduate_filter: graduateFilter,
                award_type_filter: awardTypeFilter
            },
            success: function(response) {
                if (response.success && response.data) {
                    displayResults(response.data);
                } else {
                    $('#award-search-results').html('<p>No results found</p>');
                }
            },
            error: function() {
                $('#award-search-results').html('<p>Error occurred during search</p>');
            }
        });
    }
    
    // Handle search form submission
    $('#award-search-form').on('submit', function(e) {
        e.preventDefault();
        performSearch();
    });
    
    // Display search results
    function displayResults(results) {
        if (results.length === 0) {
            $('#award-search-results').html('<p>No results found</p>');
            return;
        }
        
        let html = '<div class="search-results-count">' + results.length + ' awards found</div>';
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

    // Add a function to reset all filters
    function resetFilters() {
        // Reset all form elements to their default values
        $('#award-search-input').val('');
        $('#campus-filter').val('all');
        $('#faculty-filter').val('all');
        $('#graduate-filter').val('all');
        $('#award-type-filter').val('all');
        
        // Clear results and show a message
        $('#award-search-results').html('<p>Filters have been reset. Enter search terms or select filters to find awards.</p>');
    }

    // Add click event handler for the reset button
    $('#reset-filters-button').on('click', function() {
        resetFilters();
    });

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
            typingTimer = setTimeout(performSearch, doneTypingInterval);
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
            performSearch();
        }
    });
});