<?php

/**
 * Plugin Name:       Forge Fields
 * Plugin URI:        https://your-future-plugin-site.com/
 * Description:       Lightweight custom fields framework for WordPress with support for field groups, global fields, media fields, choice fields, and developer-friendly template functions.
 * Version:           0.1.16
 * Author:            Forge Tools
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.8.2
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
define('FF_VERSION', '0.1.16');

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
    $page = isset($_GET['page'])
        ? sanitize_key($_GET['page'])
        : '';

    $is_ff_screen = (
        strpos($hook, 'forge-fields') !== false
        || in_array(
            $page,
            [
                'forge-fields',
                'forge-fields-edit',
                'forge-fields-global',
                'forge-fields-settings',
                'forge-fields-import-export',
                'forge-fields-uninstall',
            ],
            true
        )
    );

    /**
     * Determine whether this is a WordPress post/page editor.
     */
    $is_editor_screen = in_array(
        $hook,
        ['post.php', 'post-new.php'],
        true
    );

    /**
     * Forge Fields does not need any assets on unrelated admin screens.
     */
    if (! $is_ff_screen && ! $is_editor_screen) {
        return;
    }

    /**
     * Track which heavier WordPress dependencies are actually required.
     */
    $needs_media  = false;
    $needs_editor = false;

    /**
     * On normal post/page editor screens, only load Forge Fields assets
     * when at least one active Field Group actually applies.
     */
    if ($is_editor_screen) {

        $screen = get_current_screen();

        $post_type = ($screen && ! empty($screen->post_type))
            ? (string) $screen->post_type
            : '';

        /**
         * Forge Fields currently supports Page and Post locations.
         */
        if (! in_array($post_type, ['page', 'post'], true)) {
            return;
        }

        $post_id = 0;

        if (isset($_GET['post'])) {
            $post_id = absint($_GET['post']);
        } elseif (isset($_POST['post_ID'])) {
            $post_id = absint($_POST['post_ID']);
        }

        $groups = ff_get_all_groups();

        $has_applicable_group = false;

        foreach ($groups as $group) {

            if (! is_array($group)) {
                continue;
            }

            $status = isset($group['status'])
                ? (string) $group['status']
                : 'active';

            if ($status !== 'active') {
                continue;
            }

            $location = isset($group['location'])
                ? (string) $group['location']
                : 'page';

            if ($location !== $post_type) {
                continue;
            }

            $target = isset($group['location_target'])
                ? (string) $group['location_target']
                : '';

            /**
             * A specifically targeted group cannot apply to a brand-new
             * unsaved post because it does not have a post ID yet.
             */
            if ($target !== '') {

                if (! $post_id || (string) $post_id !== $target) {
                    continue;
                }
            }

            $has_applicable_group = true;

            foreach ((array) ($group['fields'] ?? []) as $field) {

                if (! is_array($field)) {
                    continue;
                }

                $type = isset($field['type'])
                    ? sanitize_key((string) $field['type'])
                    : 'text';

                if (in_array($type, ['image', 'file'], true)) {
                    $needs_media = true;
                }

                if ($type === 'wysiwyg') {
                    $needs_editor = true;
                }
            }
        }

        /**
         * Nothing from Forge Fields will appear on this editor screen.
         */
        if (! $has_applicable_group) {
            return;
        }
    }

    /**
     * The Global Fields page renders actual field controls, so inspect
     * Global groups to determine whether Media or WYSIWYG assets are needed.
     */
    if ($page === 'forge-fields-global') {

        $groups = ff_get_all_groups();

        foreach ($groups as $group) {

            if (! is_array($group)) {
                continue;
            }

            $status = isset($group['status'])
                ? (string) $group['status']
                : 'active';

            $location = isset($group['location'])
                ? (string) $group['location']
                : 'page';

            if ($status !== 'active' || $location !== 'global') {
                continue;
            }

            foreach ((array) ($group['fields'] ?? []) as $field) {

                if (! is_array($field)) {
                    continue;
                }

                $type = isset($field['type'])
                    ? sanitize_key((string) $field['type'])
                    : 'text';

                if (in_array($type, ['image', 'file'], true)) {
                    $needs_media = true;
                }

                if ($type === 'wysiwyg') {
                    $needs_editor = true;
                }
            }
        }
    }

    /**
     * Only load WordPress Media Library when Image or File fields exist
     * on the current screen.
     */
    if ($needs_media) {
        wp_enqueue_media();
    }

    /**
     * Only load the WordPress editor API when a WYSIWYG field exists.
     */
    if (
        $needs_editor
        && function_exists('wp_enqueue_editor')
    ) {
        wp_enqueue_editor();
    }

    /**
     * Dashicons are used throughout Forge Fields.
     */
    wp_enqueue_style('dashicons');

    /**
     * Editor button styles are only required when WYSIWYG exists.
     */
    if ($needs_editor) {
        wp_enqueue_style('editor-buttons');
    }

    /**
     * Forge Fields admin stylesheet.
     */
    wp_enqueue_style(
        'ff-admin-fields',
        FF_URL . 'assets/css/admin-fields.min.css',
        [],
        FF_VERSION
    );

    /**
     * Only attach WordPress media dependencies when media fields
     * actually exist on the current screen.
     */
    $script_dependencies = ['jquery'];

    if ($needs_media) {
        $script_dependencies[] = 'media-editor';
        $script_dependencies[] = 'media-views';
        $script_dependencies[] = 'wp-util';
    }

    /**
     * Forge Fields admin JavaScript.
     */
    /**
     * Forge Fields admin/builder behavior.
     *
     * This script is only required on Forge Fields' own admin screens.
     * Normal Page/Post editors do not need the Field Group builder,
     * drag-and-drop, bulk actions, location controls, or other admin UI.
     */
    if ($is_ff_screen) {
        wp_enqueue_script(
            'ff-admin-fields',
            FF_URL . 'assets/js/admin-fields.min.js',
            $script_dependencies,
            FF_VERSION,
            true
        );
    }

    /**
     * Rendered field-control behavior.
     *
     * Page/Post editors and Global Fields need interactive controls
     * such as Password, Range, Media, and Required validation.
     */
    if ($is_editor_screen || $page === 'forge-fields-global') {
        wp_enqueue_script(
            'ff-admin-controls',
            FF_URL . 'assets/js/admin-controls.min.js',
            $script_dependencies,
            FF_VERSION,
            true
        );
    }

    /**
     * Forge Fields plugin screens use some native WordPress components.
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
