<?php
/**
 * The class responsible for handling the grid-only shortcode.
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

class City_Club_Network_Grid_Shortcode {

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
     * Reference to the main shortcode class for data methods.
     *
     * @since    1.0.0
     * @access   protected
     * @var      object    $shortcode_instance    Instance of the main shortcode class.
     */
    protected $shortcode_instance;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     * @param    object    $shortcode_instance The main shortcode class instance.
     */
    public function __construct($plugin_name, $version, $shortcode_instance) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->shortcode_instance = $shortcode_instance;

        $this->register_shortcodes();
    }

    /**
     * Register the shortcodes.
     *
     * @since    1.0.0
     */
    public function register_shortcodes() {
        add_shortcode('city_club_grid', array($this, 'city_club_grid_shortcode'));
    }

    /**
     * Callback for the city_club_grid shortcode (Grid Only with Filters).
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The shortcode output.
     */
    public function city_club_grid_shortcode($atts) {
        // Normalize attribute keys to lowercase
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        // Override default attributes with user attributes
        $atts = shortcode_atts(
            array(
                'city' => '',
                'facility' => '',
                'membership_category' => '',
                'per_page' => 9, // Default to 9 for 3x3 grid
                'title' => 'Explore Our <span class="ccn-title-highlight">Club Network</span>',
                'subtitle' => 'Find the perfect City Club location with our interactive tools',
                'show_title' => 'yes', // Whether to show the title
            ),
            $atts,
            'city_club_grid'
        );

        // Enqueue necessary styles and scripts
        wp_enqueue_style($this->plugin_name);
        wp_enqueue_style($this->plugin_name . '-grid-only'); // Grid-only CSS
        wp_enqueue_script($this->plugin_name); // Main public JS
        wp_enqueue_script($this->plugin_name . '-grid-only'); // Grid-only JS

        // Start output buffering
        ob_start();

        // Get the clubs data using the shortcode instance reference
        $filters = array(
            'city' => isset($_GET['ccn_city']) ? sanitize_text_field($_GET['ccn_city']) : (isset($atts['city']) ? $atts['city'] : ''),
            'facility' => isset($_GET['ccn_facility']) ? sanitize_text_field($_GET['ccn_facility']) : (isset($atts['facility']) ? $atts['facility'] : ''),
            'membership_category' => isset($_GET['ccn_membership_category']) ? sanitize_text_field($_GET['ccn_membership_category']) : (isset($atts['membership_category']) ? $atts['membership_category'] : ''),
        );

        $clubs = $this->shortcode_instance->get_clubs_data($filters);
        $available_filters = $this->shortcode_instance->get_available_filters();

        // Pagination
        $per_page = isset($atts['per_page']) ? intval($atts['per_page']) : 9;
        $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
        $total_clubs = count($clubs);
        $total_pages = ceil($total_clubs / $per_page);

        // Slice the clubs array for pagination
        $offset = ($current_page - 1) * $per_page;
        $clubs_paged = array_slice($clubs, $offset, $per_page);

        // Base URL for pagination links
        $base_url = remove_query_arg('paged', get_pagenum_link(999999999));

        // Include the grid-only template
        include CCN_PLUGIN_DIR . 'public/partials/city-club-network-grid-only.php';

        // Return the buffered content
        return ob_get_clean();
    }
}