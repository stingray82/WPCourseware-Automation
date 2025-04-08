<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_enqueue_scripts', function($hook) {
    if (isset($_GET['page']) && $_GET['page'] === 'wpc-automation-settings') {
        wp_enqueue_script(
            'wpc-automation-webhooks',
            plugins_url('assets/js/webhook-ui.js', dirname(__FILE__)), // ✅ Correct path
            ['jquery'],
            null,
            true
        );

        wp_localize_script('wpc-automation-webhooks', 'wpcAutomationAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('wpc_automation_webhook_nonce')
        ]);
    }
});




add_action('wp_ajax_wpc_automation_add_webhook', function() {
    check_ajax_referer('wpc_automation_webhook_nonce');

    $trigger      = sanitize_text_field($_POST['trigger']);
    $url          = esc_url_raw($_POST['url']);
    $send_api_key = sanitize_text_field($_POST['send_api_key']) === 'true' ? 'yes' : 'no';

    $webhooks = get_option('wpc_automation_webhooks', []);
    $webhooks[] = [
        'trigger'      => $trigger,
        'url'          => $url,
        'send_api_key' => $send_api_key
    ];

    update_option('wpc_automation_webhooks', $webhooks);
    wp_send_json_success($webhooks);
});

add_action('wp_ajax_wpc_automation_delete_webhook', function() {
    check_ajax_referer('wpc_automation_webhook_nonce');
    $index = intval($_POST['index']);
    $webhooks = get_option('wpc_automation_webhooks', []);
    if (isset($webhooks[$index])) {
        unset($webhooks[$index]);
        $webhooks = array_values($webhooks);
        update_option('wpc_automation_webhooks', $webhooks);
    }
    wp_send_json_success($webhooks);
});

add_action('wp_ajax_wpc_automation_list_webhooks', function() {
    check_ajax_referer('wpc_automation_webhook_nonce');
    $webhooks = get_option('wpc_automation_webhooks', []);
    wp_send_json_success($webhooks);
});