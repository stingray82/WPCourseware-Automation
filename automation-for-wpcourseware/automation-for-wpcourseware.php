<?php
/**
 * Plugin Name:         Automation for WPCourseware
 * Description:         A plugin to automatically enroll and remove users from courses using an automator like flowmattic
 * Tested up to:        6.8.1
 * Requires at least:   6.5
 * Requires PHP:        8.0
 * Version:             1.2
 * Author:              reallyusefulplugins.com
 * Author URI:          https://reallyusefulplugins.com
 * License:             GPL2
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         automation-for-wpcourseware
 * Website:             https://reallyusefulplugins.com
 */

if ( ! defined('ABSPATH') ) {
    exit; // Prevent direct access
}

// Define plugin constants
define('rup_wpc_auto_automation_for__wpcourseware_VERSION', '1.2');
define('rup_wpc_auto_automation_for__wpcourseware_SLUG', 'automation-for-wpcourseware'); // Replace with your unique slug if needed
define('rup_wpc_auto_automation_for__wpcourseware_MAIN_FILE', __FILE__);
define('rup_wpc_auto_automation_for__wpcourseware_DIR', plugin_dir_path(__FILE__));
define('rup_wpc_auto_automation_for__wpcourseware_URL', plugin_dir_url(__FILE__));

// Include functions (assuming functions.php is in the includes folder)
require_once rup_wpc_auto_automation_for__wpcourseware_DIR . 'includes/install.php';
require_once rup_wpc_auto_automation_for__wpcourseware_DIR . 'includes/functions.php';
require_once rup_wpc_auto_automation_for__wpcourseware_DIR . 'includes/api.php';
require_once rup_wpc_auto_automation_for__wpcourseware_DIR . 'includes/admin.php';
require_once rup_wpc_auto_automation_for__wpcourseware_DIR . 'includes/webhook-dispatcher.php';
require_once rup_wpc_auto_automation_for__wpcourseware_DIR . 'includes/webhook-admin.php';


// Activation hook
function rup_wpc_auto_automation_for__wpcourseware_activate() {
    update_option('rup_wpc_auto_automation_for__wpcourseware_activated', time());
}
register_activation_hook(__FILE__, 'rup_wpc_auto_automation_for__wpcourseware_activate');

// Deactivation hook
function rup_wpc_auto_automation_for__wpcourseware_deactivate() {
    delete_option('rup_wpc_auto_automation_for__wpcourseware_activated');
}
register_deactivation_hook(__FILE__, 'rup_wpc_auto_automation_for__wpcourseware_deactivate');

function rup_wpc_auto_automation_for__wpcourseware_initialize_plugin_update_checker() {
    // Ensure the required function is available.
    if ( ! function_exists( 'get_plugin_data' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    // Get the plugin data from the header.
    $plugin_data = get_plugin_data( __FILE__ );
    
    // Build the constant name prefix using the Text Domain.
    $prefix = 'rup_' . $plugin_data['TextDomain'];

    // Define the constants and their corresponding values.
    $constants = array(
        '_version'         => $plugin_data['Version'],
        '_slug'            => $plugin_data['TextDomain'],
        '_main_file'       => __FILE__,
        '_dir'             => plugin_dir_path( __FILE__ ),
        '_url'             => plugin_dir_url( __FILE__ ),
        '_access_key'      => 'uZRY29gy2CzxmWQ96ti6KB6ER2E3w9Bf7',
        '_server_location' => 'https://updater.reallyusefulplugins.com/u/'
    );

    // Loop through the array and define each constant dynamically.
    foreach ( $constants as $suffix => $value ) {
        if ( ! defined( $prefix . $suffix ) ) {
            define( $prefix . $suffix, $value );
        }
    }

    // Retrieve the dynamic constants for easier reference.
    $version         = constant($prefix . '_version');
    $slug            = constant($prefix . '_slug');
    $main_file       = constant($prefix . '_main_file');
    $dir             = constant($prefix . '_dir');
    $url             = constant($prefix . '_url');
    $access_key      = constant($prefix . '_access_key');
    $server_location = constant($prefix . '_server_location');

    // Build the update server URL dynamically.
    $updateserver = $server_location . '?key=' . $access_key . '&action=get_metadata&slug=' . $slug;

    // Include the update checker.
    require_once $dir . 'plugin-update-checker/plugin-update-checker.php';

    // Use the fully qualified class name to build the update checker.
    $my_plugin_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        $updateserver,
        $main_file,
        $slug
    );
}

add_action( 'init', 'rup_wpc_auto_automation_for__wpcourseware_initialize_plugin_update_checker' );