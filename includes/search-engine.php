<?php
/**
 * Search Engine for Award Search Plugin
 * 
 * Handles search functionality and filtering logic
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class Award_Search_Engine {
    /**
     * Data manager instance
     * 
     * @var Award_Search_Data_Manager
     */
    private $data_manager;
    
    /**
     * Initialize the search engine
     * 
     * @param Award_Search_Data_Manager $data_manager The data manager instance
     */
    public function __construct($data_manager) {
        $this->data_manager = $data_manager;
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
    public function search_awards($search_term, $campus_filter = 'all', $faculty_filter = 'all', $graduate_filter = 'all', $award_type_filter = 'all') {
        $json_data = $this->data_manager->get_data();
        
        if (empty($json_data)) {
            return array();
        }
    
        $results = array();
        $search_term = strtolower($search_term);
    
        // Search through each award entry
        foreach ($json_data as $award) {
            // Skip if not an array
            if (!is_array($award)) {
                continue;
            }
            
            // Apply filters
            if (!$this->matches_campus_filter($award, $campus_filter)) {
                continue;
            }
            
            if (!$this->matches_faculty_filter($award, $faculty_filter)) {
                continue;
            }
            
            if (!$this->matches_graduate_filter($award, $graduate_filter)) {
                continue;
            }
            
            if (!$this->matches_award_type_filter($award, $award_type_filter)) {
                continue;
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
     * Check if award matches campus filter
     * 
     * @param array $award The award data
     * @param string $campus_filter The campus filter
     * @return bool True if matches or no filter, false otherwise
     */
    private function matches_campus_filter($award, $campus_filter) {
        if ($campus_filter === 'all') {
            return true;
        }
        
        // If looking for "A" (All Campuses), match exactly
        if ($campus_filter === 'A') {
            return $award['Campus'] === 'A';
        }
        
        // For Vancouver (V) or Okanagan (O) filters,
        // include both exact matches and "A" (Available at all campuses)
        return $award['Campus'] === $campus_filter || $award['Campus'] === 'A';
    }
    
    /**
     * Check if award matches faculty filter
     * 
     * @param array $award The award data
     * @param string $faculty_filter The faculty filter
     * @return bool True if matches or no filter, false otherwise
     */
    private function matches_faculty_filter($award, $faculty_filter) {
        if ($faculty_filter === 'all') {
            return true;
        }
        
        if (!isset($award['Administering Unit']) || empty($award['Administering Unit'])) {
            return false;
        }
        
        $adminUnit = $award['Administering Unit'];
        
        // Check if there are multiple units
        if (strpos($adminUnit, ';') !== false) {
            $adminUnitParts = array_map('trim', explode(';', $adminUnit));
            
            // Check each part for a match
            foreach ($adminUnitParts as $part) {
                $award_faculty = $this->extract_faculty_name($part);
                
                if ($award_faculty === $faculty_filter) {
                    return true;
                }
            }
            
            return false;
        } else {
            // Single unit
            $award_faculty = $this->extract_faculty_name($adminUnit);
            return $award_faculty === $faculty_filter;
        }
    }
    
    /**
     * Check if award matches graduate filter
     * 
     * @param array $award The award data
     * @param string $graduate_filter The graduate filter
     * @return bool True if matches or no filter, false otherwise
     */
    private function matches_graduate_filter($award, $graduate_filter) {
        if ($graduate_filter === 'all') {
            return true;
        }
        
        return isset($award['Eligible Learner Level']) && $award['Eligible Learner Level'] === $graduate_filter;
    }
    
    /**
     * Check if award matches award type filter
     * 
     * @param array $award The award data
     * @param string $award_type_filter The award type filter
     * @return bool True if matches or no filter, false otherwise
     */
    private function matches_award_type_filter($award, $award_type_filter) {
        if ($award_type_filter === 'all') {
            return true;
        }
        
        if (!isset($award['Award Type'])) {
            return false;
        }
        
        // Map the user-friendly filter value to the database code
        switch ($award_type_filter) {
            case 'Award':
                return $award['Award Type'] === 'AWRD';
            case 'Scholarship':
                return $award['Award Type'] === 'SCHL';
            case 'Prize':
                return $award['Award Type'] === 'PRIZ';
            case 'Fellowship':
                return $award['Award Type'] === 'FELL';
            case 'Bursaries': 
                return $award['Award Type'] === 'BURS';
            default:
                return $award['Award Type'] === $award_type_filter;
        }
    }
    
    /**
     * Get unique faculties from JSON data
     * 
     * @return array Array of faculty names
     */
    public function get_unique_faculties() {
        $json_data = $this->data_manager->get_data();
        $faculties = array();
        
        if (!empty($json_data)) {
            foreach ($json_data as $award) {
                if (isset($award['Administering Unit']) && !empty($award['Administering Unit'])) {
                    $adminUnit = $award['Administering Unit'];
                    
                    // Check if the admin unit contains multiple entries (e.g., separated by semicolons)
                    if (strpos($adminUnit, ';') !== false) {
                        // Split by semicolon and process each part
                        $adminUnitParts = array_map('trim', explode(';', $adminUnit));
                        
                        foreach ($adminUnitParts as $part) {
                            $faculty_name = $this->extract_faculty_name($part);
                            
                            if (!empty($faculty_name) && !in_array($faculty_name, $faculties)) {
                                $faculties[] = $faculty_name;
                            }
                        }
                    } else {
                        // Process single entry
                        $faculty_name = $this->extract_faculty_name($adminUnit);
                        
                        if (!empty($faculty_name) && !in_array($faculty_name, $faculties)) {
                            $faculties[] = $faculty_name;
                        }
                    }
                }
            }
        }
        
        sort($faculties); // Sort alphabetically
        return $faculties;
    }
    
    /**
     * Get faculties filtered by campus
     * 
     * @param string $campus Campus code (V, O, or all)
     * @return array Array of faculty names
     */
    public function get_faculties_by_campus($campus = 'all') {
        $json_data = $this->data_manager->get_data();
        $faculties = array();
        
        if (!empty($json_data)) {
            foreach ($json_data as $award) {
                // Skip if not matching campus filter
                if ($campus !== 'all' && 
                    (!isset($award['Campus']) || $award['Campus'] !== $campus)) {
                    continue;
                }
                
                if (isset($award['Administering Unit']) && !empty($award['Administering Unit'])) {
                    $adminUnit = $award['Administering Unit'];
                    
                    // Check if the admin unit contains multiple entries
                    if (strpos($adminUnit, ';') !== false) {
                        // Split by semicolon and process each part
                        $adminUnitParts = array_map('trim', explode(';', $adminUnit));
                        
                        foreach ($adminUnitParts as $part) {
                            $faculty_name = $this->extract_faculty_name($part);
                            
                            if (!empty($faculty_name) && !in_array($faculty_name, $faculties)) {
                                $faculties[] = $faculty_name;
                            }
                        }
                    } else {
                        // Process single entry
                        $faculty_name = $this->extract_faculty_name($adminUnit);
                        
                        if (!empty($faculty_name) && !in_array($faculty_name, $faculties)) {
                            $faculties[] = $faculty_name;
                        }
                    }
                }
            }
        }
        
        sort($faculties); // Sort alphabetically
        return $faculties;
    }
    
    /**
     * Helper method to extract faculty name from admin unit string
     * 
     * @param string $adminUnit The admin unit string
     * @return string The extracted faculty name
     */
    private function extract_faculty_name($adminUnit) {
        $faculty_name = '';
        
        // If the string contains " - ", take everything after it
        $pos = strpos($adminUnit, ' - ');
        if ($pos !== false) {
            $faculty_name = substr($adminUnit, $pos + 3); // 3 = length of " - "
        } else {
            // Fallback to original value if pattern not found
            $faculty_name = $adminUnit;
        }
        
        // Further clean up the faculty name (remove any parenthetical campus info)
        $parenthesis_pos = strpos($faculty_name, ' (');
        if ($parenthesis_pos !== false) {
            $faculty_name = substr($faculty_name, 0, $parenthesis_pos);
        }
        
        return trim($faculty_name);
    }
}