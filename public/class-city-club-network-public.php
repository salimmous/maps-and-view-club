<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

class City_Club_Network_Public {

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
     * The shortcodes instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      City_Club_Network_Shortcodes    $shortcodes    The shortcodes instance.
     */
    protected $shortcodes;

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

        // Initialize the main shortcodes (which also handles data retrieval for templates)
        $this->shortcodes = new City_Club_Network_Shortcodes($this->plugin_name, $this->version);

        // Initialize grid-only shortcode
        $plugin_grid_shortcode = new City_Club_Network_Grid_Shortcode($this->plugin_name, $this->version, $this->shortcodes);

        // Initialize search bar shortcode
        $plugin_search_shortcode = new City_Club_Network_Search_Shortcode($this->plugin_name, $this->version, $this->shortcodes); // Added

        // Add AJAX handler for modal
        add_action('wp_ajax_ccn_get_club_details', array($this, 'ccn_get_club_details_ajax_handler'));
        add_action('wp_ajax_nopriv_ccn_get_club_details', array($this, 'ccn_get_club_details_ajax_handler')); // Allow for non-logged-in users

        // Add AJAX handler for loading map view
        add_action('wp_ajax_ccn_load_map_view', array($this, 'ccn_load_map_view_ajax_handler'));
        add_action('wp_ajax_nopriv_ccn_load_map_view', array($this, 'ccn_load_map_view_ajax_handler'));
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            CCN_PLUGIN_URL . 'public/css/city-club-network-public.css',
            array(),
            $this->version,
            'all'
        );

        // Modal CSS
        wp_enqueue_style(
            $this->plugin_name . '-modal',
            CCN_PLUGIN_URL . 'public/css/city-club-network-modal.css',
            array($this->plugin_name), // Depends on main style
            $this->version,
            'all'
        );

        // Map CSS - Register always, enqueue conditionally
        wp_register_style(
            $this->plugin_name . '-map',
            CCN_PLUGIN_URL . 'public/css/city-club-network-map.css',
            array($this->plugin_name),
            $this->version,
            'all'
        );

        // Grid-only CSS - Register always, enqueue conditionally
        wp_register_style(
            $this->plugin_name . '-grid-only',
            CCN_PLUGIN_URL . 'public/css/city-club-network-grid-only.css',
            array($this->plugin_name),
            $this->version,
            'all'
        );

        // Search Bar CSS - Register always, enqueue conditionally
        wp_register_style(
            $this->plugin_name . '-search-bar',
            CCN_PLUGIN_URL . 'public/css/city-club-network-search-bar.css',
            array(), // No dependency on main style needed
            $this->version,
            'all'
        );

        // Potentially add inline styles for colors from settings
        // $this->add_inline_color_styles();
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Main plugin script (handles view switching, potentially AJAX filters later)
        wp_enqueue_script(
            $this->plugin_name,
            CCN_PLUGIN_URL . 'public/js/city-club-network-public.js',
            array('jquery'),
            $this->version,
            true
        );

        // Grid-only script (Register only, enqueue when needed by grid-only shortcode)
        wp_register_script(
            $this->plugin_name . '-grid-only',
            CCN_PLUGIN_URL . 'public/js/city-club-network-grid-only.js',
            array('jquery'),
            $this->version,
            true
        );

        // Google Maps API (Register only, enqueue when needed by map view/modal, using key from settings)
        $api_key = get_option('ccn_google_maps_api_key', '');
        if (!empty($api_key)) {
            wp_register_script(
                $this->plugin_name . '-google-maps',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places&callback=initMap', // IMPORTANT: Use the actual callback name
                array(),
                null, // Google handles versioning
                true // Load in footer
            );
        } else {
             // Optionally log a warning if the key is missing and map view is attempted
             // This could be done within the shortcode handler when view=map
        }

        // Map script (Register only, enqueue when needed by shortcode handler or AJAX)
        $map_dependencies = array('jquery');
        if (!empty($api_key)) {
            // Map script depends on Google Maps API only if key exists
            // Note: Google Maps API script now calls initMap directly via callback=initMap
            // So, our map script doesn't strictly need to depend on it here,
            // but it needs the google.maps object to be available.
            // $map_dependencies[] = $this->plugin_name . '-google-maps';
        }
        wp_register_script(
            $this->plugin_name . '-map',
            CCN_PLUGIN_URL . 'public/js/city-club-network-map.js',
            $map_dependencies,
            $this->version,
            true // Load in footer
        );

        // Modal script (Always needed if shortcode is present, depends on jQuery)
        wp_enqueue_script(
            $this->plugin_name . '-modal',
            CCN_PLUGIN_URL . 'public/js/city-club-network-modal.js',
            array('jquery'), // Doesn't strictly need Maps API to open/close, but map inside modal might
            $this->version,
            true
        );

        // Localize script data for AJAX, settings, etc.
        // Pass data primarily to the main public script now for AJAX map loading
        wp_localize_script(
            $this->plugin_name, // Target main public script
            'ccn_public_data', // Use a different object name to avoid conflicts
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'modal_nonce' => wp_create_nonce('ccn_get_club_details_nonce'), // Nonce for modal
                'map_nonce' => wp_create_nonce('ccn_load_map_view_nonce'), // Nonce for map loading
                'plugin_url' => CCN_PLUGIN_URL,
                'google_maps_api_key_present' => !empty($api_key), // Let JS know if API key exists
                'text' => array( // Get customized text from settings with defaults
                    'loadingmap' => __('Loading Map View...', 'city-club-network'),
                    'map_load_error' => __('Could not load map view. Please try again.', 'city-club-network'),
                ),
                // Modal-specific data can still be localized separately if preferred,
                // but keeping it together might be simpler.
                'modal_data' => array(
                    'default_image' => CCN_PLUGIN_URL . 'public/images/default-club-image.jpg',
                    'text' => array(
                        'book_tour' => get_option('ccn_book_tour_text', __('Book a Tour', 'city-club-network')),
                        'get_directions' => get_option('ccn_get_directions_text', __('Get Directions', 'city-club-network')),
                        'choose_plan' => get_option('ccn_choose_plan_text', __('Choose Plan', 'city-club-network')),
                        'loading' => __('Loading...', 'city-club-network'),
                        'error' => __('Could not load club details. Please try again.', 'city-club-network'),
                        'no_facilities' => __('No specific facilities listed.', 'city-club-network'),
                        'no_classes' => __('Class schedule not available.', 'city-club-network'),
                        'no_memberships' => __('Membership information not available.', 'city-club-network'),
                        'view_pdf' => __('View Schedule PDF', 'city-club-network'),
                        'download_pdf' => __('Download Schedule PDF', 'city-club-network'),
                    ),
                    'icons' => array(
                        'location' => CCN_PLUGIN_URL . 'public/images/location-icon.svg',
                        'hours' => CCN_PLUGIN_URL . 'public/images/clock-icon.svg',
                        'star_filled' => CCN_PLUGIN_URL . 'public/images/star-filled.svg',
                        'star_half' => CCN_PLUGIN_URL . 'public/images/star-half.svg',
                        'star_empty' => CCN_PLUGIN_URL . 'public/images/star-empty.svg',
                    )
                )
            )
        );
    }

    /**
     * AJAX handler to fetch detailed club information for the modal.
     *
     * @since 1.1.0
     */
    public function ccn_get_club_details_ajax_handler() {
        // Verify nonce
        check_ajax_referer('ccn_get_club_details_nonce', 'nonce');

        // Get Club ID
        $club_id = isset($_POST['club_id']) ? intval($_POST['club_id']) : 0;

        if (!$club_id || get_post_type($club_id) !== 'city_club' || get_post_status($club_id) !== 'publish') {
            wp_send_json_error(array('message' => __('Invalid Club ID.', 'city-club-network')));
            return;
        }

        // Fetch Club Data (Re-use Shortcode method for consistency, passing empty filters)
        // Or keep the detailed fetch logic here if preferred
        $club_data = $this->get_detailed_club_data($club_id); // Use a helper method

        if (!$club_data) {
             wp_send_json_error(array('message' => __('Could not retrieve club data.', 'city-club-network')));
             return;
        }

        wp_send_json_success($club_data);
    }

     /**
     * AJAX handler to load map view HTML and config data.
     *
     * @since 1.2.0
     */
    public function ccn_load_map_view_ajax_handler() {
        check_ajax_referer('ccn_load_map_view_nonce', 'nonce');

        // Get current filters from the request (e.g., passed via POST/GET)
        $filters = array(
            'city' => isset($_REQUEST['ccn_city']) ? sanitize_text_field($_REQUEST['ccn_city']) : '',
            'facility' => isset($_REQUEST['ccn_facility']) ? sanitize_text_field($_REQUEST['ccn_facility']) : '',
            'membership_category' => isset($_REQUEST['ccn_membership_category']) ? sanitize_text_field($_REQUEST['ccn_membership_category']) : '',
        );

        // Get map config data (similar to localize_map_data in shortcode class)
        $map_config = $this->shortcodes->get_map_config_data($filters); // Assume this method exists or create it

        // Get map view HTML
        ob_start();
        // Pass necessary variables to the partial if needed (like $plugin_instance or $this_ref)
        $plugin_instance = $this->shortcodes; // Pass shortcode instance for get_clubs_data
        include CCN_PLUGIN_DIR . 'public/partials/city-club-network-map-view.php';
        $map_html = ob_get_clean();

        // Enqueue necessary scripts/styles if they weren't already
        // Note: This might be tricky in AJAX. Better to ensure they are registered always.
        wp_enqueue_style($this->plugin_name . '-map');
        wp_enqueue_script($this->plugin_name . '-map');
        if (!empty(get_option('ccn_google_maps_api_key', ''))) {
            wp_enqueue_script($this->plugin_name . '-google-maps');
        }


        wp_send_json_success(array(
            'html' => $map_html,
            'map_config' => $map_config
        ));
    }


    /**
     * Helper function to get detailed data for a single club.
     * (Extracted from the original AJAX handler)
     *
     * @since 1.2.0
     * @param int $club_id The ID of the club post.
     * @return array|null Club data array or null if invalid ID.
     */
    private function get_detailed_club_data($club_id) {
         if (!$club_id || get_post_type($club_id) !== 'city_club' || get_post_status($club_id) !== 'publish') {
            return null;
        }

        $club_data = array(
            'id' => $club_id,
            'title' => get_the_title($club_id),
            'description' => apply_filters('the_content', get_post_field('post_content', $club_id)),
            'thumbnail' => get_the_post_thumbnail_url($club_id, 'large') ?: CCN_PLUGIN_URL . 'public/images/default-club-image.jpg',
            'address' => get_post_meta($club_id, '_club_address', true),
            'is_premium' => (bool) get_post_meta($club_id, '_club_is_premium', true),
            'latitude' => get_post_meta($club_id, '_club_latitude', true),
            'longitude' => get_post_meta($club_id, '_club_longitude', true),
            'rating' => get_post_meta($club_id, '_club_rating', true),
            'reviews_count' => get_post_meta($club_id, '_club_reviews_count', true),
            'hours' => array(
                'mf' => get_post_meta($club_id, '_club_hours_mf', true),
                'sat' => get_post_meta($club_id, '_club_hours_sat', true),
                'sun' => get_post_meta($club_id, '_club_hours_sun', true),
            ),
            'contact' => array(
                'phone' => get_post_meta($club_id, '_club_contact_phone', true),
                'email' => get_post_meta($club_id, '_club_contact_email', true),
                'website' => get_post_meta($club_id, '_club_contact_website', true),
            ),
            'urls' => array(
                'book_tour' => get_post_meta($club_id, '_club_book_tour_url', true),
                'permalink' => get_permalink($club_id),
                'class_schedule_pdf' => get_post_meta($club_id, '_club_class_schedule_pdf', true),
            ),
            'facilities' => array(),
            'classes' => array(),
            'memberships' => array(),
        );

        // Get Facilities
        $facility_terms = get_the_terms($club_id, 'facility');
        if ($facility_terms && !is_wp_error($facility_terms)) {
            foreach ($facility_terms as $term) {
                $club_data['facilities'][] = array(
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'icon_url' => get_term_meta($term->term_id, 'facility_icon_url', true),
                    'description' => get_term_meta($term->term_id, 'facility_description', true),
                );
            }
        }

        // Get Classes
        $classes_raw = get_post_meta($club_id, '_club_classes_data', true);
        if (!empty($classes_raw)) {
            $lines = explode("\n", trim($classes_raw));
            foreach ($lines as $line) {
                $parts = explode('|', trim($line));
                if (count($parts) >= 4) {
                    $club_data['classes'][] = array(
                        'name' => trim($parts[0]),
                        'schedule' => trim($parts[1]),
                        'level' => trim($parts[2]),
                        'instructor' => trim($parts[3]),
                    );
                }
            }
        }

        // Get Memberships
        $memberships_raw = get_post_meta($club_id, '_club_memberships_data', true);
        if (!empty($memberships_raw)) {
            $lines = explode("\n", trim($memberships_raw));
            foreach ($lines as $line) {
                $parts = explode('|', trim($line));
                if (count($parts) >= 5) {
                    $features = isset($parts[3]) ? array_map('trim', explode(',', $parts[3])) : array();
                    $club_data['memberships'][] = array(
                        'name' => trim($parts[0]),
                        'price' => trim($parts[1]),
                        'period' => trim($parts[2]),
                        'features' => $features,
                        'is_popular' => isset($parts[4]) ? (bool)trim($parts[4]) : false,
                        'url' => isset($parts[5]) ? esc_url(trim($parts[5])) : '#',
                    );
                }
            }
        }

        return $club_data;
    }


} // End class
