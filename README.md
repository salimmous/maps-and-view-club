# City Club Network WordPress Plugin

## Overview
City Club Network is a WordPress plugin designed to display fitness clubs across Morocco with interactive grid and map views. The plugin allows users to easily browse and filter club locations, making it simple for visitors to find the perfect fitness club based on their preferences.

## Features
- **Multiple View Options**: Display clubs in grid layout, interactive map view (using `[city_club_network]`), or grid-only view (using `[city_club_grid]`).
- **Standalone Map View**: Display only the interactive map using the `[city_club_map]` shortcode.
- **Standalone Search Bar**: Display a dedicated search bar using the `[city_club_search_bar]` shortcode.
- **Advanced Filtering**: Filter clubs by city, facilities, and membership types (applies to all shortcodes).
- **Interactive Map**: Google Maps integration with custom markers, location list, and details panel.
- **Responsive Design**: Fully responsive interface that works on all devices.
- **Premium Club Highlighting**: Special indicators for premium club locations.
- **AJAX Powered Details**: Club details load dynamically in the map sidebar without page reloads.
- **Optimized Performance**: Improved map loading times and resource usage.
- **Modern Card Layout**: Enhanced grid view with 4 cards per row for better space utilization.
- **Customizable**: Settings page for API key, colors, and button text.

## Installation

1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Navigate to Plugins > Add New
4. Click on the "Upload Plugin" button at the top of the page
5. Choose the downloaded zip file and click "Install Now"
6. After installation, click "Activate Plugin"


## Configuration

### Google Maps API Key
To use the map view functionality, you need to configure a Google Maps API key:

1. Navigate to WordPress Admin > Settings > City Club Network
2. Enter your Google Maps API key in the provided field
3. Save changes

> **Note**: If you don't have a Google Maps API key, you can obtain one from the [Google Cloud Platform Console](https://console.cloud.google.com/). Make sure to enable the Maps JavaScript API, Places API, and Geocoding API for your key.

## Adding Club Locations

1. In your WordPress admin, go to "City Clubs" > "Add New"
2. Enter the club name in the title field
3. Add a description in the content editor (used in the modal)
4. Set a featured image for the club (used in grid view and modal)
5. Assign the club to a **City** using the "Cities" taxonomy box.
6. Select applicable **Facilities** using the "Facilities" taxonomy box.
7. Assign the club to **Membership Categories** using the "Membership Categories" box.
8. Fill in the **Club Details & Overview** meta box:
   - Address
   - Premium Club checkbox
   - Opening Hours (Mon-Fri, Sat, Sun)
   - Rating & Reviews Count
   - Contact Info (Phone, Email, Website)
   - "Book a Tour" Button URL
9. Fill in the **Club Location** meta box:
   - Enter Latitude and Longitude manually, OR
   - Click/drag the marker on the map (requires API key).
10. Fill in the **Club Classes & Schedule** meta box:
    - Add class details line by line in the specified format.
    - Optionally, upload or select a Class Schedule PDF.
11. Fill in the **Club Membership Plans** meta box:
    - Add membership plan details line by line in the specified format.
12. Publish the club.

## Using the Shortcodes

### 1. Combined Grid & Map View

To display the club network with both grid and map view options and filters, use:

```
[city_club_network]
```

**Shortcode Parameters (`[city_club_network]`):**

| Parameter | Description | Default | Options |
|-----------|-------------|---------|----------|
| view | Initial display mode | grid | grid, map |
| city | Filter by city slug | empty (all cities) | city slug |
| facility | Filter by facility slug | empty (all facilities) | facility slug |
| membership_category | Filter by membership category slug | empty (all types) | membership slug |
| per_page | Number of clubs per page (grid view) | 9 | any number |

**Examples:**

Display starting with map view:
`[city_club_network view="map"]`

Display clubs in Casablanca only:
`[city_club_network city="casablanca"]`

### 2. Standalone Grid View

To display *only* the grid view with the new 4-cards-per-row layout, use:

```
[city_club_grid]
```

**Shortcode Parameters (`[city_club_grid]`):**

| Parameter | Description | Default | Options |
|-----------|-------------|---------|----------|
| city | Filter by city slug | empty (all cities) | city slug |
| facility | Filter by facility slug | empty (all facilities) | facility slug |
| membership_category | Filter by membership category slug | empty (all types) | membership slug |
| per_page | Number of clubs per page | 12 | any number |

**Examples:**

Display grid of clubs in Marrakech:
`[city_club_grid city="marrakech"]`

Display grid with 16 clubs per page:
`[city_club_grid per_page="16"]`

### 3. Standalone Map View

To display *only* the interactive map view (new design), use:

```
[city_club_map]
```

**Shortcode Parameters (`[city_club_map]`):**

| Parameter | Description | Default | Options |
|-----------|-------------|---------|----------|
| city | Filter by city slug | empty (all cities) | city slug |
| facility | Filter by facility slug | empty (all facilities) | facility slug |
| membership_category | Filter by membership category slug | empty (all types) | membership slug |
| height | Set the height of the map container | 650px | e.g., `500px`, `70vh` |

**Examples:**

Display map of clubs in Rabat:
`[city_club_map city="rabat"]`

Display map of clubs with a swimming pool:
`[city_club_map facility="swimming-pool"]`

Display map with a specific height:
`[city_club_map height="500px"]`

### 4. Standalone Search Bar

To display *only* the search bar, use:

```
[city_club_search_bar]
```

This shortcode displays a search input field (for city or club name) and a dropdown for facilities. When submitted, it will redirect the user, passing the search term (`ccn_search_term`) and selected facility (`ccn_facility`) as URL parameters.

**Shortcode Parameters (`[city_club_search_bar]`):**

| Parameter | Description | Default | Options |
|-----------|-------------|---------|----------|
| results_page_url | The URL of the page where the search results should be displayed (e.g., a page containing `[city_club_network]` or `[city_club_grid]`). | Current page URL | Any valid URL |

**Examples:**

Display the search bar on the current page (results will appear on the same page after submission):
`[city_club_search_bar]`

Display the search bar and redirect results to a specific page:
`[city_club_search_bar results_page_url="/find-a-club/"]`

**Note:** The `[city_club_network]` and `[city_club_grid]` shortcodes will automatically use the `ccn_facility` URL parameter passed by this search bar to filter the displayed clubs. Filtering by the text search term (`ccn_search_term`) is not implemented in the listing shortcodes yet.

## Customization

### Settings Page
Navigate to **Settings > City Club Network** in your WordPress admin to configure:
- Google Maps API Key
- Primary, Secondary, Text, and Button Text colors (Note: Color application might require theme adjustments)
- Text for "Book a Tour", "Get Directions", and "Choose Plan" buttons.

### CSS Customization
The plugin includes CSS files that can be overridden in your theme:
- `public/css/city-club-network-public.css` - General styling for grid view, filters, etc.
- `public/css/city-club-network-grid-only.css` - Grid-only view specific styling (4-cards-per-row layout).
- `public/css/city-club-network-map.css` - Map view specific styling (sidebar, details panel).
- `public/css/city-club-network-modal.css` - Styling for the popup modal (used in grid view).
- `public/css/city-club-network-search-bar.css` - Styling for the standalone search bar.

### Card Layout Customization
The new grid layout features 4 cards per row for better space utilization on larger screens. The cards have been redesigned to display essential information in a more compact format while maintaining readability. To customize the card appearance, you can override the styles in `public/css/city-club-network-grid-only.css`.

### Performance Optimizations
The plugin now includes several performance improvements:
- Lazy loading of map resources only when needed
- Optimized marker clustering for faster rendering of multiple locations
- Reduced initial load time by deferring non-critical resources
- Improved AJAX handling for smoother user experience

To customize the appearance, add custom CSS to your theme or use a custom CSS plugin.

### Template Customization
Advanced users can copy the template files from the plugin's `public/partials` directory to a `city-club-network` folder within their theme directory (`your-theme/city-club-network/`) and modify them. The plugin will automatically use the theme's templates if they exist.

## Troubleshooting

### Map Not Displaying or Not Interactive
- Ensure you've entered a valid Google Maps API key in **Settings > City Club Network**.
- Check that your API key has the **Maps JavaScript API**, **Places API**, and **Geocoding API** enabled in the Google Cloud Console.
- Verify that the clubs have valid Latitude and Longitude coordinates saved in the **Club Location** meta box.
- Check your browser's developer console for any JavaScript errors related to Google Maps.

### Filters Not Working
- Make sure you've assigned the appropriate taxonomies (City, Facility, Membership Category) to your clubs.
- Check that the slug names used in your shortcode attributes match the actual taxonomy slugs.
- If using the search bar, ensure the `results_page_url` (if set) points to a page with a listing shortcode (`[city_club_network]` or `[city_club_grid]`).

### Sidebar Details Not Loading
- Ensure the `ccn_get_club_details` AJAX action is working correctly. Check your browser's network tab for failed AJAX requests.
- Verify that the `modal_nonce` is correctly localized in the `ccn_public_data` JavaScript object.

## Support

For support requests, feature suggestions, or bug reports, please contact the plugin developer.

---

*This plugin is designed to help fitness clubs in Morocco showcase their locations and facilities to potential members.*
