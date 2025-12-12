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
add_action( 'add_meta_boxes', function () {
    global $ff_field_groups;

    if ( empty( $ff_field_groups ) || ! is_array( $ff_field_groups ) ) {
        return;
    }

    foreach ( $ff_field_groups as $group_id => $group ) {

        $location = isset( $group['location'] ) ? $group['location'] : 'page';

        // Only attach to real post editors for now.
        if ( $location !== 'page' && $location !== 'post' ) {
            // 'global' will later become an options-style screen.
            continue;
        }

        add_meta_box(
            'ff_field_group_' . $group_id,
            esc_html( $group['title'] ),
            'ff_render_field_group_metabox',
            $location,           // post_type: 'post' or 'page'
            'normal',
            'default',
            [ 'group_id' => $group_id ]
        );
    }
});

/**
 * Render fields for a given Forge Fields meta box.
 */
function ff_render_field_group_metabox( $post, $box ) {
    global $ff_field_groups;

    $group_id = isset( $box['args']['group_id'] ) ? $box['args']['group_id'] : null;

    if ( ! $group_id || ! isset( $ff_field_groups[ $group_id ] ) ) {
        return;
    }

    $group        = $ff_field_groups[ $group_id ];
    $fields       = is_array( $group['fields'] ) ? $group['fields'] : [];
    $field_types  = ff_get_field_types();

    // Nonce for this specific group.
    wp_nonce_field( 'ff_save_group_' . $group_id, 'ff_group_nonce_' . $group_id );

    echo '<div class="ff-admin">';
    echo '<table class="form-table"><tbody>';

    foreach ( $fields as $field ) {
        if ( empty( $field['name'] ) ) {
            continue;
        }

        $name  = $field['name'];
        $label = isset( $field['label'] ) ? $field['label'] : $name;
        $type  = isset( $field['type'] ) ? $field['type'] : 'text';

        $id   = 'ff_' . esc_attr( $name );
        $meta = get_post_meta( $post->ID, $name, true );

        echo '<tr>';
        echo '<th scope="row"><label for="' . $id . '">' . esc_html( $label ) . '</label></th>';
        echo '<td>';

        // Look up type renderer, fall back to "text".
        $render_cb = isset( $field_types[ $type ]['render'] )
            ? $field_types[ $type ]['render']
            : $field_types['text']['render'];

        if ( is_callable( $render_cb ) ) {
            call_user_func( $render_cb, $field, $meta, $post );
        } else {
            // Extremely safe fallback: simple text field.
            ff_render_type_text( $field, $meta, $post );
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
    global $ff_field_groups;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }
    if ( empty( $ff_field_groups ) || ! is_array( $ff_field_groups ) ) {
        return;
    }

    $post        = get_post( $post_id );
    $field_types = ff_get_field_types();

    if ( ! $post ) {
        return;
    }

    foreach ( $ff_field_groups as $group_id => $group ) {

        $location = isset( $group['location'] ) ? $group['location'] : 'page';

        // Only save for matching post types.
        if (
            ( $location === 'post' && $post->post_type !== 'post' ) ||
            ( $location === 'page' && $post->post_type !== 'page' )
        ) {
            continue;
        }

        $nonce_key = 'ff_group_nonce_' . $group_id;
        if (
            ! isset( $_POST[ $nonce_key ] ) ||
            ! wp_verify_nonce( $_POST[ $nonce_key ], 'ff_save_group_' . $group_id )
        ) {
            continue;
        }

        if ( empty( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
            continue;
        }

        foreach ( $group['fields'] as $field ) {
            if ( empty( $field['name'] ) ) {
                continue;
            }

            $name = $field['name'];
            $type = isset( $field['type'] ) ? $field['type'] : 'text';

            if ( isset( $_POST[ $name ] ) ) {
                $raw = wp_unslash( $_POST[ $name ] );

                // Look up type sanitizer, fall back to text sanitizer.
                $sanitize_cb = isset( $field_types[ $type ]['sanitize'] )
                    ? $field_types[ $type ]['sanitize']
                    : $field_types['text']['sanitize'];

                if ( is_callable( $sanitize_cb ) ) {
                    $value = call_user_func( $sanitize_cb, $raw, $field, $post_id );
                } else {
                    $value = sanitize_text_field( $raw );
                }

                // Global filters for further validation if needed.
                $value = apply_filters( 'ff_sanitize_value', $value, $field, $post_id );
                $value = apply_filters( "ff_sanitize_value_{$type}", $value, $field, $post_id );

                update_post_meta( $post_id, $name, $value );
            } else {
                delete_post_meta( $post_id, $name );
            }
        }
    }
} );

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

    // Default: treat $context as a post ID.
    $post_id = intval( $context );

    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    if ( ! $post_id ) {
        return '';
    }

    $value = get_post_meta( $post_id, $name, true );

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

