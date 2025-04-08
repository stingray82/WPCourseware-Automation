console.log('webhook-ui.js loaded');
jQuery(document).ready(function ($) {
  const listWebhooks = () => {
    $.post(wpcAutomationAjax.ajax_url, {
      action: 'wpc_automation_list_webhooks',
      _ajax_nonce: wpcAutomationAjax.nonce
    }, function (res) {
      if (res.success) {
        const container = $('#wpc-automation-webhooks');
        container.empty();
        if (res.data.length === 0) {
          container.append('<p>No webhooks defined yet.</p>');
          return;
        }
        let table = '<table class="widefat"><thead><tr><th>Trigger</th><th>URL</th><th>Send API Key</th><th>Actions</th></tr></thead><tbody>';
        res.data.forEach((wh, idx) => {
          table += `<tr>
            <td>${wh.trigger}</td>
            <td>${wh.url}</td>
            <td>${wh.send_api_key === 'yes' ? '✅' : '❌'}</td>
            <td><button class="button delete-webhook" data-index="${idx}">Delete</button></td>
          </tr>`;
        });
        table += '</tbody></table>';
        container.append(table);
      }
    });
  };

  $(document).on('click', '.delete-webhook', function () {
    const index = $(this).data('index');
    if (!confirm('Delete this webhook?')) return;

    $.post(wpcAutomationAjax.ajax_url, {
      action: 'wpc_automation_delete_webhook',
      _ajax_nonce: wpcAutomationAjax.nonce,
      index
    }, function (res) {
      if (res.success) listWebhooks();
    });
  });

  // Add webhook form
  $('#wpc-automation-webhooks').before(`
    <div id="wpc-automation-add-form">
      <h3>Add Webhook</h3>
      <label>Trigger:
        <select id="wh-trigger">
          <option value="user-enrolled">User Enrolled</option>
          <option value="unit-completed">Unit Completed</option>
          <option value="module-completed">Module Completed</option>
          <option value="course-completed">Course Completed</option>
        </select>
      </label>
      <label>Webhook URL: <input type="url" id="wh-url" class="regular-text" /></label>
      <label><input type="checkbox" id="wh-api-key" /> Send API Key</label>
      <button class="button" id="wh-add">Add Webhook</button>
      <hr />
    </div>
  `);

  $('#wh-add').on('click', function () {
    const trigger = $('#wh-trigger').val();
    const url = $('#wh-url').val();
    const send_api_key = $('#wh-api-key').is(':checked');

    if (!url) return alert('Webhook URL is required.');

    $.post(wpcAutomationAjax.ajax_url, {
      action: 'wpc_automation_add_webhook',
      _ajax_nonce: wpcAutomationAjax.nonce,
      trigger,
      url,
      send_api_key
    }, function (res) {
      if (res.success) {
        $('#wh-url').val('');
        $('#wh-api-key').prop('checked', false);
        listWebhooks();
      }
    });
  });

  listWebhooks();
});
