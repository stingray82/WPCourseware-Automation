<?php
if ( ! defined( 'ABSPATH' ) ) exit;

register_activation_hook( __FILE__, 'wpc_automation_create_log_table' );

function wpc_automation_create_log_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'wpc_automation_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        trigger VARCHAR(100) NOT NULL,
        url TEXT NOT NULL,
        status_code SMALLINT DEFAULT 0,
        response_body TEXT,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Schedule cleanup cron
add_action('init', function() {
    if ( ! wp_next_scheduled( 'wpc_automation_cleanup_logs' ) ) {
        wp_schedule_event( time(), 'daily', 'wpc_automation_cleanup_logs' );
    }
});

add_action('wpc_automation_cleanup_logs', function() {
    global $wpdb;
    $table = $wpdb->prefix . 'wpc_automation_logs';
    $wpdb->query( $wpdb->prepare(
        "DELETE FROM $table WHERE timestamp < NOW() - INTERVAL %d DAY",
        14
    ));
});
