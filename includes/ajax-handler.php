<?php
/**
 * AJAX Handler for Award Search Plugin
 * 
 * Manages all AJAX endpoints and requests
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Award_Search_AJAX_Handler {
    /**
     * Search engine instance
     * 
     * @var Award_Search_Engine
     */
    private $search_engine;
    
    /**
     * Initialize the AJAX handler
     * 
     * @param Award_Search_Engine $search_engine The search engine instance
     */
    public function __construct($search_engine) {
        $this->search_engine = $search_engine;
        
        // Register AJAX handlers
        add_action('wp_ajax_award_search_query', array($this, 'handle_ajax_search'));
        add_action('wp_ajax_nopriv_award_search_query', array($this, 'handle_ajax_search'));
        
        add_action('wp_ajax_get_faculties_by_campus', array($this, 'get_faculties_by_campus'));
        add_action('wp_ajax_nopriv_get_faculties_by_campus', array($this, 'get_faculties_by_campus'));
    }
    
    /**
     * AJAX handler to get faculties filtered by campus
     */
    public function get_faculties_by_campus() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'award_search_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
        
        // Get and sanitize campus parameter
        $campus = isset($_POST['campus']) ? sanitize_text_field($_POST['campus']) : 'all';
        
        // Get faculties filtered by campus
        $faculties = $this->search_engine->get_faculties_by_campus($campus);
        
        wp_send_json_success(array(
            'faculties' => $faculties
        ));
        return;
    }
    
    /**
     * AJAX handler for search queries
     */
    public function handle_ajax_search() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'award_search_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }
    
        // Get and sanitize search parameters
        $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
        $campus_filter = isset($_POST['campus_filter']) ? sanitize_text_field($_POST['campus_filter']) : 'all';
        $faculty_filter = isset($_POST['faculty_filter']) ? sanitize_text_field($_POST['faculty_filter']) : 'all';
        $graduate_filter = isset($_POST['graduate_filter']) ? sanitize_text_field($_POST['graduate_filter']) : 'all';
        $award_type_filter = isset($_POST['award_type_filter']) ? sanitize_text_field($_POST['award_type_filter']) : 'all';
        
        // Pagination parameters
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10; // Default to 10 items per page
        
        // Perform search with filters
        $all_results = $this->search_engine->search_awards(
            $search_term, 
            $campus_filter, 
            $faculty_filter, 
            $graduate_filter, 
            $award_type_filter
        );
        
        // Calculate pagination info
        $total_results = count($all_results);
        $total_pages = ceil($total_results / $per_page);
        
        // Ensure page is within valid range
        $page = max(1, min($page, $total_pages));
        
        // Get paginated results
        $offset = ($page - 1) * $per_page;
        $paginated_results = array_slice($all_results, $offset, $per_page);
        
        // Return results with pagination info
        wp_send_json_success(array(
            'results' => $paginated_results,
            'pagination' => array(
                'current_page' => $page,
                'per_page' => $per_page,
                'total_results' => $total_results,
                'total_pages' => $total_pages
            )
        ));
        return;
    }
}