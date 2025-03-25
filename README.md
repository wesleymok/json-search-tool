# Award Search Plugin

## Description
A WordPress plugin that enables users to search through award data stored in JSON format. The plugin provides an intuitive interface with multiple filtering options, helping users quickly find relevant awards based on specific criteria.

## Features
- **Keyword Search**: Find awards containing specific terms
- **Real-time Updates**: Results update as users type or change filters
- **Multiple Filter Options**:
  - Campus (Vancouver/Okanagan)
  - Faculty/Department
  - Award Type (Award/Scholarship/Prize/Fellowship)
  - Graduate Level (Undergraduate/Graduate)
- **Reset Function**: One-click option to clear all filters and search results

## Installation
1. Upload the `award-search-plugin` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place your award data JSON file in the `/data/` folder within the plugin directory
4. Use the shortcode `[award_search]` on any page where you want the search functionality to appear

## Usage
In the Gutenberg editor, select the "Shortcode" block and input the shortcode listed below to display it on a page:
```
    [award_search]
```

### Shortcode Parameters
The shortcode accepts several optional parameters:
```
[award_search placeholder="Custom placeholder text" button_text="Search" results_limit="20"]
```

### JSON Data Format
The plugin expects a JSON file with award data in the following format:
```json
[
  {
    "Award Number": "12345",
    "Award Name": "Example Scholarship",
    "Award Cycle": "Winter",
    "Award Type": "SCHL",
    "Administering Unit": "Department - Faculty Name",
    "Campus": "V",
    "Eligible Learner Level": "Undergraduate",
    "Application Type (Award Profile)": "Application Required",
    "Award Description": "Description text..."
  },
  ...
]
```

## Technical Details
- The plugin uses AJAX to perform searches without page reloads
- Filter options are dynamically populated based on available data
- Results update in real-time as filters are adjusted
- Includes error handling for JSON file loading and processing

## Customization
You can modify the appearance of the search form and results by editing the CSS file located at:
```
/award-search-plugin/css/main.css
```

## Requirements
- WordPress 5.0 or higher
- PHP 7.0 or higher
- jQuery (included with WordPress)

## Author
Wesley Mok

## Version
1.0