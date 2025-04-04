<?php
/**
 * The class responsible for handling shortcodes.
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

class City_Club_Network_Shortcodes {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->register_shortcodes();
    }

    /**
     * Register the shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('city_club_network', array($this, 'city_club_network_shortcode'));
        add_shortcode('city_club_map', array($this, 'city_club_map_shortcode')); // Register new map-only shortcode
    }

    /**
     * Callback for the city_club_network shortcode (Grid + Map).
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The shortcode output.
     */
    public function city_club_network_shortcode($atts) {
        // Normalize attribute keys to lowercase
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        // Override default attributes with user attributes
        $atts = shortcode_atts(
            array(
                'view' => 'grid', // grid or map
                'city' => '',
                'facility' => '',
                'membership_category' => '', // Corrected taxonomy name
                'per_page' => 9, // Default to 9 for 3x3 grid
            ),
            $atts,
            'city_club_network'
        );

        // Determine the initial view based on attribute or potentially URL hash (handled by JS)
        $initial_view = $atts['view'];

        // Enqueue base styles and modal scripts always
        wp_enqueue_style($this->plugin_name);
        wp_enqueue_style($this->plugin_name . '-modal');
        wp_enqueue_script($this->plugin_name); // Main public JS
        wp_enqueue_script($this->plugin_name . '-modal'); // Modal JS

        // Conditionally enqueue map assets AND localize data *if* map view is requested initially
        if ($initial_view === 'map') {
            $this->enqueue_map_assets(); // Use helper function
            $this->localize_map_data($atts); // Use helper function
        }

        // Start output buffering
        ob_start();

        // Pass the instance and attributes to the template
        $plugin_instance = $this; // Pass the current instance

        // Include the main container template which might load partials dynamically
        // If map view is initial, include map partial directly.
        // If grid view is initial, include grid partial (which contains empty map container).
        // Note: The grid view partial now handles the container logic.
        include CCN_PLUGIN_DIR . 'public/partials/city-club-network-grid-view.php';


        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Callback for the city_club_map shortcode (Map Only).
     *
     * @since    1.3.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The shortcode output.
     */
    public function city_club_map_shortcode($atts) {
        // Normalize attribute keys to lowercase
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        // Override default attributes with user attributes
        $atts = shortcode_atts(
            array(
                'city' => '',
                'facility' => '',
                'membership_category' => '',
                // 'height' => '650px', // Optional height attribute
            ),
            $atts,
            'city_club_map'
        );

        // Enqueue necessary assets for the map
        $this->enqueue_map_assets();
        $this->localize_map_data($atts);

        // Start output buffering
        ob_start();

        // Pass the instance and attributes to the template
        $plugin_instance = $this; // Pass the current instance
        $is_standalone_map = true; // Flag for the template

        // Directly include the map view partial
        include CCN_PLUGIN_DIR . 'public/partials/city-club-network-map-view.php';

        // Return the buffered content
        return ob_get_clean();
    }


    /**
     * Helper function to enqueue map-related assets.
     *
     * @since 1.3.0
     */
    private function enqueue_map_assets() {
        wp_enqueue_style($this->plugin_name . '-map'); // Enqueue map CSS
        wp_enqueue_script($this->plugin_name . '-map'); // Enqueue map JS

        // Enqueue Google Maps API script only if needed and key exists
        $api_key = get_option('ccn_google_maps_api_key', '');
        if (!empty($api_key)) {
             wp_enqueue_script($this->plugin_name . '-google-maps');
        }

        // Enqueue base public script as map JS might depend on its localized data (like AJAX URL, nonce)
        wp_enqueue_script($this->plugin_name);
    }

    /**
     * Helper function to localize map configuration data.
     *
     * @since 1.3.0
     * @param array $atts Shortcode attributes or filters.
     */
    private function localize_map_data($atts = array()) {
        $map_config_data = $this->get_map_config_data($atts);
        wp_localize_script(
            $this->plugin_name . '-map', // Localize against the map script handle
            'ccn_map_config',
            $map_config_data
        );
    }


    /**
     * Get clubs data based on filters.
     *
     * @since    1.0.0
     * @param    array    $filters    The filters to apply.
     * @return   array                The clubs data.
     */
    public function get_clubs_data($filters = array()) {
        $args = array(
            'post_type' => 'city_club',
            'posts_per_page' => -1, // Get all matching clubs
            'post_status' => 'publish',
            'orderby' => 'title', // Order alphabetically by title
            'order' => 'ASC',
        );

        // Apply taxonomy filters
        $tax_query = array('relation' => 'AND'); // Use AND relation

        if (!empty($filters['city'])) {
            $tax_query[] = array(
                'taxonomy' => 'city',
                'field' => 'slug',
                'terms' => $filters['city'],
            );
        }

        if (!empty($filters['facility'])) {
            $tax_query[] = array(
                'taxonomy' => 'facility',
                'field' => 'slug',
                'terms' => $filters['facility'],
            );
        }

        // Corrected membership filter
        if (!empty($filters['membership_category'])) {
            $tax_query[] = array(
                'taxonomy' => 'membership_category', // Correct taxonomy name
                'field' => 'slug',
                'terms' => $filters['membership_category'],
            );
        }

        // Only add tax_query if there are actual filters applied
        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }

        $clubs_query = new WP_Query($args);
        $clubs = array();

        if ($clubs_query->have_posts()) {
            while ($clubs_query->have_posts()) {
                $clubs_query->the_post();
                $post_id = get_the_ID();

                // Get facilities with meta
                $facility_terms = get_the_terms($post_id, 'facility');
                $facilities_data = array();
                if ($facility_terms && !is_wp_error($facility_terms)) {
                    foreach ($facility_terms as $term) {
                        $facilities_data[] = array(
                            'name' => $term->name,
                            'slug' => $term->slug,
                            'icon_url' => get_term_meta($term->term_id, 'facility_icon_url', true),
                            'description' => get_term_meta($term->term_id, 'facility_description', true),
                        );
                    }
                }

                 // Get city term(s)
                $city_terms = get_the_terms($post_id, 'city');
                $city_name = '';
                if ($city_terms && !is_wp_error($city_terms)) {
                    $city_name = $city_terms[0]->name; // Assuming one city per club
                }

                // Get club data
                $clubs[] = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'city_name' => $city_name, // Add city name
                    'facilities' => $facilities_data, // Use the array with meta
                    'membership_category' => get_the_terms($post_id, 'membership_category'), // Keep for potential use
                    'address' => get_post_meta($post_id, '_club_address', true),
                    // Get all hours for potential use in map sidebar details
                    'hours' => array(
                        'mf' => get_post_meta($post_id, '_club_hours_mf', true),
                        'sat' => get_post_meta($post_id, '_club_hours_sat', true),
                        'sun' => get_post_meta($post_id, '_club_hours_sun', true),
                    ),
                    'rating' => get_post_meta($post_id, '_club_rating', true),
                    'reviews_count' => get_post_meta($post_id, '_club_reviews_count', true),
                    'is_premium' => (bool) get_post_meta($post_id, '_club_is_premium', true), // Ensure boolean
                    'latitude' => get_post_meta($post_id, '_club_latitude', true),
                    'longitude' => get_post_meta($post_id, '_club_longitude', true),
                    'thumbnail' => get_the_post_thumbnail_url($post_id, 'medium_large') ?: CCN_PLUGIN_URL . 'public/images/default-club-image.jpg', // Use default if no thumbnail
                    'contact_phone' => get_post_meta($post_id, '_club_contact_phone', true), // Add phone
                    'class_schedule_pdf' => get_post_meta($post_id, '_club_class_schedule_pdf', true), // Get PDF URL
                );
            }
        }

        wp_reset_postdata();
        return $clubs;
    }

    /**
     * Get available filter options for cities, facilities, and membership types.
     *
     * @since    1.0.0
     * @return   array    The available filter options.
     */
    public function get_available_filters() {
        $filters = array(
            'cities' => array(),
            'facilities' => array(),
            'membership_categories' => array() // Corrected key
        );

        // Get all city terms
        $city_terms = get_terms(array(
            'taxonomy' => 'city',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ));
        if (!is_wp_error($city_terms) && !empty($city_terms)) {
            foreach ($city_terms as $term) {
                $filters['cities'][] = array('slug' => $term->slug, 'name' => $term->name);
            }
        }

        // Get all facility terms
        $facility_terms = get_terms(array(
            'taxonomy' => 'facility',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ));
        if (!is_wp_error($facility_terms) && !empty($facility_terms)) {
             foreach ($facility_terms as $term) {
                $filters['facilities'][] = array('slug' => $term->slug, 'name' => $term->name);
            }
        }

        // Get all membership category terms
        $membership_terms = get_terms(array(
            'taxonomy' => 'membership_category', // Correct taxonomy name
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ));
        if (!is_wp_error($membership_terms) && !empty($membership_terms)) {
             foreach ($membership_terms as $term) {
                $filters['membership_categories'][] = array('slug' => $term->slug, 'name' => $term->name); // Corrected key
            }
        }

        return $filters;
    }

    /**
     * Prepare map configuration data based on filters.
     * Helper function used for both initial localization and AJAX loading.
     * Includes basic data needed for markers and the initial sidebar list.
     *
     * @since 1.2.0
     * @param array $filters Filters for city, facility, membership_category.
     * @return array Map configuration data.
     */
    public function get_map_config_data($filters = array()) {
        $clubs = $this->get_clubs_data($filters); // This now includes more fields

        // Prepare map data for JS
        $map_data = array();
        $locations_count = 0;
        foreach ($clubs as $club) {
            if (!empty($club['latitude']) && !empty($club['longitude'])) {
                // Data needed for map markers and initial sidebar list
                $map_data[] = array(
                    'id' => $club['id'],
                    'title' => $club['title'],
                    'lat' => floatval($club['latitude']),
                    'lng' => floatval($club['longitude']),
                    'isPremium' => $club['is_premium'],
                    'permalink' => $club['permalink'], // Keep for info window link
                    'address' => $club['address'], // Keep for info window
                    'city_name' => $club['city_name'], // Add city name for sidebar list grouping/display
                    // Note: Thumbnail, phone, hours, facilities are NOT included here
                    // to keep the initial load smaller. They will be fetched via AJAX.
                );
                $locations_count++;
            }
        }

        // Default center and zoom
        $default_center = array('lat' => 31.7917, 'lng' => -7.0926); // Morocco center
        $default_zoom = 6;
        $center_lat = $default_center['lat'];
        $center_lng = $default_center['lng'];
        $zoom = $default_zoom;

        if ($locations_count > 0) {
            // Basic center calculation (average)
            $total_lat = 0;
            $total_lng = 0;
            foreach ($map_data as $loc) {
                $total_lat += $loc['lat'];
                $total_lng += $loc['lng'];
            }
            $center_lat = $total_lat / $locations_count;
            $center_lng = $total_lng / $locations_count;

            // Adjust zoom based on number of locations
            if ($locations_count === 1) {
                $zoom = 14;
            } elseif ($locations_count < 5) {
                $zoom = 10;
            } else {
                $zoom = 7;
            }
        }

        // Return data array for localization or AJAX response
        return array(
            'clubs' => $map_data, // Contains only essential data for markers/list
            'center' => array('lat' => $center_lat, 'lng' => $center_lng),
            'zoom' => $zoom,
            'zoom_single' => 14, // Zoom level for single marker fitBounds
            'marker_icon_standard' => CCN_PLUGIN_URL . 'public/images/marker-standard.svg',
            'marker_icon_premium' => CCN_PLUGIN_URL . 'public/images/marker-premium.svg',
            'locations_count' => $locations_count,
            // Add icon URLs needed by the sidebar details (fetched via AJAX later, but JS needs URLs)
            'icon_urls' => array(
                'phone' => CCN_PLUGIN_URL . 'public/images/phone-icon.svg',
                'clock' => CCN_PLUGIN_URL . 'public/images/clock-icon.svg',
                'amenities' => CCN_PLUGIN_URL . 'public/images/amenities-icon.svg', // Add an amenities icon
                'directions' => CCN_PLUGIN_URL . 'public/images/directions-icon.svg', // Add a directions icon
                'location_pin' => CCN_PLUGIN_URL . 'public/images/location-icon.svg', // For sidebar list
                'club_list_pin' => CCN_PLUGIN_URL . 'public/images/location-pin-alt.svg', // Add a different pin for list items
            ),
            'text' => array( // Add text strings needed by the map sidebar JS
                 'loading_details' => __('Loading details...', 'city-club-network'),
                 'details_error' => __('Could not load details.', 'city-club-network'),
                 'amenities' => __('Amenities', 'city-club-network'),
                 'directions' => __('Itinéraire', 'city-club-network'), // Directions button text
                 'no_clubs_found' => __('No locations found matching your criteria.', 'city-club-network'),
                 'select_club' => __('Select a club from the list or map to see details.', 'city-club-network'),
            )
        );
    }


} // End class
