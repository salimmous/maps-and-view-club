<?php
/**
 * The settings page functionality of the plugin.
 *
 * @since      1.1.0
 * @package    City_Club_Network
 */

class City_Club_Network_Settings {

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.1.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.1.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.1.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Register the settings page hook
        add_action('admin_menu', array($this, 'add_settings_page'));
        // Register settings initialization hook
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Add the settings page to the admin menu under "Settings".
     *
     * @since    1.1.0
     */
    public function add_settings_page() {
        add_options_page(
            __('City Club Network Settings', 'city-club-network'), // Page title
            __('City Club Network', 'city-club-network'),          // Menu title
            'manage_options',                                     // Capability required
            'city-club-network-settings',                         // Menu slug
            array($this, 'render_settings_page')                  // Callback function to render the page
        );
    }

    /**
     * Register the settings, sections, and fields for the plugin.
     *
     * @since    1.1.0
     */
    public function register_settings() {
        // Settings Group Name (used in settings_fields())
        $option_group = 'city_club_network_settings';

        // --- General Settings Section ---
        add_settings_section(
            'ccn_general_settings',                         // Section ID
            __('General Settings', 'city-club-network'),    // Section Title
            array($this, 'render_general_settings_section'), // Callback for section description (optional)
            $option_group                                   // Page slug where section appears
        );

        // Google Maps API Key Field
        register_setting($option_group, 'ccn_google_maps_api_key', array('sanitize_callback' => 'sanitize_text_field', 'default' => ''));
        add_settings_field(
            'ccn_google_maps_api_key',                      // Field ID
            __('Google Maps API Key', 'city-club-network'), // Field Title
            array($this, 'render_text_field'),              // Callback to render the field
            $option_group,                                  // Page slug
            'ccn_general_settings',                         // Section ID
            array(                                          // Arguments for the callback
                'label_for' => 'ccn_google_maps_api_key',
                'description' => __('Enter your Google Maps API Key. Required for map functionality.', 'city-club-network') .
                                 ' <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">' . __('Get an API Key', 'city-club-network') . '</a>'
            )
        );

        // --- Customization Settings Section ---
        add_settings_section(
            'ccn_customization_settings',
            __('Appearance Customization', 'city-club-network'),
            array($this, 'render_customization_settings_section'),
            $option_group
        );

        // Primary Color Field
        register_setting($option_group, 'ccn_primary_color', array('sanitize_callback' => 'sanitize_hex_color', 'default' => '#3182ce')); // Default blue
        add_settings_field(
            'ccn_primary_color',
            __('Primary Color', 'city-club-network'),
            array($this, 'render_color_field'),
            $option_group,
            'ccn_customization_settings',
            array(
                'label_for' => 'ccn_primary_color',
                'description' => __('Main accent color (e.g., buttons, links, active tabs).', 'city-club-network')
            )
        );

        // Secondary Color Field (Example - might be used for backgrounds)
        register_setting($option_group, 'ccn_secondary_color', array('sanitize_callback' => 'sanitize_hex_color', 'default' => '#f0f4f8')); // Default light gray/blue
        add_settings_field(
            'ccn_secondary_color',
            __('Secondary Color', 'city-club-network'),
            array($this, 'render_color_field'),
            $option_group,
            'ccn_customization_settings',
            array(
                'label_for' => 'ccn_secondary_color',
                'description' => __('Secondary color (e.g., backgrounds, highlights).', 'city-club-network')
            )
        );

        // Text Color Field
        register_setting($option_group, 'ccn_text_color', array('sanitize_callback' => 'sanitize_hex_color', 'default' => '#4a5568')); // Default gray
        add_settings_field(
            'ccn_text_color',
            __('Default Text Color', 'city-club-network'),
            array($this, 'render_color_field'),
            $option_group,
            'ccn_customization_settings',
            array(
                'label_for' => 'ccn_text_color',
                'description' => __('Default text color for descriptions and content.', 'city-club-network')
            )
        );

        // Button Text Color Field
        register_setting($option_group, 'ccn_button_text_color', array('sanitize_callback' => 'sanitize_hex_color', 'default' => '#ffffff')); // Default white
        add_settings_field(
            'ccn_button_text_color',
            __('Button Text Color', 'city-club-network'),
            array($this, 'render_color_field'),
            $option_group,
            'ccn_customization_settings',
            array(
                'label_for' => 'ccn_button_text_color',
                'description' => __('Text color for primary buttons.', 'city-club-network')
            )
        );


        // --- Text Customization Section ---
         add_settings_section(
            'ccn_text_settings',
            __('Button Text Customization', 'city-club-network'),
            array($this, 'render_text_settings_section'),
            $option_group // Page slug
        );

        // Book a Tour Button Text
        register_setting($option_group, 'ccn_book_tour_text', array('sanitize_callback' => 'sanitize_text_field', 'default' => __('Book a Tour', 'city-club-network')));
        add_settings_field(
            'ccn_book_tour_text',
            __('"Book a Tour" Text', 'city-club-network'),
            array($this, 'render_text_field'),
            $option_group,
            'ccn_text_settings',
            array(
                'label_for' => 'ccn_book_tour_text',
                'description' => __('Text for the "Book a Tour" button in the modal.', 'city-club-network')
            )
        );

        // Get Directions Button Text
        register_setting($option_group, 'ccn_get_directions_text', array('sanitize_callback' => 'sanitize_text_field', 'default' => __('Get Directions', 'city-club-network')));
        add_settings_field(
            'ccn_get_directions_text',
            __('"Get Directions" Text', 'city-club-network'),
            array($this, 'render_text_field'),
            $option_group,
            'ccn_text_settings',
            array(
                'label_for' => 'ccn_get_directions_text',
                'description' => __('Text for the "Get Directions" button on cards and modal.', 'city-club-network')
            )
        );

        // Choose Plan Button Text
        register_setting($option_group, 'ccn_choose_plan_text', array('sanitize_callback' => 'sanitize_text_field', 'default' => __('Choose Plan', 'city-club-network')));
        add_settings_field(
            'ccn_choose_plan_text',
            __('"Choose Plan" Text', 'city-club-network'),
            array($this, 'render_text_field'),
            $option_group,
            'ccn_text_settings',
            array(
                'label_for' => 'ccn_choose_plan_text',
                'description' => __('Text for the "Choose Plan" button on membership cards in the modal.', 'city-club-network')
            )
        );
    }

    /**
     * Render the description for the General Settings section.
     *
     * @since    1.1.0
     */
    public function render_general_settings_section() {
        echo '<p>' . __('Configure general settings for the City Club Network plugin.', 'city-club-network') . '</p>';
    }

    /**
     * Render the description for the Customization Settings section.
     *
     * @since    1.1.0
     */
    public function render_customization_settings_section() {
        echo '<p>' . __('Customize the appearance of the club listings and details. Note: Applying these colors may require theme CSS adjustments.', 'city-club-network') . '</p>';
    }

     /**
     * Render the description for the Text Settings section.
     *
     * @since    1.1.0
     */
    public function render_text_settings_section() {
        echo '<p>' . __('Customize the text displayed on various buttons in the modal and cards.', 'city-club-network') . '</p>';
    }

    /**
     * Render a standard text input field.
     * Used for API Key and Button Texts.
     *
     * @since    1.1.0
     * @param array $args Field arguments passed from add_settings_field.
     */
    public function render_text_field($args) {
        $option_name = $args['label_for'];
        $option_value = get_option($option_name, ''); // Get saved value or default
        $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';

        printf(
            '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s" />',
            esc_attr($option_name),
            esc_attr($option_value),
            esc_attr($placeholder)
        );

        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', wp_kses_post($args['description'])); // Allow basic HTML in description
        }
    }

    /**
     * Render a color picker field.
     *
     * @since    1.1.0
     * @param array $args Field arguments.
     */
    public function render_color_field($args) {
        $option_name = $args['label_for'];
        $default_color = isset($args['default']) ? $args['default'] : '#000000';
        $option_value = get_option($option_name, $default_color);

        printf(
            '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="ccn-color-picker" data-default-color="%3$s" />',
            esc_attr($option_name),
            esc_attr($option_value),
            esc_attr($default_color)
        );

        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', wp_kses_post($args['description']));
        }
    }


    /**
     * Render the main settings page container and form.
     *
     * @since    1.1.0
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap ccn-settings-page">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <form action="options.php" method="post">
                <?php
                // Output security fields for the registered setting group
                settings_fields('city_club_network_settings');

                // Output the settings sections and their fields
                do_settings_sections('city_club_network_settings'); // Match the page slug used in add_settings_section

                // Output the save button
                submit_button(__('Save Settings', 'city-club-network'));
                ?>
            </form>
        </div>
        <?php
    }
}
