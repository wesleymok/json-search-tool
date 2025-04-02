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

class Award_JSON_Search_Plugin {
    // Store JSON data
    private $json_data = [];

    public function __construct() {
        // Initialize hooks
        add_action('init', array($this, 'register_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Register AJAX handlers
        add_action('wp_ajax_award_search_query', array($this, 'handle_ajax_search'));
        add_action('wp_ajax_nopriv_award_search_query', array($this, 'handle_ajax_search'));
        
        // Register new AJAX handler for faculties by campus
        add_action('wp_ajax_get_faculties_by_campus', array($this, 'get_faculties_by_campus'));
        add_action('wp_ajax_nopriv_get_faculties_by_campus', array($this, 'get_faculties_by_campus'));
        
        // Load JSON data
        $this->load_json_data();
    }

    /**
     * Load JSON data from file
     */
    private function load_json_data() {
        // Set the path to your JSON file
        $json_file_path = plugin_dir_path(__FILE__) . 'data/data.json';
        
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
     * Register shortcode for search form and results
     */
    public function register_shortcode() {
        add_shortcode('award_search', array($this, 'search_shortcode_callback'));
    }

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
        
        // Check if JS directory and file exist before enqueuing
        $js_file = plugin_dir_path(__FILE__) . 'js/award-search.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'award-search',
                plugin_dir_url(__FILE__) . 'js/award-search.js',
                array('jquery', 'handlebars'),  // Add handlebars as a dependency
                '1.0',
                true
            );
            
            // Localize script to pass data to JavaScript
            wp_localize_script(
                'award-search',
                'award_search_ajax',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce'    => wp_create_nonce('award_search_nonce')
                )
            );
        } else {
            error_log('Award Search Plugin: JavaScript file not found at: ' . $js_file);
        }
        
        // Check if CSS directory and file exist before enqueuing
        $css_file = plugin_dir_path(__FILE__) . 'css/main.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'award-search-style',
                plugin_dir_url(__FILE__) . 'css/main.css',
                array(),
                '1.0'
            );
        } else {
            error_log('Award Search Plugin: CSS file not found at: ' . $css_file);
        }
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
        $faculties = $this->get_faculties_by_campus_filter($campus);
        
        wp_send_json_success(array(
            'faculties' => $faculties
        ));
        return;
    }

    /**
     * Get faculties filtered by campus
     * 
     * @param string $campus Campus code (V, O, or all)
     * @return array Array of faculty names
     */
    private function get_faculties_by_campus_filter($campus = 'all') {
        $faculties = array();
        
        if (!empty($this->json_data)) {
            foreach ($this->json_data as $award) {
                // Skip if not matching campus filter
                if ($campus !== 'all' && 
                    (!isset($award['Campus']) || $award['Campus'] !== $campus)) {
                    continue;
                }
                
                if (isset($award['Administering Unit']) && !empty($award['Administering Unit'])) {
                    $adminUnit = $award['Administering Unit'];
                    $faculty_name = '';
                    
                    // Look for " - " pattern and take everything after it
                    $pos = strpos($adminUnit, ' - ');
                    if ($pos !== false) {
                        $faculty_name = substr($adminUnit, $pos + 3); // 3 = length of " - "
                    } else {
                        // Fallback to original value if pattern not found
                        $faculty_name = $adminUnit;
                    }
                    
                    if (!empty($faculty_name) && !in_array($faculty_name, $faculties)) {
                        $faculties[] = $faculty_name;
                    }
                }
            }
        }
        
        sort($faculties); // Sort alphabetically
        return $faculties;
    }

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
        $all_results = $this->search_json_data($search_term, $campus_filter, $faculty_filter, $graduate_filter, $award_type_filter);
        
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
    
    /**
     * Search through JSON data
     *
     * @param string $search_term The term to search for
     * @param string $campus_filter The campus to filter by (V, O, or all)
     * @param string $faculty_filter The faculty to filter by
     * @param string $graduate_filter The graduate level to filter by
     * @param string $award_type_filter The award type to filter by
     * @return array Search results
     */
    private function search_json_data($search_term, $campus_filter = 'all', $faculty_filter = 'all', $graduate_filter = 'all', $award_type_filter = 'all') {
        if (empty($this->json_data)) {
            return array();
        }
    
        $results = array();
        $search_term = strtolower($search_term);
    
        // Search through each award entry
        foreach ($this->json_data as $award) {
            // Skip if not an array
            if (!is_array($award)) {
                continue;
            }
            
            // Apply campus filter if not set to 'all'
            if ($campus_filter !== 'all') {
                if (!isset($award['Campus']) || $award['Campus'] !== $campus_filter) {
                    continue; // Skip this award if it doesn't match the campus filter
                }
            }
            
            // Apply faculty filter if not set to 'all'
            if ($faculty_filter !== 'all') {
                if (!isset($award['Administering Unit']) || empty($award['Administering Unit'])) {
                    continue;
                }
                
                // Extract faculty name using the same pattern approach
                $adminUnit = $award['Administering Unit'];
                $award_faculty = '';
                
                // Look for " - " pattern and take everything after it
                $pos = strpos($adminUnit, ' - ');
                if ($pos !== false) {
                    $award_faculty = substr($adminUnit, $pos + 3);
                } else {
                    $award_faculty = $adminUnit;
                }
                
                if ($award_faculty !== $faculty_filter) {
                    continue; // Skip this award if it doesn't match the faculty filter
                }
            }

            if ($graduate_filter !== 'all') {
                if (!isset($award['Eligible Learner Level']) || $award['Eligible Learner Level'] !== $graduate_filter) {
                    continue; // Skip this award if it doesn't match the campus filter
                }
            }

            if ($award_type_filter !== 'all') {
                if (!isset($award['Award Type'])) {
                    continue;
                }
            
                $award_type_match = false;
                
                // Map the user-friendly filter value to the database code
                switch ($award_type_filter) {
                    case 'Award':
                        $award_type_match = ($award['Award Type'] === 'AWRD');
                        break;
                    case 'Scholarship':
                        $award_type_match = ($award['Award Type'] === 'SCHL');
                        break;
                    case 'Prize':
                        $award_type_match = ($award['Award Type'] === 'PRIZ');
                        break;
                    case 'Fellowship':
                        $award_type_match = ($award['Award Type'] === 'FELL');
                        break;
                    case 'Bursaries': // Fixed: Changed from 'Fellowship' to 'Bursaries'
                        $award_type_match = ($award['Award Type'] === 'BURS');
                        break;
                    default:
                        $award_type_match = ($award['Award Type'] === $award_type_filter);
                }
                
                if (!$award_type_match) {
                    continue; // Skip this award if it doesn't match the award type filter
                }
            }
            
            // If search term is empty, include all awards that match the filters
            if (empty($search_term)) {
                $results[] = $award;
                continue;
            }
            
            // Search through all fields in this award
            foreach ($award as $field => $value) {
                // Convert to string if not already
                if (!is_string($value) && !is_numeric($value)) {
                    continue;
                }
                
                $value_str = (string) $value;
                if (stripos(strtolower($value_str), $search_term) !== false) {
                    $results[] = $award;
                    break; // Found in this award, no need to check other fields
                }
            }
        }
        
        return $results;
    }

    /**
     * Shortcode callback to display search form and results
     */
    public function search_shortcode_callback($atts) {
        $atts = shortcode_atts(array(
            'placeholder' => 'Search awards... (or press the "Find Awards" button to search all)',
            'button_text' => 'Search',
            'per_page' => 10,
        ), $atts);
    
        // Get unique faculties from data for the dropdown
        $faculties = $this->get_unique_faculties();
    
        ob_start();
        ?>
    <div class="award-search-container">
        <form id="award-search-form" class="award-search-form">
            <div class="search-inputs">
                <input type="text" id="award-search-input" placeholder="<?php echo esc_attr($atts['placeholder']); ?>" />
                <button type="submit" id="award-search-button"><?php echo esc_html($atts['button_text']); ?></button>
            </div>
            <div class="filters">
            <div class="campus-filter">
                <label>Campus:</label>
                <select id="campus-filter">
                    <option value="all">Select one</option>
                    <option value="V">Vancouver</option>
                    <option value="O">Okanagan</option>
                </select>
            </div>
            <div class="faculty-filter">
                <label>Faculty:</label>
                <select id="faculty-filter">
                    <option value="all">Select one</option>
                    <?php foreach ($faculties as $faculty): ?>
                        <option value="<?php echo esc_attr($faculty); ?>"><?php echo esc_html($faculty); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="graduate-filter">
                <label>Graduate Level:</label>
                <select id="graduate-filter">
                    <option value="all">Select one</option>
                    <option value="Undergraduate">Undergraduate</option>
                    <option value="Graduate">Graduate</option>
                </select>
            </div>
            <div class="award-type-filter">
                <label>Award Type:</label>
                <select id="award-type-filter">
                    <option value="all">Select one</option>
                    <option value="AWRD">Award</option>
                    <option value="BURS">Bursaries</option>
                    <option value="FELL">Fellowship</option>
                    <option value="SCHL">Scholarship</option>
                    <option value="PRIZ">Prize</option>
                </select>
            </div>
            </div>
            <br>
            <div class="reset-filters">
                <button type="button" id="reset-filters-button">Reset Filters</button>
            </div>
        </form>
        
        <div id="award-search-results" class="award-search-results">
            <!-- Results will be loaded here via JavaScript -->
        </div>
        <div id="award-search-pagination" class="award-search-pagination">
            <!-- Pagination will be added dynamically -->
        </div>
        <input type="hidden" id="current-page" value="1">
        <input type="hidden" id="per-page" value="<?php echo esc_attr($atts['per_page']); ?>">
    </div>
    
    <!-- Template for search results -->
    <script id="results-template" type="text/x-handlebars-template">
        <div class="search-results-count">{{total_results}} awards found</div>
        <div class="search-results-list">
            {{#each results}}
                <div class="award-item">
                    <h3>{{this.[Award Name]}}</h3>
                    
                    <div class="award-details">
                        <p><strong>Award Number:</strong> {{this.[Award Number]}}</p>
                        <p><strong>Award Cycle:</strong> {{this.[Award Cycle]}}</p>
                        
                        {{#if this.[Award Type]}}
                            <p><strong>Award Type:</strong> 
                                {{#ifeq this.[Award Type] "AWRD"}}Award{{/ifeq}}
                                {{#ifeq this.[Award Type] "SCHL"}}Scholarship{{/ifeq}}
                                {{#ifeq this.[Award Type] "PRIZ"}}Prize{{/ifeq}}
                                {{#ifeq this.[Award Type] "BURS"}}Bursaries{{/ifeq}}
                                {{#ifeq this.[Award Type] "FELL"}}Fellowship{{/ifeq}}
                                {{#ifneq this.[Award Type] "AWRD"}}
                                    {{#ifneq this.[Award Type] "SCHL"}}
                                        {{#ifneq this.[Award Type] "PRIZ"}}
                                            {{#ifneq this.[Award Type] "BURS"}}
                                                {{#ifneq this.[Award Type] "FELL"}}
                                                    {{this.[Award Type]}}
                                                {{/ifneq}}
                                            {{/ifneq}}
                                        {{/ifneq}}
                                    {{/ifneq}}
                                {{/ifneq}}
                            </p>
                        {{/if}}
                        
                        {{#if this.[Administering Unit]}}
                            <p><strong>Department:</strong> {{formatAdminUnit this.[Administering Unit]}}</p>
                        {{/if}}
                        
                        <p><strong>Degree Level:</strong> {{this.[Eligible Learner Level]}}</p>
                        <p><strong>Application Type:</strong> {{this.[Application Type (Award Profile)]}}</p>
                        
                        {{#if this.Campus}}
                            <p><strong>Campus:</strong>
                                {{#ifeq this.Campus "V"}}Vancouver{{/ifeq}}
                                {{#ifeq this.Campus "O"}}Okanagan{{/ifeq}}
                                {{#ifneq this.Campus "V"}}
                                    {{#ifneq this.Campus "O"}}
                                        {{this.Campus}}
                                    {{/ifneq}}
                                {{/ifneq}}
                            </p>
                        {{/if}}
                        
                        {{#if this.[Award Description]}}
                            <div class="award-description">
                                <h4>Description</h4>
                                <p>{{this.[Award Description]}}</p>
                            </div>
                        {{/if}}
                    </div>
                </div>
            {{/each}}
        </div>
    </script>

    <!-- Template for pagination -->
    <script id="pagination-template" type="text/x-handlebars-template">
        {{#if show_pagination}}
            <div class="pagination-info">
                Showing {{start_result}} to {{end_result}} of {{total_results}} awards
            </div>
            
            <div class="pagination-controls">
                {{#if has_prev}}
                    <button class="pagination-button first-page" data-page="1">First</button>
                    <button class="pagination-button" data-page="{{prev_page}}">Previous</button>
                {{else}}
                    <button class="pagination-button first-page disabled">First</button>
                    <button class="pagination-button disabled">Previous</button>
                {{/if}}
                
                {{#each page_numbers}}
                    {{#if this.current}}
                        <button class="pagination-button current" data-page="{{this.number}}">{{this.number}}</button>
                    {{else}}
                        <button class="pagination-button" data-page="{{this.number}}">{{this.number}}</button>
                    {{/if}}
                {{/each}}
                
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
    <?php
    return ob_get_clean();
    }

    /**
     * Get unique faculties from JSON data
     * 
     * @return array Array of faculty names
    */
    private function get_unique_faculties() {
        $faculties = array();
        
        if (!empty($this->json_data)) {
            foreach ($this->json_data as $award) {
                if (isset($award['Administering Unit']) && !empty($award['Administering Unit'])) {
                    $adminUnit = $award['Administering Unit'];
                    $faculty_name = '';
                    
                    // Look for " - " pattern and take everything after it
                    $pos = strpos($adminUnit, ' - ');
                    if ($pos !== false) {
                        $faculty_name = substr($adminUnit, $pos + 3); // 3 = length of " - "
                    } else {
                        // Fallback to original value if pattern not found
                        $faculty_name = $adminUnit;
                    }
                    
                    if (!empty($faculty_name) && !in_array($faculty_name, $faculties)) {
                        $faculties[] = $faculty_name;
                    }
                }
            }
        }
        
        sort($faculties); // Sort alphabetically
        return $faculties;
    }
}

// Initialize the plugin - with error handling
try {
    $award_search_plugin = new Award_JSON_Search_Plugin();
} catch (Exception $e) {
    error_log('Award Search Plugin initialization error: ' . $e->getMessage());
}