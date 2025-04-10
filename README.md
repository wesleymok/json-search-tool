# Award Search Plugin

## Description
A WordPress plugin that creates a searchable interface for award data stored in a JSON file. Users can search for awards using keywords and filter by campus, faculty, graduate level, and award type with paginated results.

## Features
- Keyword search across all award fields
- Multiple filter options (Campus, Faculty, Graduate Level, Award Type)
- Dynamic faculty filtering based on campus selection
- Pagination of search results
- Reset filters functionality
- Responsive design

## Installation
1. Upload the `award-search-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place the `[award_search]` shortcode on any page or post where you want the search interface to appear

## Usage

### Basic Usage
Add the shortcode to any page or post:
```
[award_search]
```

### Shortcode Parameters
```
[award_search]
[award_search placeholder="Custom placeholder text" button_text="Find Awards" per_page="15"]
```

- `placeholder`: Custom placeholder text for the search input (default: "Search awards... (or press the "Find Awards" button to search all)")
- `button_text`: Custom text for the search button (default: "Search")
- `per_page`: Number of results per page (default: 10)

## Technical Overview

### PHP Components (Award_JSON_Search_Plugin Class)

#### Core Structure
- Plugin initialization and hook registration
- JSON data loading from `data/data.json`
- Shortcode registration (`[award_search]`)
- AJAX handlers for search and filter processes

#### Key Methods
- `load_json_data()`: Loads award data from JSON file
- `register_shortcode()`: Registers the `[award_search]` shortcode
- `enqueue_scripts()`: Loads required JS and CSS files
- `get_faculties_by_campus()`: AJAX handler for dynamic faculty filtering
- `handle_ajax_search()`: AJAX handler for search requests
- `search_json_data()`: Core search and filtering logic
- `search_shortcode_callback()`: Generates the HTML interface
- `get_unique_faculties()`: Extracts faculty names from data

### JavaScript Implementation

#### Core Components
- Search parameter management
- Handlebars template compilation and rendering
- Event handlers for user interactions
- AJAX communication with the WordPress backend

#### Key Functions
- `updateFacultyDropdown()`: Updates faculty options based on campus
- `performSearch()`: Collects parameters and makes AJAX search request
- `displayResults()`: Renders search results using Handlebars
- `displayPagination()`: Renders pagination controls
- `resetFilters()`: Resets all form elements to defaults

#### User Experience Features
- Debounced search (500ms delay after typing)
- Automatic search on filter changes
- Scroll to top after pagination
- Loading indicators

### Data Structure
The plugin expects a JSON file with award data in the following structure:
```json
[
  {
    "Award Number": "123",
    "Award Name": "Example Award",
    "Award Description": "Description text",
    "Award Type": "SCHL",
    "Administering Unit": "CODE - Faculty Name",
    "Campus": "V",
    "Eligible Learner Level": "Undergraduate",
    "Award Cycle": "Annual",
    "Application Type (Award Profile)": "Application Required"
  },
  ...
]
```

### Award Type Mapping
- `AWRD`: Award
- `SCHL`: Scholarship
- `PRIZ`: Prize
- `BURS`: Bursaries
- `FELL`: Fellowship

### Campus Mapping
- `V`: Vancouver
- `O`: Okanagan

## Dependencies
- jQuery (included with WordPress)
- Handlebars.js (4.7.7, loaded from CDN)

## Files
- `award-search-plugin.php`: Main plugin file with PHP implementation
- `js/award-search.js`: JavaScript implementation for frontend
- `css/main.css`: Stylesheet for the search interface
- `data/data.json`: JSON file containing award data

## Error Handling
- Graceful fallbacks for missing files or invalid JSON
- Error logging for debugging
- User-friendly messages for no results

## Extending the Plugin
To extend this plugin, you can:
- Modify the Handlebars templates to change the display of results
- Add additional filters by updating both PHP and JS files
- Customize the search logic in `search_json_data()` method
- Style the interface by editing the CSS

## Author
Wesley Mok