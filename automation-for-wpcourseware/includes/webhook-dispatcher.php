<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hook listeners for FlowMattic-compatible events to send outgoing webhooks.
 * Triggers: user-enrolled, unit-completed, module-completed, course-completed
 */

add_action('flowmattic_trigger_wpcw_enroll_user', 'wpc_automation_send_webhooks_for_event');
add_action('flowmattic_trigger_wpcw_user_completed_unit', 'wpc_automation_send_webhooks_for_event');
add_action('flowmattic_trigger_wpcw_user_completed_module', 'wpc_automation_send_webhooks_for_event');
add_action('flowmattic_trigger_wpcw_user_completed_course', 'wpc_automation_send_webhooks_for_event');

function wpc_automation_send_webhooks_for_event($data) {
    $trigger_map = [
        'flowmattic_trigger_wpcw_enroll_user'         => 'user-enrolled',
        'flowmattic_trigger_wpcw_user_completed_unit' => 'unit-completed',
        'flowmattic_trigger_wpcw_user_completed_module' => 'module-completed',
        'flowmattic_trigger_wpcw_user_completed_course' => 'course-completed',
    ];

    $trigger = current_filter();
    if ( ! isset($trigger_map[$trigger]) ) return;

    $event = $trigger_map[$trigger];
    $webhooks = get_option('wpc_automation_webhooks', []);

    if ( empty($webhooks) || ! is_array($webhooks) ) return;

    foreach ( $webhooks as $webhook ) {
        if ( isset($webhook['trigger']) && $webhook['trigger'] === $event ) {
            $headers = [
                'Content-Type' => 'application/json',
            ];

            if (!empty($webhook['send_api_key']) && $webhook['send_api_key'] === 'yes') {
                $api_key = get_option('wpc_automation_api_key');
                if ($api_key) {
                    $headers['X-WPC-Automation-Key'] = $api_key;
                }
            }


            $response = wp_remote_post( $webhook['url'], [
                'headers' => $headers,
                'body'    => wp_json_encode($data),
                'timeout' => 10,
            ]);

            // Log failures
            if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) >= 400 ) {
                global $wpdb;
                $table = $wpdb->prefix . 'wpc_automation_logs';

                $wpdb->insert( $table, [
                    'timestamp'     => current_time('mysql'),
                    'trigger'       => $event,
                    'url'           => $webhook['url'],
                    'status_code'   => is_wp_error($response) ? 0 : wp_remote_retrieve_response_code($response),
                    'response_body' => is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response),
                ] );
            }
        }
    }
}
