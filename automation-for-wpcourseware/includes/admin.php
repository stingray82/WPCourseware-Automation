<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', function() {
    add_options_page(
        'WPC Automation Settings',
        'WPC Automation',
        'manage_options',
        'wpc-automation-settings',
        'wpc_automation_settings_page'
    );
});

add_action('admin_init', function() {
    register_setting('wpc_automation_settings_group', 'wpc_automation_api_key');
});

function wpc_automation_settings_page() {
    $api_key = get_option('wpc_automation_api_key', '');
    $generate_url = admin_url('admin-post.php?action=wpc_automation_generate_api_key');
    ?>
    <div class="wrap">
        <h1>WPC Automation Settings</h1>

        <form method="post" action="options.php">
            <?php settings_fields('wpc_automation_settings_group'); ?>
            <table class="form-table">
    <tr valign="top">
        <th scope="row">API Key</th>
        <td>
            <input type="text" id="wpc_automation_api_key" name="wpc_automation_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
            <button type="button" class="button button-small" onclick="navigator.clipboard.writeText(document.getElementById('wpc_automation_api_key').value)">Copy</button>
            <a href="<?php echo esc_url($generate_url); ?>" class="button">Generate API Key</a>
            <p class="description">Used for authenticating REST API requests (header: <code>X-WPC-Automation-Key</code>)</p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Enrollment Endpoint</th>
        <td>
            <code id="wpc_automation_enroll_url"><?php echo esc_url(rest_url('wpc-automation/v1/enroll')); ?></code>
            <button class="button button-small" onclick="navigator.clipboard.writeText(document.getElementById('wpc_automation_enroll_url').innerText); return false;">Copy</button>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">Removal Endpoint</th>
        <td>
            <code id="wpc_automation_remove_url"><?php echo esc_url(rest_url('wpc-automation/v1/remove')); ?></code>
            <button class="button button-small" onclick="navigator.clipboard.writeText(document.getElementById('wpc_automation_remove_url').innerText); return false;">Copy</button>
        </td>
    </tr>
</table>

<?php if (isset($_GET['api_key_generated'])) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const apiField = document.getElementById('wpc_automation_api_key');
            if (apiField) {
                apiField.value = "<?php echo esc_js(get_option('wpc_automation_api_key')); ?>";
            }
        });
    </script>
<?php endif; ?>

            <?php submit_button(); ?>
        </form>

        <hr>
        <h2>Outgoing Webhooks</h2>
        <div id="wpc-automation-webhooks"></div>
        <p class="description">Add one or more webhooks for the following triggers: user-enrolled, unit-completed, module-completed, course-completed.</p>
    </div>
    <?php
}

add_action('admin_post_wpc_automation_generate_api_key', function () {
    if (current_user_can('manage_options')) {
        $key = wp_generate_password(32, false);
        update_option('wpc_automation_api_key', $key);
        wp_redirect(add_query_arg('api_key_generated', '1', wp_get_referer()));
        exit;
    }
});

