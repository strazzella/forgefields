<?php

/**
 * Plugin Name:       Forge Fields
 * Plugin URI:        https://your-future-plugin-site.com/
 * Description:       Lightweight custom fields framework for WordPress with support for field groups, global fields, media fields, choice fields, and developer-friendly template functions.
 * Version:           0.1.17
 * Author:            Forge Tools
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.8.2
 * Requires PHP:      8.0
 * Text Domain:       forge-fields
 */

if (! defined('ABSPATH')) {
    exit;
}

define('FF_VERSION', '0.1.17');

define('FF_PATH', plugin_dir_path(__FILE__));

define('FF_URL', plugin_dir_url(__FILE__));

require_once FF_PATH . 'includes/core.php';

if (is_admin()) {

    require_once FF_PATH . 'includes/admin-page.php';

    require_once FF_PATH . 'includes/admin-ui.php';

    require_once FF_PATH . 'includes/import-export.php';
}

function ff_enqueue_admin_assets($hook)
{
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

    $is_editor_screen = in_array(
        $hook,
        ['post.php', 'post-new.php'],
        true
    );

    if (! $is_ff_screen && ! $is_editor_screen) {
        return;
    }

    $needs_media  = false;
    $needs_editor = false;

    if ($is_editor_screen) {

        $screen = get_current_screen();

        $post_type = ($screen && ! empty($screen->post_type))
            ? (string) $screen->post_type
            : '';

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
             * A targeted group cannot apply to a new unsaved post
             * because no post ID exists yet.
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

        if (! $has_applicable_group) {
            return;
        }
    }

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

    if ($needs_media) {
        wp_enqueue_media();
    }

    if (
        $needs_editor
        && function_exists('wp_enqueue_editor')
    ) {
        wp_enqueue_editor();
    }

    wp_enqueue_style('dashicons');

    if ($needs_editor) {
        wp_enqueue_style('editor-buttons');
    }

    wp_enqueue_style(
        'ff-admin-fields',
        FF_URL . 'assets/css/admin-fields.min.css',
        [],
        FF_VERSION
    );

    $script_dependencies = ['jquery'];

    if ($needs_media) {
        $script_dependencies[] = 'media-editor';
        $script_dependencies[] = 'media-views';
        $script_dependencies[] = 'wp-util';
    }

    /**
     * Builder behavior is restricted to Forge Fields admin screens.
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
     * Rendered field controls are shared by Page/Post editors
     * and Global Fields.
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

    if ($is_ff_screen) {
        wp_enqueue_style('common');
    }
}

add_action('init', 'ff_boot_field_groups');

add_action('admin_enqueue_scripts', 'ff_enqueue_admin_assets');
