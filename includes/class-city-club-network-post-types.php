<?php
/**
 * The class responsible for defining the custom post types and taxonomies.
 *
 * @since      1.0.0
 * @package    City_Club_Network
 */

class City_Club_Network_Post_Types {

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

        $this->register_hooks();
    }

    /**
     * Register the hooks for the custom post types and taxonomies.
     *
     * @since    1.0.0
     */
    private function register_hooks() {
        add_action('init', array($this, 'register_city_club_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_city_club_meta_boxes'));
        add_action('save_post_city_club', array($this, 'save_city_club_meta'));

        // Hooks for adding custom fields to 'facility' taxonomy
        add_action('facility_add_form_fields', array($this, 'add_facility_taxonomy_fields'));
        add_action('facility_edit_form_fields', array($this, 'edit_facility_taxonomy_fields'), 10, 2);
        add_action('created_facility', array($this, 'save_facility_taxonomy_meta'), 10, 2);
        add_action('edited_facility', array($this, 'save_facility_taxonomy_meta'), 10, 2);

        // Enqueue media uploader script for admin
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

     /**
     * Enqueue scripts and styles for the admin area, specifically the media uploader.
     *
     * @since 1.2.0
     * @param string $hook The current admin page hook.
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;

        // Only enqueue on the City Club post type edit screen
        if (('post.php' == $hook || 'post-new.php' == $hook) && 'city_club' === $post_type) {
            // Enqueue WordPress media scripts
            wp_enqueue_media();

            // Optionally, enqueue a custom script to handle the media uploader button click
            // This is now handled within the main admin JS file (city-club-network-admin.js)
            // wp_enqueue_script(
            //     $this->plugin_name . '-media-uploader',
            //     CCN_PLUGIN_URL . 'admin/js/ccn-media-uploader.js', // Create this file if needed
            //     array('jquery'),
            //     $this->version,
            //     true
            // );
        }
    }


    /**
     * Register the City Club custom post type.
     *
     * @since    1.0.0
     */
    public function register_city_club_post_type() {
        $labels = array(
            'name'                  => _x('City Clubs', 'Post Type General Name', 'city-club-network'),
            'singular_name'         => _x('City Club', 'Post Type Singular Name', 'city-club-network'),
            'menu_name'             => __('City Clubs', 'city-club-network'),
            'name_admin_bar'        => __('City Club', 'city-club-network'),
            'archives'              => __('Club Archives', 'city-club-network'),
            'attributes'            => __('Club Attributes', 'city-club-network'),
            'parent_item_colon'     => __('Parent Club:', 'city-club-network'),
            'all_items'             => __('All Clubs', 'city-club-network'),
            'add_new_item'          => __('Add New Club', 'city-club-network'),
            'add_new'               => __('Add New', 'city-club-network'),
            'new_item'              => __('New Club', 'city-club-network'),
            'edit_item'             => __('Edit Club', 'city-club-network'),
            'update_item'           => __('Update Club', 'city-club-network'),
            'view_item'             => __('View Club', 'city-club-network'),
            'view_items'            => __('View Clubs', 'city-club-network'),
            'search_items'          => __('Search Club', 'city-club-network'),
            'not_found'             => __('Not found', 'city-club-network'),
            'not_found_in_trash'    => __('Not found in Trash', 'city-club-network'),
            'featured_image'        => __('Club Image', 'city-club-network'),
            'set_featured_image'    => __('Set club image', 'city-club-network'),
            'remove_featured_image' => __('Remove club image', 'city-club-network'),
            'use_featured_image'    => __('Use as club image', 'city-club-network'),
            'insert_into_item'      => __('Insert into club', 'city-club-network'),
            'uploaded_to_this_item' => __('Uploaded to this club', 'city-club-network'),
            'items_list'            => __('Clubs list', 'city-club-network'),
            'items_list_navigation' => __('Clubs list navigation', 'city-club-network'),
            'filter_items_list'     => __('Filter clubs list', 'city-club-network'),
        );

        $args = array(
            'label'                 => __('City Club', 'city-club-network'),
            'description'           => __('Premium fitness clubs across Morocco', 'city-club-network'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-star-filled', // Updated icon for premium status
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
            'rest_base'             => 'city-clubs',
        );

        register_post_type('city_club', $args);
    }

    /**
     * Register taxonomies for the City Club post type.
     *
     * @since    1.0.0
     */
    public function register_taxonomies() {
        // Register City taxonomy (No changes needed here)
        $city_labels = array(
            'name'                       => _x('Cities', 'Taxonomy General Name', 'city-club-network'),
            'singular_name'              => _x('City', 'Taxonomy Singular Name', 'city-club-network'),
            'menu_name'                  => __('Cities', 'city-club-network'),
            'all_items'                  => __('All Cities', 'city-club-network'),
            'parent_item'                => __('Parent City', 'city-club-network'),
            'parent_item_colon'          => __('Parent City:', 'city-club-network'),
            'new_item_name'              => __('New City Name', 'city-club-network'),
            'add_new_item'               => __('Add New City', 'city-club-network'),
            'edit_item'                  => __('Edit City', 'city-club-network'),
            'update_item'                => __('Update City', 'city-club-network'),
            'view_item'                  => __('View City', 'city-club-network'),
            'separate_items_with_commas' => __('Separate cities with commas', 'city-club-network'),
            'add_or_remove_items'        => __('Add or remove cities', 'city-club-network'),
            'choose_from_most_used'      => __('Choose from the most used', 'city-club-network'),
            'popular_items'              => __('Popular Cities', 'city-club-network'),
            'search_items'               => __('Search Cities', 'city-club-network'),
            'not_found'                  => __('Not Found', 'city-club-network'),
            'no_terms'                   => __('No cities', 'city-club-network'),
            'items_list'                 => __('Cities list', 'city-club-network'),
            'items_list_navigation'      => __('Cities list navigation', 'city-club-network'),
        );
        $city_args = array(
            'labels'                     => $city_labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        );
        register_taxonomy('city', array('city_club'), $city_args);

        // Register Facilities taxonomy with premium facilities
        $facility_labels = array(
            'name'                       => _x('Premium Facilities', 'Taxonomy General Name', 'city-club-network'),
            'singular_name'              => _x('Premium Facility', 'Taxonomy Singular Name', 'city-club-network'),
            'menu_name'                  => __('Premium Facilities', 'city-club-network'),
            'all_items'                  => __('All Premium Facilities', 'city-club-network'),
            'parent_item'                => __('Parent Facility', 'city-club-network'),
            'parent_item_colon'          => __('Parent Facility:', 'city-club-network'),
            'new_item_name'              => __('New Premium Facility', 'city-club-network'),
            'add_new_item'               => __('Add New Premium Facility', 'city-club-network'),
            'edit_item'                  => __('Edit Premium Facility', 'city-club-network'),
            'update_item'                => __('Update Premium Facility', 'city-club-network'),
            'view_item'                  => __('View Premium Facility', 'city-club-network'),
            'separate_items_with_commas' => __('Separate premium facilities with commas', 'city-club-network'),
            'add_or_remove_items'        => __('Add or remove premium facilities', 'city-club-network'),
            'choose_from_most_used'      => __('Choose from the most used premium facilities', 'city-club-network'),
            'popular_items'              => __('Popular Premium Facilities', 'city-club-network'),
            'search_items'               => __('Search Premium Facilities', 'city-club-network'),
            'not_found'                  => __('No Premium Facilities Found', 'city-club-network'),
            'no_terms'                   => __('No premium facilities', 'city-club-network'),
            'items_list'                 => __('Premium Facilities list', 'city-club-network'),
            'items_list_navigation'      => __('Premium Facilities list navigation', 'city-club-network'),
        );
        $facility_args = array(
            'labels'                     => $facility_labels,
            'hierarchical'               => false, // Facilities are typically tags, not hierarchical
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'meta_box_cb'                => 'post_tags_meta_box', // Use checkbox UI
        );
        register_taxonomy('facility', array('city_club'), $facility_args);

        // Register Membership taxonomy (No changes needed here)
        $membership_labels = array(
            'name'                       => _x('Membership Categories', 'Taxonomy General Name', 'city-club-network'), // Renamed for clarity
            'singular_name'              => _x('Membership Category', 'Taxonomy Singular Name', 'city-club-network'),
            'menu_name'                  => __('Membership Categories', 'city-club-network'),
            'all_items'                  => __('All Membership Categories', 'city-club-network'),
            'parent_item'                => __('Parent Membership Category', 'city-club-network'),
            'parent_item_colon'          => __('Parent Membership Category:', 'city-club-network'),
            'new_item_name'              => __('New Membership Category Name', 'city-club-network'),
            'add_new_item'               => __('Add New Membership Category', 'city-club-network'),
            'edit_item'                  => __('Edit Membership Category', 'city-club-network'),
            'update_item'                => __('Update Membership Category', 'city-club-network'),
            'view_item'                  => __('View Membership Category', 'city-club-network'),
            'separate_items_with_commas' => __('Separate categories with commas', 'city-club-network'),
            'add_or_remove_items'        => __('Add or remove categories', 'city-club-network'),
            'choose_from_most_used'      => __('Choose from the most used', 'city-club-network'),
            'popular_items'              => __('Popular Membership Categories', 'city-club-network'),
            'search_items'               => __('Search Membership Categories', 'city-club-network'),
            'not_found'                  => __('Not Found', 'city-club-network'),
            'no_terms'                   => __('No membership categories', 'city-club-network'),
            'items_list'                 => __('Membership Categories list', 'city-club-network'),
            'items_list_navigation'      => __('Membership Categories list navigation', 'city-club-network'),
        );
        $membership_args = array(
            'labels'                     => $membership_labels,
            'hierarchical'               => true, // Can be hierarchical if needed (e.g., Gold > Standard)
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
        );
        register_taxonomy('membership_category', array('city_club'), $membership_args); // Renamed taxonomy slug
    }

    /**
     * Add meta boxes for the City Club post type.
     *
     * @since    1.0.0
     */
    public function add_city_club_meta_boxes() {
        add_meta_box(
            'city_club_details',
            __('Club Details & Overview', 'city-club-network'), // Renamed
            array($this, 'render_city_club_details_meta_box'),
            'city_club',
            'normal',
            'high'
        );

        add_meta_box(
            'city_club_location',
            __('Club Location', 'city-club-network'),
            array($this, 'render_city_club_location_meta_box'),
            'city_club',
            'normal',
            'high'
        );

        add_meta_box(
            'city_club_classes',
            __('Club Classes & Schedule', 'city-club-network'), // Updated title
            array($this, 'render_city_club_classes_meta_box'),
            'city_club',
            'normal',
            'default' // Lower priority
        );

        add_meta_box(
            'city_club_memberships',
            __('Club Membership Plans', 'city-club-network'),
            array($this, 'render_city_club_memberships_meta_box'),
            'city_club',
            'normal',
            'default' // Lower priority
        );
    }

    /**
     * Render the Club Details meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_city_club_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('city_club_details_meta_box', 'city_club_details_meta_box_nonce');

        // Get the saved values
        $address = get_post_meta($post->ID, '_club_address', true);
        $hours_mf = get_post_meta($post->ID, '_club_hours_mf', true) ?: '6:00 AM - 10:00 PM';
        $hours_sat = get_post_meta($post->ID, '_club_hours_sat', true) ?: '6:00 AM - 10:00 PM';
        $hours_sun = get_post_meta($post->ID, '_club_hours_sun', true) ?: '6:00 AM - 10:00 PM';
        $rating = get_post_meta($post->ID, '_club_rating', true) ?: '4.8';
        $reviews_count = get_post_meta($post->ID, '_club_reviews_count', true) ?: '120';
        $is_premium = get_post_meta($post->ID, '_club_is_premium', true) ?: '1';
        $contact_phone = get_post_meta($post->ID, '_club_contact_phone', true);
        $contact_email = get_post_meta($post->ID, '_club_contact_email', true);
        $contact_website = get_post_meta($post->ID, '_club_contact_website', true);
        $book_tour_url = get_post_meta($post->ID, '_club_book_tour_url', true);
        
        // Get premium facilities
        $facilities = array('Pool', 'Gym', 'Spa', 'Tennis');
        $selected_facilities = wp_get_object_terms($post->ID, 'facility', array('fields' => 'names'));

        ?>
        <div class="city-club-meta-box">
            <h4><?php _e('Address & Status', 'city-club-network'); ?></h4>
            <p>
                <label for="club_address"><?php _e('Club Address', 'city-club-network'); ?>:</label>
                <input type="text" id="club_address" name="club_address" value="<?php echo esc_attr($address); ?>" class="widefat">
            </p>
             <p>
                <label for="club_is_premium">
                    <input type="checkbox" id="club_is_premium" name="club_is_premium" value="1" <?php checked($is_premium, '1'); ?>>
                    <?php _e('Premium Club', 'city-club-network'); ?>
                </label>
            </p>

            <hr>
            <h4><?php _e('Opening Hours', 'city-club-network'); ?></h4>
             <p>
                <label for="club_hours_mf"><?php _e('Monday - Friday', 'city-club-network'); ?>:</label>
                <input type="text" id="club_hours_mf" name="club_hours_mf" value="<?php echo esc_attr($hours_mf); ?>" class="widefat" placeholder="<?php _e('e.g. 6:00 AM - 10:00 PM', 'city-club-network'); ?>">
            </p>
             <p>
                <label for="club_hours_sat"><?php _e('Saturday', 'city-club-network'); ?>:</label>
                <input type="text" id="club_hours_sat" name="club_hours_sat" value="<?php echo esc_attr($hours_sat); ?>" class="widefat" placeholder="<?php _e('e.g. 8:00 AM - 8:00 PM', 'city-club-network'); ?>">
            </p>
             <p>
                <label for="club_hours_sun"><?php _e('Sunday', 'city-club-network'); ?>:</label>
                <input type="text" id="club_hours_sun" name="club_hours_sun" value="<?php echo esc_attr($hours_sun); ?>" class="widefat" placeholder="<?php _e('e.g. 8:00 AM - 6:00 PM', 'city-club-network'); ?>">
            </p>

            <hr>
            <h4><?php _e('Rating & Reviews', 'city-club-network'); ?></h4>
            <p>
                <label for="club_rating"><?php _e('Rating (0-5)', 'city-club-network'); ?>:</label>
                <input type="number" id="club_rating" name="club_rating" value="<?php echo esc_attr($rating); ?>" min="0" max="5" step="0.1" style="width: 80px;">
            </p>
            <p>
                <label for="club_reviews_count"><?php _e('Number of Reviews', 'city-club-network'); ?>:</label>
                <input type="number" id="club_reviews_count" name="club_reviews_count" value="<?php echo esc_attr($reviews_count); ?>" min="0" style="width: 80px;">
            </p>

            <hr>
            <h4><?php _e('Contact Information', 'city-club-network'); ?></h4>
             <p>
                <label for="club_contact_phone"><?php _e('Contact Phone', 'city-club-network'); ?>:</label>
                <input type="text" id="club_contact_phone" name="club_contact_phone" value="<?php echo esc_attr($contact_phone); ?>" class="widefat" placeholder="<?php _e('+212 522 123 456', 'city-club-network'); ?>">
            </p>
             <p>
                <label for="club_contact_email"><?php _e('Contact Email', 'city-club-network'); ?>:</label>
                <input type="email" id="club_contact_email" name="club_contact_email" value="<?php echo esc_attr($contact_email); ?>" class="widefat" placeholder="<?php _e('casablanca@cityclub.ma', 'city-club-network'); ?>">
            </p>
             <p>
                <label for="club_contact_website"><?php _e('Website URL', 'city-club-network'); ?>:</label>
                <input type="url" id="club_contact_website" name="club_contact_website" value="<?php echo esc_attr($contact_website); ?>" class="widefat" placeholder="<?php _e('https://www.cityclub.ma', 'city-club-network'); ?>">
            </p>

            <hr>
            <h4><?php _e('Action Buttons', 'city-club-network'); ?></h4>
             <p>
                <label for="club_book_tour_url"><?php _e('"Book a Tour" Button URL', 'city-club-network'); ?>:</label>
                <input type="url" id="club_book_tour_url" name="club_book_tour_url" value="<?php echo esc_attr($book_tour_url); ?>" class="widefat" placeholder="<?php _e('Enter the URL for the booking page', 'city-club-network'); ?>">
            </p>
             <p class="description"><?php _e('The "Get Directions" button uses the Latitude/Longitude from the Club Location meta box.', 'city-club-network'); ?></p>

        </div>
        <?php
    }

    /**
     * Render the Club Location meta box.
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_city_club_location_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('city_club_location_meta_box', 'city_club_location_meta_box_nonce');

        // Get the saved values
        $latitude = get_post_meta($post->ID, '_club_latitude', true);
        $longitude = get_post_meta($post->ID, '_club_longitude', true);

        ?>
        <div class="city-club-meta-box">
            <p>
                <label for="club_latitude"><?php _e('Latitude', 'city-club-network'); ?>:</label>
                <input type="text" id="club_latitude" name="club_latitude" value="<?php echo esc_attr($latitude); ?>" class="widefat">
            </p>
            <p>
                <label for="club_longitude"><?php _e('Longitude', 'city-club-network'); ?>:</label>
                <input type="text" id="club_longitude" name="club_longitude" value="<?php echo esc_attr($longitude); ?>" class="widefat">
            </p>
            <div id="club-location-map" style="height: 300px; margin-top: 10px; background-color: #eee;">
                <!-- Map will be loaded here via JavaScript if API key is present -->
                 <?php if (!get_option('ccn_google_maps_api_key')): ?>
                 <p style="padding: 10px; text-align: center;"><?php _e('Google Maps API Key not configured in settings. Map preview disabled.', 'city-club-network'); ?></p>
                 <?php endif; ?>
            </div>
            <p class="description"><?php _e('Enter the coordinates manually or click/drag marker on the map. Map interaction requires a valid Google Maps API Key in settings.', 'city-club-network'); ?></p>
        </div>
        <?php
    }

     /**
     * Render the Club Classes meta box.
     *
     * @since    1.1.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_city_club_classes_meta_box($post) {
        wp_nonce_field('city_club_classes_meta_box', 'city_club_classes_meta_box_nonce');
        $classes_data = get_post_meta($post->ID, '_club_classes_data', true);
        $pdf_url = get_post_meta($post->ID, '_club_class_schedule_pdf', true);
        ?>
        <div class="city-club-meta-box">
            <h4><?php _e('Class List', 'city-club-network'); ?></h4>
            <p>
                <label for="club_classes_data"><?php _e('Classes Data', 'city-club-network'); ?>:</label>
                <textarea id="club_classes_data" name="club_classes_data" class="widefat" rows="8"><?php echo esc_textarea($classes_data); ?></textarea>
            </p>
            <p class="description">
                <?php _e('Enter one class per line using the following format (separated by | ):', 'city-club-network'); ?><br>
                <code><?php _e('ClassName|DaysAndTime|Level|InstructorName', 'city-club-network'); ?></code><br>
                <?php _e('Example:', 'city-club-network'); ?> <code><?php _e('Yoga Flow|Tue, Thu - 6:00 PM|all levels|Leila Mansouri', 'city-club-network'); ?></code><br>
                <?php _e('Example:', 'city-club-network'); ?> <code><?php _e('Spinning|Mon, Wed - 6:30 PM|advanced|Karim Tazi', 'city-club-network'); ?></code>
            </p>

            <hr style="margin: 20px 0;">

            <h4><?php _e('Class Schedule PDF', 'city-club-network'); ?></h4>
            <div class="ccn-pdf-uploader-container">
                <label for="club_class_schedule_pdf"><?php _e('Schedule PDF URL', 'city-club-network'); ?>:</label>
                <input type="text" id="club_class_schedule_pdf" name="club_class_schedule_pdf" value="<?php echo esc_url($pdf_url); ?>" class="widefat ccn-pdf-url-field">
                <button type="button" class="button ccn-upload-pdf-button"><?php _e('Upload/Select PDF', 'city-club-network'); ?></button>
                <button type="button" class="button ccn-remove-pdf-button" style="<?php echo empty($pdf_url) ? 'display:none;' : ''; ?>"><?php _e('Remove PDF', 'city-club-network'); ?></button>
            </div>
             <p class="description">
                <?php _e('Upload or select a PDF file containing the full class schedule. This will enable "View PDF" and "Download PDF" buttons in the modal.', 'city-club-network'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render the Club Membership Plans meta box.
     *
     * @since    1.1.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_city_club_memberships_meta_box($post) {
        wp_nonce_field('city_club_memberships_meta_box', 'city_club_memberships_meta_box_nonce');
        $memberships_data = get_post_meta($post->ID, '_club_memberships_data', true);
        ?>
        <div class="city-club-meta-box">
            <p>
                <label for="club_memberships_data"><?php _e('Membership Plans Data', 'city-club-network'); ?>:</label>
                <textarea id="club_memberships_data" name="club_memberships_data" class="widefat" rows="10"><?php echo esc_textarea($memberships_data); ?></textarea>
            </p>
            <p class="description">
                <?php _e('Enter one plan per line using the following format (separated by | ):', 'city-club-network'); ?><br>
                <code><?php _e('PlanName|Price|Frequency|Features(comma,separated)|IsPopular(1 or 0)|ChoosePlanURL', 'city-club-network'); ?></code><br>
                <?php _e('Example:', 'city-club-network'); ?> <code><?php _e('Standard|299 MAD|per month|Access to gym equipment,2 group classes per week,Locker access|0|https://example.com/standard', 'city-club-network'); ?></code><br>
                <?php _e('Example:', 'city-club-network'); ?> <code><?php _e('Premium|499 MAD|per month|Unlimited access to all facilities,Unlimited group classes,1 personal training session monthly|1|https://example.com/premium', 'city-club-network'); ?></code>
            </p>
        </div>
        <?php
    }


    /**
     * Save the meta box data.
     *
     * @since    1.0.0
     * @param    int    $post_id    The ID of the post being saved.
     */
    public function save_city_club_meta($post_id) {

        // Check if autosaving
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (isset($_POST['post_type']) && 'city_club' == $_POST['post_type']) {
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }
        } else {
             return; // Not our post type
        }

        // Verify nonce for Club Details
        if (isset($_POST['city_club_details_meta_box_nonce']) && wp_verify_nonce($_POST['city_club_details_meta_box_nonce'], 'city_club_details_meta_box')) {
            // Save club details
            $fields_to_save = [
                '_club_address' => 'club_address',
                '_club_hours_mf' => 'club_hours_mf',
                '_club_hours_sat' => 'club_hours_sat',
                '_club_hours_sun' => 'club_hours_sun',
                '_club_rating' => 'club_rating', // Sanitize as float/text?
                '_club_contact_phone' => 'club_contact_phone',
                '_club_contact_email' => 'club_contact_email', // Sanitize as email
                '_club_contact_website' => 'club_contact_website', // Sanitize as URL
                '_club_book_tour_url' => 'club_book_tour_url', // Sanitize as URL
            ];

            foreach ($fields_to_save as $meta_key => $post_field_name) {
                if (isset($_POST[$post_field_name])) {
                    $value = $_POST[$post_field_name];
                    // Basic sanitization, specific sanitization can be added
                    if ($meta_key === '_club_contact_email') {
                        $sanitized_value = sanitize_email($value);
                    } elseif ($meta_key === '_club_contact_website' || $meta_key === '_club_book_tour_url') {
                         $sanitized_value = esc_url_raw($value);
                    } elseif ($meta_key === '_club_rating') {
                         $sanitized_value = floatval($value); // Allow decimals
                         $sanitized_value = max(0, min(5, $sanitized_value)); // Clamp between 0 and 5
                    } else {
                        $sanitized_value = sanitize_text_field($value);
                    }
                    update_post_meta($post_id, $meta_key, $sanitized_value);
                }
            }

            // Integer field
            if (isset($_POST['club_reviews_count'])) {
                update_post_meta($post_id, '_club_reviews_count', absint($_POST['club_reviews_count']));
            }

            // Checkbox field
            $is_premium = isset($_POST['club_is_premium']) ? '1' : '0';
            update_post_meta($post_id, '_club_is_premium', $is_premium);
        }


        // Verify nonce for Club Location
        if (isset($_POST['city_club_location_meta_box_nonce']) && wp_verify_nonce($_POST['city_club_location_meta_box_nonce'], 'city_club_location_meta_box')) {
            // Save club location
            if (isset($_POST['club_latitude'])) {
                update_post_meta($post_id, '_club_latitude', sanitize_text_field($_POST['club_latitude']));
            }
            if (isset($_POST['club_longitude'])) {
                update_post_meta($post_id, '_club_longitude', sanitize_text_field($_POST['club_longitude']));
            }
        }

        // Verify nonce for Club Classes
        if (isset($_POST['city_club_classes_meta_box_nonce']) && wp_verify_nonce($_POST['city_club_classes_meta_box_nonce'], 'city_club_classes_meta_box')) {
            if (isset($_POST['club_classes_data'])) {
                // Sanitize each line? For now, just sanitize the whole block.
                update_post_meta($post_id, '_club_classes_data', sanitize_textarea_field($_POST['club_classes_data']));
            }
             // Save PDF URL
            if (isset($_POST['club_class_schedule_pdf'])) {
                update_post_meta($post_id, '_club_class_schedule_pdf', esc_url_raw($_POST['club_class_schedule_pdf']));
            }
        }

        // Verify nonce for Club Memberships
        if (isset($_POST['city_club_memberships_meta_box_nonce']) && wp_verify_nonce($_POST['city_club_memberships_meta_box_nonce'], 'city_club_memberships_meta_box')) {
             if (isset($_POST['club_memberships_data'])) {
                // Sanitize each line? For now, just sanitize the whole block.
                update_post_meta($post_id, '_club_memberships_data', sanitize_textarea_field($_POST['club_memberships_data']));
            }
        }
    }

    // --- Taxonomy Meta Fields ---

    /**
     * Add custom fields to the 'facility' taxonomy add form.
     *
     * @since 1.1.0
     * @param string $taxonomy The taxonomy slug.
     */
    public function add_facility_taxonomy_fields($taxonomy) {
        ?>
        <div class="form-field term-name-wrap">
            <label for="tag-name"><?php _e('Name', 'city-club-network'); ?></label>
            <input type="text" name="tag-name" id="tag-name" value="" class="widefat" onchange="checkExistingFacility(this.value)"/>
            <div id="facility-exists-notice" style="color: red; display: none;">
                <?php _e('This facility already exists!', 'city-club-network'); ?>
            </div>
            <script>
            function checkExistingFacility(value) {
                var existingFacilities = <?php echo json_encode(get_terms(array('taxonomy' => 'facility', 'hide_empty' => false, 'fields' => 'names'))); ?>;
                var notice = document.getElementById('facility-exists-notice');
                if (existingFacilities.includes(value)) {
                    notice.style.display = 'block';
                } else {
                    notice.style.display = 'none';
                }
            }
            </script>
        </div>
        <div class="form-field term-icon-url-wrap">
            <label for="facility_icon_url"><?php _e('Icon URL', 'city-club-network'); ?></label>
            <input type="url" name="facility_icon_url" id="facility_icon_url" value="" class="widefat"/>
            <p><?php _e('Enter the full URL for the facility icon (e.g., SVG or PNG).', 'city-club-network'); ?></p>
        </div>
        <div class="form-field term-description-wrap">
             <label for="facility_description"><?php _e('Facility Description', 'city-club-network'); ?></label>
             <textarea name="facility_description" id="facility_description" rows="3" class="widefat"></textarea>
             <p><?php _e('Short description of the facility.', 'city-club-network'); ?></p>
        </div>
        <?php
    }

    /**
     * Add custom fields to the 'facility' taxonomy edit form.
     *
     * @since 1.1.0
     * @param WP_Term $term     Current taxonomy term object.
     * @param string  $taxonomy Current taxonomy slug.
     */
    public function edit_facility_taxonomy_fields($term, $taxonomy) {
        $icon_url = get_term_meta($term->term_id, 'facility_icon_url', true);
        $description = get_term_meta($term->term_id, 'facility_description', true);
        ?>
        <tr class="form-field term-icon-url-wrap">
            <th scope="row"><label for="facility_icon_url"><?php _e('Icon URL', 'city-club-network'); ?></label></th>
            <td>
                <input type="url" name="facility_icon_url" id="facility_icon_url" value="<?php echo esc_url($icon_url); ?>" class="widefat"/>
                <p class="description"><?php _e('Enter the full URL for the facility icon (e.g., SVG or PNG).', 'city-club-network'); ?></p>
            </td>
        </tr>
        <tr class="form-field term-description-wrap">
             <th scope="row"><label for="facility_description"><?php _e('Facility Description', 'city-club-network'); ?></label></th>
             <td>
                 <textarea name="facility_description" id="facility_description" rows="3" class="widefat"><?php echo esc_textarea($description); ?></textarea>
                 <p class="description"><?php _e('Short description of the facility.', 'city-club-network'); ?></p>
             </td>
        </tr>
        <?php
    }

    /**
     * Save custom meta fields for the 'facility' taxonomy.
     *
     * @since 1.1.0
     * @param int    $term_id Term ID.
     * @param int    $tt_id   Term taxonomy ID.
     */
    public function save_facility_taxonomy_meta($term_id, $tt_id) {
        if (isset($_POST['facility_icon_url'])) {
            $icon_url = esc_url_raw($_POST['facility_icon_url']);
            update_term_meta($term_id, 'facility_icon_url', $icon_url);
        }
         if (isset($_POST['facility_description'])) {
            $description = sanitize_textarea_field($_POST['facility_description']);
            update_term_meta($term_id, 'facility_description', $description);
        }
    }

} // End class
