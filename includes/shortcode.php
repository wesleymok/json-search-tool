<?php
/**
 * Shortcode Handler for Award Search Plugin
 * 
 * Manages the shortcode rendering and template output
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Award_Search_Shortcode {
    /**
     * Search engine instance
     * 
     * @var Award_Search_Engine
     */
    private $search_engine;
    
    /**
     * Initialize the shortcode handler
     * 
     * @param Award_Search_Engine $search_engine The search engine instance
     */
    public function __construct($search_engine) {
        $this->search_engine = $search_engine;
        
        // Register shortcode
        add_shortcode('award_search', array($this, 'render_shortcode'));
    }
    
    /**
     * Render the search form and results container
     * 
     * @param array $atts Shortcode attributes
     * @return string The HTML output
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => 'Search awards... (or press the "Find Awards" button to search all)',
            'button_text' => 'Search',
            'per_page' => 10,
        ), $atts);
    
        // Get unique faculties from data for the dropdown
        $faculties = $this->search_engine->get_unique_faculties();
        
        // Start output buffering
        ob_start();
        
        // Include all the templates
        include AWARD_SEARCH_PLUGIN_DIR . 'templates/search-form-template.php';
        include AWARD_SEARCH_PLUGIN_DIR . 'templates/results-template.php';
        include AWARD_SEARCH_PLUGIN_DIR . 'templates/pagination-template.php';
        
        // Return the buffered output
        return ob_get_clean();
    }
}