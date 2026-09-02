<?php

/**
 * Plugin Name:       Forge Fields
 * Plugin URI:        https://your-future-plugin-site.com/
 * Description:       Lightweight custom fields framework for WordPress with support for field groups, global fields, media fields, choice fields, and developer-friendly template functions.
 * Version:           0.1.4
 * Author:            Forge Tools
 * Author URI:        https://your-future-site.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Text Domain:       forge-fields
 */


/**
 * Prevent direct access to this file outside of WordPress.
 */
if (! defined('ABSPATH')) {
    exit;
}


/**
 * Core Forge Fields constants.
 *
 * FF_VERSION is used for plugin versioning and asset cache busting.
 * FF_PATH provides the absolute filesystem path to the plugin.
 * FF_URL provides the public URL to the plugin directory.
 */
define('FF_VERSION', '0.1.4');

define('FF_PATH', plugin_dir_path(__FILE__));

define('FF_URL', plugin_dir_url(__FILE__));


/**
 * Load the core Forge Fields functionality.
 *
 * Core functionality is loaded on both the front end and in the
 * WordPress administration area.
 */
require_once FF_PATH . 'includes/core.php';


/**
 * Load admin-only Forge Fields functionality.
 *
 * These files are only required when WordPress is processing
 * an administration request.
 */
if (is_admin()) {

    require_once FF_PATH . 'includes/admin-page.php';

    require_once FF_PATH . 'includes/admin-ui.php';

    require_once FF_PATH . 'includes/import-export.php';
}


/**
 * Enqueue Forge Fields admin assets.
 *
 * Loads the scripts, styles, WordPress media library, editor assets,
 * and supporting dependencies required by Forge Fields screens and
 * post/page editor field groups.
 *
 * Assets are only loaded on Forge Fields administration screens or
 * WordPress post/page editor screens to avoid loading unnecessary
 * resources throughout the rest of the WordPress admin area.
 *
 * @param string $hook Current WordPress admin page hook.
 */
function ff_enqueue_admin_assets($hook)
{
    /**
     * Determine whether the current request is a Forge Fields screen.
     */
    $page         = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';

    $is_ff_screen = (strpos($hook, 'forge-fields') !== false)
        || in_array($page, ['forge-fields', 'forge-fields-edit', 'forge-fields-global'], true);

    /**
     * Determine whether the current screen is the WordPress
     * post/page editor.
     */
    $is_editor_screen = in_array($hook, ['post.php', 'post-new.php'], true);

    /**
     * Do not load Forge Fields assets on unrelated admin screens.
     */
    if (! $is_ff_screen && ! $is_editor_screen) {
        return;
    }

    /**
     * Load the WordPress media library used by Image and File fields.
     */
    wp_enqueue_media();

    /**
     * Load the WordPress editor API when available for WYSIWYG fields.
     */
    if (function_exists('wp_enqueue_editor')) {
        wp_enqueue_editor();
    }

    /**
     * Load WordPress-provided admin styles used by Forge Fields.
     */
    wp_enqueue_style('dashicons');

    wp_enqueue_style('editor-buttons');

    /**
     * Load the Inter font used by the Forge Fields admin interface.
     */
    wp_enqueue_style(
        'ff-font-inter',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
        [],
        null
    );

    /**
     * Load Forge Fields admin styles.
     */
    wp_enqueue_style(
        'ff-admin-fields',
        FF_URL . 'assets/css/admin-fields.css',
        [],
        FF_VERSION
    );

    /**
     * Load Forge Fields admin JavaScript and its WordPress dependencies.
     */
    wp_enqueue_script(
        'ff-admin-fields',
        FF_URL . 'assets/js/admin-fields.js',
        ['jquery', 'media-editor', 'media-views', 'wp-util'],
        FF_VERSION,
        true
    );

    /**
     * Load the WordPress common admin stylesheet on Forge Fields
     * screens where native WordPress admin components are used.
     */
    if ($is_ff_screen) {
        wp_enqueue_style('common');
    }
}


/**
 * Register saved Forge Fields field groups during WordPress initialization.
 */
add_action('init', 'ff_boot_field_groups');


/**
 * Load Forge Fields assets on supported WordPress admin screens.
 */
add_action('admin_enqueue_scripts', 'ff_enqueue_admin_assets');
