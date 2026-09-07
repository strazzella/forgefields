<?php

/**
 * Prevent direct access to this file outside of WordPress.
 */
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Define the shared choices parser only when another implementation
 * has not already been loaded.
 */
if (! function_exists('ff_parse_choices_string')) {
    /**
     * Parse a multiline choices string into a sanitized value => label map.
     *
     * Supports "value|label", "value:label", and plain label formats.
     *
     * @param mixed $raw Raw choices string.
     *
     * @return array Sanitized choices map.
     */
    function ff_parse_choices_string($raw)
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $raw) as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            if (strpos($line, '|') !== false) {
                list($value, $label) = array_map('trim', explode('|', $line, 2));
            } elseif (strpos($line, ':') !== false) {
                list($value, $label) = array_map('trim', explode(':', $line, 2));
            } else {
                $label = $line;
                $value = sanitize_key($line);
            }

            $out[sanitize_key($value)] = sanitize_text_field($label);
        }
        return $out;
    }
}

/**
 * Normalize and validate multiline field choices before saving.
 *
 * Converts supported choice formats into a consistent representation
 * and returns validation errors or warnings when encountered.
 *
 * @param mixed $raw Raw choices string.
 *
 * @return array Normalized choices plus validation messages.
 */
function ff_normalize_choices_string($raw)
{

    $lines     = preg_split('/\r\n|\r|\n/', (string) $raw);
    $normalized = [];
    $errors     = [];
    $warnings   = [];

    foreach ($lines as $i => $line) {

        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $value = '';
        $label = '';

        if (strpos($line, '|') !== false) {
            [$value, $label] = array_map('trim', explode('|', $line, 2));
        } elseif (strpos($line, ':') !== false) {
            [$value, $label] = array_map('trim', explode(':', $line, 2));
        } else {
            $value = $line;
            $label = '';
        }

        $value = sanitize_key($value);
        if ($value === '') {
            $errors[] = sprintf(
                'Line %d is invalid. Each choice must have a value.',
                $i + 1
            );
            continue;
        }

        if ($label === '') {
            $normalized[] = $value;
        } else {
            $label = sanitize_text_field($label);
            if ($label === '') {
                $errors[] = sprintf(
                    'Line %d has an empty label.',
                    $i + 1
                );
                continue;
            }

            $normalized[] = $value . ' : ' . $label;
        }
    }

    if (empty($normalized)) {
        $errors[] = 'At least one valid choice is required.';
    }

    return [
        'normalized' => implode("\n", $normalized),
        'errors'     => $errors,
        'warnings'   => $warnings,
    ];
}




/**
 * Customize Forge Fields admin screens before the standard admin header.
 *
 * Adds Forge Fields body classes, screen-specific styles and scripts,
 * renders the branded admin header/subheader, and supplies contextual
 * actions such as Add Field and Save Changes.
 */
add_action('in_admin_header', function () {
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if (
        ! in_array(
            $page,
            [
                'forge-fields',
                'forge-fields-edit',
                'forge-fields-global',
                'forge-fields-settings',
            ],
            true
        )
    ) {
        return;
    }

    /**
     * Add Forge Fields-specific body classes on plugin admin screens.
     *
     * @param string $classes Existing WordPress admin body classes.
     *
     * @return string Updated body class string.
     */
    add_filter('admin_body_class', function ($classes) {
        return trim($classes . ' ff-has-brandbar ff-has-subbar');
    });

    add_action('admin_head', function () {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if (!in_array($page, ['forge-fields', 'forge-fields-edit'], true)) {
            return;
        }
        echo '<style>
            /* hidden by default */
            tr.ff-field-settings { display:none !important; }
            /* shown when JS says so */
            tr.ff-field-settings.is-visible { display:table-row !important; }
        </style>';
    });

    $is_new   = ($page === 'forge-fields-edit' && empty($_GET['group']));
    if ($page === 'forge-fields') {

        $subtitle = 'Field Groups';
    } elseif ($page === 'forge-fields-global') {

        $subtitle = 'Global Fields';
    } elseif ($page === 'forge-fields-settings') {

        $subtitle = 'Settings';
    } else {

        $subtitle = $is_new
            ? 'Add New Field Group'
            : 'Edit Field Group';
    }

    ff_render_admin_brandbar($subtitle, '');

    $right_html = '';
    if ($page === 'forge-fields') {
        $cta_url   = admin_url('admin.php?page=forge-fields-edit');
        ff_render_admin_subbar($subtitle, $cta_url, 'Add New');
        return;
    }

    if ($page === 'forge-fields-edit') {

        $last_saved_html = '';

        $group_id = isset($_GET['group'])
            ? sanitize_text_field(wp_unslash($_GET['group']))
            : '';

        if ($group_id) {
            $groups = ff_get_all_groups();

            if (
                isset($groups[$group_id]['last_saved'])
                && $groups[$group_id]['last_saved']
            ) {
                $last_saved_html = sprintf(
                    '<span class="ff-last-saved">Last saved: %s</span>',
                    esc_html(
                        wp_date(
                            'F j, Y \a\t g:i:s A',
                            (int) $groups[$group_id]['last_saved'],
                            wp_timezone()
                        )
                    )
                );
            }
        }

        $right_html = sprintf(
            '<button type="button"
                 class="button ff-subbar__btn"
                 id="ff-add-field">
            Add Field
        </button>

        <button type="submit"
                class="button button-primary ff-subbar__btn"
                form="ff-edit-form"
                name="ff_save_field_group"
                value="1">
            Save Changes
        </button>

        %s',
            $last_saved_html
        );
    }

    if ($page === 'forge-fields-global') {

        $last_saved = get_option('ff_global_fields_last_saved');

        $last_saved_html = '';

        if ($last_saved) {
            $last_saved_html = sprintf(
                '<span class="ff-last-saved">Last saved: %s</span>',
                esc_html(
                    wp_date(
                        'F j, Y \a\t g:i:s A',
                        (int) $last_saved,
                        wp_timezone()
                    )
                )
            );
        }

        $right_html = sprintf(
            '<button type="submit"
                 class="button button-primary ff-subbar__btn"
                 form="ff-global-form"
                 name="ff_save_global"
                 value="1">
            Save Global Fields
        </button>
        %s',
            $last_saved_html
        );
    }

    if ($page === 'forge-fields-settings') {

        $version_html = sprintf(
            '<span class="ff-settings-version">Forge Fields - Version %s</span>',
            esc_html(FF_VERSION)
        );

        ff_render_admin_subbar(
            $subtitle,
            '',
            '',
            $version_html
        );

        return;
    }

    ff_render_admin_subbar($subtitle, '', 'Add New', $right_html);
});

/**
 * Render the Forge Fields branded admin header.
 *
 * @param string $subtitle  Optional current-screen subtitle.
 * @param string $add_url   Optional URL for a header action button.
 * @param string $add_label Optional label for the header action button.
 */
function ff_render_admin_brandbar($subtitle = '', $add_url = '', $add_label = 'Add New')
{
    $page       = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    $list_page  = 'forge-fields';
    $is_current = ($page === $list_page);

    $home_url = admin_url('admin.php?page=' . $list_page);
?>
    <div class="ff-brandbar" role="banner" aria-label="Forge Fields">
        <div class="ff-brandbar__inner">
            <div class="ff-brandbar__left">
                <span class="ff-brandbar__logo">
                    <a href="<?php echo esc_url($home_url); ?>">
                        <img src="<?php echo esc_url(FF_URL . 'assets/img/forge-logo2.png'); ?>" alt="Forge Fields Logo"></span>
                </a>
                <?php if ($is_current) : ?>
                    <a class="ff-brandbar__title" href="<?php echo esc_url($home_url); ?>">Forge Fields</a>
                <?php else : ?>
                    <a class="ff-brandbar__title" href="<?php echo esc_url($home_url); ?>">Forge Fields</a>
                <?php endif; ?>

                <?php if ($subtitle) : ?>
                    <span class="ff-brandbar__subtitle">— <?php echo esc_html($subtitle); ?></span>
                <?php endif; ?>
            </div>

            <div class="ff-brandbar__right">
                <?php if ($add_url) : ?>
                    <a class="button ff-brandbar__btn" href="<?php echo esc_url($add_url); ?>">
                        + <?php echo esc_html($add_label); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
}

/**
 * Render the Forge Fields secondary admin navigation bar.
 *
 * Displays the current screen title and either a contextual action button
 * or prebuilt right-side action markup.
 *
 * @param string $title      Screen title.
 * @param string $cta_url    Optional call-to-action URL.
 * @param string $cta_label  Optional call-to-action label.
 * @param string $right_html Optional trusted internal action markup.
 */
function ff_render_admin_subbar($title, $cta_url = '', $cta_label = 'Add New', $right_html = '')
{ ?>
    <div class="ff-subbar" role="navigation" aria-label="Forge Fields secondary bar">
        <div class="ff-subbar__inner">
            <h1 class="ff-subbar__title"><?php echo esc_html($title); ?></h1>

            <div class="ff-subbar__actions">
                <?php
                if ($right_html) {
                    echo $right_html;
                } elseif ($cta_url) { ?>
                    <a class="button button-primary ff-subbar__btn"
                        href="<?php echo esc_url($cta_url); ?>">
                        + <?php echo esc_html($cta_label); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
<?php }

/**
 * Register Forge Fields admin menu and submenu pages.
 */
add_action('admin_menu', function () {

    $parent_slug = 'forge-fields';

    add_menu_page(
        'Forge Fields',
        'Forge Fields',
        'manage_options',
        $parent_slug,
        'ff_render_field_groups_list',
        'dashicons-editor-table',
        80
    );

    add_submenu_page(
        $parent_slug,
        'Field Groups',
        'Field Groups',
        'manage_options',
        $parent_slug,
        'ff_render_field_groups_list'
    );

    add_submenu_page(
        null,
        'Add New Field Group',
        'Add New',
        'manage_options',
        'forge-fields-edit',
        'ff_render_field_group_edit'
    );

    add_submenu_page(
        $parent_slug,
        'Global Fields',
        'Global Fields',
        'manage_options',
        'forge-fields-global',
        'ff_render_global_options_page'
    );

    add_submenu_page(
        $parent_slug,
        'Settings',
        'Settings',
        'manage_options',
        'forge-fields-settings',
        'ff_render_settings_page'
    );

    add_submenu_page(
        null,
        'Deactivate Forge Fields',
        'Deactivate Forge Fields',
        'activate_plugins',
        'forge-fields-deactivate',
        'ff_render_deactivate_page'
    );
});

add_filter('admin_body_class', function ($classes) {
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if (
        in_array(
            $page,
            [
                'forge-fields',
                'forge-fields-edit',
                'forge-fields-global',
                'forge-fields-settings',
            ],
            true
        )
    ) {
        $classes .= ' ff-has-brandbar';
    }
    return $classes;
});

/**
 * Process field-group save requests during admin initialization.
 */
add_action('admin_init', 'ff_handle_field_group_save');

/**
 * Validate, sanitize, and persist a Forge Fields field group.
 *
 * Handles capability and nonce checks, field definitions, location rules,
 * duplicate names/titles, choice normalization, validation errors, and
 * the final field-group save/redirect flow.
 */
function ff_handle_field_group_save()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $page = isset($_REQUEST['page'])
        ? sanitize_key(wp_unslash($_REQUEST['page']))
        : '';

    if ($page !== 'forge-fields-edit') {
        return;
    }

    if (! isset($_POST['ff_save_field_group'])) {
        return;
    }

    check_admin_referer('ff_save_field_group');

    $groups = ff_get_all_groups();

    $posted_group_id = isset($_POST['ff_group_id'])
        ? sanitize_text_field(wp_unslash($_POST['ff_group_id']))
        : '';

    /**
     * Preserve the existing field definitions before the group is
     * overwritten so removed fields can be detected after validation.
     */
    $existing_fields = [];

    if (
        $posted_group_id !== ''
        && $posted_group_id !== 'new'
        && isset($groups[$posted_group_id]['fields'])
        && is_array($groups[$posted_group_id]['fields'])
    ) {
        $existing_fields = $groups[$posted_group_id]['fields'];
    }

    $title = isset($_POST['ff_group_title'])
        ? substr(
            sanitize_text_field(
                wp_unslash($_POST['ff_group_title'])
            ),
            0,
            100
        )
        : '';

    $location = isset($_POST['ff_location'])
        ? sanitize_text_field(wp_unslash($_POST['ff_location']))
        : 'page';

    if (! in_array($location, ['page', 'post', 'global'], true)) {
        $location = 'page';
    }

    $location_target = '';

    if ($location === 'page') {
        $location_target = isset($_POST['ff_location_target_page'])
            ? absint($_POST['ff_location_target_page'])
            : 0;
    } elseif ($location === 'post') {
        $location_target = isset($_POST['ff_location_target_post'])
            ? absint($_POST['ff_location_target_post'])
            : 0;
    }

    $fields_raw = isset($_POST['ff_fields']) && is_array($_POST['ff_fields'])
        ? $_POST['ff_fields']
        : [];

    $fields          = [];
    $has_save_errors = false;
    $used_names      = [];
    $error_messages  = [];

    foreach ($fields_raw as $field_raw) {

        $name = isset($field_raw['name'])
            ? substr(
                sanitize_key(
                    wp_unslash($field_raw['name'])
                ),
                0,
                50
            )
            : '';

        $label = isset($field_raw['label'])
            ? substr(
                sanitize_text_field(
                    wp_unslash($field_raw['label'])
                ),
                0,
                50
            )
            : '';

        $type = isset($field_raw['type'])
            ? sanitize_key(
                wp_unslash($field_raw['type'])
            )
            : 'text';

        $allowed_types = [
            'text',
            'textarea',
            'number',
            'email',
            'url',
            'range',
            'password',
            'image',
            'file',
            'wysiwyg',
            'select',
            'checkbox',
            'radio',
            'button_group',
            'true_false',
            'tab',
        ];

        if (! in_array($type, $allowed_types, true)) {
            $type = 'text';
        }

        if ($name === '' && $label === '') {
            continue;
        }

        if ($type === 'tab') {

            if ($label === '') {
                $has_save_errors = true;
                $error_messages[] = 'Each Tab field must have a label.';
                continue;
            }

            $fields[] = [
                'label' => $label,
                'name'  => '',
                'type'  => 'tab',
            ];

            continue;
        }

        if ($label === '') {

            $has_save_errors = true;
            $error_messages[] = 'Each field must have a label.';

            $fields[] = [
                'label' => '',
                'name'  => $name,
                'type'  => $type,
            ];

            continue;
        }

        if ($name === '') {
            $name = sanitize_key(
                strtolower(
                    str_replace(' ', '_', $label)
                )
            );
        }

        if ($name === '') {

            $has_save_errors = true;

            $error_messages[] = sprintf(
                'Field "%s" needs a valid name.',
                $label
            );

            continue;
        }

        if (isset($used_names[$name])) {

            $has_save_errors = true;

            $error_messages[] = sprintf(
                'Duplicate field name "%s" is not allowed.',
                $name
            );

            continue;
        }

        $used_names[$name] = true;

        $choice_types = [
            'select',
            'checkbox',
            'radio',
            'button_group',
        ];

        $choices_to_save = '';

        if (in_array($type, $choice_types, true)) {

            $choices_raw = isset($field_raw['choices'])
                ? trim(
                    wp_unslash(
                        (string) $field_raw['choices']
                    )
                )
                : '';

            $result = ff_normalize_choices_string($choices_raw);

            if (! empty($result['errors'])) {

                $has_save_errors = true;

                foreach ($result['errors'] as $message) {
                    $error_messages[] = sprintf(
                        'Field "%s": %s',
                        $label,
                        $message
                    );
                }

                $choices_to_save = $choices_raw;
            } else {
                $choices_to_save = $result['normalized'];
            }
        }

        /**
         * Sanitize optional field settings.
         */
        $default_value = isset($field_raw['default_value'])
            ? sanitize_text_field(
                wp_unslash(
                    (string) $field_raw['default_value']
                )
            )
            : '';

        $required = ! empty($field_raw['required'])
            ? 1
            : 0;

        $character_limit = isset($field_raw['character_limit'])
            ? absint($field_raw['character_limit'])
            : 0;

        $prepend = isset($field_raw['prepend'])
            ? sanitize_text_field(
                wp_unslash(
                    (string) $field_raw['prepend']
                )
            )
            : '';

        $append = isset($field_raw['append'])
            ? sanitize_text_field(
                wp_unslash(
                    (string) $field_raw['append']
                )
            )
            : '';

        $prepend_append_types = [
            'text',
            'number',
            'email',
            'password',
        ];

        if (! in_array($type, $prepend_append_types, true)) {
            $prepend = '';
            $append  = '';
        }
        /**
         * Character limits only apply to compatible text-based fields.
         */
        $character_limit_types = [
            'text',
            'textarea',
            'email',
            'url',
            'password',
        ];

        if (! in_array($type, $character_limit_types, true)) {
            $character_limit = 0;
        }

        /**
         * Build the sanitized field definition.
         */
        $row = [
            'name'            => $name,
            'label'           => $label,
            'type'            => $type,
            'default_value'   => $default_value,
            'required'        => $required,
            'character_limit' => $character_limit,
            'prepend'         => $prepend,
            'append'          => $append,
        ];
        /**
         * Choice-based fields also store their normalized choices.
         */
        if (in_array($type, $choice_types, true)) {
            $row['choices'] = $choices_to_save;
        }

        $fields[] = $row;
    }

    if ($title === '') {
        $has_save_errors = true;
        $error_messages[] = 'Please enter a Field Group title.';
    }

    if ($title !== '') {

        foreach ($groups as $existing_id => $existing_group) {

            if ($existing_id === $posted_group_id) {
                continue;
            }

            $existing_status = isset($existing_group['status'])
                ? $existing_group['status']
                : 'active';

            if ($existing_status === 'trash') {
                continue;
            }

            $existing_title = isset($existing_group['title'])
                ? trim((string) $existing_group['title'])
                : '';

            if (
                $existing_title !== ''
                && strcasecmp($existing_title, trim($title)) === 0
            ) {
                $has_save_errors = true;

                $error_messages[] = sprintf(
                    'A field group named "%s" already exists.',
                    $title
                );

                break;
            }
        }
    }

    if ($has_save_errors) {

        $error_key = 'ff_save_errors_' . get_current_user_id();

        set_transient(
            $error_key,
            $error_messages,
            60
        );

        $redirect_url = admin_url(
            'admin.php?page=forge-fields-edit'
        );

        if ($posted_group_id !== '' && $posted_group_id !== 'new') {
            $redirect_url = add_query_arg(
                'group',
                $posted_group_id,
                $redirect_url
            );
        }

        $redirect_url = add_query_arg(
            'ff_notice',
            'save_error',
            $redirect_url
        );

        wp_safe_redirect($redirect_url);
        exit;
    }

    $current_status = 'active';

    if (
        $posted_group_id &&
        isset($groups[$posted_group_id]['status'])
    ) {
        $current_status = $groups[$posted_group_id]['status'];
    }

    if (
        $posted_group_id === ''
        || $posted_group_id === 'new'
    ) {
        $posted_group_id = ff_generate_group_id();
    }

    /**
     * Determine which previously saved fields have been removed
     * from this Field Group.
     */
    $existing_field_names = [];

    foreach ($existing_fields as $existing_field) {

        $existing_name = isset($existing_field['name'])
            ? sanitize_key((string) $existing_field['name'])
            : '';

        if ($existing_name !== '') {
            $existing_field_names[] = $existing_name;
        }
    }

    $new_field_names = [];

    foreach ($fields as $new_field) {

        $new_name = isset($new_field['name'])
            ? sanitize_key((string) $new_field['name'])
            : '';

        if ($new_name !== '') {
            $new_field_names[] = $new_name;
        }
    }

    $removed_field_names = array_values(
        array_diff(
            $existing_field_names,
            $new_field_names
        )
    );

    $group = [
        'id'              => $posted_group_id,
        'title'           => $title,
        'location'        => $location,
        'location_target' => $location_target
            ? (string) $location_target
            : '',
        'fields'          => $fields,
        'status'          => $current_status,
        'last_saved' => time(),
    ];

    $groups[$posted_group_id] = $group;

    ff_save_all_groups($groups);

    /**
     * A removed field no longer belongs to this Field Group.
     * Delete only this group's namespaced values for that field.
     */
    foreach ($removed_field_names as $removed_field_name) {
        ff_delete_group_field_values(
            $posted_group_id,
            $removed_field_name
        );
    }

    $redirect_url = add_query_arg(
        [
            'page'      => 'forge-fields-edit',
            'group'     => $posted_group_id,
            'ff_notice' => 'saved',
        ],
        admin_url('admin.php')
    );

    wp_safe_redirect($redirect_url);
    exit;
}

/**
 * Process individual and bulk field-group actions during admin initialization.
 */
add_action('admin_init', 'ff_handle_field_group_actions');

/**
 * Handle field-group lifecycle and bulk actions.
 *
 * Supports trash, restore, permanent delete, activate, deactivate,
 * duplicate, and cache-clearing actions with nonce verification.
 */
function ff_handle_field_group_actions()
{

    if (! current_user_can('manage_options')) {
        return;
    }

    $page = isset($_REQUEST['page'])
        ? sanitize_key(wp_unslash($_REQUEST['page']))
        : '';

    if ($page !== 'forge-fields') {
        return;
    }

    $groups = ff_get_all_groups();

    if (
        isset(
            $_POST['ff_bulk_action'],
            $_POST['ff_group_ids'],
            $_POST['ff_bulk_nonce']
        )
        && $_POST['ff_bulk_action'] !== '-1'
        && is_array($_POST['ff_group_ids'])
    ) {

        if (
            ! wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['ff_bulk_nonce'])
                ),
                'ff_bulk_groups'
            )
        ) {
            wp_die('Security check failed.');
        }

        $bulk_action = sanitize_key(
            wp_unslash($_POST['ff_bulk_action'])
        );

        $allowed_bulk_actions = [
            'trash',
            'activate',
            'deactivate',
            'duplicate',
            'restore',
            'delete',
        ];

        if (! in_array($bulk_action, $allowed_bulk_actions, true)) {
            return;
        }

        $selected_ids = array_map(
            'sanitize_text_field',
            array_map(
                'wp_unslash',
                $_POST['ff_group_ids']
            )
        );

        $current_view = isset($_POST['ff_view'])
            ? sanitize_key(wp_unslash($_POST['ff_view']))
            : 'all';

        $modified = false;

        foreach ($selected_ids as $group_id) {

            if (! isset($groups[$group_id])) {
                continue;
            }

            switch ($bulk_action) {

                case 'trash':
                    $groups[$group_id]['status'] = 'trash';
                    $modified = true;
                    break;

                case 'activate':
                    $groups[$group_id]['status'] = 'active';
                    $modified = true;
                    break;

                case 'deactivate':
                    $groups[$group_id]['status'] = 'inactive';
                    $modified = true;
                    break;

                case 'duplicate':
                    $original = $groups[$group_id];
                    $new_id   = ff_generate_group_id();

                    $copy           = $original;
                    $copy['id']     = $new_id;
                    $copy['status'] = 'active';

                    $base_title = isset($original['title'])
                        ? $original['title']
                        : '';

                    $copy['title'] = $base_title !== ''
                        ? $base_title . ' (Copy)'
                        : '(no title) (Copy)';

                    $groups[$new_id] = $copy;

                    $modified = true;
                    break;

                case 'restore':
                    $groups[$group_id]['status'] = 'active';
                    $modified = true;
                    break;

                case 'delete':
                    unset($groups[$group_id]);
                    $modified = true;
                    break;
            }
        }

        if ($modified) {
            ff_save_all_groups($groups);
        }

        $redirect_url = admin_url(
            'admin.php?page=forge-fields'
        );

        if ($current_view) {
            $redirect_url = add_query_arg(
                'ff_view',
                $current_view,
                $redirect_url
            );
        }

        $redirect_url = add_query_arg(
            'ff_notice',
            'bulk_' . $bulk_action,
            $redirect_url
        );

        wp_safe_redirect($redirect_url);
        exit;
    }

    $action = isset($_GET['ff_action'])
        ? sanitize_key(wp_unslash($_GET['ff_action']))
        : '';

    $group_id = isset($_GET['group_id'])
        ? sanitize_text_field(
            wp_unslash($_GET['group_id'])
        )
        : '';

    if (
        $action === ''
        || $group_id === ''
        || ! isset($groups[$group_id])
    ) {
        return;
    }

    $nonce = isset($_GET['_wpnonce'])
        ? sanitize_text_field(
            wp_unslash($_GET['_wpnonce'])
        )
        : '';

    if (
        ! wp_verify_nonce(
            $nonce,
            'ff_group_action_' . $action . '_' . $group_id
        )
    ) {
        wp_die('Security check failed.');
    }

    $notice = '';

    switch ($action) {

        case 'trash':
            $groups[$group_id]['status'] = 'trash';
            ff_save_all_groups($groups);
            $notice = 'trashed';
            break;

        case 'restore':
            $groups[$group_id]['status'] = 'active';
            ff_save_all_groups($groups);
            $notice = 'restored';
            break;

        case 'delete':
            unset($groups[$group_id]);
            ff_save_all_groups($groups);
            $notice = 'deleted';
            break;

        case 'deactivate':
            $groups[$group_id]['status'] = 'inactive';
            ff_save_all_groups($groups);
            $notice = 'deactivated';
            break;

        case 'activate':
            $groups[$group_id]['status'] = 'active';
            ff_save_all_groups($groups);
            $notice = 'activated';
            break;

        case 'duplicate':
            $original = $groups[$group_id];
            $new_id   = ff_generate_group_id();

            $copy           = $original;
            $copy['id']     = $new_id;
            $copy['status'] = 'active';

            $base_title = isset($original['title'])
                ? $original['title']
                : '';

            $copy['title'] = $base_title !== ''
                ? $base_title . ' (Copy)'
                : '(no title) (Copy)';

            $groups[$new_id] = $copy;

            ff_save_all_groups($groups);

            $notice = 'duplicated';
            break;

        case 'clear_cache':
            wp_cache_delete(
                'ff_field_groups',
                'options'
            );

            $notice = 'cache_cleared';
            break;

        default:
            return;
    }

    $redirect_url = admin_url(
        'admin.php?page=forge-fields'
    );

    if (isset($_GET['ff_view'])) {
        $redirect_url = add_query_arg(
            'ff_view',
            sanitize_key(
                wp_unslash($_GET['ff_view'])
            ),
            $redirect_url
        );
    }

    if ($notice) {
        $redirect_url = add_query_arg(
            'ff_notice',
            $notice,
            $redirect_url
        );
    }

    wp_safe_redirect($redirect_url);
    exit;
}

/**
 * Render the main Forge Fields field-group list screen.
 *
 * Provides filtering, search, sorting, counts, bulk actions, status controls,
 * location details, and links for managing existing field groups.
 */
function ff_render_field_groups_list()
{

    if (! current_user_can('manage_options')) {
        return;
    }

    $groups = ff_get_all_groups();
    $current_view = isset($_GET['ff_view'])
        ? sanitize_key(wp_unslash($_GET['ff_view']))
        : 'all';

    if (! in_array($current_view, ['all', 'active', 'trash'], true)) {
        $current_view = 'all';
    }

    $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'title';
    $order   = isset($_GET['order']) ? strtolower(sanitize_text_field($_GET['order'])) : 'asc';
    $order   = ($order === 'desc') ? 'desc' : 'asc';

    $search_term = isset($_GET['ff_search'])
        ? trim(sanitize_text_field(wp_unslash($_GET['ff_search'])))
        : '';

    $count_all    = 0;
    $count_active = 0;
    $count_trash  = 0;

    foreach ($groups as $g) {
        $status = isset($g['status']) ? $g['status'] : 'active';

        if ($status === 'trash') {
            $count_trash++;
        } else {
            $count_all++;
            if ($status === 'active') {
                $count_active++;
            }
        }
    }

    $list_base_url = admin_url('admin.php?page=forge-fields');

    $notice = '';

    if (isset($_GET['ff_notice'])) {
        $code = sanitize_key(wp_unslash($_GET['ff_notice']));

        switch ($code) {
            case 'trashed':
                $notice = 'Field group moved to trash.';
                break;
            case 'restored':
                $notice = 'Field group restored.';
                break;
            case 'deleted':
                $notice = 'Field group deleted permanently.';
                break;
            case 'deactivated':
                $notice = 'Field group deactivated.';
                break;
            case 'activated':
                $notice = 'Field group activated.';
                break;
            case 'duplicated':
                $notice = 'Field group duplicated.';
                break;
            case 'cache_cleared':
                $notice = 'Field group cache cleared.';
                break;
            case 'bulk_trash':
                $notice = 'Selected field groups moved to trash.';
                break;
            case 'bulk_activate':
                $notice = 'Selected field groups activated.';
                break;
            case 'bulk_deactivate':
                $notice = 'Selected field groups deactivated.';
                break;
            case 'bulk_duplicate':
                $notice = 'Selected field groups duplicated.';
                break;
            case 'bulk_restore':
                $notice = 'Selected field groups restored.';
                break;
            case 'bulk_delete':
                $notice = 'Selected field groups deleted permanently.';
                break;
        }
    }

?>
    <div class="wrap ff-admin ff-list ff-has-brand">

        <?php
        $all_url    = remove_query_arg('ff_view', $list_base_url);
        $active_url = add_query_arg('ff_view', 'active', $list_base_url);
        $trash_url  = add_query_arg('ff_view', 'trash',  $list_base_url);
        ?>

        <?php if ($notice) : ?>
            <div class="notice notice-success inline is-dismissible">
                <p><?php echo esc_html($notice); ?></p>
            </div>
        <?php endif; ?>

        <?php
        $search_base_url = admin_url('admin.php?page=forge-fields');
        if ($current_view !== 'all') {
            $search_base_url = add_query_arg('ff_view', $current_view, $search_base_url);
        }
        if ($orderby) {
            $search_base_url = add_query_arg('orderby', $orderby, $search_base_url);
        }
        if ($order) {
            $search_base_url = add_query_arg('order', $order, $search_base_url);
        }
        ?>

        <div class="ff-list-toolbar">
            <ul class="subsubsub">
                <li class="all">
                    <a href="<?php echo esc_url($all_url); ?>"
                        class="<?php echo ($current_view === 'all') ? 'current' : ''; ?>">
                        All <span class="count">(<?php echo intval($count_all); ?>)</span>
                    </a> |
                </li>

                <li class="active">
                    <a href="<?php echo esc_url($active_url); ?>"
                        class="<?php echo ($current_view === 'active') ? 'current' : ''; ?>">
                        Active <span class="count">(<?php echo intval($count_active); ?>)</span>
                    </a> |
                </li>

                <li class="trash">
                    <a href="<?php echo esc_url($trash_url); ?>"
                        class="<?php echo ($current_view === 'trash') ? 'current' : ''; ?>">
                        Trash <span class="count">(<?php echo intval($count_trash); ?>)</span>
                    </a>
                </li>
            </ul>

            <form method="get"
                action="<?php echo esc_url(admin_url('admin.php')); ?>"
                class="ff-search-form ff-admin">
                <input type="hidden" name="page" value="forge-fields" />
                <input type="hidden" name="ff_view" value="<?php echo esc_attr($current_view); ?>" />
                <?php if ($orderby) : ?>
                    <input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>" />
                <?php endif; ?>
                <?php if ($order) : ?>
                    <input type="hidden" name="order" value="<?php echo esc_attr($order); ?>" />
                <?php endif; ?>

                <input type="search"
                    name="ff_search"
                    value="<?php echo esc_attr($search_term); ?>"
                    class="regular-text ff-search-input"
                    placeholder="" />

                <?php if ($search_term !== '') : ?>
                    <a href="<?php echo esc_url($search_base_url); ?>"
                        class="button button-secondary ff-search-clear">
                        ×
                    </a>
                <?php endif; ?>

                <button class="button button-primary">
                    Search Field Groups
                </button>
            </form>
        </div>

        <?php
        $display_groups = [];

        foreach ($groups as $group_id => $group) {
            $status = isset($group['status']) ? $group['status'] : 'active';

            if ($current_view === 'trash') {
                if ($status !== 'trash') {
                    continue;
                }
            } else {
                if ($status === 'trash') {
                    continue;
                }
                if ($current_view === 'active' && $status !== 'active') {
                    continue;
                }
            }

            if ($search_term !== '') {
                $title     = isset($group['title']) ? $group['title'] : '';
                $group_key = ! empty($group['id']) ? $group['id'] : $group_id;

                $haystack = strtolower($title . ' ' . $group_key);
                $needle   = strtolower($search_term);

                if (strpos($haystack, $needle) === false) {
                    continue;
                }
            }

            $display_groups[$group_id] = $group;
        }

        if ($orderby === 'title') {
            uasort($display_groups, function ($a, $b) use ($order) {
                $titleA = isset($a['title']) ? strtolower($a['title']) : '';
                $titleB = isset($b['title']) ? strtolower($b['title']) : '';

                if ($titleA === $titleB) return 0;

                return ($order === 'asc')
                    ? (($titleA < $titleB) ? -1 : 1)
                    : (($titleA > $titleB) ? -1 : 1);
            });
        }

        /**
         * Paginate Field Groups.
         *
         * The list displays a maximum of 10 groups per page after
         * filtering, searching, and sorting have been applied.
         */
        $visible_count = count($display_groups);

        $per_page     = 10;
        $current_page = isset($_GET['paged'])
            ? max(1, absint($_GET['paged']))
            : 1;

        $total_pages = max(
            1,
            (int) ceil($visible_count / $per_page)
        );

        /**
         * Prevent an invalid page number from producing an empty list.
         */
        $current_page = min($current_page, $total_pages);

        $offset = ($current_page - 1) * $per_page;

        $paged_groups = array_slice(
            $display_groups,
            $offset,
            $per_page,
            true
        );

        if (empty($display_groups)) : ?>
            <p>No field groups found for this view. Click “Add New” to create one.</p>
        <?php else : ?>

            <form method="post" action="" class="ff-admin ff-list-table">
                <?php wp_nonce_field('ff_bulk_groups', 'ff_bulk_nonce'); ?>
                <input type="hidden" name="ff_view"
                    value="<?php echo esc_attr($current_view); ?>">

                <?php
                $bulk_actions = [];

                if ($current_view === 'trash') {
                    $bulk_actions = [
                        'restore' => 'Restore',
                        'delete'  => 'Delete Permanently',
                    ];
                } else {
                    $bulk_actions = [
                        'trash'      => 'Move to Trash',
                        'duplicate'  => 'Duplicate',
                        'activate'   => 'Activate',
                        'deactivate' => 'Deactivate',
                    ];
                }

                $render_bulk = function ($position) use (
                    $bulk_actions,
                    $visible_count,
                    $current_page,
                    $total_pages,
                    $current_view,
                    $orderby,
                    $order,
                    $search_term
                ) {
                    $id = $position === 'top'
                        ? 'ff-bulk-action-selector-top'
                        : 'ff-bulk-action-selector-bottom';
                ?>
                    <div class="tablenav <?php echo $position === 'top' ? 'top' : 'bottom'; ?>">
                        <div class="alignleft actions bulkactions">
                            <label for="<?php echo esc_attr($id); ?>" class="screen-reader-text">
                                Bulk actions
                            </label>
                            <div class="ff-select-wrap">
                                <select name="ff_bulk_action" id="<?php echo esc_attr($id); ?>">
                                    <option value="-1">Bulk actions</option>
                                    <?php foreach ($bulk_actions as $value => $label) : ?>
                                        <option value="<?php echo esc_attr($value); ?>">
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="submit"
                                class="button action"
                                value="Apply">
                        </div>

                        <?php if ($position === 'bottom') : ?>
                            <div class="tablenav-pages">

                                <span class="displaying-num">
                                    <?php
                                    if ($visible_count === 1) {
                                        echo '1 item';
                                    } else {
                                        echo intval($visible_count) . ' items';
                                    }
                                    ?>
                                </span>

                                <?php if ($total_pages > 1) : ?>
                                    <?php
                                    $pagination_base = admin_url(
                                        'admin.php?page=forge-fields'
                                    );

                                    if ($current_view !== 'all') {
                                        $pagination_base = add_query_arg(
                                            'ff_view',
                                            $current_view,
                                            $pagination_base
                                        );
                                    }

                                    if ($orderby) {
                                        $pagination_base = add_query_arg(
                                            'orderby',
                                            $orderby,
                                            $pagination_base
                                        );
                                    }

                                    if ($order) {
                                        $pagination_base = add_query_arg(
                                            'order',
                                            $order,
                                            $pagination_base
                                        );
                                    }

                                    if ($search_term !== '') {
                                        $pagination_base = add_query_arg(
                                            'ff_search',
                                            $search_term,
                                            $pagination_base
                                        );
                                    }
                                    ?>

                                    <span class="pagination-links">

                                        <?php if ($current_page > 1) : ?>
                                            <a
                                                class="prev-page button"
                                                href="<?php echo esc_url(
                                                            add_query_arg(
                                                                'paged',
                                                                $current_page - 1,
                                                                $pagination_base
                                                            )
                                                        ); ?>">
                                                ‹
                                            </a>
                                        <?php endif; ?>

                                        <span class="paging-input">
                                            <?php echo intval($current_page); ?>
                                            of
                                            <span class="total-pages">
                                                <?php echo intval($total_pages); ?>
                                            </span>
                                        </span>

                                        <?php if ($current_page < $total_pages) : ?>
                                            <a
                                                class="next-page button"
                                                href="<?php echo esc_url(
                                                            add_query_arg(
                                                                'paged',
                                                                $current_page + 1,
                                                                $pagination_base
                                                            )
                                                        ); ?>">
                                                ›
                                            </a>
                                        <?php endif; ?>

                                    </span>
                                <?php endif; ?>

                            </div>
                        <?php endif; ?>

                        <br class="clear" />
                    </div>
                <?php
                };
                ?>

                <?php
                $render_header_row = function () use ($orderby, $order, $current_view) {

                    $next_order = ($orderby === 'title' && $order === 'asc') ? 'desc' : 'asc';

                    $title_sort_url = add_query_arg(
                        [
                            'orderby' => 'title',
                            'order'   => $next_order,
                            'ff_view' => $current_view,
                        ],
                        admin_url('admin.php?page=forge-fields')
                    );

                    $sort_class = ($orderby === 'title')
                        ? 'sorted ' . $order
                        : 'sortable asc';
                ?>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <input id="cb-select-all-1" type="checkbox" />
                        </td>

                        <th scope="col"
                            class="manage-column column-title <?php echo esc_attr($sort_class); ?>">
                            <a href="<?php echo esc_url($title_sort_url); ?>">
                                <span>Title</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>

                        <th scope="col" class="manage-column ff-bar-title">Forge Group Key</th>
                        <th scope="col" class="manage-column ff-bar-title">Location</th>
                        <th scope="col" class="manage-column ff-bar-title">Fields</th>
                        <th scope="col" class="manage-column ff-bar-title">Status</th>
                    </tr>
                <?php
                };
                ?>

                <table class="ff-list-table widefat fixed ff-admin striped">
                    <thead class="ff-list-bar">
                        <?php $render_header_row(); ?>
                    </thead>
                    <tbody>
                        <?php foreach ($paged_groups as $group_id => $group) :

                            $title    = ! empty($group['title']) ? $group['title'] : '(no title)';
                            $location = isset($group['location']) ? $group['location'] : 'page';
                            $fields   = isset($group['fields']) && is_array($group['fields']) ? $group['fields'] : [];
                            $field_cnt = count($fields);

                            $status = isset($group['status']) ? $group['status'] : 'active';
                            $status_label = ($status === 'active') ? 'Active' : 'Deactivated';

                            $group_key = ! empty($group['id']) ? $group['id'] : $group_id;

                            $edit_base_url = admin_url('admin.php?page=forge-fields-edit');
                            $edit_url      = add_query_arg(['group' => $group_id], $edit_base_url);

                            if ($current_view === 'trash') {
                                $restore_url = wp_nonce_url(
                                    add_query_arg(
                                        [
                                            'ff_view'   => 'trash',
                                            'ff_action' => 'restore',
                                            'group_id'  => $group_id,
                                        ],
                                        $list_base_url
                                    ),
                                    'ff_group_action_restore_' . $group_id
                                );

                                $delete_url = wp_nonce_url(
                                    add_query_arg(
                                        [
                                            'ff_view'   => 'trash',
                                            'ff_action' => 'delete',
                                            'group_id'  => $group_id,
                                        ],
                                        $list_base_url
                                    ),
                                    'ff_group_action_delete_' . $group_id
                                );
                            } else {
                                $trash_url = wp_nonce_url(
                                    add_query_arg(
                                        [
                                            'ff_view'   => $current_view,
                                            'ff_action' => 'trash',
                                            'group_id'  => $group_id,
                                        ],
                                        $list_base_url
                                    ),
                                    'ff_group_action_trash_' . $group_id
                                );

                                $dup_url = wp_nonce_url(
                                    add_query_arg(
                                        [
                                            'ff_view'   => $current_view,
                                            'ff_action' => 'duplicate',
                                            'group_id'  => $group_id,
                                        ],
                                        $list_base_url
                                    ),
                                    'ff_group_action_duplicate_' . $group_id
                                );

                                $toggle_action = ($status === 'active') ? 'deactivate' : 'activate';

                                $toggle_url = wp_nonce_url(
                                    add_query_arg(
                                        [
                                            'ff_view'   => $current_view,
                                            'ff_action' => $toggle_action,
                                            'group_id'  => $group_id,
                                        ],
                                        $list_base_url
                                    ),
                                    'ff_group_action_' . $toggle_action . '_' . $group_id
                                );

                                $clear_url = wp_nonce_url(
                                    add_query_arg(
                                        [
                                            'ff_view'   => $current_view,
                                            'ff_action' => 'clear_cache',
                                            'group_id'  => $group_id,
                                        ],
                                        $list_base_url
                                    ),
                                    'ff_group_action_clear_cache_' . $group_id
                                );
                            }
                        ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <input type="checkbox"
                                        name="ff_group_ids[]"
                                        value="<?php echo esc_attr($group_id); ?>" />
                                </th>

                                <td class="column-primary">
                                    <strong>
                                        <?php if ($current_view === 'trash') : ?>
                                            <?php echo esc_html($title); ?>
                                        <?php else : ?>
                                            <a href="<?php echo esc_url($edit_url); ?>">
                                                <?php echo esc_html($title); ?>
                                            </a>
                                        <?php endif; ?>
                                    </strong>

                                    <div class="row-actions">
                                        <?php if ($current_view === 'trash') : ?>
                                            <span class="restore">
                                                <a href="<?php echo esc_url($restore_url); ?>">Restore</a> |
                                            </span>
                                            <span class="delete">
                                                <a href="<?php echo esc_url($delete_url); ?>" class="submitdelete">
                                                    Delete Permanently
                                                </a>
                                            </span>
                                        <?php else : ?>
                                            <span class="edit">
                                                <a href="<?php echo esc_url($edit_url); ?>">Edit</a> |
                                            </span>
                                            <span class="duplicate">
                                                <a href="<?php echo esc_url($dup_url); ?>">Duplicate</a> |
                                            </span>
                                            <span class="deactivate">
                                                <a href="<?php echo esc_url($toggle_url); ?>">
                                                    <?php echo ($status === 'active') ? 'Deactivate' : 'Activate'; ?>
                                                </a> |
                                            </span>
                                            <span class="trash">
                                                <a href="<?php echo esc_url($trash_url); ?>" class="submitdelete">
                                                    Trash
                                                </a> |
                                            </span>
                                            <span class="clear-cache">
                                                <a href="<?php echo esc_url($clear_url); ?>">Clear Cache</a>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <code class="ff-group-key" data-key="<?php echo esc_attr($group_key); ?>">
                                        <?php echo esc_html($group_key); ?>
                                    </code>
                                    <button
                                        type="button"
                                        class="button-link ff-copy-key"
                                        data-key="<?php echo esc_attr($group_key); ?>"
                                        aria-label="Copy key"
                                        title="Copy to clipboard">
                                        <span class="screen-reader-text">Copy</span>
                                    </button>
                                </td>

                                <td>
                                    <?php
                                    $location_target = isset($group['location_target']) ? (string) $group['location_target'] : '';

                                    if ($location === 'page') {
                                        $label = 'Page';

                                        if ($location_target !== '') {
                                            $target_post = get_post((int) $location_target);
                                            $target_text = $target_post ? get_the_title($target_post) : 'Missing Page';
                                        } else {
                                            $target_text = 'All Pages';
                                        }

                                        echo '<strong>' . esc_html($label) . '</strong> ';
                                        echo '<span class="ff-location-target">(' . esc_html($target_text) . ')</span>';
                                    } elseif ($location === 'post') {
                                        $label = 'Post';

                                        if ($location_target !== '') {
                                            $target_post = get_post((int) $location_target);
                                            $target_text = $target_post ? get_the_title($target_post) : 'Missing Post';
                                        } else {
                                            $target_text = 'All Posts';
                                        }

                                        echo '<strong>' . esc_html($label) . '</strong> ';
                                        echo '<span class="ff-location-target">(' . esc_html($target_text) . ')</span>';
                                    } elseif ($location === 'global') {
                                        echo '<strong>Global</strong>';
                                    } else {
                                        echo esc_html(ucfirst((string) $location));
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    $count_html = '<span class="ff-chip" title="Fields">' . intval($field_cnt) . '</span>';
                                    if ($current_view === 'trash') {
                                        echo $count_html;
                                    } else {
                                        echo '<a href="' . esc_url($edit_url) . '" class="ff-count-link">' . $count_html . '</a>';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    $status_key   = strtolower($status);
                                    $status_label = ($status_key === 'active') ? 'Active' : ($status_key === 'inactive' ? 'Inactive' : ucfirst($status_key));
                                    $status_class = 'ff-status ff-status--' . esc_attr($status_key);
                                    echo '<span class="' . $status_class . '">' . esc_html($status_label) . '</span>';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="ff-list-bar">
                        <?php $render_header_row(); ?>
                    </tfoot>
                </table>
                <?php
                $render_bulk('bottom');
                ?>
            </form>
        <?php endif; ?>
    </div>
<?php
}

/**
 * Render a single Global Fields settings row.
 *
 * Chooses the appropriate control based on the field type and uses the
 * currently stored global value when available.
 *
 * @param array $field  Global field definition.
 * @param array $stored Stored global field values.
 */
function ff_render_global_field_row(array $field, array $stored)
{
    if (empty($field['name'])) {
        return;
    }

    $name  = $field['name'];
    $label = isset($field['label']) ? $field['label'] : $name;
    $type  = isset($field['type'])  ? $field['type']  : 'text';

    $id    = 'ff_global_' . esc_attr($name);
    $value = isset($stored[$name]) ? $stored[$name] : '';
?>
    <tr>
        <th scope="row">
            <label for="<?php echo $id; ?>">
                <?php echo esc_html($label); ?>
            </label>
        </th>
        <td>
            <?php
            switch ($type) {
                case 'textarea':
                    printf(
                        '<textarea name="ff_global[%1$s]" id="%2$s" rows="3" class="large-text">%3$s</textarea>',
                        esc_attr($name),
                        esc_attr($id),
                        esc_textarea($value)
                    );
                    break;

                case 'number':
                    printf(
                        '<input type="number" name="ff_global[%1$s]" id="%2$s" value="%3$s" class="regular-text" />',
                        esc_attr($name),
                        esc_attr($id),
                        esc_attr($value)
                    );
                    break;

                case 'range':
                    $min  = isset($field['min'])  ? (int) $field['min']  : 0;
                    $max  = isset($field['max'])  ? (int) $field['max']  : 100;
                    $step = isset($field['step']) ? (int) $field['step'] : 1;
                    $val  = ($value === '' ? $min : (int) $value);

                    $slider_id = $id . '_slider';
                    $num_id    = $id . '_num';
            ?>
                    <div class="ff-range-wrap">
                        <input
                            type="range"
                            class="ff-range-slider"
                            id="<?php echo esc_attr($slider_id); ?>"
                            name="ff_global[<?php echo esc_attr($name); ?>]"
                            min="<?php echo esc_attr($min); ?>"
                            max="<?php echo esc_attr($max); ?>"
                            step="<?php echo esc_attr($step); ?>"
                            value="<?php echo esc_attr($val); ?>"
                            data-target="#<?php echo esc_attr($num_id); ?>" />
                        <input
                            type="number"
                            class="small-text ff-range-number"
                            id="<?php echo esc_attr($num_id); ?>"
                            min="<?php echo esc_attr($min); ?>"
                            max="<?php echo esc_attr($max); ?>"
                            step="<?php echo esc_attr($step); ?>"
                            value="<?php echo esc_attr($val); ?>"
                            data-target="#<?php echo esc_attr($slider_id); ?>" />
                    </div>
                <?php
                    break;

                case 'password':
                    echo '<div class="ff-password-wrap">';
                    echo '<input type="password" name="ff_global[' . esc_attr($name) . ']" id="' . esc_attr($id) . '" value="' . esc_attr((string) $value) . '" class="regular-text ff-password-input" maxlength="45" autocomplete="off" />';
                    echo '<button type="button" class="ff-password-toggle" data-target="#' . esc_attr($id) . '" aria-label="Show password" aria-controls="' . esc_attr($id) . '">';
                    echo '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>';
                    echo '</button>';
                    echo '</div>';
                    break;

                case 'email':
                case 'url':
                case 'text':
                    $input_type = in_array($type, ['email', 'url'], true) ? $type : 'text';
                    printf(
                        '<input type="%4$s" name="ff_global[%1$s]" id="%2$s" value="%3$s" class="regular-text" />',
                        esc_attr($name),
                        esc_attr($id),
                        esc_attr((string) $value),
                        esc_attr($input_type)
                    );
                    break;

                case 'wysiwyg':
                    echo '<div class="ff-editor-card">';
                    wp_editor(
                        is_string($value) ? $value : '',
                        $id,
                        [
                            'textarea_name' => "ff_global[$name]",
                            'textarea_rows' => 12,
                            'media_buttons' => true,
                            'tinymce'       => [
                                'branding'  => false,
                                'menubar'   => false,
                                'statusbar' => false,
                                'toolbar1'  => 'formatselect,bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,alignleft,aligncenter,alignright,|,removeformat',
                                'toolbar2'  => '',
                            ],
                            'quicktags'     => true,
                            'editor_height' => 260,
                            'editor_class'  => 'ff-editor',
                        ]
                    );
                    echo '</div>';
                    break;

                case 'image':
                    $img_src = '';
                    if ($value) {
                        $src = wp_get_attachment_image_src((int) $value, 'thumbnail');
                        if ($src) {
                            $img_src = $src[0];
                        }
                    }
                ?>
                    <div class="ff-media-wrap" data-type="image">
                        <input type="hidden" name="ff_global[<?php echo esc_attr($name); ?>]" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>">
                        <div class="ff-media-preview-wrap" style="margin-bottom:8px;">
                            <img class="ff-media-preview" src="<?php echo esc_url($img_src); ?>" style="<?php echo $img_src ? '' : 'display:none;'; ?>max-height:80px;border-radius:4px;">
                        </div>
                        <button type="button" class="button ff-media-select" data-target="<?php echo esc_attr($id); ?>">Select Image</button>
                        <button type="button" class="button ff-media-clear" data-target="<?php echo esc_attr($id); ?>" style="<?php echo $value ? '' : 'display:none;'; ?>">Clear</button>
                    </div>
                <?php
                    break;

                case 'file':
                    $file_url  = $value ? wp_get_attachment_url((int) $value) : '';
                    $file_name = $file_url ? wp_basename($file_url) : '';
                ?>
                    <div class="ff-media-wrap" data-type="file">
                        <input type="hidden" name="ff_global[<?php echo esc_attr($name); ?>]" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>">
                        <div class="ff-media-fileline" style="margin-bottom:8px;">
                            <span class="dashicons dashicons-media-document" aria-hidden="true"></span>
                            <a class="ff-media-fileurl" href="<?php echo esc_url($file_url); ?>" target="_blank" style="<?php echo $file_url ? '' : 'display:none;'; ?>"><?php echo esc_html($file_name); ?></a>
                            <span class="ff-media-nofile" style="<?php echo $file_url ? 'display:none;' : ''; ?>">No file selected.</span>
                        </div>
                        <button type="button" class="button ff-media-select" data-target="<?php echo esc_attr($id); ?>">Select File</button>
                        <button type="button" class="button ff-media-clear" data-target="<?php echo esc_attr($id); ?>" style="<?php echo $value ? '' : 'display:none;'; ?>">Clear</button>
                    </div>
            <?php
                    break;

                case 'select':
                    $choices_map = ff_parse_choices_string($field['choices'] ?? '');
                    $current     = is_scalar($value) ? (string) $value : '';
                    echo '<div class="ff-select-wrap">';
                    echo '<select name="ff_global[' . esc_attr($name) . ']" id="' . esc_attr($id) . '">';
                    foreach ($choices_map as $v => $lbl) {
                        printf(
                            '<option value="%1$s"%3$s>%2$s</option>',
                            esc_attr($v),
                            esc_html($lbl),
                            selected($current, (string) $v, false)
                        );
                    }
                    echo '</select>';
                    echo '</div>';
                    break;

                case 'radio':
                    $choices_map = ff_parse_choices_string($field['choices'] ?? '');
                    $current     = is_scalar($value) ? (string) $value : '';
                    foreach ($choices_map as $v => $lbl) {
                        $field_id = $id . '_' . sanitize_key((string) $v);
                        printf(
                            '<label for="%1$s" style="display:inline-block;margin-right:12px;">
                                <input type="radio" name="ff_global[%2$s]" id="%1$s" value="%3$s" %4$s>
                                %5$s
                            </label>',
                            esc_attr($field_id),
                            esc_attr($name),
                            esc_attr($v),
                            checked($current, (string) $v, false),
                            esc_html($lbl)
                        );
                    }
                    break;

                case 'button_group':
                    $choices_map = ff_parse_choices_string($field['choices'] ?? '');
                    $current     = is_scalar($value) ? (string) $value : '';
                    echo '<div class="ff-button-group" role="radiogroup">';
                    foreach ($choices_map as $v => $lbl) {
                        $field_id = $id . '_' . sanitize_key((string) $v);
                        printf(
                            '<label class="ff-button-group__btn" for="%1$s">
                                <input class="ff-button-group__input" type="radio" name="ff_global[%2$s]" id="%1$s" value="%3$s" %4$s>
                                <span class="ff-button-group__label">%5$s</span>
                            </label>',
                            esc_attr($field_id),
                            esc_attr($name),
                            esc_attr($v),
                            checked($current, (string) $v, false),
                            esc_html($lbl)
                        );
                    }
                    echo '</div>';
                    break;

                case 'checkbox':
                    $choices_map = ff_parse_choices_string($field['choices'] ?? '');
                    $current     = is_array($value) ? array_map('strval', $value) : [];
                    foreach ($choices_map as $v => $lbl) {
                        $field_id = $id . '_' . sanitize_key((string) $v);
                        printf(
                            '<label for="%1$s" style="display:inline-block;margin-right:12px;">
                                <input type="checkbox" name="ff_global[%2$s][]" id="%1$s" value="%3$s" %4$s>
                                %5$s
                            </label>',
                            esc_attr($field_id),
                            esc_attr($name),
                            esc_attr($v),
                            in_array((string) $v, $current, true) ? 'checked' : '',
                            esc_html($lbl)
                        );
                    }
                    break;

                case 'true_false':
                    $checked = ! empty($value);

                    echo '<div class="ff-true-false-control">';

                    echo '<span class="ff-true-false-label">False</span>';

                    echo '<label class="ff-toggle-field">';

                    echo '<input
        type="checkbox"
        name="ff_global[' . esc_attr($name) . ']"
        id="' . esc_attr($id) . '"
        value="1"'
                        . checked($checked, true, false)
                        . '>';

                    echo '<span class="ff-toggle" aria-hidden="true"></span>';

                    echo '</label>';

                    echo '<span class="ff-true-false-label">True</span>';

                    echo '</div>';

                    break;
            }
            ?>
        </td>
    </tr>
<?php
}

/**
 * Render and process the Global Fields settings page.
 *
 * Loads all global field-group definitions, validates and saves submitted
 * global values, records the last-saved timestamp, and renders each field.
 */
function ff_render_global_options_page()
{

    if (! current_user_can('manage_options')) {
        return;
    }

    $groups        = ff_get_all_groups();
    $global_groups = [];

    foreach ($groups as $group_id => $group) {
        $status   = isset($group['status']) ? $group['status'] : 'active';
        $location = isset($group['location']) ? $group['location'] : 'page';

        if ($status === 'trash') {
            continue;
        }

        if ($location === 'global') {
            $global_groups[$group_id] = $group;
        }
    }

    $stored = get_option('ff_global_fields', []);
    if (! is_array($stored)) {
        $stored = [];
    }

    $notices = [];

    if (isset($_POST['ff_save_global'])) {

        check_admin_referer('ff_save_global');

        $raw = isset($_POST['ff_global']) && is_array($_POST['ff_global'])
            ? $_POST['ff_global']
            : [];

        $type_map = [];
        foreach ($global_groups as $group) {
            if (empty($group['fields']) || ! is_array($group['fields'])) {
                continue;
            }
            foreach ($group['fields'] as $f) {
                if (empty($f['name'])) {
                    continue;
                }
                $type_map[$f['name']] = isset($f['type']) ? $f['type'] : 'text';
            }
        }

        $new_values = [];

        foreach ($raw as $field_name => $value_raw) {
            $name = sanitize_key($field_name);

            if ($name === '' || ! isset($type_map[$name])) {
                continue;
            }

            $type = $type_map[$name];

            switch ($type) {
                case 'number':
                    $new_values[$name] = is_array($value_raw) ? 0 : floatval($value_raw);
                    break;

                case 'range':
                    $new_values[$name] = is_array($value_raw) ? 0 : intval($value_raw);
                    break;

                case 'email':
                    $new_values[$name] = is_array($value_raw) ? '' : sanitize_email($value_raw);
                    break;

                case 'url':
                    $new_values[$name] = is_array($value_raw) ? '' : esc_url_raw($value_raw);
                    break;

                case 'password':
                    $new_values[$name] = is_array($value_raw)
                        ? ''
                        : ff_sanitize_type_password(wp_unslash($value_raw), ['name' => $name, 'type' => 'password'], 0);
                    break;

                case 'wysiwyg':
                    $new_values[$name] = is_array($value_raw)
                        ? ''
                        : wp_kses_post(
                            wp_unslash($value_raw)
                        );
                    break;

                case 'textarea':
                    $new_values[$name] = is_array($value_raw)
                        ? ''
                        : sanitize_textarea_field(
                            wp_unslash($value_raw)
                        );
                    break;

                case 'text':
                    $new_values[$name] = is_array($value_raw)
                        ? ''
                        : sanitize_text_field(
                            wp_unslash($value_raw)
                        );
                    break;

                case 'image':
                case 'file':
                    $new_values[$name] = is_array($value_raw) ? 0 : absint($value_raw);
                    break;

                case 'select':
                case 'radio':
                case 'button_group':
                    $new_values[$name] = is_array($value_raw) ? '' : sanitize_key((string) $value_raw);
                    break;

                case 'checkbox':
                    if (is_array($value_raw)) {

                        $raw_values = wp_unslash($value_raw);

                        $vals = array_map(
                            static function ($v) {
                                return sanitize_key((string) $v);
                            },
                            $raw_values
                        );

                        $vals = array_values(array_filter($vals, static fn($v) => $v !== ''));
                        $new_values[$name] = $vals;
                    } else {
                        $new_values[$name] = [];
                    }
                    break;

                case 'true_false':
                    $new_values[$name] = empty($value_raw) ? 0 : 1;
                    break;

                default:
                    $new_values[$name] = is_array($value_raw) ? '' : sanitize_text_field(wp_unslash($value_raw));
                    break;
            }
        }

        update_option('ff_global_fields', $new_values);
        update_option(
            'ff_global_fields_last_saved',
            time()
        );
        $stored  = $new_values;
        $notices[] = [
            'type'    => 'updated',
            'message' => 'Global fields saved.',
        ];
    }


?>
    <div class="wrap ff-admin ff-global ff-has-brandbar">
        <?php foreach ($notices as $notice) :
            $cls = 'notice inline';
            switch ($notice['type'] ?? '') {
                case 'error':
                    $cls .= ' notice-error';
                    break;
                case 'updated':
                    $cls .= ' notice-success';
                    break;
                case 'warning':
                    $cls .= ' notice-warning';
                    break;
                default:
                    $cls .= ' notice-info';
                    break;
            }
        ?>
            <div class="<?php echo esc_attr($cls); ?> is-dismissible">
                <p><?php echo esc_html($notice['message']); ?></p>
            </div>
        <?php endforeach; ?>

        <?php if (empty($global_groups)) : ?>
            <p>No global fields have been created yet. Create a field group and set its location to Global to get started.
            </p>
        <?php else : ?>
            <form method="post" action="" id="ff-global-form">
                <?php wp_nonce_field('ff_save_global'); ?>

                <?php foreach ($global_groups as $group_id => $group) :

                    $title  = ! empty($group['title']) ? $group['title'] : '(no title)';
                    $fields = isset($group['fields']) && is_array($group['fields'])
                        ? $group['fields']
                        : [];
                ?>

                    <h2><?php echo esc_html($title); ?></h2>

                    <?php if (empty($fields)) : ?>
                        <p><em>No fields defined in this group.</em></p>
                    <?php else : ?>
                        <?php
                        $sections = ff_group_fields_into_tab_sections($fields);
                        $has_tabs = count($sections) > 1 || (count($sections) === 1 && $sections[0]['label'] !== '');
                        ?>
                        <div class="ff-options-card">
                            <?php if ($has_tabs) : ?>
                                <div class="ff-tabs" data-ff-tabs>
                                    <div class="ff-tab-nav">
                                        <?php foreach ($sections as $index => $section) : ?>
                                            <button
                                                type="button"
                                                class="ff-tab-button<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                                data-ff-tab="<?php echo esc_attr($index); ?>">
                                                <?php echo esc_html($section['label'] ?: 'General'); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php foreach ($sections as $index => $section) : ?>
                                        <div
                                            class="ff-tab-panel<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                            data-ff-tab-panel="<?php echo esc_attr($index); ?>">
                                            <table class="form-table" role="presentation">
                                                <tbody>
                                                    <?php foreach ($section['fields'] as $field) : ?>
                                                        <?php ff_render_global_field_row($field, $stored); ?>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <table class="form-table" role="presentation">
                                    <tbody>
                                        <?php foreach ($fields as $field) : ?>
                                            <?php ff_render_global_field_row($field, $stored); ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php endforeach; ?>

            </form>
        <?php endif; ?>
    </div>
<?php
}

function ff_normalize_choices_textarea($raw_text)
{
    $warnings = [];
    $errors   = [];

    $lines = preg_split('/\r\n|\r|\n/', (string) $raw_text);
    $out_lines = [];
    $seen_values = [];

    foreach ($lines as $i => $line) {
        $original = trim((string) $line);
        if ($original === '') {
            continue;
        }

        $has_pipe  = (strpos($original, '|') !== false);
        $has_colon = (strpos($original, ':') !== false);

        if ($has_pipe) {
            list($value_raw, $label_raw) = array_map('trim', explode('|', $original, 2));
        } elseif ($has_colon) {
            list($value_raw, $label_raw) = array_map('trim', explode(':', $original, 2));
        } else {
            $value_raw = $original;
            $label_raw = '';
        }

        $value_sanitized = sanitize_key($value_raw);

        if (($has_pipe || $has_colon) && $value_sanitized === '') {
            $errors[] = sprintf(
                'Line %d: Invalid choice value "%s". Use letters/numbers/underscores/dashes before the ":" or "|".',
                $i + 1,
                $value_raw
            );
            continue;
        }

        if (! $has_pipe && ! $has_colon && $value_sanitized === '') {
            $errors[] = sprintf(
                'Line %d: Invalid choice "%s". Use at least one letter or number.',
                $i + 1,
                $original
            );
            continue;
        }

        if ($value_raw !== $value_sanitized) {
            $warnings[] = sprintf(
                'Line %d: Value "%s" was cleaned to "%s".',
                $i + 1,
                $value_raw,
                $value_sanitized
            );
        }

        $label_clean = sanitize_text_field((string) $label_raw);
        if (($has_pipe || $has_colon) && $label_clean === '') {
            $label_clean = $value_sanitized;
        }

        if (isset($seen_values[$value_sanitized])) {
            $errors[] = sprintf(
                'Line %d: Duplicate choice value "%s". Each choice value must be unique.',
                $i + 1,
                $value_sanitized
            );
            continue;
        }
        $seen_values[$value_sanitized] = true;

        if (! $has_pipe && ! $has_colon) {
            $out_lines[] = $value_sanitized;
        } else {
            $out_lines[] = $value_sanitized . ' : ' . $label_clean;
        }
    }

    return [implode("\n", $out_lines), $warnings, $errors];
}

/**
 * Replace the normal Forge Fields Deactivate action with a controlled
 * deactivation flow.
 *
 * This allows administrators to explicitly choose whether Forge Fields
 * data should be preserved or permanently removed before deactivation.
 */
add_filter(
    'plugin_action_links_forge-fields/forge-fields.php',
    'ff_plugin_action_links'
);

function ff_plugin_action_links($actions)
{
    if (isset($actions['deactivate'])) {

        $deactivate_url = wp_nonce_url(
            admin_url('admin.php?page=forge-fields-deactivate'),
            'ff_deactivate_plugin'
        );

        $actions['deactivate'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url($deactivate_url),
            esc_html__('Deactivate', 'forge-fields')
        );
    }

    return $actions;
}


/**
 * Render the Forge Fields deactivation confirmation screen.
 */
function ff_render_deactivate_page()
{
    if (! current_user_can('activate_plugins')) {
        wp_die(
            esc_html__(
                'You do not have permission to deactivate plugins.',
                'forge-fields'
            )
        );
    }

    check_admin_referer('ff_deactivate_plugin');
?>
    <div class="wrap">
        <h1>Deactivate Forge Fields</h1>

        <p>
            Choose what should happen to your Forge Fields data
            before the plugin is deactivated.
        </p>

        <p>
            <strong>Keep Data</strong> preserves your field groups,
            global values, and saved post/page values so they are
            available if Forge Fields is activated again.
        </p>

        <p>
            <strong>Delete All Data</strong> permanently removes all
            Forge Fields data from the database. This cannot be undone.
        </p>

        <form method="post">
            <?php
            wp_nonce_field(
                'ff_confirm_deactivate',
                'ff_deactivate_nonce'
            );
            ?>

            <p class="submit">
                <button
                    type="submit"
                    name="ff_deactivate_choice"
                    value="keep"
                    class="button button-primary">
                    Keep Data &amp; Deactivate
                </button>

                <button
                    type="submit"
                    name="ff_deactivate_choice"
                    value="delete"
                    class="button">
                    Delete All Data &amp; Deactivate
                </button>

                <a
                    href="<?php echo esc_url(admin_url('plugins.php')); ?>"
                    class="button">
                    Cancel
                </a>
            </p>
        </form>
    </div>
<?php
}


/**
 * Handle the confirmed Forge Fields deactivation request.
 */
add_action('admin_init', 'ff_handle_plugin_deactivation');

function ff_handle_plugin_deactivation()
{
    $page = isset($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    if ($page !== 'forge-fields-deactivate') {
        return;
    }

    if (! isset($_POST['ff_deactivate_choice'])) {
        return;
    }

    if (! current_user_can('activate_plugins')) {
        wp_die(
            esc_html__(
                'You do not have permission to deactivate plugins.',
                'forge-fields'
            )
        );
    }

    check_admin_referer(
        'ff_confirm_deactivate',
        'ff_deactivate_nonce'
    );

    $choice = sanitize_key(
        wp_unslash($_POST['ff_deactivate_choice'])
    );

    if (! in_array($choice, ['keep', 'delete'], true)) {
        wp_die(
            esc_html__(
                'Invalid Forge Fields deactivation choice.',
                'forge-fields'
            )
        );
    }

    /**
     * Preserve data unless permanent removal was explicitly selected.
     */
    if ($choice === 'keep') {

        update_option(
            'ff_delete_data_on_uninstall',
            0
        );
    }

    /**
     * Permanently remove Forge Fields database data before
     * deactivating the plugin.
     */
    if ($choice === 'delete') {

        delete_option('ff_field_groups');
        delete_option('ff_global_fields');

        /**
         * Legacy Forge Fields option.
         */
        delete_option('ff_field_group');

        /**
         * Remove all Forge Fields post/page metadata.
         */
        global $wpdb;

        $meta_key_pattern =
            $wpdb->esc_like('_ff_') . '%';

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta}
                 WHERE meta_key LIKE %s",
                $meta_key_pattern
            )
        );

        delete_option(
            'ff_delete_data_on_uninstall'
        );
    }

    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    deactivate_plugins(
        'forge-fields/forge-fields.php',
        false,
        false
    );

    wp_safe_redirect(
        admin_url('plugins.php?deactivate=true')
    );

    exit;
}

add_action('admin_init', 'ff_handle_plugin_uninstall');

/**
 * Handle the confirmed Forge Fields uninstall request.
 *
 * Verifies permissions and nonce, stores the user's data-removal choice,
 * deactivates the plugin if necessary, and invokes WordPress plugin deletion.
 */
function ff_handle_plugin_uninstall()
{

    $page = isset($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    if ($page !== 'forge-fields-uninstall') {
        return;
    }

    if (empty($_POST['ff_confirm_delete'])) {
        return;
    }

    if (! current_user_can('delete_plugins')) {
        wp_die(esc_html__('You do not have permission to delete plugins.', 'forge-fields'));
    }

    check_admin_referer(
        'ff_confirm_uninstall',
        'ff_uninstall_nonce'
    );

    update_option(
        'ff_delete_data_on_uninstall',
        isset($_POST['ff_delete_plugin_data']) ? 1 : 0
    );

    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $plugin = 'forge-fields/forge-fields.php';

    if (is_plugin_active($plugin)) {
        deactivate_plugins($plugin, false, false);
    }

    $result = delete_plugins([$plugin]);

    if (is_wp_error($result)) {
        wp_die(
            esc_html($result->get_error_message())
        );
    }

    wp_safe_redirect(
        admin_url('plugins.php?deleted=true')
    );

    exit;
}

/**
 * Render the Add/Edit Field Group screen.
 *
 * Loads an existing group when editing, restores validation errors when
 * present, prepares location targets and field definitions, and renders
 * the field-group editor interface.
 */
function ff_render_field_group_edit()
{

    if (! current_user_can('manage_options')) {
        return;
    }

    $groups   = ff_get_all_groups();
    $group_id = isset($_GET['group']) ? sanitize_text_field(wp_unslash($_GET['group'])) : '';
    $is_new   = true;
    $notices  = [];

    $ff_other_group_field_names = [
        'global'     => [],
        'non_global' => [],
    ];

    foreach ($groups as $existing_group_id => $existing_group) {

        if ((string) $existing_group_id === (string) $group_id) {
            continue;
        }

        if (! is_array($existing_group)) {
            continue;
        }

        $existing_status = isset($existing_group['status'])
            ? (string) $existing_group['status']
            : 'active';

        /**
         * Trashed groups are not active naming conflicts.
         */
        if ($existing_status === 'trash') {
            continue;
        }

        if (
            empty($existing_group['fields'])
            || ! is_array($existing_group['fields'])
        ) {
            continue;
        }

        $existing_location = isset($existing_group['location'])
            ? sanitize_key((string) $existing_group['location'])
            : 'page';

        $scope = $existing_location === 'global'
            ? 'global'
            : 'non_global';

        foreach ($existing_group['fields'] as $existing_field) {

            $existing_name = isset($existing_field['name'])
                ? sanitize_key((string) $existing_field['name'])
                : '';

            if ($existing_name === '') {
                continue;
            }

            $ff_other_group_field_names[$scope][$existing_name] = true;
        }
    }

    $ff_other_group_field_names['global'] = array_keys(
        $ff_other_group_field_names['global']
    );

    $ff_other_group_field_names['non_global'] = array_keys(
        $ff_other_group_field_names['non_global']
    );

    if (isset($_GET['ff_notice'])) {

        $notice_code = sanitize_key(
            wp_unslash($_GET['ff_notice'])
        );

        if ($notice_code === 'saved') {
            $notices[] = [
                'type'    => 'updated',
                'message' => 'Field group saved.',
            ];
        }

        if ($notice_code === 'save_error') {

            $error_key = 'ff_save_errors_' . get_current_user_id();

            $saved_errors = get_transient($error_key);

            if (is_array($saved_errors)) {

                foreach ($saved_errors as $message) {
                    $notices[] = [
                        'type'    => 'error',
                        'message' => $message,
                    ];
                }
            }

            delete_transient($error_key);
        }
    }

    $group = [
        'id'       => '',
        'title'    => '',
        'location' => 'page',
        'location_target' => '',
        'fields'   => [],
        'status'   => 'active',
    ];

    if ($group_id && isset($groups[$group_id]) && is_array($groups[$group_id])) {
        $group   = $groups[$group_id];
        $is_new  = false;
    }

    $title           = isset($group['title']) ? $group['title'] : '';
    $location        = isset($group['location']) ? $group['location'] : 'page';
    $location_target = isset($group['location_target']) ? (string) $group['location_target'] : '';
    $fields          = isset($group['fields']) && is_array($group['fields']) ? $group['fields'] : [];

    $page_options = get_posts([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    $post_options = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $type_groups = [
        'Basic' => ['text', 'textarea', 'number', 'email', 'url', 'range', 'password'],
        'Content' => ['image', 'file', 'wysiwyg'],
        'Choice'  => ['select', 'checkbox', 'radio', 'button_group', 'true_false'],
        'Layout' => ['tab'],
    ];

?>
    <div class="wrap ff-admin ff-edit ff-has-brandbar">


        <?php foreach ($notices as $n) :
            $cls = 'notice inline';
            switch ($n['type'] ?? '') {
                case 'error':
                    $cls .= ' notice-error';
                    break;
                case 'updated':
                    $cls .= ' notice-success';
                    break;
                case 'warning':
                    $cls .= ' notice-warning';
                    break;
                default:
                    $cls .= ' notice-info';
                    break;
            }
        ?>
            <div class="<?php echo esc_attr($cls); ?> is-dismissible">
                <p><?php echo esc_html($n['message']); ?></p>
            </div>
        <?php endforeach; ?>


        <form method="post" action="" id="ff-edit-form">
            <script>
                window.ffFieldNameRegistry = <?php
                                                echo wp_json_encode(
                                                    $ff_other_group_field_names
                                                );
                                                ?>;
            </script>
            <?php wp_nonce_field('ff_save_field_group'); ?>
            <input type="hidden" name="ff_group_id"
                value="<?php echo esc_attr(isset($group['id']) ? $group['id'] : ''); ?>">

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="ff_group_title">Group Title</label></th>
                    <td>
                        <input type="text"
                            id="ff_group_title"
                            name="ff_group_title"
                            class="regular-text"
                            maxlength="75"
                            value="<?php echo esc_attr($title); ?>">
                        <p class="description">Add a descriptive title for this field group.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="ff_location">Location</label></th>
                    <td>
                        <div class="ff-location-row" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                            <div class="ff-select-wrap">
                                <select id="ff_location" name="ff_location">
                                    <option value="page" <?php selected($location, 'page'); ?>>Page</option>
                                    <option value="post" <?php selected($location, 'post'); ?>>Post</option>
                                    <option value="global" <?php selected($location, 'global'); ?>>Global</option>
                                </select>
                            </div>

                            <div class="ff-select-wrap" id="ff_location_target_page_wrap" <?php echo $location === 'page' ? '' : 'style="display:none;"'; ?>>
                                <select id="ff_location_target_page" name="ff_location_target_page">
                                    <option value="">All Pages</option>
                                    <?php foreach ($page_options as $page_post) : ?>
                                        <option
                                            value="<?php echo esc_attr($page_post->ID); ?>"
                                            <?php selected($location === 'page' ? $location_target : '', (string) $page_post->ID); ?>>
                                            <?php echo esc_html(get_the_title($page_post) ?: '(no title)'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="ff-select-wrap" id="ff_location_target_post_wrap" <?php echo $location === 'post' ? '' : 'style="display:none;"'; ?>>
                                <select id="ff_location_target_post" name="ff_location_target_post">
                                    <option value="">All Posts</option>
                                    <?php foreach ($post_options as $single_post) : ?>
                                        <option
                                            value="<?php echo esc_attr($single_post->ID); ?>"
                                            <?php selected($location === 'post' ? $location_target : '', (string) $single_post->ID); ?>>
                                            <?php echo esc_html(get_the_title($single_post) ?: '(no title)'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <p class="description">
                            Choose whether this field group appears on all pages/posts, or only one specific item. Global affects site-wide.
                        </p>
                    </td>
                </tr>

                <?php if (! empty($group_id)) : ?>
                    <tr class="ff-group-key-row">
                        <th scope="row">
                            Forge Group Key
                        </th>

                        <td>
                            <div class="ff-edit-group-key">
                                <code
                                    class="ff-group-key"
                                    data-key="<?php echo esc_attr($group_id); ?>">
                                    <?php echo esc_html($group_id); ?>
                                </code>

                                <button
                                    type="button"
                                    class="button-link ff-copy-key"
                                    data-key="<?php echo esc_attr($group_id); ?>"
                                    aria-label="Copy Forge Key"
                                    title="Copy to clipboard">
                                    <span class="screen-reader-text">Copy</span>
                                </button>

                                <span class="ff-edit-group-key__help">
                                    Use with <code>ff_get_field()</code> when field names exist in multiple Field Groups.
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>

            <h2>Fields</h2>
            <p>Define your fields (label, name, type). Add or remove rows as needed.</p>


            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:30px;"></th>

                        <th style="width:42px;" class="ff-field-check-column">
                            <input
                                type="checkbox"
                                id="ff-select-all-fields"
                                class="ff-select-all-fields"
                                aria-label="Select all fields">
                        </th>

                        <th class="ff-bar-title">Label</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th style="width:150px;"></th>
                    </tr>
                </thead>

                <tbody id="ff-fields-body">
                    <?php
                    if (empty($fields)) {
                        $fields = [
                            [
                                'label' => '',
                                'name'  => '',
                                'type'  => 'text',
                            ],
                        ];
                    }

                    foreach ($fields as $index => $field) :
                        $label = isset($field['label']) ? $field['label'] : '';
                        $name  = isset($field['name'])  ? $field['name']  : '';
                        $type  = isset($field['type'])  ? $field['type']  : 'text';

                        $default_value = isset($field['default_value'])
                            ? (string) $field['default_value']
                            : '';

                        $required = ! empty($field['required']);

                        $character_limit = isset($field['character_limit'])
                            ? absint($field['character_limit'])
                            : 0;

                        $prepend = isset($field['prepend'])
                            ? (string) $field['prepend']
                            : '';

                        $append = isset($field['append'])
                            ? (string) $field['append']
                            : '';
                    ?>
                        <?php
                        $choice_types = ['select', 'checkbox', 'radio', 'button_group'];
                        $choices_raw  = isset($field['choices']) ? (string) $field['choices'] : '';
                        $is_choice    = in_array($type, $choice_types, true);
                        ?>
                        <tr class="ff-field-row" data-index="<?php echo esc_attr($index); ?>">
                            <td class="ff-field-handle" aria-label="Drag" title="Drag"></td>

                            <td class="ff-field-check-column">
                                <input
                                    type="checkbox"
                                    class="ff-field-select"
                                    aria-label="Select field">
                            </td>

                            <td>
                                <input type="text"
                                    name="ff_fields[<?php echo $index; ?>][label]"
                                    value="<?php echo esc_attr($label); ?>"
                                    class="regular-text ff-field-label"
                                    maxlength="50"
                                    data-index="<?php echo esc_attr($index); ?>"
                                    data-field-part="label">
                            </td>

                            <td>
                                <input type="text"
                                    name="ff_fields[<?php echo $index; ?>][name]"
                                    value="<?php echo esc_attr($name); ?>"
                                    class="regular-text ff-field-name"
                                    maxlength="50"
                                    data-index="<?php echo esc_attr($index); ?>"
                                    data-field-part="name">
                            </td>

                            <td>
                                <div class="ff-select-wrap">
                                    <select name="ff_fields[<?php echo $index; ?>][type]" class="ff-field-type" data-field-part="type">
                                        <?php foreach ($type_groups as $group_label => $opts) : ?>
                                            <optgroup label="<?php echo esc_attr($group_label); ?>">
                                                <?php foreach ($opts as $t) : ?>
                                                    <?php
                                                    $pretty = [
                                                        'text'     => 'Text',
                                                        'textarea' => 'Textarea',
                                                        'number'   => 'Number',
                                                        'email'    => 'Email',
                                                        'url'      => 'URL',
                                                        'range'    => 'Range',
                                                        'password' => 'Password',
                                                        'image'    => 'Image',
                                                        'file'     => 'File',
                                                        'wysiwyg'  => 'WYSIWYG Editor',
                                                        'select'  => 'Select',
                                                        'checkbox'  => 'Checkbox',
                                                        'radio'  => 'Radio',
                                                        'button_group'  => 'Button Group',
                                                        'true_false'  => 'True/False',
                                                        'tab'          => 'Tab',
                                                    ];
                                                    ?>
                                                    <option value="<?php echo esc_attr($t); ?>" <?php selected($type, $t); ?>>
                                                        <?php echo esc_html($pretty[$t] ?? ucfirst($t)); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>

                            <td class="ff-field-actions">
                                <button
                                    type="button"
                                    class="button-link ff-field-options-toggle"
                                    aria-expanded="false">
                                    Options
                                </button>

                                <span class="ff-field-action-separator">|</span>

                                <a href="#" class="ff-field-remove">
                                    Remove
                                </a>
                            </td>
                        </tr>
                        <tr
                            class="ff-field-settings is-hidden"
                            data-index="<?php echo esc_attr($index); ?>"
                            data-ff-settings
                            style="display:none;">

                            <td colspan="6">

                                <div class="ff-field-options-panel">

                                    <div class="ff-field-options-header">
                                        <strong>Field Options</strong>
                                    </div>

                                    <div class="ff-field-options-grid">

                                        <div
                                            class="ff-field-option ff-option-default"
                                            data-ff-option="default">

                                            <label
                                                for="ff-default-value-<?php echo esc_attr($index); ?>">
                                                Default Value
                                            </label>

                                            <input
                                                type="text"
                                                id="ff-default-value-<?php echo esc_attr($index); ?>"
                                                name="ff_fields[<?php echo esc_attr($index); ?>][default_value]"
                                                value="<?php echo esc_attr($default_value); ?>"
                                                class="regular-text">

                                            <p class="description">
                                                Used when no value has been saved yet.
                                            </p>

                                        </div>

                                        <div
                                            class="ff-field-option ff-option-character-limit"
                                            data-ff-option="character-limit">

                                            <label
                                                for="ff-character-limit-<?php echo esc_attr($index); ?>">
                                                Character Limit
                                            </label>

                                            <input
                                                type="number"
                                                id="ff-character-limit-<?php echo esc_attr($index); ?>"
                                                name="ff_fields[<?php echo esc_attr($index); ?>][character_limit]"
                                                value="<?php echo esc_attr($character_limit); ?>"
                                                min="0"
                                                step="1">

                                            <p class="description">
                                                Leave at 0 for no limit.
                                            </p>

                                        </div>

                                        <div
                                            class="ff-field-option ff-option-required"
                                            data-ff-option="required">

                                            <label>
                                                Required
                                            </label>

                                            <label class="ff-toggle-field">
                                                <input
                                                    type="checkbox"
                                                    name="ff_fields[<?php echo esc_attr($index); ?>][required]"
                                                    value="1"
                                                    <?php checked($required); ?>>

                                                <span class="ff-toggle" aria-hidden="true"></span>
                                            </label>

                                            <p class="description">
                                                Is this field required?
                                            </p>

                                        </div>

                                        <div
                                            class="ff-field-option ff-option-prepend"
                                            data-ff-option="prepend">

                                            <label
                                                for="ff-prepend-<?php echo esc_attr($index); ?>">
                                                Prepend
                                            </label>

                                            <input
                                                type="text"
                                                id="ff-prepend-<?php echo esc_attr($index); ?>"
                                                name="ff_fields[<?php echo esc_attr($index); ?>][prepend]"
                                                value="<?php echo esc_attr($prepend); ?>"
                                                class="regular-text">

                                            <p class="description">
                                                Appears before the input.
                                            </p>

                                        </div>

                                        <div
                                            class="ff-field-option ff-option-append"
                                            data-ff-option="append">

                                            <label
                                                for="ff-append-<?php echo esc_attr($index); ?>">
                                                Append
                                            </label>

                                            <input
                                                type="text"
                                                id="ff-append-<?php echo esc_attr($index); ?>"
                                                name="ff_fields[<?php echo esc_attr($index); ?>][append]"
                                                value="<?php echo esc_attr($append); ?>"
                                                class="regular-text">

                                            <p class="description">
                                                Appears after the input.
                                            </p>

                                        </div>

                                    </div>

                                    <div
                                        class="ff-field-setting ff-setting-choices"
                                        data-ff-option="choices">

                                        <label>
                                            Choices <span class="ff-required-indicator">*</span>
                                        </label>

                                        <textarea
                                            name="ff_fields[<?php echo esc_attr($index); ?>][choices]"
                                            rows="4"
                                            class="large-text"
                                            placeholder="value : Label&#10;pro : Pro Plan&#10;enterprise : Enterprise"><?php echo esc_textarea($choices_raw); ?></textarea>

                                        <p class="description">
                                            <strong>Required.</strong> Add at least one choice, one per line. Use
                                            <code>value : Label</code> or
                                            <code>value|Label</code>. If only <code>value</code> is entered, Forge Fields will still generate a safe stored value automatically.
                                        </p>

                                    </div>

                                </div>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th style="width:30px;"></th>

                        <th style="width:42px;" class="ff-field-check-column">
                            <input
                                type="checkbox"
                                id="ff-select-all-fields-bottom"
                                class="ff-select-all-fields"
                                aria-label="Select all fields">
                        </th>

                        <th class="ff-bar-title">Label</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th style="width:150px;"></th>
                    </tr>
                </tfoot>
            </table>

            <div class="ff-field-bulk-actions">

                <div class="ff-select-wrap">
                    <select id="ff-field-bulk-action">
                        <option value="">Bulk actions</option>
                        <option value="remove">Remove selected</option>
                    </select>
                </div>

                <button
                    type="button"
                    class="button"
                    id="ff-apply-field-bulk-action">
                    Apply
                </button>

            </div>

            <script type="text/html" id="ff-field-row-template">
                <tr class="ff-field-row" data-index="__INDEX__">
                    <td class="ff-field-handle" aria-label="Drag" title="Drag"></td>

                    <td class="ff-field-check-column">
                        <input
                            type="checkbox"
                            class="ff-field-select"
                            aria-label="Select field">
                    </td>

                    <td>
                        <input type="text"
                            name="ff_fields[__INDEX__][label]"
                            value=""
                            class="regular-text ff-field-label"
                            data-field-part="label">
                    </td>

                    <td>
                        <input type="text"
                            name="ff_fields[__INDEX__][name]"
                            value=""
                            class="regular-text ff-field-name"
                            data-field-part="name">
                    </td>

                    <td>
                        <div class="ff-select-wrap">
                            <select name="ff_fields[__INDEX__][type]" class="ff-field-type" data-field-part="type">
                                <?php foreach ($type_groups as $group_label => $opts) : ?>
                                    <optgroup label="<?php echo esc_attr($group_label); ?>">
                                        <?php foreach ($opts as $t) : ?>
                                            <?php
                                            $pretty = [
                                                'text'     => 'Text',
                                                'textarea' => 'Textarea',
                                                'number'   => 'Number',
                                                'email'    => 'Email',
                                                'url'      => 'URL',
                                                'range'    => 'Range',
                                                'password' => 'Password',
                                                'image'    => 'Image',
                                                'file'     => 'File',
                                                'wysiwyg'  => 'WYSIWYG Editor',
                                                'select'  => 'Select',
                                                'checkbox'  => 'Checkbox',
                                                'radio'  => 'Radio',
                                                'button_group'  => 'Button Group',
                                                'true_false'  => 'True/False',
                                                'tab'          => 'Tab',
                                            ];
                                            ?>
                                            <option value="<?php echo esc_attr($t); ?>">
                                                <?php echo esc_html($pretty[$t] ?? ucfirst($t)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </td>

                    <td class="ff-field-actions">
                        <button
                            type="button"
                            class="button-link ff-field-options-toggle"
                            aria-expanded="false">
                            Options
                        </button>

                        <span class="ff-field-action-separator">|</span>

                        <a href="#" class="ff-field-remove">
                            Remove
                        </a>
                    </td>
                </tr>

                <tr
                    class="ff-field-settings is-hidden"
                    data-index="__INDEX__"
                    data-ff-settings
                    style="display:none">

                    <td colspan="6">

                        <div class="ff-field-options-panel">

                            <div class="ff-field-options-header">
                                <strong>Field Options</strong>
                            </div>

                            <div class="ff-field-options-grid">

                                <div
                                    class="ff-field-option ff-option-default"
                                    data-ff-option="default">

                                    <label for="ff-default-value-__INDEX__">
                                        Default Value
                                    </label>

                                    <input
                                        type="text"
                                        id="ff-default-value-__INDEX__"
                                        name="ff_fields[__INDEX__][default_value]"
                                        value=""
                                        class="regular-text">

                                    <p class="description">
                                        Used when no value has been saved yet.
                                    </p>

                                </div>

                                <div
                                    class="ff-field-option ff-option-character-limit"
                                    data-ff-option="character-limit">

                                    <label for="ff-character-limit-__INDEX__">
                                        Character Limit
                                    </label>

                                    <input
                                        type="number"
                                        id="ff-character-limit-__INDEX__"
                                        name="ff_fields[__INDEX__][character_limit]"
                                        value="0"
                                        min="0"
                                        step="1">

                                    <p class="description">
                                        Leave at 0 for no limit.
                                    </p>

                                </div>

                                <div
                                    class="ff-field-option ff-option-required"
                                    data-ff-option="required">

                                    <label>
                                        Required
                                    </label>

                                    <label class="ff-toggle-field">
                                        <input
                                            type="checkbox"
                                            name="ff_fields[<?php echo esc_attr($index); ?>][required]"
                                            value="1"
                                            <?php checked($required); ?>>

                                        <span class="ff-toggle" aria-hidden="true"></span>
                                    </label>
                                    <p class="description">
                                        Is this field required?
                                    </p>
                                </div>

                            </div>

                            <div
                                class="ff-field-setting ff-setting-choices"
                                data-ff-option="choices">

                                <label>
                                    Choices <span class="ff-required-indicator">*</span>
                                </label>

                                <textarea
                                    name="ff_fields[__INDEX__][choices]"
                                    rows="4"
                                    class="large-text"
                                    placeholder="value : Label&#10;"></textarea>

                                <p class="description">
                                    <strong>Required.</strong> Add at least one choice, one per line.
                                    Supported formats:
                                    <code>value : Label</code>,
                                    <code>value|Label</code>,
                                    or <code>value</code>.
                                </p>

                            </div>

                        </div>

                    </td>
                </tr>
            </script>


        </form>
        <div id="ff-confirm" class="ff-confirm is-hidden" role="dialog" aria-modal="true" aria-labelledby="ff-confirm-title" aria-describedby="ff-confirm-desc">
            <div class="ff-confirm__overlay" data-ff-close></div>
            <div class="ff-confirm__dialog" role="document" tabindex="-1">
                <h2 id="ff-confirm-title" class="ff-confirm__title">
                    Remove field?
                </h2>
                <p id="ff-confirm-desc" class="ff-confirm__desc">
                    This will remove <strong class="ff-confirm__field-name">this field</strong> from the group.
                </p>
                <div class="ff-confirm__actions">
                    <button type="button" class="button" data-ff-close>Cancel</button>
                    <button type="button" class="button ff-button-danger" id="ff-confirm-yes">Remove</button>
                </div>
                <button type="button" class="ff-confirm__x" aria-label="Close" data-ff-close>×</button>
            </div>
        </div>
    </div>
<?php
}

/**
 * Remove transient Forge Fields notice parameters from the browser URL.
 *
 * Notices are rendered once after redirects using query parameters such
 * as ff_notice. Once the page is displayed, those parameters are removed
 * so refreshing the browser does not display the same notice again.
 */
add_action('admin_footer', function () {

    $page = isset($_GET['page'])
        ? sanitize_key(wp_unslash($_GET['page']))
        : '';

    $forge_pages = [
        'forge-fields',
        'forge-fields-edit',
        'forge-fields-global',
        'forge-fields-settings',
    ];

    if (! in_array($page, $forge_pages, true)) {
        return;
    }

    if (! isset($_GET['ff_notice'])) {
        return;
    }
?>
    <script>
        (function() {
            const url = new URL(window.location.href);

            url.searchParams.delete('ff_notice');
            url.searchParams.delete('imported');
            url.searchParams.delete('skipped');

            window.history.replaceState({},
                document.title,
                url.pathname + url.search + url.hash
            );
        })();
    </script>
<?php
});
