<?php
/**
 * Plugin Name: Award Search Plugin
 * Description: Search through Award JSON data and display results. Filter by Campus and Faculties.
 * Version: 1.0
 * Author: Wesley Mok
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AWARD_SEARCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AWARD_SEARCH_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once AWARD_SEARCH_PLUGIN_DIR . 'includes/data-manager.php';
require_once AWARD_SEARCH_PLUGIN_DIR . 'includes/search-engine.php';
require_once AWARD_SEARCH_PLUGIN_DIR . 'includes/ajax-handler.php';
require_once AWARD_SEARCH_PLUGIN_DIR . 'includes/shortcode.php';

/**
 * Main plugin class to handle initialization
 */
class Award_Search_Plugin {
    /**
     * Data manager instance
     * 
     * @var Award_Search_Data_Manager
     */
    private $data_manager;
    
    /**
     * Search engine instance
     * 
     * @var Award_Search_Engine
     */
    private $search_engine;
    
    /**
     * AJAX handler instance
     * 
     * @var Award_Search_AJAX_Handler
     */
    private $ajax_handler;
    
    /**
     * Shortcode handler instance
     * 
     * @var Award_Search_Shortcode
     */
    private $shortcode;
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        // Load the JSON data
        $this->data_manager = new Award_Search_Data_Manager();
        
        // Initialize search engine with the data
        $this->search_engine = new Award_Search_Engine($this->data_manager);
        
        // Setup AJAX handler
        $this->ajax_handler = new Award_Search_AJAX_Handler($this->search_engine);
        
        // Setup shortcode
        $this->shortcode = new Award_Search_Shortcode($this->search_engine);
        
        // Register scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue necessary scripts and styles
     */
/**
 * Enqueue necessary scripts and styles
 */
public function enqueue_scripts() {
    wp_enqueue_script('jquery');
    
    // Enqueue Handlebars
    wp_enqueue_script(
        'handlebars',
        'https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.7/handlebars.min.js',
        array('jquery'),
        '4.7.7',
        true
    );
    
    // Load modular JS files in proper order with dependencies
    wp_enqueue_script(
        'award-search-templates',
        AWARD_SEARCH_PLUGIN_URL . 'assets/js/templates.js',
        array('jquery', 'handlebars'),
        '1.0',
        true
    );
    
    wp_enqueue_script(
        'award-search-filters',
        AWARD_SEARCH_PLUGIN_URL . 'assets/js/filters.js',
        array('jquery', 'award-search-templates'),
        '1.0',
        true
    );
    
    wp_enqueue_script(
        'award-search-pagination',
        AWARD_SEARCH_PLUGIN_URL . 'assets/js/pagination.js',
        array('jquery', 'award-search-templates'),
        '1.0',
        true
    );
    
    wp_enqueue_script(
        'award-search-core',
        AWARD_SEARCH_PLUGIN_URL . 'assets/js/search.js',
        array('jquery', 'award-search-templates', 'award-search-filters', 'award-search-pagination'),
        '1.0',
        true
    );
    
    wp_enqueue_script(
        'award-search-main',
        AWARD_SEARCH_PLUGIN_URL . 'assets/js/main.js',
        array('jquery', 'award-search-templates', 'award-search-filters', 'award-search-pagination', 'award-search-core'),
        '1.0',
        true
    );
    
    // Localize script to pass data to JavaScript
    wp_localize_script(
        'award-search-core',
        'award_search_ajax',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('award_search_nonce'),
            'plugin_url' => AWARD_SEARCH_PLUGIN_URL
        )
    );
    
    // Enqueue styles
    wp_enqueue_style(
        'award-search-style',
        AWARD_SEARCH_PLUGIN_URL . 'assets/css/main.css',
        array(),
        '1.0'
    );
}
}

// Initialize the plugin - with error handling
try {
    $award_search_plugin = new Award_Search_Plugin();
} catch (Exception $e) {
    error_log('Award Search Plugin initialization error: ' . $e->getMessage());
}