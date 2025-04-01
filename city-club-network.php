<?php
/**
 * Plugin Name: City Club Network
 * Plugin URI: https://example.com/plugins/city-club-network
 * Description: A WordPress plugin to display fitness clubs across Morocco with grid and map views.
 * Version: 1.0.0
 * Author: WordPress Developer
 * Author URI: https://example.com
 * Text Domain: city-club-network
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('CCN_VERSION', '1.0.0');
define('CCN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CCN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CCN_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once CCN_PLUGIN_DIR . 'includes/class-city-club-network.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function run_city_club_network() {
    $plugin = new City_Club_Network();
    $plugin->run();
}

// Start the plugin
run_city_club_network();
