<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/*
 * Read the choice made on the Forge Fields delete screen.
 */
$delete_data = (bool) get_option(
    'ff_delete_data_on_uninstall',
    false
);

/*
 * If the user chose to preserve data, remove only the temporary
 * uninstall preference and leave all Forge Fields data intact.
 */
if (! $delete_data) {
    delete_option('ff_delete_data_on_uninstall');
    return;
}

/*
 * Delete Forge Fields options.
 */
delete_option('ff_field_groups');
delete_option('ff_global_fields');

/*
 * Legacy Forge Fields option.
 */
delete_option('ff_field_group');

/*
 * Delete Forge Fields post/page metadata.
 */
global $wpdb;

$wpdb->query(
    "DELETE FROM {$wpdb->postmeta}
     WHERE meta_key LIKE '\\_ff\\_%'"
);

/*
 * Remove the uninstall preference itself.
 */
delete_option('ff_delete_data_on_uninstall');
