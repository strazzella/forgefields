<?php

/**
 * Prevent direct access to this file outside of WordPress.
 */
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Runtime registry for active Forge Fields field groups.
 *
 * Field groups loaded during WordPress initialization are registered
 * here for use throughout the current request.
 */
global $ff_field_groups;
$ff_field_groups = [];

/**
 * Generate a unique identifier for a field group.
 *
 * @return string Unique Forge Fields group ID.
 */
function ff_generate_group_id()
{
    return uniqid('ff_group_');
}

/**
 * Retrieve all saved Forge Fields field groups.
 *
 * @return array Saved field groups, or an empty array when none exist.
 */
function ff_get_all_groups()
{
    $groups = get_option('ff_field_groups', []);

    return is_array($groups) ? $groups : [];
}

/**
 * Return the registered Forge Fields core field types.
 *
 * Each field type maps to a render callback and sanitization callback.
 * The registry is filterable through the ff_field_types hook.
 *
 * @return array Registered field type definitions.
 */
function ff_get_field_types()
{
    static $types = null;

    if ($types === null) {
        $types = [
            'text' => [
                'render'   => 'ff_render_type_text',
                'sanitize' => 'ff_sanitize_type_text',
            ],
            'textarea' => [
                'render'   => 'ff_render_type_textarea',
                'sanitize' => 'ff_sanitize_type_textarea',
            ],
            'number' => [
                'render'   => 'ff_render_type_number',
                'sanitize' => 'ff_sanitize_type_number',
            ],
            'email' => [
                'render'   => 'ff_render_type_email',
                'sanitize' => 'ff_sanitize_type_email',
            ],
            'url' => [
                'render'   => 'ff_render_type_url',
                'sanitize' => 'ff_sanitize_type_url',
            ],
            'range' => [
                'render'   => 'ff_render_type_range',
                'sanitize' => 'ff_sanitize_type_range',
            ],
            'password' => [
                'render'   => 'ff_render_type_password',
                'sanitize' => 'ff_sanitize_type_password',
            ],
            'tab' => [
                'render'   => 'ff_render_type_tab',
                'sanitize' => 'ff_sanitize_type_tab',
            ],
        ];

        $types = apply_filters('ff_field_types', $types);
    }

    return $types;
}

/**
 * Render a standard text field input.
 *
 * @param array   $field Field definition.
 * @param mixed   $value Current field value.
 * @param WP_Post $post  Current post object.
 */
function ff_render_type_text(array $field, $value, WP_Post $post)
{
    $name = $field['name'];
    $id   = 'ff_' . esc_attr($name);

    printf(
        '<input type="text" name="%1$s" id="%2$s" value="%3$s" class="regular-text" />',
        esc_attr($name),
        esc_attr($id),
        esc_attr($value)
    );
}

/**
 * Render a multiline textarea field.
 *
 * @param array   $field Field definition.
 * @param mixed   $value Current field value.
 * @param WP_Post $post  Current post object.
 */
function ff_render_type_textarea(array $field, $value, WP_Post $post)
{
    $name = $field['name'];
    $id   = 'ff_' . esc_attr($name);

    printf(
        '<textarea name="%1$s" id="%2$s" rows="3" class="large-text">%3$s</textarea>',
        esc_attr($name),
        esc_attr($id),
        esc_textarea($value)
    );
}

/**
 * Render a numeric input field.
 *
 * @param array   $field Field definition.
 * @param mixed   $value Current field value.
 * @param WP_Post $post  Current post object.
 */
function ff_render_type_number(array $field, $value, WP_Post $post)
{
    $name = $field['name'];
    $id   = 'ff_' . esc_attr($name);

    printf(
        '<input type="number" name="%1$s" id="%2$s" value="%3$s" class="regular-text ff-number-input" />',
        esc_attr($name),
        esc_attr($id),
        esc_attr($value)
    );
}

/**
 * Render an email input field.
 *
 * @param array   $field Field definition.
 * @param mixed   $value Current field value.
 * @param WP_Post $post  Current post object.
 */
function ff_render_type_email(array $field, $value, WP_Post $post)
{
    $name = $field['name'];
    $id   = 'ff_' . esc_attr($name);

    printf(
        '<input type="email" name="%1$s" id="%2$s" value="%3$s" class="regular-text" />',
        esc_attr($name),
        esc_attr($id),
        esc_attr($value)
    );
}

/**
 * Render a URL input field.
 *
 * @param array   $field Field definition.
 * @param mixed   $value Current field value.
 * @param WP_Post $post  Current post object.
 */
function ff_render_type_url(array $field, $value, WP_Post $post)
{
    $name = $field['name'];
    $id   = 'ff_' . esc_attr($name);

    printf(
        '<input type="url" name="%1$s" id="%2$s" value="%3$s" class="regular-text" />',
        esc_attr($name),
        esc_attr($id),
        esc_attr($value)
    );
}

/**
 * Render a synchronized range slider and numeric input.
 *
 * @param array   $field Field definition, including optional min/max values.
 * @param mixed   $value Current field value.
 * @param WP_Post $post  Current post object.
 */
function ff_render_type_range(array $field, $value, WP_Post $post)
{
    $name = $field['name'];

    $min = isset($field['min']) ? (int) $field['min'] : 0;
    $max = isset($field['max']) ? (int) $field['max'] : 100;

    $value = ($value === '' ? $min : (int) $value);

    $id_slider = 'ff_' . esc_attr($name) . '_range';
    $id_number = 'ff_' . esc_attr($name) . '_number';
?>
    <div class="ff-range-wrap">
        <input
            type="range"
            name="<?php echo esc_attr($name); ?>"
            id="<?php echo esc_attr($id_slider); ?>"
            class="ff-range-slider"
            min="<?php echo esc_attr($min); ?>"
            max="<?php echo esc_attr($max); ?>"
            value="<?php echo esc_attr($value); ?>"
            data-target="#<?php echo esc_attr($id_number); ?>" />
        <input
            type="number"
            id="<?php echo esc_attr($id_number); ?>"
            class="small-text ff-range-number"
            min="<?php echo esc_attr($min); ?>"
            max="<?php echo esc_attr($max); ?>"
            value="<?php echo esc_attr($value); ?>"
            data-target="#<?php echo esc_attr($id_slider); ?>" />
    </div>
<?php
}

/**
 * Render a password field with a visibility toggle control.
 *
 * @param array   $field Field definition.
 * @param mixed   $value Current field value.
 * @param WP_Post $post  Current post object.
 */
function ff_render_type_password(array $field, $value, WP_Post $post)
{
    $name = $field['name'];
    $id   = 'ff_' . esc_attr($name);

    echo '<div class="ff-password-wrap">';

    echo '<input 
        type="password"
        class="regular-text ff-password-input"
        id="' . esc_attr($id) . '"
        name="' . esc_attr($name) . '"
        value="' . esc_attr($value) . '"
        maxlength="45"
        autocomplete="off"
    >';

    echo '<button 
        type="button" 
        class="ff-password-toggle" 
        data-target="#' . esc_attr($id) . '" 
        aria-label="Show password"
        aria-controls="' . esc_attr($id) . '"
    >
        <span class="dashicons dashicons-hidden"></span>
    </button>';

    echo '</div>';
}

/**
 * Render a tab placeholder for field-group section organization.
 *
 * Tab fields are structural and do not store a value themselves.
 *
 * @param array   $field Field definition.
 * @param mixed   $value Current field value.
 * @param WP_Post $post  Current post object.
 */
function ff_render_type_tab(array $field, $value, WP_Post $post)
{
    $label = isset($field['label']) ? $field['label'] : 'Tab';

    echo '<div class="ff-tab-placeholder">';
    echo '<strong>' . esc_html($label) . '</strong>';
    echo '</div>';
}


/**
 * Sanitize a standard text field value before storage.
 *
 * @param mixed $raw     Raw submitted value.
 * @param array $field   Field definition.
 * @param int   $post_id Current post ID.
 *
 * @return string Sanitized text value.
 */
function ff_sanitize_type_text($raw, array $field, $post_id)
{
    return sanitize_text_field($raw);
}

/**
 * Sanitize a textarea field value before storage.
 *
 * @param mixed $raw     Raw submitted value.
 * @param array $field   Field definition.
 * @param int   $post_id Current post ID.
 *
 * @return string Sanitized textarea value.
 */
function ff_sanitize_type_textarea($raw, array $field, $post_id)
{
    return sanitize_textarea_field($raw);
}

/**
 * Sanitize a numeric field value before storage.
 *
 * Empty or non-numeric values are normalized to an empty string.
 *
 * @param mixed $raw     Raw submitted value.
 * @param array $field   Field definition.
 * @param int   $post_id Current post ID.
 *
 * @return int|float|string Sanitized numeric value or an empty string.
 */
function ff_sanitize_type_number($raw, array $field, $post_id)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return '';
    }
    return is_numeric($raw) ? $raw + 0 : '';
}

/**
 * Sanitize an email field value before storage.
 *
 * @param mixed $raw     Raw submitted value.
 * @param array $field   Field definition.
 * @param int   $post_id Current post ID.
 *
 * @return string Sanitized email address.
 */
function ff_sanitize_type_email($raw, array $field, $post_id)
{
    $san = sanitize_email($raw);
    return $san ? $san : '';
}

/**
 * Sanitize a URL field value before storage.
 *
 * Adds an HTTPS scheme when the submitted value does not include
 * an HTTP or HTTPS scheme.
 *
 * @param mixed $raw     Raw submitted value.
 * @param array $field   Field definition.
 * @param int   $post_id Current post ID.
 *
 * @return string Sanitized URL.
 */
function ff_sanitize_type_url($raw, array $field, $post_id)
{
    $raw = trim($raw);

    if ($raw === '') {
        return '';
    }

    if (
        strpos($raw, 'http://') !== 0 &&
        strpos($raw, 'https://') !== 0
    ) {
        $raw = 'https://' . $raw;
    }

    $san = esc_url_raw($raw);

    return $san ? $san : '';
}

/**
 * Sanitize and constrain a range field value.
 *
 * Numeric values are clamped to the configured minimum and maximum.
 *
 * @param mixed $raw     Raw submitted value.
 * @param array $field   Field definition.
 * @param int   $post_id Current post ID.
 *
 * @return float|string Sanitized range value or an empty string.
 */
function ff_sanitize_type_range($raw, array $field, $post_id)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return '';
    }
    if (! is_numeric($raw)) {
        return '';
    }

    $value = (float) $raw;

    $min = isset($field['min']) ? (float) $field['min'] : 0;
    $max = isset($field['max']) ? (float) $field['max'] : 100;

    if ($value < $min) $value = $min;
    if ($value > $max) $value = $max;

    return $value;
}

/**
 * Normalize a password field value before storage.
 *
 * @param mixed $raw     Raw submitted value.
 * @param array $field   Field definition.
 * @param int   $post_id Current post ID.
 *
 * @return string Normalized password value.
 */
function ff_sanitize_type_password($raw, array $field, $post_id)
{
    $raw = (string) $raw;
    $raw = wp_check_invalid_utf8($raw);
    $raw = trim($raw);

    return $raw;
}

/**
 * Return an empty value for structural tab fields.
 *
 * Tab fields organize the admin interface and do not store post meta.
 *
 * @param mixed $raw     Raw submitted value.
 * @param array $field   Field definition.
 * @param int   $post_id Current post ID.
 *
 * @return string Always an empty string.
 */
function ff_sanitize_type_tab($raw, array $field, $post_id)
{
    return '';
}

/**
 * Register a field group in the current request's runtime registry.
 *
 * Applies default group values and ensures the fields collection is
 * represented as an array before registration.
 *
 * @param array $group Field group definition.
 */
function ff_register_field_group(array $group)
{
    global $ff_field_groups;

    if (empty($group['id'])) {
        return;
    }

    $defaults = [
        'title'    => '',
        'location' => 'page',
        'fields'   => [],
    ];

    $group = wp_parse_args($group, $defaults);

    if (! is_array($group['fields'])) {
        $group['fields'] = [];
    }

    $ff_field_groups[$group['id']] = $group;
}

/**
 * Load saved field groups and register active groups during WordPress boot.
 *
 * Also migrates the legacy single-group option into the current
 * multi-group storage format when necessary.
 */
function ff_boot_field_groups()
{
    $groups = get_option('ff_field_groups', null);

    if ($groups === null) {
        $legacy = get_option('ff_field_group', []);

        if (is_array($legacy) && ! empty($legacy)) {
            if (empty($legacy['id'])) {
                $legacy['id'] = 'ff_legacy_group';
            }

            $groups = [$legacy['id'] => $legacy];
            update_option('ff_field_groups', $groups);
            delete_option('ff_field_group');
        } else {
            $groups = [];
            update_option('ff_field_groups', $groups);
        }
    }

    if (! is_array($groups) || empty($groups)) {
        return;
    }

    foreach ($groups as $group) {

        if (! is_array($group)) {
            continue;
        }

        $status = isset($group['status']) ? $group['status'] : 'active';

        if ($status !== 'active') {
            continue;
        }

        if (empty($group['id'])) {
            $group['id'] = ff_generate_group_id();
        }

        ff_register_field_group($group);
    }
}

/**
 * Register Forge Fields meta boxes on supported post and page screens.
 *
 * Only active groups matching the current content type and optional
 * location target are registered.
 */
add_action('add_meta_boxes', function () {
    $groups = ff_get_all_groups();
    if (empty($groups) || ! is_array($groups)) {
        return;
    }

    $current_post_id = 0;
    if (isset($_GET['post'])) {
        $current_post_id = absint($_GET['post']);
    } elseif (isset($_POST['post_ID'])) {
        $current_post_id = absint($_POST['post_ID']);
    }

    foreach ($groups as $group_id => $group) {
        $location = isset($group['location']) ? $group['location'] : 'page';
        $target   = isset($group['location_target']) ? (string) $group['location_target'] : '';
        $status   = isset($group['status']) ? $group['status'] : 'active';

        if ($status !== 'active') {
            continue;
        }

        if (! in_array($location, ['page', 'post'], true)) {
            continue;
        }

        if ($target !== '' && (string) $current_post_id !== $target) {
            continue;
        }

        add_meta_box(
            'ff_field_group_' . $group_id,
            esc_html($group['title']),
            'ff_render_field_group_metabox',
            $location,
            'normal',
            'default',
            ['group_id' => $group_id]
        );
    }
});

/**
 * Render a single Forge Fields row inside a post/page meta box.
 *
 * The field definition determines which control is rendered and the
 * current value is loaded from post meta.
 *
 * @param array   $field Field definition.
 * @param WP_Post $post  Current post object.
 */
function ff_render_metabox_field_row(array $field, WP_Post $post)
{
    $name = $field['name'] ?? '';
    if ($name === '') {
        return;
    }

    $label    = $field['label'] ?? $name;
    $type     = $field['type']  ?? 'text';
    $meta_key = '_ff_' . $name;
    $value    = get_post_meta($post->ID, $meta_key, true);

    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($meta_key) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';

    switch ($type) {
        case 'wysiwyg':
            $editor_id = 'ff_' . sanitize_key($name);
            wp_editor(
                is_string($value) ? $value : '',
                $editor_id,
                [
                    'textarea_name' => $meta_key,
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
            break;

        case 'textarea':
            printf(
                '<textarea name="%1$s" id="%2$s" rows="6" class="large-text">%3$s</textarea>',
                esc_attr($meta_key),
                esc_attr($meta_key),
                esc_textarea((string) $value)
            );
            break;

        case 'number':
            printf(
                '<input type="number" class="small-text" name="%1$s" id="%2$s" value="%3$s">',
                esc_attr($meta_key),
                esc_attr($meta_key),
                esc_attr((string) $value)
            );
            break;

        case 'email':
        case 'url':
        case 'text':
            $input = in_array($type, ['email', 'url'], true) ? $type : 'text';
            echo '<input type="' . esc_attr($input) . '" class="regular-text" name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '" value="' . esc_attr((string) $value) . '">';
            break;

        case 'password':
            echo '<div class="ff-password-wrap">';
            echo '<input type="password" class="regular-text ff-password-input" name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '" value="' . esc_attr((string) $value) . '" maxlength="45" autocomplete="off">';
            echo '<button type="button" class="ff-password-toggle" data-target="#' . esc_attr($meta_key) . '" aria-label="Show password" aria-controls="' . esc_attr($meta_key) . '">';
            echo '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>';
            echo '</button>';
            echo '</div>';
            break;

        case 'range':
            $min  = isset($field['min'])  ? (int) $field['min']  : 0;
            $max  = isset($field['max'])  ? (int) $field['max']  : 100;
            $step = isset($field['step']) ? (int) $field['step'] : 1;
            $val  = ($value === '' ? $min : (int) $value);

            $slider_id = $meta_key . '_slider';
            $num_id    = $meta_key . '_num';

            echo '<div class="ff-range-wrap">';
            echo '<input type="range" class="ff-range-slider" id="' . esc_attr($slider_id) . '" name="' . esc_attr($meta_key) . '" min="' . esc_attr($min) . '" max="' . esc_attr($max) . '" step="' . esc_attr($step) . '" value="' . esc_attr($val) . '" data-target="#' . esc_attr($num_id) . '">';
            echo '<input type="number" class="small-text ff-range-number" id="' . esc_attr($num_id) . '" min="' . esc_attr($min) . '" max="' . esc_attr($max) . '" step="' . esc_attr($step) . '" value="' . esc_attr($val) . '" data-target="#' . esc_attr($slider_id) . '">';
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

            echo '<div class="ff-media-wrap" data-type="image">';
            echo '<input type="hidden" name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '" value="' . esc_attr((string) $value) . '">';
            echo '<div class="ff-media-preview-wrap" style="margin-bottom:8px;">';
            echo '<img class="ff-media-preview" src="' . esc_url($img_src) . '" style="' . ($img_src ? '' : 'display:none;') . 'max-height:80px;border-radius:4px;">';
            echo '</div>';
            echo '<button type="button" class="button ff-media-select" data-target="' . esc_attr($meta_key) . '">Select Image</button>';
            echo '<button type="button" class="button ff-media-clear" data-target="' . esc_attr($meta_key) . '" style="' . ($value ? '' : 'display:none;') . '">Clear</button>';
            echo '</div>';
            break;

        case 'file':
            $file_url  = $value ? wp_get_attachment_url((int) $value) : '';
            $file_name = $file_url ? wp_basename($file_url) : '';

            echo '<div class="ff-media-wrap" data-type="file">';
            echo '<input type="hidden" name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '" value="' . esc_attr((string) $value) . '">';
            echo '<div class="ff-media-fileline" style="margin-bottom:8px;">';
            echo '<span class="dashicons dashicons-media-document" aria-hidden="true"></span> ';
            echo '<a class="ff-media-fileurl" href="' . esc_url($file_url) . '" target="_blank" style="' . ($file_url ? '' : 'display:none;') . '">' . esc_html($file_name) . '</a>';
            echo '<span class="ff-media-nofile" style="' . ($file_url ? 'display:none;' : '') . '">No file selected.</span>';
            echo '</div>';
            echo '<button type="button" class="button ff-media-select" data-target="' . esc_attr($meta_key) . '">Select File</button>';
            echo '<button type="button" class="button ff-media-clear" data-target="' . esc_attr($meta_key) . '" style="' . ($value ? '' : 'display:none;') . '">Clear</button>';
            echo '</div>';
            break;

        case 'select':
            $choices_map = ff_parse_choices_string($field['choices'] ?? '');
            $current     = is_scalar($value) ? (string) $value : '';

            echo '<div class="ff-select-wrap">';
            echo '<select name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '">';
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
                $field_id = $meta_key . '_' . sanitize_key((string) $v);
                printf(
                    '<label for="%1$s" style="display:inline-block;margin-right:12px;">
                        <input type="radio" name="%2$s" id="%1$s" value="%3$s" %4$s>
                        %5$s
                    </label>',
                    esc_attr($field_id),
                    esc_attr($meta_key),
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
                $field_id = $meta_key . '_' . sanitize_key((string) $v);
                printf(
                    '<label class="ff-button-group__btn" for="%1$s">
                        <input class="ff-button-group__input" type="radio" name="%2$s" id="%1$s" value="%3$s" %4$s>
                        <span class="ff-button-group__label">%5$s</span>
                    </label>',
                    esc_attr($field_id),
                    esc_attr($meta_key),
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
                $field_id = $meta_key . '_' . sanitize_key((string) $v);
                printf(
                    '<label for="%1$s" style="display:inline-block;margin-right:12px;">
                        <input type="checkbox" name="%2$s[]" id="%1$s" value="%3$s" %4$s>
                        %5$s
                    </label>',
                    esc_attr($field_id),
                    esc_attr($meta_key),
                    esc_attr($v),
                    in_array((string) $v, $current, true) ? 'checked' : '',
                    esc_html($lbl)
                );
            }
            break;

        case 'true_false':
            $checked = ! empty($value);
            printf(
                '<label>
                    <input type="checkbox" name="%1$s" id="%2$s" value="1" %3$s>
                    %4$s
                </label>',
                esc_attr($meta_key),
                esc_attr($meta_key),
                checked($checked, true, false),
                esc_html__('Enabled', 'forge-fields')
            );
            break;
    }

    echo '</td>';
    echo '</tr>';
}

/**
 * Render a complete Forge Fields field-group meta box.
 *
 * Handles the field-group nonce, optional tab navigation, and rendering
 * of each individual field row.
 *
 * @param WP_Post $post Current post object.
 * @param array   $box  WordPress meta box configuration.
 */
function ff_render_field_group_metabox($post, $box)
{
    $group_id = isset($box['args']['group_id']) ? $box['args']['group_id'] : '';
    $groups   = ff_get_all_groups();

    if (! $group_id || empty($groups[$group_id])) {
        echo '<p>Group not found.</p>';
        return;
    }

    $group  = $groups[$group_id];
    $fields = is_array($group['fields'] ?? null) ? $group['fields'] : [];

    if (empty($fields)) {
        echo '<p><em>No fields defined in this group.</em></p>';
        return;
    }

    wp_nonce_field('ff_save_post_fields', 'ff_meta_nonce');

    $sections = ff_group_fields_into_tab_sections($fields);
    $has_tabs = count($sections) > 1 || (count($sections) === 1 && $sections[0]['label'] !== '');

    echo '<div class="ff-mb">';

    if ($has_tabs) {
        echo '<div class="ff-tabs" data-ff-tabs>';
        echo '<div class="ff-tab-nav">';

        foreach ($sections as $index => $section) {
            echo '<button type="button" class="ff-tab-button' . ($index === 0 ? ' is-active' : '') . '" data-ff-tab="' . esc_attr($index) . '">';
            echo esc_html($section['label'] ?: 'General');
            echo '</button>';
        }

        echo '</div>';

        foreach ($sections as $index => $section) {
            echo '<div class="ff-tab-panel' . ($index === 0 ? ' is-active' : '') . '" data-ff-tab-panel="' . esc_attr($index) . '">';
            echo '<table class="form-table"><tbody>';

            foreach ($section['fields'] as $field) {
                ff_render_metabox_field_row($field, $post);
            }

            echo '</tbody></table>';
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '<table class="form-table"><tbody>';

        foreach ($fields as $field) {
            ff_render_metabox_field_row($field, $post);
        }

        echo '</tbody></table>';
    }

    echo '</div>';
}

/**
 * Save Forge Fields values when a post or page is saved.
 *
 * The save routine ignores autosaves and revisions, verifies the Forge
 * Fields nonce and user capability, limits processing to applicable
 * active groups, sanitizes values by field type, and updates or removes
 * the corresponding post meta.
 */
add_action('save_post', function ($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (wp_is_post_revision($post_id)) {
        return;
    }

    if (! isset($_POST['ff_meta_nonce'])) {
        return;
    }

    $nonce = sanitize_text_field(
        wp_unslash($_POST['ff_meta_nonce'])
    );

    if (! wp_verify_nonce($nonce, 'ff_save_post_fields')) {
        return;
    }
    if (! current_user_can('edit_post', $post_id)) return;

    $groups = ff_get_all_groups();

    foreach ($groups as $group) {

        $status = isset($group['status'])
            ? $group['status']
            : 'active';

        if ($status !== 'active') {
            continue;
        }

        $location = isset($group['location'])
            ? $group['location']
            : 'page';

        if (! in_array($location, ['page', 'post'], true)) {
            continue;
        }

        if (get_post_type($post_id) !== $location) {
            continue;
        }

        $target = isset($group['location_target'])
            ? (string) $group['location_target']
            : '';

        if ($target !== '' && (string) $post_id !== $target) {
            continue;
        }

        foreach ((array) ($group['fields'] ?? []) as $field) {
            $name = $field['name'] ?? '';
            $type = $field['type'] ?? 'text';

            if ($name === '') {
                continue;
            }

            $key     = '_ff_' . $name;
            $has_key = array_key_exists($key, $_POST);

            if (! $has_key && ! in_array($type, ['checkbox', 'true_false'], true)) {
                continue;
            }

            $raw = $has_key ? $_POST[$key] : null;

            switch ($type) {

                case 'wysiwyg':
                    $val = $has_key ? wp_kses_post(wp_unslash($raw)) : '';
                    break;

                case 'image':
                case 'file':
                    $val = $has_key && ! is_array($raw) ? (int) $raw : 0;
                    break;

                case 'number':
                case 'range':
                    if (! $has_key || is_array($raw)) {
                        $val = '';
                    } else {
                        $raw_str = trim((string) $raw);
                        $val     = ($raw_str === '' || ! is_numeric($raw_str)) ? '' : 0 + $raw_str;
                    }
                    break;

                case 'email':
                    $val = $has_key && ! is_array($raw) ? sanitize_email($raw) : '';
                    break;

                case 'url':
                    if (! $has_key || is_array($raw)) {
                        $val = '';
                    } else {
                        $url = trim((string) $raw);
                        if (
                            strpos($url, 'http://') !== 0 &&
                            strpos($url, 'https://') !== 0
                        ) {
                            $url = 'https://' . $url;
                        }
                        $san = esc_url_raw($url);
                        $val = $san ? $san : '';
                    }
                    break;

                case 'select':
                case 'radio':
                case 'button_group':
                    $val = ($has_key && ! is_array($raw))
                        ? sanitize_key((string) $raw)
                        : '';
                    break;

                case 'checkbox':
                    if ($has_key && is_array($raw)) {

                        $raw_values = wp_unslash($raw);

                        $vals = array_map(
                            static function ($v) {
                                return sanitize_key((string) $v);
                            },
                            $raw_values
                        );

                        $vals = array_values(
                            array_filter(
                                $vals,
                                static fn($v) => $v !== ''
                            )
                        );

                        $val = $vals;
                    } else {
                        $val = [];
                    }
                    break;

                case 'true_false':
                    $val = ($has_key && ! empty($raw)) ? 1 : 0;
                    break;

                default:
                    $val = ($has_key && ! is_array($raw))
                        ? sanitize_text_field(wp_unslash($raw))
                        : '';
                    break;
            }

            if ($val === '' || $val === [] || $val === null) {
                delete_post_meta($post_id, '_ff_' . $name);
            } else {
                update_post_meta($post_id, '_ff_' . $name, $val);
            }
        }
    }
});

/**
 * Retrieve a Forge Fields value.
 *
 * By default the value is read from the current post. Passing a post ID
 * retrieves a value from that post, while passing "global" or "option"
 * retrieves a Global Fields value.
 *
 * @param string          $field_name Field name.
 * @param int|string|null $post_id    Post ID, "global", "option", or null.
 *
 * @return mixed Stored field value or an empty string when unavailable.
 */
function ff_get_field($field_name, $post_id = null)
{
    if (! $field_name) {
        return '';
    }

    if ($post_id === 'global' || $post_id === 'option') {
        $global = get_option('ff_global_fields', []);
        return isset($global[$field_name]) ? $global[$field_name] : '';
    }

    if ($post_id === null) {
        $post_id = get_the_ID();
    }

    if (! $post_id) {
        return '';
    }

    $value = get_post_meta($post_id, '_ff_' . $field_name, true);

    return $value;
}

/**
 * Retrieve a Global Fields value.
 *
 * Provides a concise wrapper around ff_get_field() for global values and
 * allows a fallback value when the field is empty.
 *
 * @param string $name    Global field name.
 * @param mixed  $default Value returned when the field is empty.
 *
 * @return mixed Stored global value or the supplied default.
 */
function ff_get_global($name, $default = '')
{
    $value = ff_get_field($name, 'global');

    if ($value === '') {
        return $default;
    }

    return $value;
}

/**
 * Define the choices parser only when another implementation has not
 * already been registered.
 */
if (! function_exists('ff_parse_choices_string')) {
    /**
     * Parse a multiline choices definition into a value => label array.
     *
     * Supports "value|label", "value:label", or plain label lines.
     *
     * @param mixed $raw Raw multiline choices string.
     *
     * @return array Sanitized choice values and labels.
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
 * Organize field definitions into tabbed sections.
 *
 * Groups normal fields beneath structural Tab fields. When no Tab fields
 * exist, all fields are returned in a single untitled section.
 *
 * @param array $fields Field definitions.
 *
 * @return array Field sections suitable for meta-box rendering.
 */
function ff_group_fields_into_tab_sections(array $fields)
{
    $has_tabs = false;

    foreach ($fields as $field) {
        $type = isset($field['type']) ? $field['type'] : 'text';
        if ($type === 'tab') {
            $has_tabs = true;
            break;
        }
    }

    if (! $has_tabs) {
        return [
            [
                'label'  => '',
                'fields' => $fields,
            ],
        ];
    }

    $sections       = [];
    $current_label  = 'General';
    $current_fields = [];

    foreach ($fields as $field) {
        $type = isset($field['type']) ? $field['type'] : 'text';

        if ($type === 'tab') {
            if (! empty($current_fields)) {
                $sections[] = [
                    'label'  => $current_label,
                    'fields' => $current_fields,
                ];
            }

            $current_label  = ! empty($field['label']) ? $field['label'] : 'Tab';
            $current_fields = [];
            continue;
        }

        $current_fields[] = $field;
    }

    if (! empty($current_fields)) {
        $sections[] = [
            'label'  => $current_label,
            'fields' => $current_fields,
        ];
    }

    return $sections;
}

/**
 * Persist the complete Forge Fields field-group collection.
 *
 * @param array $groups Field groups to save.
 */
function ff_save_all_groups(array $groups)
{
    update_option('ff_field_groups', $groups);
}
