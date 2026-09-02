<?php

if (! defined('ABSPATH')) {
    exit;
}

function ff_render_settings_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $notice = isset($_GET['ff_notice'])
        ? sanitize_key(wp_unslash($_GET['ff_notice']))
        : '';

    $imported = isset($_GET['imported'])
        ? absint($_GET['imported'])
        : 0;

    $skipped = isset($_GET['skipped'])
        ? absint($_GET['skipped'])
        : 0;

?>
    <div class="wrap ff-admin ff-settings ff-has-brandbar">

        <?php if ($notice === 'imported') : ?>
            <div class="notice notice-success inline is-dismissible">
                <p>
                    <?php
                    printf(
                        'Field groups imported successfully. Imported: %d. Skipped: %d.',
                        $imported,
                        $skipped
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($notice === 'invalid_file') : ?>
            <div class="notice notice-error inline is-dismissible">
                <p>The selected file is not a valid Forge Fields JSON export.</p>
            </div>
        <?php endif; ?>

        <?php if ($notice === 'upload_error') : ?>
            <div class="notice notice-error inline is-dismissible">
                <p>The JSON file could not be uploaded.</p>
            </div>
        <?php endif; ?>

        <?php if ($notice === 'feedback_sent') : ?>
            <div class="notice notice-success inline is-dismissible">
                <p>Your Forge Fields feedback was sent successfully.</p>
            </div>
        <?php endif; ?>

        <?php if ($notice === 'feedback_failed') : ?>
            <div class="notice notice-error inline is-dismissible">
                <p>Forge Fields could not send your feedback. Please try again later.</p>
            </div>
        <?php endif; ?>

        <?php if ($notice === 'feedback_empty') : ?>
            <div class="notice notice-error inline is-dismissible">
                <p>Please enter a feedback message before submitting.</p>
            </div>
        <?php endif; ?>

        <div class="ff-settings-grid">

            <div class="ff-settings-card">

                <h2>Export Field Groups</h2>

                <p>
                    Download all Forge Fields field-group definitions as a JSON file.
                    Saved field values are not included.
                </p>

                <form method="post">

                    <?php wp_nonce_field(
                        'ff_export_field_groups',
                        'ff_export_nonce'
                    ); ?>

                    <button
                        type="submit"
                        name="ff_export_field_groups"
                        value="1"
                        class="button button-primary">
                        Export JSON
                    </button>

                </form>

            </div>

            <div class="ff-settings-card">

                <h2>Import Field Groups</h2>

                <p>
                    Import field-group definitions from a Forge Fields JSON export.
                </p>

                <form
                    method="post"
                    enctype="multipart/form-data">

                    <?php wp_nonce_field(
                        'ff_import_field_groups',
                        'ff_import_nonce'
                    ); ?>

                    <div class="ff-settings-field">

                        <label for="ff_import_file">
                            JSON File
                        </label>

                        <input
                            type="file"
                            id="ff_import_file"
                            name="ff_import_file"
                            accept=".json,application/json"
                            required>

                    </div>

                    <div class="ff-settings-field">

                        <label for="ff_import_conflict">
                            Existing field groups
                        </label>

                        <div class="ff-select-wrap">

                            <select
                                id="ff_import_conflict"
                                name="ff_import_conflict">
                                <option value="skip">
                                    Skip existing
                                </option>

                                <option value="overwrite">
                                    Overwrite existing
                                </option>

                                <option value="duplicate">
                                    Create duplicates
                                </option>
                            </select>

                        </div>

                    </div>

                    <button
                        type="submit"
                        name="ff_import_field_groups"
                        value="1"
                        class="button button-primary">
                        Import JSON
                    </button>

                </form>

            </div>

            <div class="ff-settings-card">

                <h2>Feedback</h2>

                <p>
                    Found a bug or have an idea for Forge Fields? Send feedback directly from WordPress.
                    Your WordPress version, PHP version, Forge Fields version, site URL, and account email
                    will be included to help diagnose issues.
                </p>

                <form method="post">

                    <?php wp_nonce_field(
                        'ff_submit_feedback',
                        'ff_feedback_nonce'
                    ); ?>

                    <div class="ff-settings-field">

                        <label for="ff_feedback_type">
                            Feedback Type
                        </label>

                        <div class="ff-select-wrap">
                            <select
                                id="ff_feedback_type"
                                name="ff_feedback_type">
                                <option value="bug">Bug Report</option>
                                <option value="feature">Feature Request</option>
                                <option value="general">General Feedback</option>
                            </select>
                        </div>

                    </div>

                    <div class="ff-settings-field">

                        <label for="ff_feedback_message">
                            Message
                        </label>

                        <textarea
                            id="ff_feedback_message"
                            name="ff_feedback_message"
                            rows="6"
                            class="large-text"
                            maxlength="3000"
                            required></textarea>

                    </div>

                    <button
                        type="submit"
                        name="ff_submit_feedback"
                        value="1"
                        class="button button-primary">
                        Submit Feedback
                    </button>

                </form>

            </div>

        </div>

    </div>
<?php
}


/* EXPORT */
add_action('admin_init', 'ff_handle_field_group_export');

function ff_handle_field_group_export()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    if (! isset($_POST['ff_export_field_groups'])) {
        return;
    }

    check_admin_referer(
        'ff_export_field_groups',
        'ff_export_nonce'
    );

    $groups = ff_get_all_groups();

    foreach ($groups as &$group) {
        unset($group['last_saved']);
    }
    unset($group);

    $plugin_version = defined('FF_VERSION')
        ? FF_VERSION
        : 'unknown';

    $export = [
        'format'               => 'forge-fields',
        'schema_version'       => 1,
        'forge_fields_version' => $plugin_version,
        'exported_at'          => wp_date(
            'c',
            time(),
            wp_timezone()
        ),
        'field_groups'         => $groups,
    ];

    $json = wp_json_encode(
        $export,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );

    if ($json === false) {
        wp_die('Forge Fields could not generate the JSON export.');
    }

    $filename = sprintf(
        'forge-fields-%s.json',
        wp_date(
            'Y-m-d-His',
            time(),
            wp_timezone()
        )
    );

    nocache_headers();

    header(
        'Content-Type: application/json; charset=utf-8'
    );

    header(
        'Content-Disposition: attachment; filename="' .
            $filename .
            '"'
    );

    header(
        'Content-Length: ' . strlen($json)
    );

    echo $json;
    exit;
}


/* IMPORT */
add_action('admin_init', 'ff_handle_field_group_import');

function ff_handle_field_group_import()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    if (! isset($_POST['ff_import_field_groups'])) {
        return;
    }

    check_admin_referer(
        'ff_import_field_groups',
        'ff_import_nonce'
    );

    $settings_url = admin_url(
        'admin.php?page=forge-fields-settings'
    );

    if (
        empty($_FILES['ff_import_file'])
        || ! isset($_FILES['ff_import_file']['error'])
        || $_FILES['ff_import_file']['error'] !== UPLOAD_ERR_OK
    ) {
        wp_safe_redirect(
            add_query_arg(
                'ff_notice',
                'upload_error',
                $settings_url
            )
        );

        exit;
    }

    $file = $_FILES['ff_import_file'];

    $filename = isset($file['name'])
        ? sanitize_file_name($file['name'])
        : '';

    $filetype = wp_check_filetype_and_ext(
        $file['tmp_name'],
        $filename,
        [
            'json' => 'application/json',
        ]
    );

    if (
        empty($filetype['ext'])
        || $filetype['ext'] !== 'json'
    ) {
        wp_safe_redirect(
            add_query_arg(
                'ff_notice',
                'invalid_file',
                $settings_url
            )
        );

        exit;
    }

    $extension = strtolower(
        pathinfo($filename, PATHINFO_EXTENSION)
    );

    if ($extension !== 'json') {
        wp_safe_redirect(
            add_query_arg(
                'ff_notice',
                'invalid_file',
                $settings_url
            )
        );

        exit;
    }

    if (
        ! empty($file['size'])
        && (int) $file['size'] > 2 * MB_IN_BYTES
    ) {
        wp_safe_redirect(
            add_query_arg(
                'ff_notice',
                'invalid_file',
                $settings_url
            )
        );

        exit;
    }

    $tmp_name = isset($file['tmp_name'])
        ? $file['tmp_name']
        : '';

    if (
        ! $tmp_name
        || ! is_uploaded_file($tmp_name)
    ) {
        wp_safe_redirect(
            add_query_arg(
                'ff_notice',
                'upload_error',
                $settings_url
            )
        );

        exit;
    }

    $json = file_get_contents($tmp_name);

    if ($json === false) {
        wp_safe_redirect(
            add_query_arg(
                'ff_notice',
                'invalid_file',
                $settings_url
            )
        );

        exit;
    }

    $data = json_decode($json, true);

    if (
        ! is_array($data)
        || ($data['format'] ?? '') !== 'forge-fields'
        || empty($data['field_groups'])
        || ! is_array($data['field_groups'])
    ) {
        wp_safe_redirect(
            add_query_arg(
                'ff_notice',
                'invalid_file',
                $settings_url
            )
        );

        exit;
    }

    $conflict_mode = isset($_POST['ff_import_conflict'])
        ? sanitize_key(
            wp_unslash($_POST['ff_import_conflict'])
        )
        : 'skip';

    if (
        ! in_array(
            $conflict_mode,
            ['skip', 'overwrite', 'duplicate'],
            true
        )
    ) {
        $conflict_mode = 'skip';
    }

    $existing_groups = ff_get_all_groups();

    $imported = 0;
    $skipped  = 0;

    foreach ($data['field_groups'] as $group_key => $group) {

        if (! is_array($group)) {
            $skipped++;
            continue;
        }

        $import_id = ! empty($group['id'])
            ? sanitize_text_field($group['id'])
            : sanitize_text_field($group_key);

        if ($import_id === '') {
            $skipped++;
            continue;
        }

        $group['id'] = $import_id;

        $group['title'] = isset($group['title'])
            ? sanitize_text_field($group['title'])
            : '(no title)';

        $group['location'] = isset($group['location'])
            ? sanitize_key($group['location'])
            : 'page';

        $group['location_target'] =
            isset($group['location_target'])
            ? sanitize_text_field(
                (string) $group['location_target']
            )
            : '';

        $group['status'] = isset($group['status'])
            ? sanitize_key($group['status'])
            : 'active';

        $group['fields'] =
            isset($group['fields'])
            && is_array($group['fields'])
            ? $group['fields']
            : [];

        $group['last_saved'] = time();

        $already_exists = isset(
            $existing_groups[$import_id]
        );

        if (
            $already_exists
            && $conflict_mode === 'skip'
        ) {
            $skipped++;
            continue;
        }

        if (
            $already_exists
            && $conflict_mode === 'duplicate'
        ) {
            $new_id = ff_generate_group_id();

            $group['id'] = $new_id;

            $group['title'] .= ' (Imported Copy)';

            $existing_groups[$new_id] = $group;

            $imported++;
            continue;
        }

        $existing_groups[$import_id] = $group;

        $imported++;
    }

    ff_save_all_groups($existing_groups);

    $redirect_url = add_query_arg(
        [
            'ff_notice' => 'imported',
            'imported'  => $imported,
            'skipped'   => $skipped,
        ],
        $settings_url
    );

    wp_safe_redirect($redirect_url);
    exit;
}

/* SUBMIT FEEDBACK */
add_action('admin_init', 'ff_handle_feedback_submission');

function ff_handle_feedback_submission()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    if (! isset($_POST['ff_submit_feedback'])) {
        return;
    }

    check_admin_referer(
        'ff_submit_feedback',
        'ff_feedback_nonce'
    );

    $settings_url = admin_url(
        'admin.php?page=forge-fields-settings'
    );

    $type = isset($_POST['ff_feedback_type'])
        ? sanitize_key(
            wp_unslash($_POST['ff_feedback_type'])
        )
        : 'general';

    $allowed_types = [
        'bug',
        'feature',
        'general',
    ];

    if (! in_array($type, $allowed_types, true)) {
        $type = 'general';
    }

    $message = isset($_POST['ff_feedback_message'])
        ? sanitize_textarea_field(
            wp_unslash($_POST['ff_feedback_message'])
        )
        : '';

    if ($message === '') {
        wp_safe_redirect(
            add_query_arg(
                'ff_notice',
                'feedback_empty',
                $settings_url
            )
        );

        exit;
    }

    $current_user = wp_get_current_user();

    $type_labels = [
        'bug'     => 'Bug Report',
        'feature' => 'Feature Request',
        'general' => 'General Feedback',
    ];

    $type_label = $type_labels[$type];

    $subject = sprintf(
        '[Forge Fields] %s',
        $type_label
    );

    $body = sprintf(
        "Forge Fields Feedback\n\n" .
            "Type: %s\n" .
            "Plugin Version: %s\n" .
            "WordPress Version: %s\n" .
            "PHP Version: %s\n" .
            "Site URL: %s\n" .
            "User: %s <%s>\n\n" .
            "Message:\n%s",
        $type_label,
        defined('FF_VERSION') ? FF_VERSION : 'Unknown',
        get_bloginfo('version'),
        PHP_VERSION,
        home_url(),
        $current_user->display_name,
        $current_user->user_email,
        $message
    );

    $to = 'useforgedev@gmail.com.com';

    $sent = wp_mail(
        $to,
        $subject,
        $body
    );

    if (! $sent) {
        error_log('Forge Fields feedback email failed to send.');
    }

    wp_safe_redirect(
        add_query_arg(
            'ff_notice',
            $sent
                ? 'feedback_sent'
                : 'feedback_failed',
            $settings_url
        )
    );

    exit;
}

add_action('wp_mail_failed', function ($error) {
    error_log(
        'Forge Fields wp_mail error: ' .
            $error->get_error_message()
    );
});
