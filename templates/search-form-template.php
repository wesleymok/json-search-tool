<?php
/**
 * Template for the award search form
 *
 * @var array $atts Shortcode attributes
 * @var array $faculties List of faculties for the dropdown
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="award-search-container">
    <!-- Search Form -->
    <form id="award-search-form" class="award-search-form">
        <!-- Search Input -->
        <div class="search-inputs">
            <input type="text" id="award-search-input" placeholder="<?php echo esc_attr($atts['placeholder']); ?>" />
            <button type="submit" id="award-search-button"><?php echo esc_html($atts['button_text']); ?></button>
        </div>
        
        <!-- Filters Section -->
        <div class="filters">
            <!-- Campus Filter -->
            <div class="campus-filter">
                <label>Campus:</label>
                <select id="campus-filter">
                    <option value="all">Select one</option>
                    <option value="V">Vancouver</option>
                    <option value="O">Okanagan</option>
                    <option value="A">Both Campuses</option>
                </select>
            </div>
            
            <!-- Faculty Filter -->
            <div class="faculty-filter">
                <label>Faculty:</label>
                <select id="faculty-filter">
                    <option value="all">Select one</option>
                    <?php foreach ($faculties as $faculty): ?>
                        <option value="<?php echo esc_attr($faculty); ?>"><?php echo esc_html($faculty); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Graduate Level Filter -->
            <div class="graduate-filter">
                <label>Graduate Level:</label>
                <select id="graduate-filter">
                    <option value="all">Select one</option>
                    <option value="Undergraduate">Undergraduate</option>
                    <option value="Graduate">Graduate</option>
                </select>
            </div>
            
            <!-- Award Type Filter -->
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
        <!-- Reset Button -->
        <div class="reset-filters">
            <button type="button" id="reset-filters-button">Reset Filters</button>
        </div>
    </form>
    
    <!-- Results Container -->
    <div id="award-search-results" class="award-search-results">
        <!-- Results will be loaded here via JavaScript -->
    </div>
    
    <!-- Pagination Container -->
    <div id="award-search-pagination" class="award-search-pagination">
        <!-- Pagination will be added dynamically -->
    </div>
    
    <!-- Hidden Fields -->
    <input type="hidden" id="current-page" value="1">
    <input type="hidden" id="per-page" value="<?php echo esc_attr($atts['per_page']); ?>">
</div>