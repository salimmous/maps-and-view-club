<?php
/**
 * The class responsible for handling the search bar shortcode.
 *
 * @since      1.4.0
 * @package    City_Club_Network
 */

class City_Club_Network_Search_Shortcode {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.4.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.4.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Reference to the main shortcode class for data methods.
     *
     * @since    1.4.0
     * @access   protected
     * @var      City_Club_Network_Shortcodes    $shortcode_instance    Instance of the main shortcode class.
     */
    protected $shortcode_instance;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.4.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     * @param    City_Club_Network_Shortcodes $shortcode_instance The main shortcode class instance.
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
     * @since    1.4.0
     */
    public function register_shortcodes() {
        add_shortcode('city_club_search_bar', array($this, 'city_club_search_bar_shortcode'));
    }

    /**
     * Callback for the city_club_search_bar shortcode.
     *
     * @since    1.4.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The shortcode output.
     */
    public function city_club_search_bar_shortcode($atts) {
        // Normalize attribute keys to lowercase
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        // Override default attributes with user attributes
        $atts = shortcode_atts(
            array(
                'results_page_url' => '', // URL of the page where results are displayed (e.g., page with [city_club_network])
            ),
            $atts,
            'city_club_search_bar'
        );

        // Enqueue necessary styles
        wp_enqueue_style($this->plugin_name . '-search-bar');

        // Get available facilities using the main shortcode instance
        $available_filters = $this->shortcode_instance->get_available_filters();
        $facilities = isset($available_filters['facilities']) ? $available_filters['facilities'] : array();

        // Determine the form action URL
        $form_action_url = !empty($atts['results_page_url']) ? esc_url($atts['results_page_url']) : get_permalink(); // Default to current page if not set

        // Start output buffering
        ob_start();

        // Include the search bar template
        include CCN_PLUGIN_DIR . 'public/partials/city-club-network-search-bar.php';

        // Return the buffered content
        return ob_get_clean();
    }
}
