<?php
/**
 * Plugin Name: Forge Fields
 * Description: Lightweight custom fields framework.
 * Version:     0.1.1
 * Author:      Forge Plugins
 * License:     GPL2+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FF_VERSION', '0.1.1' );
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
 * Enqueue admin JS/CSS for Forge Fields.
 * - On Forge Fields screens
 * - On post/page editors where Forge Fields meta boxes appear
 */
function ff_enqueue_admin_assets( $hook ) {
    $page         = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
    $is_ff_screen = ( strpos( $hook, 'forge-fields' ) !== false )
        || in_array( $page, [ 'forge-fields', 'forge-fields-edit', 'forge-fields-global' ], true );

    $is_editor_screen = in_array( $hook, [ 'post.php', 'post-new.php' ], true );

    if ( ! $is_ff_screen && ! $is_editor_screen ) {
        return;
    }

    wp_enqueue_media();

    if ( function_exists( 'wp_enqueue_editor' ) ) {
        wp_enqueue_editor();
    }

    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'editor-buttons' );

    wp_enqueue_style(
        'ff-admin-fields',
        FF_URL . 'assets/css/admin-fields.css',
        [],
        FF_VERSION
    );

    wp_enqueue_script(
        'ff-admin-fields',
        FF_URL . 'assets/js/admin-fields.js',
        [ 'jquery', 'media-editor', 'media-views', 'wp-util' ],
        FF_VERSION,
        true
    );

    if ( $is_ff_screen ) {
        wp_enqueue_style( 'common' );
    }
}

/**
 * On init, load all field groups from the database
 * and register them with Forge Fields.
 */
add_action( 'init', 'ff_boot_field_groups' );
add_action( 'admin_enqueue_scripts', 'ff_enqueue_admin_assets' );