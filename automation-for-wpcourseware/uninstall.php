<?php
if ( ! defined('WP_UNINSTALL_PLUGIN') ) {
    exit;
}

// Remove plugin options
delete_option('rup_wpc_auto_automation_for__wpcourseware_activated');
