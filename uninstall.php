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

delete_option('ff_field_group');

global $wpdb;

$wpdb->query(
    "DELETE FROM {$wpdb->postmeta}
     WHERE meta_key LIKE '\\_ff\\_%'"
);

delete_option('ff_delete_data_on_uninstall');
