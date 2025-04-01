<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

class City_Club_Network_Admin {

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
     * The post types instance.
     *
     * @since    1.0.0
     * @access   protected
     * @var      City_Club_Network_Post_Types    $post_types    The post types instance.
     */
    protected $post_types;

    /**
     * The settings instance.
     *
     * @since    1.1.0 // Added settings instance
     * @access   protected
     * @var      City_Club_Network_Settings    $settings    The settings instance.
     */
    protected $settings;


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

        // Initialize the post types
        $this->post_types = new City_Club_Network_Post_Types($this->plugin_name, $this->version);
        // Initialize settings - This is now handled by the main plugin class hook registration
        // $this->settings = new City_Club_Network_Settings($this->plugin_name, $this->version);
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     * @param string $hook The current admin page hook.
     */
    public function enqueue_styles($hook) {
        // Always enqueue general admin styles
        wp_enqueue_style(
            $this->plugin_name . '-admin', // Consistent handle
            CCN_PLUGIN_URL . 'admin/css/city-club-network-admin.css',
            array(),
            $this->version,
            'all'
        );

        // Enqueue WP Color Picker styles only on OUR settings page
        // The hook for a page added by add_options_page is 'settings_page_{menu_slug}'
        if ('settings_page_city-club-network-settings' === $hook) {
             wp_enqueue_style('wp-color-picker');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     * @param string $hook The current admin page hook.
     */
    public function enqueue_scripts($hook) {

        // Dependencies array
        $dependencies = array('jquery');

        // Add color picker dependency only on the settings page
        if ('settings_page_city-club-network-settings' === $hook) {
            $dependencies[] = 'wp-color-picker';
        }

        // General admin script (for color picker init, map init, etc.)
        wp_enqueue_script(
            $this->plugin_name . '-admin', // Consistent handle
            CCN_PLUGIN_URL . 'admin/js/city-club-network-admin.js',
            $dependencies,
            $this->version,
            true // Load in footer
        );

        // Enqueue Google Maps API only on the City Club post edit screen AND if API key exists
        global $post_type;
        if ('post.php' == $hook || 'post-new.php' == $hook) {
            if ('city_club' === $post_type) {
                $api_key = get_option('ccn_google_maps_api_key', '');
                if (!empty($api_key)) {
                    wp_enqueue_script(
                        $this->plugin_name . '-google-maps',
                        'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places&callback=Function.prototype', // Use dummy callback, init called by our script
                        array(), // No WP dependencies for the API script itself
                        null, // Google handles versioning
                        true
                    );
                    // Make our admin script depend on the Google Maps script ONLY on this page
                    wp_add_inline_script(
                         $this->plugin_name . '-admin',
                         'console.log("CCN: Google Maps API enqueued for CPT edit screen.");',
                         'before' // Add before main script execution
                    );
                     // Add dependency dynamically (less common, but possible)
                     /*
                     global $wp_scripts;
                     $handle = $this->plugin_name . '-admin';
                     if (isset($wp_scripts->registered[$handle])) {
                         $wp_scripts->registered[$handle]->deps[] = $this->plugin_name . '-google-maps';
                     }
                     */

                } else {
                    // Optionally add an inline script to inform the user if the key is missing
                     wp_add_inline_script(
                        $this->plugin_name . '-admin',
                        'console.warn("City Club Network: Google Maps API Key is missing in settings. Location map functionality limited.");'
                    );
                }
            }
        }

        // Localize script data (can be used by admin.js)
        wp_localize_script(
            $this->plugin_name . '-admin', // Use the consistent handle
            'ccn_admin_data',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'plugin_url' => CCN_PLUGIN_URL,
                'nonce' => wp_create_nonce('ccn_admin_nonce'), // Example nonce
                'google_maps_api_key_present' => !empty(get_option('ccn_google_maps_api_key', '')) // Pass API key status
            )
        );
    }
}
