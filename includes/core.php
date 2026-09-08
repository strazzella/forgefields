<?php


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


function ff_generate_group_id()
{
    return uniqid('ff_group_');
}

/**
 * Build the post meta key used to store a Forge Fields value.
 *
 * Field values are namespaced by both Field Group ID and field name
 * so identical field names can safely exist in different groups.
 *
 * Example:
 * Group ID:   ff_group_6a997bba1fdae
 * Field name: test
 *
 * Result:
 * _ff_ff_group_6a997bba1fdae_test
 *
 * @param string $group_id   Forge Fields group ID.
 * @param string $field_name Forge Fields field name.
 *
 * @return string Namespaced WordPress post meta key.
 */
function ff_get_post_meta_key($group_id, $field_name)
{
    $group_id   = sanitize_key((string) $group_id);
    $field_name = sanitize_key((string) $field_name);

    if ($group_id === '' || $field_name === '') {
        return '';
    }

    return '_ff_' . $group_id . '_' . $field_name;
}


function ff_delete_group_field_values($group_id, $field_name)
{
    $meta_key = ff_get_post_meta_key(
        $group_id,
        $field_name
    );

    if ($meta_key === '') {
        return false;
    }

    return delete_metadata(
        'post',
        0,
        $meta_key,
        '',
        true
    );
}


function ff_get_all_groups($refresh = false)
{
    static $groups = null;


    if (! $refresh && $groups !== null) {
        return $groups;
    }


    $stored_groups = get_option(
        'ff_field_groups',
        []
    );


    $groups = is_array($stored_groups)
        ? $stored_groups
        : [];

    return $groups;
}


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


function ff_render_type_tab(array $field, $value, WP_Post $post)
{
    $label = isset($field['label']) ? $field['label'] : 'Tab';

    echo '<div class="ff-tab-placeholder">';
    echo '<strong>' . esc_html($label) . '</strong>';
    echo '</div>';
}


function ff_sanitize_type_text($raw, array $field, $post_id)
{
    return sanitize_text_field($raw);
}


function ff_sanitize_type_textarea($raw, array $field, $post_id)
{
    return sanitize_textarea_field($raw);
}


function ff_sanitize_type_number($raw, array $field, $post_id)
{
    $raw = trim((string) $raw);
    if ($raw === '') {
        return '';
    }
    return is_numeric($raw) ? $raw + 0 : '';
}


function ff_sanitize_type_email($raw, array $field, $post_id)
{
    $san = sanitize_email($raw);
    return $san ? $san : '';
}


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


function ff_sanitize_type_password($raw, array $field, $post_id)
{
    $raw = (string) $raw;
    $raw = wp_check_invalid_utf8($raw);
    $raw = trim($raw);

    return $raw;
}


function ff_sanitize_type_tab($raw, array $field, $post_id)
{
    return '';
}


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

    foreach ($groups as $group_id => $group) {

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


function ff_render_metabox_field_row(array $field, WP_Post $post, $group_id)
{
    $name = $field['name'] ?? '';
    if ($name === '') {
        return;
    }

    $label         = $field['label'] ?? $name;
    $type          = $field['type'] ?? 'text';
    $required = ! empty($field['required']);
    $meta_key      = ff_get_post_meta_key($group_id, $name);
    $default_value = isset($field['default_value'])
        ? $field['default_value']
        : '';

    $character_limit = isset($field['character_limit'])
        ? absint($field['character_limit'])
        : 0;

    $prepend = isset($field['prepend'])
        ? (string) $field['prepend']
        : '';

    $append = isset($field['append'])
        ? (string) $field['append']
        : '';

    $maxlength_attr = $character_limit > 0
        ? ' maxlength="' . esc_attr($character_limit) . '"'
        : '';

    $prepend_html = $prepend !== ''
        ? '<span class="ff-input-affix ff-input-prepend">' . esc_html($prepend) . '</span>'
        : '';

    $append_html = $append !== ''
        ? '<span class="ff-input-affix ff-input-append">' . esc_html($append) . '</span>'
        : '';

    /**
     * Prefer the new group-scoped post meta key.
     *
     * If this field still has a value stored under the legacy
     * _ff_<field_name> key, migrate that value into the new
     * group-scoped key when it can be done safely.
     */
    if (metadata_exists('post', $post->ID, $meta_key)) {

        $value = get_post_meta(
            $post->ID,
            $meta_key,
            true
        );
    } else {

        $legacy_meta_key = '_ff_' . sanitize_key($name);

        if (metadata_exists('post', $post->ID, $legacy_meta_key)) {

            $groups = ff_get_all_groups();
            $matching_groups = [];

            foreach ($groups as $candidate_group_id => $candidate_group) {

                if (! is_array($candidate_group)) {
                    continue;
                }

                $status = isset($candidate_group['status'])
                    ? $candidate_group['status']
                    : 'active';

                if ($status !== 'active') {
                    continue;
                }

                $location = isset($candidate_group['location'])
                    ? $candidate_group['location']
                    : 'page';

                if ($location !== get_post_type($post->ID)) {
                    continue;
                }

                $target = isset($candidate_group['location_target'])
                    ? (string) $candidate_group['location_target']
                    : '';

                if (
                    $target !== ''
                    && (string) $post->ID !== $target
                ) {
                    continue;
                }

                foreach ((array) ($candidate_group['fields'] ?? []) as $candidate_field) {

                    $candidate_name = isset($candidate_field['name'])
                        ? sanitize_key((string) $candidate_field['name'])
                        : '';

                    if ($candidate_name !== sanitize_key($name)) {
                        continue;
                    }

                    $matching_groups[] = $candidate_group_id;
                    break;
                }
            }

            /**
             * Only migrate legacy data when exactly one applicable
             * Field Group owns this field name.
             */
            if (
                count($matching_groups) === 1
                && reset($matching_groups) === $group_id
            ) {
                $value = get_post_meta(
                    $post->ID,
                    $legacy_meta_key,
                    true
                );

                update_post_meta(
                    $post->ID,
                    $meta_key,
                    $value
                );

                delete_post_meta(
                    $post->ID,
                    $legacy_meta_key
                );
            } else {
                $value = $default_value;
            }
        } else {
            $value = $default_value;
        }
    }

    $row_class = $required
        ? ' class="ff-required-field"'
        : '';

    echo '<tr' . $row_class . ' data-ff-field-type="' . esc_attr($type) . '">';
    echo '<th scope="row">';
    echo '<label for="' . esc_attr($meta_key) . '">';
    echo esc_html($label);

    if ($required) {
        echo ' <span class="ff-required-indicator" aria-hidden="true">*</span>';
    }

    echo '</label>';
    echo '</th>';
    echo '<td>';

    $required_attr = $required
        ? ' required aria-required="true"'
        : '';

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
                esc_textarea((string) $value),
                $required_attr,
                $maxlength_attr
            );
            break;

        case 'number':

            if ($prepend !== '' || $append !== '') {
                echo '<div class="ff-input-affix-wrap">';
                echo $prepend_html;
            }

            echo '<input type="number" class="small-text" name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '" value="' . esc_attr((string) $value) . '"' . $required_attr . '>';

            if ($prepend !== '' || $append !== '') {
                echo $append_html;
                echo '</div>';
            }

            break;

        case 'email':
        case 'url':
        case 'text':
            $input = in_array($type, ['email', 'url'], true)
                ? $type
                : 'text';

            $supports_affixes = in_array(
                $type,
                ['text', 'email'],
                true
            );

            if ($supports_affixes && ($prepend !== '' || $append !== '')) {
                echo '<div class="ff-affix-input">';

                if ($prepend !== '') {
                    echo '<span class="ff-affix-input__addon ff-affix-input__addon--prepend">'
                        . esc_html($prepend)
                        . '</span>';
                }
            }

            echo '<input type="' . esc_attr($input) . '"'
                . ' class="' . ($supports_affixes && ($prepend !== '' || $append !== '') ? 'ff-affix-input__field' : 'regular-text') . '"'
                . ' name="' . esc_attr($meta_key) . '"'
                . ' id="' . esc_attr($meta_key) . '"'
                . ' value="' . esc_attr((string) $value) . '"'
                . $required_attr
                . $maxlength_attr
                . '>';

            if ($supports_affixes && ($prepend !== '' || $append !== '')) {
                if ($append !== '') {
                    echo '<span class="ff-affix-input__addon ff-affix-input__addon--append">'
                        . esc_html($append)
                        . '</span>';
                }

                echo '</div>';
            }

            break;

        case 'password':

            if ($prepend !== '' || $append !== '') {
                echo '<div class="ff-input-affix-wrap">';
                echo $prepend_html;
            }

            echo '<div class="ff-password-wrap">';
            echo '<input type="password" class="regular-text ff-password-input" name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '" value="' . esc_attr((string) $value) . '" autocomplete="off"' . $required_attr . $maxlength_attr . '>';
            echo '<button type="button" class="ff-password-toggle" data-target="#' . esc_attr($meta_key) . '" aria-label="Show password" aria-controls="' . esc_attr($meta_key) . '">';
            echo '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>';
            echo '</button>';
            echo '</div>';

            if ($prepend !== '' || $append !== '') {
                echo $append_html;
                echo '</div>';
            }

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
            echo '<select name="' . esc_attr($meta_key) . '" id="' . esc_attr($meta_key) . '"' . $required_attr . '>';
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

            echo '<div class="ff-true-false-control">';

            echo '<label class="ff-toggle-field">';

            echo '<input
        type="checkbox"
        class="ff-true-false-input"
        name="' . esc_attr($meta_key) . '"
        id="' . esc_attr($meta_key) . '"
        value="1"'
                . checked($checked, true, false)
                . '>';

            echo '<span class="ff-toggle" aria-hidden="true"></span>';

            echo '</label>';

            echo '<span class="ff-true-false-label">'
                . ($checked ? 'True' : 'False')
                . '</span>';

            echo '</div>';

            break;
    }

    echo '</td>';
    echo '</tr>';
}


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
                ff_render_metabox_field_row(
                    $field,
                    $post,
                    $group_id
                );
            }

            echo '</tbody></table>';
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '<table class="form-table"><tbody>';

        foreach ($fields as $field) {
            ff_render_metabox_field_row(
                $field,
                $post,
                $group_id
            );
        }

        echo '</tbody></table>';
    }

    echo '</div>';
}


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

    foreach ($groups as $group_id => $group) {

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

            $key = ff_get_post_meta_key(
                $group_id,
                $name
            );

            if ($key === '') {
                continue;
            }

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
                delete_post_meta($post_id, $key);
            } else {
                update_post_meta($post_id, $key, $val);
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
function ff_get_field($field_name, $post_id = null, $group_id = null)
{
    $field_name = sanitize_key((string) $field_name);

    if ($field_name === '') {
        return '';
    }

    /**
     * Global/options lookup.
     *
     * Global Fields continue using their existing site-wide
     * storage and are not stored as post meta.
     */
    if ($post_id === 'global' || $post_id === 'option') {
        $global = get_option('ff_global_fields', []);

        return isset($global[$field_name])
            ? $global[$field_name]
            : '';
    }


    if ($post_id === null) {
        $post_id = get_the_ID();
    }

    $post_id = absint($post_id);

    if (! $post_id) {
        return '';
    }

    $groups = ff_get_all_groups();

    if (empty($groups) || ! is_array($groups)) {
        return '';
    }

    /**
     * An explicit Forge Key removes any ambiguity.
     *
     * Example:
     * ff_get_field(
     *     'meta_title',
     *     null,
     *     'ff_group_6a997bba1fdae'
     * );
     */
    if ($group_id !== null && $group_id !== '') {

        $group_id = sanitize_key((string) $group_id);

        if (
            $group_id === ''
            || ! isset($groups[$group_id])
            || ! is_array($groups[$group_id])
        ) {
            return '';
        }

        $group = $groups[$group_id];

        if (($group['status'] ?? 'active') !== 'active') {
            return '';
        }

        $field_exists = false;

        foreach ((array) ($group['fields'] ?? []) as $field) {

            if (
                isset($field['name'])
                && sanitize_key((string) $field['name']) === $field_name
            ) {
                $field_exists = true;
                break;
            }
        }

        if (! $field_exists) {
            return '';
        }

        $meta_key = ff_get_post_meta_key(
            $group_id,
            $field_name
        );

        return $meta_key !== ''
            ? get_post_meta($post_id, $meta_key, true)
            : '';
    }

    /**
     * No Forge Key was supplied.
     *
     * Find active Field Groups that:
     * - apply to this post type,
     * - apply to this specific post when targeted,
     * - contain the requested field name.
     */
    $matching_group_ids = [];

    $post_type = get_post_type($post_id);

    if (! $post_type) {
        return '';
    }

    foreach ($groups as $candidate_group_id => $group) {

        if (! is_array($group)) {
            continue;
        }

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

        if ($location !== $post_type) {
            continue;
        }

        $target = isset($group['location_target'])
            ? (string) $group['location_target']
            : '';

        if (
            $target !== ''
            && (string) $post_id !== $target
        ) {
            continue;
        }

        foreach ((array) ($group['fields'] ?? []) as $field) {

            $candidate_name = isset($field['name'])
                ? sanitize_key((string) $field['name'])
                : '';

            if ($candidate_name !== $field_name) {
                continue;
            }

            $matching_group_ids[] = $candidate_group_id;
            break;
        }
    }


    if (empty($matching_group_ids)) {
        return '';
    }

    /**
     * More than one applicable group contains the same field name.
     *
     * Do not guess which value the developer intended.
     * They must supply the Forge Key as the third argument.
     */
    if (count($matching_group_ids) > 1) {

        if (defined('WP_DEBUG') && WP_DEBUG) {
            trigger_error(
                sprintf(
                    'Forge Fields: Field "%s" exists in multiple applicable Field Groups. Choose a unique name or specify a Forge Key as the third argument to ff_get_field().',
                    $field_name
                ),
                E_USER_WARNING
            );
        }

        return '';
    }

    $resolved_group_id = reset($matching_group_ids);

    $meta_key = ff_get_post_meta_key(
        $resolved_group_id,
        $field_name
    );

    if ($meta_key === '') {
        return '';
    }


    if (metadata_exists('post', $post_id, $meta_key)) {
        return get_post_meta(
            $post_id,
            $meta_key,
            true
        );
    }

    /**
     * Backwards compatibility for legacy Forge Fields values.
     *
     * Because we have already established that exactly one
     * applicable Field Group owns this field name, the legacy
     * value can be migrated safely.
     */
    $legacy_meta_key = '_ff_' . $field_name;

    if (metadata_exists('post', $post_id, $legacy_meta_key)) {

        $legacy_value = get_post_meta(
            $post_id,
            $legacy_meta_key,
            true
        );

        update_post_meta(
            $post_id,
            $meta_key,
            $legacy_value
        );

        delete_post_meta(
            $post_id,
            $legacy_meta_key
        );

        return $legacy_value;
    }

    return '';
}


function ff_get_global($name, $default = '')
{
    $value = ff_get_field($name, 'global');

    if ($value === '') {
        return $default;
    }

    return $value;
}


if (! function_exists('ff_parse_choices_string')) {

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
 * Retrieve the configured choices for a Forge Fields choice field.
 *
 * Supported field types:
 * - Select
 * - Checkbox
 * - Radio
 * - Button Group
 *
 * By default, the field is resolved against the current post/page.
 * Passing "global" or "option" searches Global Field Groups.
 * A Forge Group Key may be supplied as the third argument when the
 * same field name exists in multiple Field Groups.
 *
 * Example:
 *
 * ff_get_field_choices('color');
 *
 * Returns:
 *
 * [
 *     'red'   => 'Red',
 *     'green' => 'Green',
 *     'blue'  => 'Blue',
 * ]
 *
 * @param string          $field_name Field name.
 * @param int|string|null $post_id    Post ID, "global", "option", or null.
 * @param string|null     $group_id   Optional Forge Group Key.
 *
 * @return array Configured value => label choices, or an empty array.
 */
function ff_get_field_choices(
    $field_name,
    $post_id = null,
    $group_id = null
) {
    $field_name = sanitize_key(
        (string) $field_name
    );

    if ($field_name === '') {
        return [];
    }

    $choice_types = [
        'select',
        'checkbox',
        'radio',
        'button_group',
    ];

    $groups = ff_get_all_groups();

    if (empty($groups) || ! is_array($groups)) {
        return [];
    }

    /**
     * Explicit Forge Group Key.
     *
     * When supplied, the group itself resolves any ambiguity.
     */
    if ($group_id !== null && $group_id !== '') {

        $group_id = sanitize_key(
            (string) $group_id
        );

        if (
            $group_id === ''
            || ! isset($groups[$group_id])
            || ! is_array($groups[$group_id])
        ) {
            return [];
        }

        $group = $groups[$group_id];

        if (($group['status'] ?? 'active') !== 'active') {
            return [];
        }

        foreach ((array) ($group['fields'] ?? []) as $field) {

            $candidate_name = isset($field['name'])
                ? sanitize_key(
                    (string) $field['name']
                )
                : '';

            if ($candidate_name !== $field_name) {
                continue;
            }

            $type = isset($field['type'])
                ? sanitize_key(
                    (string) $field['type']
                )
                : '';

            if (! in_array($type, $choice_types, true)) {
                return [];
            }

            return ff_parse_choices_string(
                $field['choices'] ?? ''
            );
        }

        return [];
    }


    $is_global =
        $post_id === 'global'
        || $post_id === 'option';


    if (! $is_global) {

        if ($post_id === null) {
            $post_id = get_the_ID();
        }

        $post_id = absint($post_id);

        if (! $post_id) {
            return [];
        }

        $post_type = get_post_type($post_id);

        if (! $post_type) {
            return [];
        }
    }

    $matching_fields = [];

    foreach ($groups as $candidate_group_id => $group) {

        if (! is_array($group)) {
            continue;
        }

        if (($group['status'] ?? 'active') !== 'active') {
            continue;
        }

        $location = isset($group['location'])
            ? sanitize_key(
                (string) $group['location']
            )
            : 'page';


        if ($is_global) {

            if ($location !== 'global') {
                continue;
            }
        } else {


            if (
                ! in_array($location, ['page', 'post'], true)
                || $location !== $post_type
            ) {
                continue;
            }

            $target = isset($group['location_target'])
                ? (string) $group['location_target']
                : '';

            if (
                $target !== ''
                && (string) $post_id !== $target
            ) {
                continue;
            }
        }

        foreach ((array) ($group['fields'] ?? []) as $field) {

            $candidate_name = isset($field['name'])
                ? sanitize_key(
                    (string) $field['name']
                )
                : '';

            if ($candidate_name !== $field_name) {
                continue;
            }

            $type = isset($field['type'])
                ? sanitize_key(
                    (string) $field['type']
                )
                : '';

            if (! in_array($type, $choice_types, true)) {
                return [];
            }

            $matching_fields[] = [
                'group_id' => $candidate_group_id,
                'field'    => $field,
            ];

            break;
        }
    }


    if (empty($matching_fields)) {
        return [];
    }


    if (count($matching_fields) > 1) {

        if (defined('WP_DEBUG') && WP_DEBUG) {

            trigger_error(
                sprintf(
                    'Forge Fields: Choice field "%s" exists in multiple applicable Field Groups. Specify a Forge Group Key as the third argument to ff_get_field_choices().',
                    $field_name
                ),
                E_USER_WARNING
            );
        }

        return [];
    }

    $field = $matching_fields[0]['field'];

    return ff_parse_choices_string(
        $field['choices'] ?? ''
    );
}


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


function ff_save_all_groups(array $groups)
{
    update_option(
        'ff_field_groups',
        $groups
    );


    ff_get_all_groups(true);
}
