<?php
/**
 * Plugin Name:       Automation for WPCourseware
 * Description:       A plugin to automatically enroll and remove users from courses using an automator like flowmattic
 * Tested up to:      6.8.1
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Version:           1.3.1
 * Author:            reallyusefulplugins.com
 * Author URI:        https://reallyusefulplugins.com
 * License:           GPL2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       automation-for-wpcourseware
 * Website:           https://reallyusefulplugins.com
 */

if ( ! defined('ABSPATH') ) {
    exit; // Prevent direct access
}

// Define plugin constants
define('rup_wpc_auto_automation_for__wpcourseware_VERSION', '1.3.1');
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

// ──────────────────────────────────────────────────────────────────────────
//  Updater bootstrap (plugins_loaded priority 1):
// ──────────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', function() {
    // 1) Load our universal drop-in. Because that file begins with "namespace UUPD\V1;",
    //    both the class and the helper live under UUPD\V1.
    require_once __DIR__ . '/includes/updater.php';

    // 2) Build a single $updater_config array:
    $updater_config = [
        'plugin_file' => plugin_basename( __FILE__ ),             // e.g. "simply-static-export-notify/simply-static-export-notify.php"
        'slug'        => 'automation-for-wpcourseware',           // must match your updater‐server slug
        'name'        => 'Automation for WPCourseware',         // human‐readable plugin name
        'version'     => rup_wpc_auto_automation_for__wpcourseware_VERSION, // same as the VERSION constant above
        'key'         => 'CeW5jUv66xCMVZd83QTema',                 // your secret key for private updater
        'server'      => 'https://raw.githubusercontent.com/stingray82/WPCourseware-Automation/main/uupd/index.json',
    ];

    // 3) Call the helper in the UUPD\V1 namespace:
    \RUP\Updater\Updater_V1::register( $updater_config );
}, 1 );