<?php
/**
 * The main plugin class.
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

class City_Club_Network {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      City_Club_Network_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

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
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->plugin_name = 'city-club-network';
        $this->version = CCN_VERSION; // Use defined constant

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters of the core plugin.
        require_once CCN_PLUGIN_DIR . 'includes/class-city-club-network-loader.php';

        // The class responsible for defining all actions that occur in the admin area.
        require_once CCN_PLUGIN_DIR . 'admin/class-city-club-network-admin.php';

        // The class responsible for the settings page. (Load definition)
        require_once CCN_PLUGIN_DIR . 'admin/class-city-club-network-settings.php';

        // The class responsible for defining all actions that occur in the public-facing side of the site.
        require_once CCN_PLUGIN_DIR . 'public/class-city-club-network-public.php';

        // The class responsible for defining the custom post type for clubs
        require_once CCN_PLUGIN_DIR . 'includes/class-city-club-network-post-types.php';

        // The class responsible for handling the shortcodes
        require_once CCN_PLUGIN_DIR . 'includes/class-city-club-network-shortcodes.php';

        // The class responsible for handling the grid-only shortcode
        require_once CCN_PLUGIN_DIR . 'includes/class-city-club-network-grid-shortcode.php';

        // The class responsible for handling the search bar shortcode
        require_once CCN_PLUGIN_DIR . 'includes/class-city-club-network-search-shortcode.php'; // Added search shortcode class

        $this->loader = new City_Club_Network_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // Instantiate admin class (handles CPT meta boxes, admin scripts/styles)
        $plugin_admin = new City_Club_Network_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Instantiate settings class (handles settings page registration and fields)
        // The hooks ('admin_menu', 'admin_init') are added within the Settings class constructor
        $plugin_settings = new City_Club_Network_Settings($this->get_plugin_name(), $this->get_version());

        // Note: The Post Types class constructor registers its own 'init' hooks internally
        // $plugin_post_types = new City_Club_Network_Post_Types($this->get_plugin_name(), $this->get_version());
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        // Instantiate public class (handles public scripts/styles, AJAX, shortcodes)
        $plugin_public = new City_Club_Network_Public($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // AJAX hook registration is now inside City_Club_Network_Public constructor
        // $this->loader->add_action('wp_ajax_ccn_get_club_details', $plugin_public, 'ccn_get_club_details_ajax_handler');
        // $this->loader->add_action('wp_ajax_nopriv_ccn_get_club_details', $plugin_public, 'ccn_get_club_details_ajax_handler');

        // Shortcode registration is now inside City_Club_Network_Public constructor via Shortcodes class
        // $plugin_shortcodes = new City_Club_Network_Shortcodes($this->get_plugin_name(), $this->get_version());
        // add_shortcode('city_club_network', array($plugin_shortcodes, 'city_club_network_shortcode'));
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    City_Club_Network_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
