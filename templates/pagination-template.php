<?php
/**
 * Template for pagination (Handlebars)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Template for pagination -->
<script id="pagination-template" type="text/x-handlebars-template">
    {{#if show_pagination}}
        <div class="pagination-info">
            Showing {{start_result}} to {{end_result}} of {{total_results}} awards
        </div>
        
        <div class="pagination-controls">
            <!-- First/Previous buttons -->
            {{#if has_prev}}
                <button class="pagination-button first-page" data-page="1">First</button>
                <button class="pagination-button" data-page="{{prev_page}}">Previous</button>
            {{else}}
                <button class="pagination-button first-page disabled">First</button>
                <button class="pagination-button disabled">Previous</button>
            {{/if}}
            
            <!-- Page numbers -->
            {{#each page_numbers}}
                {{#if this.current}}
                    <button class="pagination-button current" data-page="{{this.number}}">{{this.number}}</button>
                {{else}}
                    <button class="pagination-button" data-page="{{this.number}}">{{this.number}}</button>
                {{/if}}
            {{/each}}
            
            <!-- Next/Last buttons -->
            {{#if has_next}}
                <button class="pagination-button" data-page="{{next_page}}">Next</button>
                <button class="pagination-button last-page" data-page="{{total_pages}}">Last</button>
            {{else}}
                <button class="pagination-button disabled">Next</button>
                <button class="pagination-button last-page disabled">Last</button>
            {{/if}}
        </div>
    {{/if}}
</script>