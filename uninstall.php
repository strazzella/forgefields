<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$delete_data = (bool) get_option(
    'ff_delete_data_on_uninstall',
    false
);

if (! $delete_data) {
    delete_option('ff_delete_data_on_uninstall');

    return;
}

delete_option('ff_field_groups');
delete_option('ff_global_fields');

/**
 * Legacy Forge Fields option.
 */
delete_option('ff_field_group');

global $wpdb;

$meta_key_pattern = $wpdb->esc_like('_ff_') . '%';

$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->postmeta}
         WHERE meta_key LIKE %s",
        $meta_key_pattern
    )
);

delete_option('ff_delete_data_on_uninstall');
