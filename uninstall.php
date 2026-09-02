<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/*
 * Forge Fields uninstall cleanup.
 *
 * WordPress defines WP_UNINSTALL_PLUGIN only when this file
 * is executed through the official plugin uninstall process.
 */

$delete_data = (bool) get_option(
    'ff_delete_data_on_uninstall',
    false
);

/*
 * Preserve Forge Fields data unless the user explicitly
 * chose to remove it.
 */
if (! $delete_data) {
    delete_option('ff_delete_data_on_uninstall');
    return;
}

/*
 * Remove Forge Fields options.
 */
delete_option('ff_field_groups');
delete_option('ff_global_fields');

/*
 * Legacy option cleanup.
 */
delete_option('ff_field_group');

/*
 * Remove Forge Fields post metadata.
 */
global $wpdb;

$meta_key_pattern = $wpdb->esc_like('_ff_') . '%';

$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta}
         WHERE meta_key LIKE %s",
        $meta_key_pattern
    )
);

/*
 * Remove the uninstall preference itself.
 */
delete_option('ff_delete_data_on_uninstall');
