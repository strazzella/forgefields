<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Runtime registry of all field groups loaded on init.
 */
global $ff_field_groups;
$ff_field_groups = [];

/**
 * -------------------------------------------------------------------------
 * FIELD TYPE REGISTRY (first step of abstraction)
 * -------------------------------------------------------------------------
 *
 * Each field type declares:
 *   - 'render'   => callable( $field, $value, $post )
 *   - 'sanitize' => callable( $raw_value, $field, $post_id )
 *
 * You can filter this via `ff_field_types` later to add custom types.
 */
function ff_get_field_types() {
    static $types = null;

    if ( $types === null ) {
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
        ];

        // Allow plugins/themes to modify or add field types.
        $types = apply_filters( 'ff_field_types', $types );
    }

    return $types;
}

/**
 * -------------------------------------------------------------------------
 * FIELD TYPE RENDERERS
 * -------------------------------------------------------------------------
 * These only render the <input>/<textarea> itself.
 * The <tr>, <th>, label etc. stay in ff_render_field_group_metabox().
 */

function ff_render_type_text( array $field, $value, WP_Post $post ) {
    $name = $field['name'];
    $id   = 'ff_' . esc_attr( $name );

    printf(
        '<input type="text" name="%1$s" id="%2$s" value="%3$s" class="regular-text" />',
        esc_attr( $name ),
        esc_attr( $id ),
        esc_attr( $value )
    );
}

function ff_render_type_textarea( array $field, $value, WP_Post $post ) {
    $name = $field['name'];
    $id   = 'ff_' . esc_attr( $name );

    printf(
        '<textarea name="%1$s" id="%2$s" rows="3" class="large-text">%3$s</textarea>',
        esc_attr( $name ),
        esc_attr( $id ),
        esc_textarea( $value )
    );
}

function ff_render_type_number( array $field, $value, WP_Post $post ) {
    $name = $field['name'];
    $id   = 'ff_' . esc_attr( $name );

    printf(
        '<input type="number" name="%1$s" id="%2$s" value="%3$s" class="regular-text ff-number-input" />',
        esc_attr( $name ),
        esc_attr( $id ),
        esc_attr( $value )
    );
}

function ff_render_type_email( array $field, $value, WP_Post $post ) {
    $name = $field['name'];
    $id   = 'ff_' . esc_attr( $name );

    printf(
        '<input type="email" name="%1$s" id="%2$s" value="%3$s" class="regular-text" />',
        esc_attr( $name ),
        esc_attr( $id ),
        esc_attr( $value )
    );
}

function ff_render_type_url( array $field, $value, WP_Post $post ) {
    $name = $field['name'];
    $id   = 'ff_' . esc_attr( $name );

    printf(
        '<input type="url" name="%1$s" id="%2$s" value="%3$s" class="regular-text" />',
        esc_attr( $name ),
        esc_attr( $id ),
        esc_attr( $value )
    );
}

function ff_render_type_range( array $field, $value, WP_Post $post ) {
    $name = $field['name'];

    // default range 0–100
    $min = isset( $field['min'] ) ? (int) $field['min'] : 0;
    $max = isset( $field['max'] ) ? (int) $field['max'] : 100;

    // normalise current value
    $value = ( $value === '' ? $min : (int) $value );

    $id_slider = 'ff_' . esc_attr( $name ) . '_range';
    $id_number = 'ff_' . esc_attr( $name ) . '_number';
    ?>
    <div class="ff-range-wrap">
        <input
            type="range"
            name="<?php echo esc_attr( $name ); ?>"
            id="<?php echo esc_attr( $id_slider ); ?>"
            class="ff-range-slider"
            min="<?php echo esc_attr( $min ); ?>"
            max="<?php echo esc_attr( $max ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            data-target="#<?php echo esc_attr( $id_number ); ?>"
        />
        <input
            type="number"
            id="<?php echo esc_attr( $id_number ); ?>"
            class="small-text ff-range-number"
            min="<?php echo esc_attr( $min ); ?>"
            max="<?php echo esc_attr( $max ); ?>"
            value="<?php echo esc_attr( $value ); ?>"
            data-target="#<?php echo esc_attr( $id_slider ); ?>"
        />
    </div>
    <?php
}



function ff_render_type_password( array $field, $value, WP_Post $post ) {
    $name = $field['name'];
    $id   = 'ff_' . esc_attr( $name );

    echo '<div class="ff-password-wrap">';
    echo '<input type="password" class="regular-text ff-password-input" id="' . esc_attr( $id )
    . '" name="' . esc_attr( $name )
    . '" value="' . esc_attr( $value ) . '">';

    echo '<button type="button" class="ff-password-toggle" data-target="#' . esc_attr( $id ) . '" aria-label="Show password">
            <span class="dashicons dashicons-hidden"></span>
        </button>';

    echo '</div>';

}




/**
 * -------------------------------------------------------------------------
 * FIELD TYPE SANITIZERS
 * -------------------------------------------------------------------------
 * Tiny wrappers for now, but this is where per-type rules live.
 */

function ff_sanitize_type_text( $raw, array $field, $post_id ) {
    return sanitize_text_field( $raw );
}

function ff_sanitize_type_textarea( $raw, array $field, $post_id ) {
    // Could allow some HTML here later if you want.
    return sanitize_textarea_field( $raw );
}

function ff_sanitize_type_number( $raw, array $field, $post_id ) {
    // Basic numeric sanitization – keep empty if not numeric.
    $raw = trim( (string) $raw );
    if ( $raw === '' ) {
        return '';
    }
    return is_numeric( $raw ) ? $raw + 0 : '';
}

function ff_sanitize_type_email( $raw, array $field, $post_id ) {
    $san = sanitize_email( $raw );
    return $san ? $san : '';
}

function ff_sanitize_type_url( $raw, array $field, $post_id ) {
    $raw = trim( $raw );

    if ( $raw === '' ) {
        return '';
    }

    // If user types example.com or www.example.com, prepend https://
    if (
        strpos( $raw, 'http://' ) !== 0 &&
        strpos( $raw, 'https://' ) !== 0
    ) {
        $raw = 'https://' . $raw;
    }

    // Final sanitize using WP helper
    $san = esc_url_raw( $raw );

    return $san ? $san : '';
}

function ff_sanitize_type_range( $raw, array $field, $post_id ) {
    $raw = trim( (string) $raw );
    if ( $raw === '' ) {
        return '';
    }
    if ( ! is_numeric( $raw ) ) {
        return '';
    }

    $value = (float) $raw;

    $min = isset( $field['min'] ) ? (float) $field['min'] : 0;
    $max = isset( $field['max'] ) ? (float) $field['max'] : 100;

    if ( $value < $min ) $value = $min;
    if ( $value > $max ) $value = $max;

    return $value;
}

function ff_sanitize_type_password( $raw, array $field, $post_id ) {
    // basically text, but without trimming the middle or stripping symbols
    $raw = (string) $raw;
    $raw = wp_check_invalid_utf8( $raw );
    $raw = trim( $raw );

    /**
     * You can tighten this later if you want specific password rules.
     */
    return $raw;
}


/**
 * Register a field group at runtime.
 *
 * Called from forge-fields.php on init after loading ff_field_groups.
 */
function ff_register_field_group( array $group ) {
    global $ff_field_groups;

    if ( empty( $group['id'] ) ) {
        return;
    }

    $defaults = [
        'title'    => '',
        'location' => 'page', // 'page' | 'post' | 'global'
        'fields'   => [],
    ];

    $group = wp_parse_args( $group, $defaults );

    if ( ! is_array( $group['fields'] ) ) {
        $group['fields'] = [];
    }

    $ff_field_groups[ $group['id'] ] = $group;
}

/**
 * Attach meta boxes for all registered groups on posts/pages.
 */
add_action('add_meta_boxes', function () {
    // Prefer the helper over a global
    $groups = ff_get_all_groups();
    if (empty($groups) || !is_array($groups)) {
        return;
    }

    foreach ($groups as $group_id => $group) {
        $location = isset($group['location']) ? $group['location'] : 'page';

        // Only attach to real post editors for now.
        if (!in_array($location, ['page','post'], true)) {
            continue; // 'global' is handled on your options screen
        }

        add_meta_box(
            'ff_field_group_' . $group_id,
            esc_html($group['title']),
            'ff_render_field_group_metabox', // <- this function must exist (renderer I sent)
            $location,                       // 'post' or 'page'
            'normal',
            'default',
            ['group_id' => $group_id]
        );
    }
});


/**
 * Render fields for a given Forge Fields meta box.
 */
function ff_render_field_group_metabox( $post, $box ) {
    // Make sure the group exists
    $group_id = isset( $box['args']['group_id'] ) ? $box['args']['group_id'] : '';
    $groups   = ff_get_all_groups();

    if ( ! $group_id || empty( $groups[ $group_id ] ) ) {
        echo '<p>Group not found.</p>';
        return;
    }

    $group  = $groups[ $group_id ];
    $fields = is_array( $group['fields'] ?? null ) ? $group['fields'] : [];

    if ( empty( $fields ) ) {
        echo '<p><em>No fields defined in this group.</em></p>';
        return;
    }

    // One nonce for all fields in this box
    wp_nonce_field( 'ff_save_post_fields', 'ff_meta_nonce' );

    echo '<div class="ff-mb">';
    echo '<table class="form-table"><tbody>';

    foreach ( $fields as $field ) {
        $name = $field['name'] ?? '';
        if ( $name === '' ) {
            continue;
        }

        $label   = $field['label'] ?? $name;
        $type    = $field['type']  ?? 'text';
        $meta_key = '_ff_' . $name; // meta key used for storage
        $value    = get_post_meta( $post->ID, $meta_key, true );

        echo '<tr>';
        echo '<th scope="row"><label for="'. esc_attr( $meta_key ) .'">'. esc_html( $label ) .'</label></th>';
        echo '<td>';

        switch ( $type ) {

            case 'wysiwyg':
                $editor_id = 'ff_' . sanitize_key( $name );
                wp_editor(
                    is_string( $value ) ? $value : '',
                    $editor_id,
                    [
                        'textarea_name' => $meta_key,   // IMPORTANT: matches save_post
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

            case 'image':
                $img_src = '';
                if ( $value ) {
                    $src = wp_get_attachment_image_src( (int) $value, 'thumbnail' );
                    if ( $src ) {
                        $img_src = $src[0];
                    }
                }
                ?>
                <div class="ff-media-wrap" data-type="image">
                    <input type="hidden"
                           name="<?php echo esc_attr( $meta_key ); ?>"
                           id="<?php echo esc_attr( $meta_key ); ?>"
                           value="<?php echo esc_attr( $value ); ?>">
                    <div class="ff-media-preview-wrap" style="margin-bottom:8px;">
                        <img class="ff-media-preview"
                             src="<?php echo esc_url( $img_src ); ?>"
                             style="<?php echo $img_src ? '' : 'display:none;'; ?>max-height:80px;border-radius:4px;">
                    </div>
                    <button type="button" class="button ff-media-select" data-target="<?php echo esc_attr( $meta_key ); ?>">Select Image</button>
                    <button type="button" class="button ff-media-clear"  data-target="<?php echo esc_attr( $meta_key ); ?>" style="<?php echo $value ? '' : 'display:none;'; ?>">Clear</button>
                </div>
                <?php
                break;

            case 'file':
                $file_url  = $value ? wp_get_attachment_url( (int) $value ) : '';
                $file_name = $file_url ? wp_basename( $file_url ) : '';
                ?>
                <div class="ff-media-wrap" data-type="file">
                    <input type="hidden"
                           name="<?php echo esc_attr( $meta_key ); ?>"
                           id="<?php echo esc_attr( $meta_key ); ?>"
                           value="<?php echo esc_attr( $value ); ?>">
                    <div class="ff-media-fileline" style="margin-bottom:8px;">
                        <span class="dashicons dashicons-media-document" aria-hidden="true"></span>
                        <a class="ff-media-fileurl"
                           href="<?php echo esc_url( $file_url ); ?>"
                           target="_blank"
                           style="<?php echo $file_url ? '' : 'display:none;'; ?>">
                            <?php echo esc_html( $file_name ); ?>
                        </a>
                        <span class="ff-media-nofile" style="<?php echo $file_url ? 'display:none;' : ''; ?>">
                            No file selected.
                        </span>
                    </div>
                    <button type="button" class="button ff-media-select" data-target="<?php echo esc_attr( $meta_key ); ?>">Select File</button>
                    <button type="button" class="button ff-media-clear"  data-target="<?php echo esc_attr( $meta_key ); ?>" style="<?php echo $value ? '' : 'display:none;'; ?>">Clear</button>
                </div>
                <?php
                break;

            /**
             * CHOICE TYPES – THIS IS THE BIT YOU WERE MISSING
             */
            case 'select':
                $choices = function_exists( 'ff_parse_choices_string' )
                    ? ff_parse_choices_string( $field['choices'] ?? '' )
                    : [];
                $current = is_scalar( $value ) ? (string) $value : '';
                echo '<div class="ff-select-wrap">';
                echo '<select name="'. esc_attr( $meta_key ) .'" id="'. esc_attr( $meta_key ) .'">';
                foreach ( $choices as $val => $lbl ) {
                    printf(
                        '<option value="%1$s"%3$s>%2$s</option>',
                        esc_attr( $val ),
                        esc_html( $lbl ),
                        selected( $current, (string) $val, false )
                    );
                }
                echo '</select>';
                echo '</div>';
                break;

            case 'radio':
            case 'button_group':
                $choices = function_exists( 'ff_parse_choices_string' )
                    ? ff_parse_choices_string( $field['choices'] ?? '' )
                    : [];
                $current = is_scalar( $value ) ? (string) $value : '';
                foreach ( $choices as $val => $lbl ) {
                    $field_id = $meta_key . '_' . sanitize_key( (string) $val );
                    printf(
                        '<label for="%1$s" style="display:inline-block;margin-right:12px;">
                            <input type="radio" name="%2$s" id="%1$s" value="%3$s" %4$s>
                            %5$s
                        </label>',
                        esc_attr( $field_id ),
                        esc_attr( $meta_key ),
                        esc_attr( $val ),
                        checked( $current, (string) $val, false ),
                        esc_html( $lbl )
                    );
                }
                break;

            case 'checkbox':
                $choices = function_exists( 'ff_parse_choices_string' )
                    ? ff_parse_choices_string( $field['choices'] ?? '' )
                    : [];
                $current = is_array( $value ) ? array_map( 'strval', $value ) : [];
                foreach ( $choices as $val => $lbl ) {
                    $field_id = $meta_key . '_' . sanitize_key( (string) $val );
                    printf(
                        '<label for="%1$s" style="display:inline-block;margin-right:12px;">
                            <input type="checkbox" name="%2$s[]" id="%1$s" value="%3$s" %4$s>
                            %5$s
                        </label>',
                        esc_attr( $field_id ),
                        esc_attr( $meta_key ),
                        esc_attr( $val ),
                        in_array( (string) $val, $current, true ) ? 'checked' : '',
                        esc_html( $lbl )
                    );
                }
                break;

            case 'true_false':
                $checked = ! empty( $value );
                printf(
                    '<label>
                        <input type="checkbox" name="%1$s" id="%1$s" value="1" %2$s>
                        %3$s
                    </label>',
                    esc_attr( $meta_key ),
                    checked( $checked, true, false ),
                    esc_html__( 'Enabled', 'forge-fields' )
                );
                break;

            /**
             * BASIC TYPES
             */
            case 'textarea':
                echo '<textarea class="large-text" rows="4" name="'. esc_attr( $meta_key ) .'" id="'. esc_attr( $meta_key ) .'">'. esc_textarea( (string) $value ) .'</textarea>';
                break;

            case 'number':
                echo '<input type="number" class="small-text" name="'. esc_attr( $meta_key ) .'" id="'. esc_attr( $meta_key ) .'" value="'. esc_attr( (string) $value ) .'">';
                break;

            case 'range':
                $min  = isset( $field['min'] )  ? (int) $field['min']  : 0;
                $max  = isset( $field['max'] )  ? (int) $field['max']  : 100;
                $step = isset( $field['step'] ) ? (int) $field['step'] : 1;
                $val  = ( $value === '' ? $min : (int) $value );

                $slider_id = esc_attr( $meta_key ) . '_slider';
                $num_id    = esc_attr( $meta_key ) . '_num';
                ?>
                <div class="ff-range-wrap">
                    <input
                        type="range"
                        class="ff-range-slider"
                        id="<?php echo $slider_id; ?>"
                        name="<?php echo esc_attr( $meta_key ); ?>"
                        min="<?php echo esc_attr( $min ); ?>"
                        max="<?php echo esc_attr( $max ); ?>"
                        step="<?php echo esc_attr( $step ); ?>"
                        value="<?php echo esc_attr( $val ); ?>"
                        data-target="#<?php echo $num_id; ?>"
                    />
                    <input
                        type="number"
                        class="small-text ff-range-number"
                        id="<?php echo $num_id; ?>"
                        min="<?php echo esc_attr( $min ); ?>"
                        max="<?php echo esc_attr( $max ); ?>"
                        step="<?php echo esc_attr( $step ); ?>"
                        value="<?php echo esc_attr( $val ); ?>"
                        data-target="#<?php echo $slider_id; ?>"
                    />
                </div>
                <?php
                break;

            case 'email':
            case 'url':
            case 'password':
            case 'text':
            default:
                $input = in_array( $type, ['email','url','password'], true ) ? $type : 'text';
                echo '<input type="'. esc_attr( $input ) .'" class="regular-text" name="'. esc_attr( $meta_key ) .'" id="'. esc_attr( $meta_key ) .'" value="'. esc_attr( (string) $value ) .'">';
                break;
        }

        echo '</td></tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}



/**
 * Save meta values for all registered field groups.
 */
add_action( 'save_post', function( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['ff_meta_nonce'] ) || ! wp_verify_nonce( $_POST['ff_meta_nonce'], 'ff_save_post_fields' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $groups = ff_get_all_groups();

    foreach ( $groups as $group ) {
        $location = $group['location'] ?? 'page';
        if ( ! in_array( $location, [ 'post', 'page' ], true ) ) {
            continue; // ignore 'global'
        }

        foreach ( (array) ( $group['fields'] ?? [] ) as $field ) {
            $name = $field['name'] ?? '';
            $type = $field['type'] ?? 'text';

            if ( $name === '' ) {
                continue;
            }

            $key     = '_ff_' . $name;
            $has_key = array_key_exists( $key, $_POST );

            // For most types, if there's no submitted value, skip.
            // For checkboxes / true_false we treat "missing" as empty/off.
            if ( ! $has_key && ! in_array( $type, [ 'checkbox', 'true_false' ], true ) ) {
                continue;
            }

            $raw = $has_key ? $_POST[ $key ] : null;

            switch ( $type ) {

                case 'wysiwyg':
                    $val = $has_key ? wp_kses_post( wp_unslash( $raw ) ) : '';
                    break;

                case 'image':
                case 'file':
                    $val = $has_key && ! is_array( $raw ) ? (int) $raw : 0;
                    break;

                case 'number':
                case 'range':
                    if ( ! $has_key || is_array( $raw ) ) {
                        $val = '';
                    } else {
                        $raw_str = trim( (string) $raw );
                        $val     = ( $raw_str === '' || ! is_numeric( $raw_str ) ) ? '' : 0 + $raw_str;
                    }
                    break;

                case 'email':
                    $val = $has_key && ! is_array( $raw ) ? sanitize_email( $raw ) : '';
                    break;

                case 'url':
                    if ( ! $has_key || is_array( $raw ) ) {
                        $val = '';
                    } else {
                        $url = trim( (string) $raw );
                        if (
                            strpos( $url, 'http://' ) !== 0 &&
                            strpos( $url, 'https://' ) !== 0
                        ) {
                            $url = 'https://' . $url;
                        }
                        $san = esc_url_raw( $url );
                        $val = $san ? $san : '';
                    }
                    break;

                case 'select':
                case 'radio':
                case 'button_group':
                    $val = ( $has_key && ! is_array( $raw ) )
                        ? sanitize_key( (string) $raw )
                        : '';
                    break;

                case 'checkbox':
                    if ( $has_key && is_array( $raw ) ) {
                        $vals = array_map(
                            static function( $v ) {
                                return sanitize_key( (string) $v );
                            },
                            $raw
                        );
                        $vals = array_values(
                            array_filter(
                                $vals,
                                static fn( $v ) => $v !== ''
                            )
                        );
                        $val = $vals;
                    } else {
                        $val = []; // nothing checked
                    }
                    break;

                case 'true_false':
                    // treated like a single on/off checkbox
                    $val = ( $has_key && ! empty( $raw ) ) ? 1 : 0;
                    break;

                default:
                    $val = ( $has_key && ! is_array( $raw ) )
                        ? sanitize_text_field( wp_unslash( $raw ) )
                        : '';
                    break;
            }

            // Save or delete
            if ( $val === '' || $val === 0 || $val === [] ) {
                delete_post_meta( $post_id, $key );
            } else {
                update_post_meta( $post_id, $key, $val );
            }
        }
    }
});



/**
 * Front-end helper:
 *
 *   echo ff_get_field( 'hero_heading' );        // current post
 *   echo ff_get_field( 'hero_heading', 123 );   // explicit post ID
 *   echo ff_get_field( 'hero_heading', 'global' ); // global/options
 *   echo ff_get_field( 'hero_heading', 'option' ); // alias for global
 */
function ff_get_field( $name, $context = 0 ) {
    $name = sanitize_key( $name );

    if ( ! $name ) {
        return '';
    }

    // Global / options context, ACF-style.
    if (
        $context === 'global' ||
        $context === 'option' ||
        $context === 'options'
    ) {
        $all = get_option( 'ff_global_fields', [] );

        if ( ! is_array( $all ) ) {
            $all = [];
        }

        $value = isset( $all[ $name ] ) ? $all[ $name ] : '';

        return is_string( $value ) ? $value : '';
    }

    // Default: treat $context as a post ID (post/page fields).
    $post_id = intval( $context );
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    if ( ! $post_id ) {
        return '';
    }

    // Your meta boxes save with a "_ff_" prefix (e.g. "_ff_plan")
    $meta_key = '_ff_' . $name;

    $value = get_post_meta( $post_id, $meta_key, true );

    // Normalise return to string for consistency
    return is_string( $value ) ? $value : '';
}

/**
 * Get a global (site-wide) Forge Field value.
 *
 * Usage:
 *   echo ff_get_global( 'meta_title' );
 */
function ff_get_global( $name, $default = '' ) {
    $value = ff_get_field( $name, 'global' );

    if ( $value === '' ) {
        return $default;
    }

    return $value;
}

