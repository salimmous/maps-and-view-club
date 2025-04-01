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
    }

    /**
     * Callback for the city_club_network shortcode.
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
            wp_enqueue_style($this->plugin_name . '-map'); // Enqueue map CSS
            wp_enqueue_script($this->plugin_name . '-map'); // Enqueue map JS

            // Enqueue Google Maps API script only if needed and key exists
            $api_key = get_option('ccn_google_maps_api_key', '');
            if (!empty($api_key)) {
                 wp_enqueue_script($this->plugin_name . '-google-maps');
            }

            // Localize data specifically for map view
            $map_config_data = $this->get_map_config_data($atts);
            wp_localize_script(
                $this->plugin_name . '-map',
                'ccn_map_config',
                $map_config_data
            );
        }

        // Start output buffering
        ob_start();

        // Pass the instance and attributes to the template
        $plugin_instance = $this; // Pass the current instance

        // Include the main container template which might load partials dynamically
        // If map view is initial, include map partial directly.
        // If grid view is initial, include grid partial (which contains empty map container).
        if ($initial_view === 'map') {
             include CCN_PLUGIN_DIR . 'public/partials/city-club-network-map-view.php';
        } else {
             include CCN_PLUGIN_DIR . 'public/partials/city-club-network-grid-view.php';
        }

        // Return the buffered content
        return ob_get_clean();
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

                // Get club data
                $clubs[] = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'city' => get_the_terms($post_id, 'city'), // Keep for potential use, though filter uses slug
                    'facilities' => $facilities_data, // Use the array with meta
                    'membership_category' => get_the_terms($post_id, 'membership_category'), // Keep for potential use
                    'address' => get_post_meta($post_id, '_club_address', true),
                    // Combine hours for simple display if needed, or keep separate
                    'opening_hours' => get_post_meta($post_id, '_club_hours_mf', true), // Example: Use M-F hours for card display
                    'rating' => get_post_meta($post_id, '_club_rating', true),
                    'reviews_count' => get_post_meta($post_id, '_club_reviews_count', true),
                    'is_premium' => (bool) get_post_meta($post_id, '_club_is_premium', true), // Ensure boolean
                    'latitude' => get_post_meta($post_id, '_club_latitude', true),
                    'longitude' => get_post_meta($post_id, '_club_longitude', true),
                    'thumbnail' => get_the_post_thumbnail_url($post_id, 'medium_large'), // Use a slightly larger thumbnail
                    'map_link' => '', // Placeholder for potential direct map link meta
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
     *
     * @since 1.2.0
     * @param array $filters Filters for city, facility, membership_category.
     * @return array Map configuration data.
     */
    public function get_map_config_data($filters = array()) {
        $clubs = $this->get_clubs_data($filters);

        // Prepare map data for JS
        $map_data = array();
        $locations_count = 0;
        foreach ($clubs as $club) {
            if (!empty($club['latitude']) && !empty($club['longitude'])) {
                $map_data[] = array(
                    'id' => $club['id'],
                    'title' => $club['title'],
                    'lat' => floatval($club['latitude']),
                    'lng' => floatval($club['longitude']),
                    'isPremium' => $club['is_premium'],
                    'permalink' => $club['permalink'],
                    'address' => $club['address'],
                    'thumbnail' => $club['thumbnail'] ?: CCN_PLUGIN_URL . 'public/images/default-club-image.jpg',
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
            'clubs' => $map_data,
            'center' => array('lat' => $center_lat, 'lng' => $center_lng),
            'zoom' => $zoom,
            'zoom_single' => 14, // Zoom level for single marker fitBounds
            'marker_icon_standard' => CCN_PLUGIN_URL . 'public/images/marker-standard.svg',
            'marker_icon_premium' => CCN_PLUGIN_URL . 'public/images/marker-premium.svg',
            'locations_count' => $locations_count,
        );
    }


} // End class
