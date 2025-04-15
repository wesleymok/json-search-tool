<?php
/**
 * Data Manager Class for Award Search Plugin
 * 
 * Responsible for loading and managing the award data from JSON file
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Award_Search_Data_Manager {
    /**
     * Store JSON data
     * 
     * @var array
     */
    private $json_data = [];
    
    /**
     * Initialize the data manager
     */
    public function __construct() {
        $this->load_json_data();
    }
    
    /**
     * Load JSON data from file
     */
    private function load_json_data() {
        // Set the path to your JSON file
        $json_file_path = AWARD_SEARCH_PLUGIN_DIR . 'data/data.json';
        
        // Check if file exists and is readable
        if (file_exists($json_file_path) && is_readable($json_file_path)) {
            $json_content = file_get_contents($json_file_path);
            
            // Make sure we got content
            if ($json_content !== false) {
                $decoded_data = json_decode($json_content, true);
                
                // Check for JSON errors
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->json_data = $decoded_data;
                } else {
                    // Log the error but continue with empty data
                    error_log('Award Search Plugin: Error decoding JSON file: ' . json_last_error_msg());
                    $this->json_data = array();
                }
            } else {
                error_log('Award Search Plugin: Could not read JSON file content');
                $this->json_data = array();
            }
        } else {
            // File not found - create empty array but don't crash
            error_log('Award Search Plugin: JSON file not found or not readable at: ' . $json_file_path);
            $this->json_data = array();
        }
    }
    
    /**
     * Get all award data
     * 
     * @return array The JSON data
     */
    public function get_data() {
        return $this->json_data;
    }
    
    /**
     * Check if data is loaded
     * 
     * @return bool True if data is loaded, false otherwise
     */
    public function has_data() {
        return !empty($this->json_data);
    }
    
    /**
     * Get a specific award by ID
     * 
     * @param string $id The award ID to find
     * @return array|null The award data or null if not found
     */
    public function get_award_by_id($id) {
        foreach ($this->json_data as $award) {
            if (isset($award['Award Number']) && $award['Award Number'] === $id) {
                return $award;
            }
        }
        
        return null;
    }
}