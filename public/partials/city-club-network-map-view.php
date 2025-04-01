<?php
/**
 * Template for displaying the City Club Network in map view.
 * This partial might be loaded dynamically via JS or included conditionally.
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

// Ensure $this or equivalent is available
if (!isset($this) && isset($plugin_instance)) {
    $this_ref = $plugin_instance;
} elseif (isset($this)) {
    $this_ref = $this;
} else {
    echo '<p>Error: Plugin context not available for map view.</p>';
    return;
}

// Get the clubs data based on current filters (passed via GET or defaults)
$filters = array(
    'city' => isset($_GET['ccn_city']) ? sanitize_text_field($_GET['ccn_city']) : (isset($atts['city']) ? $atts['city'] : ''),
    'facility' => isset($_GET['ccn_facility']) ? sanitize_text_field($_GET['ccn_facility']) : (isset($atts['facility']) ? $atts['facility'] : ''),
    'membership' => isset($_GET['ccn_membership']) ? sanitize_text_field($_GET['ccn_membership']) : (isset($atts['membership']) ? $atts['membership'] : ''),
);

$clubs = $this_ref->get_clubs_data($filters);

// Prepare map data
$map_data = array();
$locations_count = 0;
foreach ($clubs as $club) {
    if (!empty($club['latitude']) && !empty($club['longitude'])) {
        $map_data[] = array(
            'id' => $club['id'],
            'title' => $club['title'],
            'lat' => floatval($club['latitude']), // Ensure float
            'lng' => floatval($club['longitude']), // Ensure float
            'isPremium' => !empty($club['is_premium']),
            'permalink' => $club['permalink'],
            'address' => $club['address'],
            'thumbnail' => $club['thumbnail'], // Use thumbnail URL directly
        );
        $locations_count++;
    }
}

// Default center and zoom (can be adjusted based on data or settings)
$default_center = array('lat' => 31.7917, 'lng' => -7.0926); // Center of Morocco
$default_zoom = 6;

// Attempt to calculate center based on markers if possible
// Note: This is a basic calculation; more robust methods exist
$center_lat = $default_center['lat'];
$center_lng = $default_center['lng'];
if ($locations_count > 0) {
    $total_lat = 0;
    $total_lng = 0;
    foreach ($map_data as $loc) {
        $total_lat += $loc['lat'];
        $total_lng += $loc['lng'];
    }
    $center_lat = $total_lat / $locations_count;
    $center_lng = $total_lng / $locations_count;
    // Adjust zoom based on number of locations? Could be complex.
    if ($locations_count === 1) {
        $default_zoom = 14; // Zoom in closer for a single location
    } elseif ($locations_count < 5) {
         $default_zoom = 10;
    } else {
         $default_zoom = 7; // Zoom out a bit more for many locations
    }
}


// Localize the script with map data - This should happen where the map script is enqueued
// We'll assume it's done correctly in the main plugin or class file.
// Example of data structure for localization:
/*
wp_localize_script(
    $this->plugin_name . '-map', // Handle for the map JS file
    'ccn_map_config', // Object name in JS
    array(
        'clubs' => $map_data,
        'center' => array('lat' => $center_lat, 'lng' => $center_lng),
        'zoom' => $default_zoom,
        'marker_icon_standard' => CCN_PLUGIN_URL . 'public/images/marker-standard.svg',
        'marker_icon_premium' => CCN_PLUGIN_URL . 'public/images/marker-premium.svg',
        // Add API key if needed directly in JS (though better via settings)
        // 'api_key' => get_option('ccn_google_maps_api_key', '')
    )
);
*/
?>

<div class="ccn-map-layout-container">
    <div class="ccn-map-sidebar">
        <div class="ccn-map-finder-header">
            <h3>Interactive Club Finder</h3>
            <p>Discover our locations across Morocco</p>
            <div class="ccn-locations-count">
                <i class="ccn-location-icon"></i> <!-- Icon via CSS -->
                <span><?php echo $locations_count; ?> Location<?php echo ($locations_count !== 1) ? 's' : ''; ?> Found</span>
            </div>
        </div>

        <div id="ccn-map-clubs-list" class="ccn-map-clubs-list">
            <?php if (!empty($map_data)) : ?>
                <?php foreach ($map_data as $club) : ?>
                    <div class="ccn-map-club-item" data-club-id="<?php echo esc_attr($club['id']); ?>">
                        <div class="ccn-map-club-thumbnail">
                            <?php if (!empty($club['thumbnail'])) : ?>
                                <img src="<?php echo esc_url($club['thumbnail']); ?>" alt="<?php echo esc_attr($club['title']); ?>" loading="lazy">
                            <?php else : ?>
                                <div class="ccn-map-club-no-thumbnail"></div> <!-- Placeholder styled by CSS -->
                            <?php endif; ?>
                            <?php if ($club['isPremium']) : ?>
                                <span class="ccn-premium-badge">Premium</span>
                            <?php endif; ?>
                        </div>
                        <div class="ccn-map-club-info">
                            <h4 class="ccn-map-club-title"><?php echo esc_html($club['title']); ?></h4>
                            <?php if (!empty($club['address'])) : ?>
                                <p class="ccn-map-club-address">
                                    <i class="ccn-location-pin-icon"></i> <!-- Icon via CSS -->
                                    <?php echo esc_html($club['address']); ?>
                                </p>
                            <?php endif; ?>
                            <a href="<?php echo esc_url($club['permalink']); ?>" class="ccn-map-club-link" target="_blank">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="ccn-no-clubs-found" style="padding: 20px; text-align: center;">
                    <p>No locations found matching your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="ccn-map-area">
        <div id="ccn-map">
            <!-- Map will be loaded here by the Google Maps API callback -->
            <p style="text-align: center; padding-top: 50px; color: #666;">Loading map...</p>
        </div>
    </div>
</div>
