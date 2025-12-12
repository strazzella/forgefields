<?php
/**
 * Plugin Name: Forge Fields
 * Description: Lightweight custom fields framework.
 * Version:     0.1.0
 * Author:      Forge Plugins
 * License:     GPL2+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FF_PATH', plugin_dir_path( __FILE__ ) );
define( 'FF_URL',  plugin_dir_url( __FILE__ ) );

// Core field-group & metabox logic.
require_once FF_PATH . 'includes/core.php';

// Admin UI (only in dashboard).
if ( is_admin() ) {
    require_once FF_PATH . 'includes/admin-page.php';
    require_once FF_PATH . 'includes/admin-ui.php';
}

/**
 * On init, load all field groups from the database
 * and register them with Forge Fields.
 *
 * Uses the new `ff_field_groups` option. If that’s empty but the old
 * single-group `ff_field_group` option exists, we migrate it.
 */
add_action( 'init', function() {

    $groups = get_option( 'ff_field_groups', null );

    if ( $groups === null ) {
        $legacy = get_option( 'ff_field_group', [] );

        if ( is_array( $legacy ) && ! empty( $legacy ) ) {
            if ( empty( $legacy['id'] ) ) {
                $legacy['id'] = 'ff_legacy_group';
            }

            $groups = [ $legacy['id'] => $legacy ];
            update_option( 'ff_field_groups', $groups );
            delete_option( 'ff_field_group' );
        } else {
            $groups = [];
            update_option( 'ff_field_groups', $groups );
        }
    }

    if ( ! is_array( $groups ) || empty( $groups ) ) {
        return;
    }

    foreach ( $groups as $group ) {
        if ( ! is_array( $group ) ) {
            continue;
        }

        // 🔹 Only load *active* groups.
        $status = isset( $group['status'] ) ? $group['status'] : 'active';
        if ( $status !== 'active' ) {
            continue;
        }

        if ( empty( $group['id'] ) ) {
            $group['id'] = uniqid( 'ff_group_' );
        }

        ff_register_field_group( $group );
    }
} );


/**
 * Enqueue admin JS/CSS for Forge Fields.
 * - On Forge Fields screens (list/edit/global)
 * - On post/page editors where the metabox appears
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    // Detect our plugin screens
    $page         = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
    $is_ff_screen = ( strpos( $hook, 'forge-fields' ) !== false )
                    || in_array( $page, [ 'forge-fields','forge-fields-edit','forge-fields-global' ], true );

    // Classic post/page editors (meta boxes)
    $is_editor_screen = in_array( $hook, [ 'post.php', 'post-new.php' ], true );

    if ( ! $is_ff_screen && ! $is_editor_screen ) {
        return;
    }

    // Core deps
    wp_enqueue_media();                // image/file picker
    if ( function_exists( 'wp_enqueue_editor' ) ) {
        wp_enqueue_editor();           // WYSIWYG editor
    }
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'editor-buttons' ); // TinyMCE/Quicktags toolbar styling

    // Your assets
    wp_enqueue_style(
        'ff-admin-fields',
        FF_URL . 'assets/css/admin-fields.css',
        [],
        '0.1.0'
    );

    wp_enqueue_script(
        'ff-admin-fields',
        FF_URL . 'assets/js/admin-fields.js',
        [ 'jquery', 'media-editor', 'media-views', 'wp-util' ],
        '0.1.0',
        true
    );

    // Optional: WP admin base styles on Forge Fields pages
    if ( $is_ff_screen ) {
        wp_enqueue_style( 'common' );
        // wp_enqueue_style( 'list-tables' );
    }
});



