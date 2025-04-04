<?php
/**
 * Template for displaying the City Club Network in map view.
 * Redesigned layout: Map left, Sidebar right with details panel.
 * Can be loaded standalone via [city_club_map] shortcode or within the main [city_club_network] shortcode.
 *
 * @since      1.3.0
 * @package    City_Club_Network
 */

// Ensure $plugin_instance or $this_ref is available
if (!isset($this_ref) && isset($plugin_instance)) {
    $this_ref = $plugin_instance;
} elseif (!isset($this_ref) && isset($this)) {
     $this_ref = $this; // Fallback if included directly somehow
} elseif (!isset($this_ref)) {
    echo '<p>Error: Plugin context not available for map view.</p>';
    return;
}

// Check if this is loaded as a standalone map
$is_standalone = isset($is_standalone_map) && $is_standalone_map;

// Get map config data (already localized by the shortcode handler)
// We don't need to fetch clubs here again, JS will use the localized 'ccn_map_config'

// Prepare data for the initial sidebar list (group by city if possible)
// Note: ccn_map_config.clubs should contain id, title, lat, lng, isPremium, city_name
// We'll rely on JS to populate this list dynamically from ccn_map_config

?>

<div class="ccn-map-layout-container <?php echo $is_standalone ? 'ccn-standalone-map' : ''; ?>">

    <!-- Map Area (Left Side) -->
    <div class="ccn-map-area">
        <div id="ccn-map">
            <!-- Map will be loaded here by the Google Maps API callback -->
        </div>
    </div>

    <!-- Sidebar (Right Side) -->
    <div class="ccn-map-sidebar">
        <div class="ccn-map-sidebar-header">
            <i class="ccn-sidebar-icon-pin"></i>
            <h3><?php _e('Nos Clubs', 'city-club-network'); ?></h3>
        </div>

        <!-- Club List -->
        <div id="ccn-map-clubs-list" class="ccn-map-clubs-list">
            <!-- Club list items will be populated by JS -->
        </div>

        <!-- Selected Club Details -->
        <div id="ccn-map-selected-club-details" class="ccn-map-selected-club-details">
            <!-- Details will be populated by JS via AJAX -->
            <div class="ccn-details-placeholder">
                <i class="ccn-info-icon"></i>
                <span><?php _e('Select a club to see details', 'city-club-network'); ?></span>
            </div>
            <div class="ccn-details-error" style="display: none;">
                <i class="ccn-error-icon"></i>
                <span><?php _e('Could not load details.', 'city-club-network'); ?></span>
            </div>
            <div class="ccn-details-content" style="display: none;">
                <h4 class="ccn-details-title"></h4>
                <p class="ccn-details-address"><i class="ccn-details-icon ccn-icon-location"></i> <span></span></p>
                <p class="ccn-details-phone"><i class="ccn-details-icon ccn-icon-phone"></i> <span></span></p>
                <div class="ccn-details-hours">
                    <p><i class="ccn-details-icon ccn-icon-clock"></i> <span class="ccn-hours-mf"></span></p>
                    <p><i class="ccn-details-icon ccn-icon-clock transparent"></i> <span class="ccn-hours-sat"></span></p>
                    <p><i class="ccn-details-icon ccn-icon-clock transparent"></i> <span class="ccn-hours-sun"></span></p>
                </div>
                <div class="ccn-details-amenities">
                    <h5><i class="ccn-details-icon ccn-icon-amenities"></i> <?php _e('Amenities', 'city-club-network'); ?></h5>
                    <ul class="ccn-amenities-list">
                        <!-- Amenities populated by JS -->
                    </ul>
                </div>
                <a href="#" class="ccn-details-directions-button" target="_blank">
                    <i class="ccn-details-icon ccn-icon-directions"></i>
                    <span><?php _e('Itinéraire', 'city-club-network'); ?></span>
                </a>
            </div>
        </div>
    </div>

</div>
