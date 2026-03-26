<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'ff_parse_choices_string' ) ) {
    function ff_parse_choices_string( $raw ) {
        $out = [];
        foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
            $line = trim( (string) $line );
            if ( $line === '' ) { continue; }

            if ( strpos( $line, '|' ) !== false ) {
                list( $value, $label ) = array_map( 'trim', explode( '|', $line, 2 ) );
            } elseif ( strpos( $line, ':' ) !== false ) {
                list( $value, $label ) = array_map( 'trim', explode( ':', $line, 2 ) );
            } else {
                $label = $line;
                $value = sanitize_key( $line );
            }

            $out[ sanitize_key( $value ) ] = sanitize_text_field( $label );
        }
        return $out;
    }
}

/**
 * Normalize and validate a choices textarea.
 *
 * - Accepts lines in: "value : Label", "value|Label", or "value"
 * - Produces canonical lines: "value : Label"
 * - Soft-warns when it had to sanitize/clean inputs
 * - Hard-errors when result would be broken (no choices, empty value, duplicates)
 *
 * @return array { normalized: string, warnings: string[], errors: string[] }
 */
/**
 * Normalize a choices textarea into a canonical format.
 *
 * Returns:
 * [
 *   'normalized' => string,
 *   'errors'     => array,
 *   'warnings'   => array,
 * ]
 */
function ff_normalize_choices_string( $raw ) {

    $lines     = preg_split( '/\r\n|\r|\n/', (string) $raw );
    $normalized = [];
    $errors     = [];
    $warnings   = [];

    foreach ( $lines as $i => $line ) {

        $line = trim( $line );
        if ( $line === '' ) {
            continue;
        }

        $value = '';
        $label = '';

        // value | Label
        if ( strpos( $line, '|' ) !== false ) {
            [ $value, $label ] = array_map( 'trim', explode( '|', $line, 2 ) );

        // value : Label
        } elseif ( strpos( $line, ':' ) !== false ) {
            [ $value, $label ] = array_map( 'trim', explode( ':', $line, 2 ) );

        // value (single word)
        } else {
            $value = $line;
            $label = '';
        }

        // Validate value
        $value = sanitize_key( $value );
        if ( $value === '' ) {
            $errors[] = sprintf(
                'Line %d is invalid. Each choice must have a value.',
                $i + 1
            );
            continue;
        }

        // Normalize output
        if ( $label === '' ) {
            // IMPORTANT: keep single-word format
            $normalized[] = $value;
        } else {
            $label = sanitize_text_field( $label );
            if ( $label === '' ) {
                $errors[] = sprintf(
                    'Line %d has an empty label.',
                    $i + 1
                );
                continue;
            }

            $normalized[] = $value . ' : ' . $label;
        }
    }

    if ( empty( $normalized ) ) {
        $errors[] = 'At least one valid choice is required.';
    }

    return [
        'normalized' => implode( "\n", $normalized ),
        'errors'     => $errors,
        'warnings'   => $warnings,
    ];
}




add_action('in_admin_header', function () {
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if (!in_array($page, ['forge-fields','forge-fields-edit','forge-fields-global'], true)) {
        return;
    }

    add_filter('admin_body_class', function ($classes) {
        return trim($classes . ' ff-has-brandbar ff-has-subbar');
    });

    // CSS: keep it simple & stronger
    add_action('admin_head', function () {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if (!in_array($page, ['forge-fields','forge-fields-edit'], true)) {
            return;
        }
        echo '<style>
            /* hidden by default */
            tr.ff-field-settings { display:none !important; }
            /* shown when JS says so */
            tr.ff-field-settings.is-visible { display:table-row !important; }
        </style>';
    });

    // JS: init + on-change + handle new rows
    add_action('admin_footer', function () {
        $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if ($page !== 'forge-fields-edit') return;
        ?>
        <script>
        (function(){
    // Only these types use the Choices textarea
    const choiceTypes = ['select','checkbox','radio','button_group'];

    function updateRow(row) {
        const select = row.querySelector('.ff-field-type');
        const settings = row.nextElementSibling;
        if (!select || !settings || !settings.matches('[data-ff-settings]')) return;
        const shouldShow = choiceTypes.indexOf(select.value) !== -1;
        settings.style.display = shouldShow ? '' : 'none';
        settings.classList.toggle('is-hidden', !shouldShow);
    }

    // Initial pass
    document.querySelectorAll('tr.ff-field-row').forEach(updateRow);

    // Live changes
    document.addEventListener('change', function(e){
        if (e.target && e.target.classList.contains('ff-field-type')) {
        const row = e.target.closest('tr.ff-field-row');
        if (row) updateRow(row);
        }
    });
    })();
    </script>
    <?php
});



    $is_new   = ($page === 'forge-fields-edit' && empty($_GET['group']));
    $subtitle = ($page === 'forge-fields')
        ? 'Field Groups'
        : (($page === 'forge-fields-global')
            ? 'Global Fields'
            : ($is_new ? 'Add New Field Group' : 'Edit Field Group'));

    ff_render_admin_brandbar($subtitle, '');

    // Build right side content:
    $right_html = '';
    if ($page === 'forge-fields') {
        // list page keeps the Add New link
        $cta_url   = admin_url('admin.php?page=forge-fields-edit');
        ff_render_admin_subbar($subtitle, $cta_url, 'Add New');
        return;
    }

    if ($page === 'forge-fields-edit') {
        // Buttons target the form by id="ff-edit-form"
        $right_html = sprintf(
            '<button type="button" class="button ff-subbar__btn" id="ff-add-field">Add Field</button>
             <button type="submit" class="button button-primary ff-subbar__btn"
                     form="ff-edit-form" name="ff_save_field_group" value="1">Save Changes</button>'
        );
    }

    if ($page === 'forge-fields-global') {
    $right_html = sprintf(
        '<button type="submit" class="button button-primary ff-subbar__btn"
                 form="ff-global-form" name="ff_save_global" value="1">Save Global Fields</button>'
    );
}

    ff_render_admin_subbar($subtitle, '', 'Add New', $right_html);
});

/**
 * Forge Fields admin brand bar (ACF-style).
 *
 * @param string $subtitle  e.g. 'Field Groups', 'Edit Field Group', 'Global Fields'
 * @param string $add_url   optional CTA url (e.g. Add New). Pass '' to hide.
 * @param string $add_label CTA label (defaults to 'Add New')
 */
function ff_render_admin_brandbar( $subtitle = '', $add_url = '', $add_label = 'Add New' ) {
    // are we already on the list page?
    $page       = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    $list_page  = 'forge-fields';
    $is_current = ($page === $list_page);

    // target URL for the brand title
    $home_url = admin_url( 'admin.php?page=' . $list_page );
    ?>
    <div class="ff-brandbar" role="banner" aria-label="Forge Fields">
        <div class="ff-brandbar__inner">
            <div class="ff-brandbar__left">
                <span class="ff-brandbar__logo" aria-hidden="true">FF</span>

                <?php if ( $is_current ) : ?>
                    <a class="ff-brandbar__title" href="<?php echo esc_url( $home_url ); ?>">Forge Fields</a>
                <?php else : ?>
                    <a class="ff-brandbar__title" href="<?php echo esc_url( $home_url ); ?>">Forge Fields</a>
                <?php endif; ?>

                <?php if ( $subtitle ) : ?>
                    <span class="ff-brandbar__subtitle">— <?php echo esc_html( $subtitle ); ?></span>
                <?php endif; ?>
            </div>

            <div class="ff-brandbar__right">
                <?php if ( $add_url ) : ?>
                    <a class="button ff-brandbar__btn" href="<?php echo esc_url( $add_url ); ?>">
                        + <?php echo esc_html( $add_label ); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}


/**
 * Forge Fields secondary page bar (under the brand bar).
 *
 * @param string $title
 * @param string $cta_url
 * @param string $cta_label
 */
function ff_render_admin_subbar( $title, $cta_url = '', $cta_label = 'Add New', $right_html = '' ) { ?>
    <div class="ff-subbar" role="navigation" aria-label="Forge Fields secondary bar">
        <div class="ff-subbar__inner">
            <h1 class="ff-subbar__title"><?php echo esc_html( $title ); ?></h1>

            <div class="ff-subbar__actions">
                <?php
                if ( $right_html ) {
                    echo $right_html; // already escaped below where we build it
                } elseif ( $cta_url ) { ?>
                    <a class="button button-primary ff-subbar__btn"
                       href="<?php echo esc_url( $cta_url ); ?>">
                        + <?php echo esc_html( $cta_label ); ?>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
<?php }

/**
 * Register menu + submenus.
 */
add_action( 'admin_menu', function() {

    $parent_slug = 'forge-fields';

    // Top-level: list of groups
    add_menu_page(
        'Forge Fields',
        'Forge Fields',
        'manage_options',
        $parent_slug,
        'ff_render_field_groups_list',
        'dashicons-editor-table',
        80
    );

    // Explicit "Field Groups" submenu (same as parent)
    add_submenu_page(
        $parent_slug,
        'Field Groups',
        'Field Groups',
        'manage_options',
        $parent_slug,
        'ff_render_field_groups_list'
    );

    // "Add New" / Edit screen
    add_submenu_page(
        null,
        'Add New Field Group',
        'Add New',
        'manage_options',
        'forge-fields-edit',
        'ff_render_field_group_edit'
    );

    // Global Options
    add_submenu_page(
        $parent_slug,
        'Global Fields',
        'Global Fields',
        'manage_options',
        'forge-fields-global',
        'ff_render_global_options_page'
    );
} );

// Adds a body class only on Forge Fields admin screens so we can pad #wpcontent for the fixed brand bar.
add_filter('admin_body_class', function($classes){
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if (in_array($page, ['forge-fields','forge-fields-edit','forge-fields-global'], true)) {
        $classes .= ' ff-has-brandbar';
    }
    return $classes;
});


/**
 * LIST SCREEN
 * -------------------------------------------------------------------------
 * Shows all field groups with All / Active / Trash filters.
 */
function ff_render_field_groups_list() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // ---------------------------------------------------------------------
    // 1) Load groups
    // ---------------------------------------------------------------------
    $groups = ff_get_all_groups();

        // ---------------------------------------------------------------------
    // 2) Handle BULK actions (POST)
    // ---------------------------------------------------------------------
    if (
        isset( $_POST['ff_bulk_action'], $_POST['ff_group_ids'], $_POST['ff_bulk_nonce'] )
        && $_POST['ff_bulk_action'] !== '-1'
        && is_array( $_POST['ff_group_ids'] )
    ) {
        if ( ! wp_verify_nonce( $_POST['ff_bulk_nonce'], 'ff_bulk_groups' ) ) {
            // Invalid nonce: just ignore bulk request.
        } else {
            $bulk_action = sanitize_key( wp_unslash( $_POST['ff_bulk_action'] ) );
            $selected_ids = array_map(
                'sanitize_text_field',
                array_map( 'wp_unslash', $_POST['ff_group_ids'] )
            );

            $current_view_post = isset( $_POST['ff_view'] )
                ? sanitize_key( wp_unslash( $_POST['ff_view'] ) )
                : 'all';

            $modified = false;

            foreach ( $selected_ids as $id ) {
                if ( ! isset( $groups[ $id ] ) ) {
                    continue;
                }

                switch ( $bulk_action ) {
                    case 'trash':
                        $groups[ $id ]['status'] = 'trash';
                        $modified = true;
                        break;

                    case 'activate':
                        $groups[ $id ]['status'] = 'active';
                        $modified = true;
                        break;

                    case 'deactivate':
                        $groups[ $id ]['status'] = 'inactive';
                        $modified = true;
                        break;

                    case 'duplicate':
                        $original = $groups[ $id ];
                        $new_id   = uniqid( 'ff_group_' );

                        $copy           = $original;
                        $copy['id']     = $new_id;
                        $copy['status'] = 'active';

                        $base_title    = isset( $original['title'] ) ? $original['title'] : '';
                        $copy['title'] = $base_title !== ''
                            ? $base_title . ' (Copy)'
                            : '(no title) (Copy)';

                        $groups[ $new_id ] = $copy;
                        $modified          = true;
                        break;

                    case 'restore':
                        $groups[ $id ]['status'] = 'active';
                        $modified = true;
                        break;

                    case 'delete':
                        unset( $groups[ $id ] );
                        $modified = true;
                        break;
                }
            }

            if ( $modified ) {
                ff_save_all_groups( $groups );
            }

            // Redirect back to list, keeping current view and adding notice.
            $redirect_url = admin_url( 'admin.php?page=forge-fields' );

            if ( $current_view_post ) {
                $redirect_url = add_query_arg( 'ff_view', $current_view_post, $redirect_url );
            }

            $redirect_url = add_query_arg(
                'ff_notice',
                'bulk_' . $bulk_action,
                $redirect_url
            );

            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    // ---------------------------------------------------------------------
    // 2) Handle actions: trash / restore / delete
    // ---------------------------------------------------------------------
    $action     = isset( $_GET['ff_action'] ) ? sanitize_key( wp_unslash( $_GET['ff_action'] ) ) : '';
    $group_id   = isset( $_GET['group_id'] ) ? sanitize_text_field( wp_unslash( $_GET['group_id'] ) ) : '';
    $notice_msg = ''; // will be turned into a query arg

if ( $action && $group_id && isset( $groups[ $group_id ] ) ) {
    $nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';

    if ( wp_verify_nonce( $nonce, 'ff_group_action_' . $action . '_' . $group_id ) ) {

        switch ( $action ) {
            case 'trash':
                $groups[ $group_id ]['status'] = 'trash';
                ff_save_all_groups( $groups );
                $notice_msg = 'trashed';
                break;

            case 'restore':
                $groups[ $group_id ]['status'] = 'active';
                ff_save_all_groups( $groups );
                $notice_msg = 'restored';
                break;

            case 'delete':
                unset( $groups[ $group_id ] );
                ff_save_all_groups( $groups );
                $notice_msg = 'deleted';
                break;

            case 'deactivate':
                $groups[ $group_id ]['status'] = 'inactive';
                ff_save_all_groups( $groups );
                $notice_msg = 'deactivated';
                break;

            case 'activate':
                $groups[ $group_id ]['status'] = 'active';
                ff_save_all_groups( $groups );
                $notice_msg = 'activated';
                break;

            case 'duplicate':
                // >>> your original logic stays exactly the same <<<
                $original = $groups[ $group_id ];

                // New ID for the copy.
                $new_id = ff_generate_group_id();

                // Clone the group and adjust.
                $copy           = $original;
                $copy['id']     = $new_id;
                $copy['status'] = 'active';

                $base_title    = isset( $original['title'] ) ? $original['title'] : '';
                $copy['title'] = $base_title !== ''
                    ? $base_title . ' (Copy)'
                    : '(no title) (Copy)';

                // Save under the new key.
                $groups[ $new_id ] = $copy;
                ff_save_all_groups( $groups );

                $notice_msg = 'duplicated';
                break;

            case 'clear_cache':
                wp_cache_delete( 'ff_field_groups', 'options' );
                $notice_msg = 'cache_cleared';
                break;
        }

        // Redirect back to list, carrying notice code & current view
        $redirect_url = remove_query_arg( [ 'ff_action', 'group_id', '_wpnonce' ], admin_url( 'admin.php?page=forge-fields' ) );

        if ( isset( $_GET['ff_view'] ) ) {
            $redirect_url = add_query_arg(
                'ff_view',
                sanitize_key( wp_unslash( $_GET['ff_view'] ) ),
                $redirect_url
            );
        }

        if ( $notice_msg ) {
            $redirect_url = add_query_arg( 'ff_notice', $notice_msg, $redirect_url );
        }

        wp_safe_redirect( $redirect_url );
        exit;
    }
}

    // Re-load after any modifications (mainly useful if something changed).
    $groups = ff_get_all_groups();

    // ---------------------------------------------------------------------
    // 3) Determine current view: all | active | trash
    //     - "All" = everything that is NOT in trash
    //     - "Active" = status === active
    //     - "Trash" = status === trash
    // ---------------------------------------------------------------------
    $current_view = isset( $_GET['ff_view'] )
        ? sanitize_key( wp_unslash( $_GET['ff_view'] ) )
        : 'all';

    if ( ! in_array( $current_view, [ 'all', 'active', 'trash' ], true ) ) {
        $current_view = 'all';
    }

    // ---------------------------------------------------------------------
    // Sorting params: orderby=title, order=asc|desc
    // ---------------------------------------------------------------------
    $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'title';
    $order   = isset($_GET['order']) ? strtolower(sanitize_text_field($_GET['order'])) : 'asc';
    $order   = ($order === 'desc') ? 'desc' : 'asc';


    // ---------------------------------------------------------------------
    // Search term (similar to ACF "Search Field Groups")
    // ---------------------------------------------------------------------
    $search_term = isset( $_GET['ff_search'] )
        ? trim( sanitize_text_field( wp_unslash( $_GET['ff_search'] ) ) )
        : '';

    $count_all    = 0; // non-trash
    $count_active = 0;
    $count_trash  = 0;

    foreach ( $groups as $g ) {
        $status = isset( $g['status'] ) ? $g['status'] : 'active';

        if ( $status === 'trash' ) {
            $count_trash++;
        } else {
            $count_all++;
            if ( $status === 'active' ) {
                $count_active++;
            }
        }
    }

    // Build base URL for views/actions.
    $list_base_url = admin_url( 'admin.php?page=forge-fields' );

    // ------------------------------------------------------------------
    // 4) Turn ?ff_notice=code into a user-friendly message
    // ------------------------------------------------------------------
    $notice = '';

        if ( isset( $_GET['ff_notice'] ) ) {
        $code = sanitize_key( wp_unslash( $_GET['ff_notice'] ) );

        switch ( $code ) {
            case 'trashed':
                $notice = 'Field group moved to trash.';
                break;
            case 'restored':
                $notice = 'Field group restored.';
                break;
            case 'deleted':
                $notice = 'Field group deleted permanently.';
                break;
            case 'deactivated':
                $notice = 'Field group deactivated.';
                break;
            case 'activated':
                $notice = 'Field group activated.';
                break;
            case 'duplicated':
                $notice = 'Field group duplicated.';
                break;
            case 'cache_cleared':
                $notice = 'Field group cache cleared.';
                break;

            // Bulk variants
            case 'bulk_trash':
                $notice = 'Selected field groups moved to trash.';
                break;
            case 'bulk_activate':
                $notice = 'Selected field groups activated.';
                break;
            case 'bulk_deactivate':
                $notice = 'Selected field groups deactivated.';
                break;
            case 'bulk_duplicate':
                $notice = 'Selected field groups duplicated.';
                break;
            case 'bulk_restore':
                $notice = 'Selected field groups restored.';
                break;
            case 'bulk_delete':
                $notice = 'Selected field groups deleted permanently.';
                break;
        }
    }



    ?>
    <div class="wrap ff-admin ff-list ff-has-brand">

                  <?php
        // Small "All | Active | Trash" filter links, ACF-style.
        $all_url    = remove_query_arg( 'ff_view', $list_base_url );
        $active_url = add_query_arg( 'ff_view', 'active', $list_base_url );
        $trash_url  = add_query_arg( 'ff_view', 'trash',  $list_base_url );
        ?>

       <?php if ( $notice ) : ?>
            <div class="notice notice-success inline is-dismissible">
                <p><?php echo esc_html( $notice ); ?></p>
            </div>
        <?php endif; ?>

        <?php
        // ----------------------------------------------------
        // Search Field Groups form (uses $search_term etc.)
        // ----------------------------------------------------
        // Build base URL for "clear" link (keeps view + sorting)
        $search_base_url = admin_url( 'admin.php?page=forge-fields' );
        if ( $current_view !== 'all' ) {
            $search_base_url = add_query_arg( 'ff_view', $current_view, $search_base_url );
        }
        if ( $orderby ) {
            $search_base_url = add_query_arg( 'orderby', $orderby, $search_base_url );
        }
        if ( $order ) {
            $search_base_url = add_query_arg( 'order', $order, $search_base_url );
        }
        ?>

<div class="ff-list-toolbar">
      <ul class="subsubsub">
            <li class="all">
                <a href="<?php echo esc_url( $all_url ); ?>"
                   class="<?php echo ( $current_view === 'all' ) ? 'current' : ''; ?>">
                    All <span class="count">(<?php echo intval( $count_all ); ?>)</span>
                </a> |
            </li>

            <li class="active">
                <a href="<?php echo esc_url( $active_url ); ?>"
                   class="<?php echo ( $current_view === 'active' ) ? 'current' : ''; ?>">
                    Active <span class="count">(<?php echo intval( $count_active ); ?>)</span>
                </a> |
            </li>

            <li class="trash">
                <a href="<?php echo esc_url( $trash_url ); ?>"
                   class="<?php echo ( $current_view === 'trash' ) ? 'current' : ''; ?>">
                    Trash <span class="count">(<?php echo intval( $count_trash ); ?>)</span>
                </a>
            </li>
        </ul>

        <form method="get"
              action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>"
              class="ff-search-form ff-admin">
            <input type="hidden" name="page" value="forge-fields" />
            <input type="hidden" name="ff_view" value="<?php echo esc_attr( $current_view ); ?>" />
            <?php if ( $orderby ) : ?>
                <input type="hidden" name="orderby" value="<?php echo esc_attr( $orderby ); ?>" />
            <?php endif; ?>
            <?php if ( $order ) : ?>
                <input type="hidden" name="order" value="<?php echo esc_attr( $order ); ?>" />
            <?php endif; ?>

            <input type="search"
                   name="ff_search"
                   value="<?php echo esc_attr( $search_term ); ?>"
                   class="regular-text ff-search-input"
                   placeholder="Search field groups..." />

            <?php if ( $search_term !== '' ) : ?>
                <a href="<?php echo esc_url( $search_base_url ); ?>"
                   class="button button-secondary ff-search-clear">
                    ×
                </a>
            <?php endif; ?>

            <button class="button button-primary">
                Search Field Groups
            </button>
        </form>
        </div>

        <?php
        // Filter groups according to current view.
        $display_groups = [];

        foreach ( $groups as $group_id => $group ) {
            $status = isset( $group['status'] ) ? $group['status'] : 'active';

            if ( $current_view === 'trash' ) {
                if ( $status !== 'trash' ) {
                    continue;
                }
            } else {
                // "all" or "active" views never show trashed items.
                if ( $status === 'trash' ) {
                    continue;
                }
                if ( $current_view === 'active' && $status !== 'active' ) {
                    continue;
                }
            }

            // If a search term is provided, match against title + key
            if ( $search_term !== '' ) {
                $title     = isset( $group['title'] ) ? $group['title'] : '';
                $group_key = ! empty( $group['id'] ) ? $group['id'] : $group_id;

                $haystack = strtolower( $title . ' ' . $group_key );
                $needle   = strtolower( $search_term );

                if ( strpos( $haystack, $needle ) === false ) {
                    continue; // no match, skip this group
                }
            }

            $display_groups[ $group_id ] = $group;
        }

        if ($orderby === 'title') {
            uasort($display_groups, function ($a, $b) use ($order) {
                $titleA = isset($a['title']) ? strtolower($a['title']) : '';
                $titleB = isset($b['title']) ? strtolower($b['title']) : '';

                if ($titleA === $titleB) return 0;

                return ($order === 'asc')
                    ? (($titleA < $titleB) ? -1 : 1)
                    : (($titleA > $titleB) ? -1 : 1);
            });
        }

        $visible_count = count( $display_groups );

        if ( empty( $display_groups ) ) : ?>
            <p>No field groups found for this view. Click “Add New” to create one.</p>
        <?php else : ?>

            <form method="post" action="" class="ff-admin ff-list-table">
                <?php wp_nonce_field( 'ff_bulk_groups', 'ff_bulk_nonce' ); ?>
                <input type="hidden" name="ff_view"
                       value="<?php echo esc_attr( $current_view ); ?>">

                <?php
                // Bulk actions depend on view.
                $bulk_actions = [];

                if ( $current_view === 'trash' ) {
                    $bulk_actions = [
                        'restore' => 'Restore',
                        'delete'  => 'Delete Permanently',
                    ];
                } else {
                    $bulk_actions = [
                        'trash'      => 'Move to Trash',
                        'duplicate'  => 'Duplicate',
                        'activate'   => 'Activate',
                        'deactivate' => 'Deactivate',
                    ];
                }

                // Helper to render the bulk action dropdown.
                $render_bulk = function( $position ) use ( $bulk_actions, $visible_count ) {
                    $id = $position === 'top'
                        ? 'ff-bulk-action-selector-top'
                        : 'ff-bulk-action-selector-bottom';
                    ?>
                    <div class="tablenav <?php echo $position === 'top' ? 'top' : 'bottom'; ?>">
                        <div class="alignleft actions bulkactions">
                            <label for="<?php echo esc_attr( $id ); ?>" class="screen-reader-text">
                                Bulk actions
                            </label>
                            <div class="ff-select-wrap">
                            <select name="ff_bulk_action" id="<?php echo esc_attr( $id ); ?>">
                                <option value="-1">Bulk actions</option>
                                <?php foreach ( $bulk_actions as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>">
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            </div>
                            <input type="submit"
                                   class="button action"
                                   value="Apply">
                        </div>

                        <?php if ( $position === 'bottom' ) : ?>
            <div class="tablenav-pages one-page">
                <span class="displaying-num">
                    <?php
                    // “1 item” vs “3 items”
                    if ( $visible_count === 1 ) {
                        echo '1 item';
                    } else {
                        echo intval( $visible_count ) . ' items';
                    }
                    ?>
                </span>
            </div>
        <?php endif; ?>

                        <br class="clear" />
                    </div>
                    <?php
                };

                // Top bulk actions bar.
                // $render_bulk( 'top' );
                ?>

                <?php
                // Helper: render the header row (used in thead and tfoot).
                $render_header_row = function () use ( $orderby, $order, $current_view ) {

                    // Sorting URL + classes for Title
                    $next_order = ( $orderby === 'title' && $order === 'asc' ) ? 'desc' : 'asc';

                    $title_sort_url = add_query_arg(
                        [
                            'orderby' => 'title',
                            'order'   => $next_order,
                            'ff_view' => $current_view,
                        ],
                        admin_url( 'admin.php?page=forge-fields' )
                    );

                    $sort_class = ( $orderby === 'title' )
                        ? 'sorted ' . $order
                        : 'sortable asc';
                    ?>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <input id="cb-select-all-1" type="checkbox" />
                        </td>

                        <th scope="col"
                            class="manage-column column-title <?php echo esc_attr( $sort_class ); ?>">
                            <a href="<?php echo esc_url( $title_sort_url ); ?>">
                                <span>Title</span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>

                        <th scope="col" class="manage-column ff-bar-title">Forge Key</th>
                        <th scope="col" class="manage-column ff-bar-title">Location</th>
                        <th scope="col" class="manage-column ff-bar-title">Fields</th>
                        <th scope="col" class="manage-column ff-bar-title">Status</th>
                    </tr>
                    <?php
                };
                ?>

                <table class="ff-list-table widefat fixed ff-admin striped">
                    <thead class="ff-list-bar">
                        <?php $render_header_row(); ?>
                    </thead>
                    <tbody>
                    <?php foreach ( $display_groups as $group_id => $group ) :

                        $title    = ! empty( $group['title'] ) ? $group['title'] : '(no title)';
                        $location = isset( $group['location'] ) ? $group['location'] : 'page';
                        $fields   = isset( $group['fields'] ) && is_array( $group['fields'] ) ? $group['fields'] : [];
                        $field_cnt = count( $fields );

                        $status = isset( $group['status'] ) ? $group['status'] : 'active';
                        $status_label = ( $status === 'active' ) ? 'Active' : 'Deactivated';

                        // Safe key (prefer stored ID, fall back to array key)
                        $group_key = ! empty( $group['id'] ) ? $group['id'] : $group_id;

                        $edit_base_url = admin_url( 'admin.php?page=forge-fields-edit' );
                        $edit_url      = add_query_arg( [ 'group' => $group_id ], $edit_base_url );

                    // Action URLs (all go back to list page).
                    if ( $current_view === 'trash' ) {
                        // Restore
                        $restore_url = wp_nonce_url(
                            add_query_arg(
                                [
                                    'ff_view'   => 'trash',
                                    'ff_action' => 'restore',
                                    'group_id'  => $group_id,
                                ],
                                $list_base_url
                            ),
                            'ff_group_action_restore_' . $group_id
                        );

                        // Delete permanently
                        $delete_url = wp_nonce_url(
                            add_query_arg(
                                [
                                    'ff_view'   => 'trash',
                                    'ff_action' => 'delete',
                                    'group_id'  => $group_id,
                                ],
                                $list_base_url
                            ),
                            'ff_group_action_delete_' . $group_id
                        );
                    } else {
                        // Trash from active/all views
                        $trash_url = wp_nonce_url(
                            add_query_arg(
                                [
                                    'ff_view'   => $current_view,
                                    'ff_action' => 'trash',
                                    'group_id'  => $group_id,
                                ],
                                $list_base_url
                            ),
                            'ff_group_action_trash_' . $group_id
                        );

                        // Duplicate with nonce
                        $dup_url = wp_nonce_url(
                            add_query_arg(
                                [
                                    'ff_view'   => $current_view,
                                    'ff_action' => 'duplicate',
                                    'group_id'  => $group_id,
                                ],
                                $list_base_url
                            ),
                            'ff_group_action_duplicate_' . $group_id
                        );

                        // Toggle active/inactive
                        $toggle_action = ( $status === 'active' ) ? 'deactivate' : 'activate';

                        $toggle_url = wp_nonce_url(
                            add_query_arg(
                                [
                                    'ff_view'   => $current_view,
                                    'ff_action' => $toggle_action,
                                    'group_id'  => $group_id,
                                ],
                                $list_base_url
                            ),
                            'ff_group_action_' . $toggle_action . '_' . $group_id
                        );

                        $clear_url = wp_nonce_url(
                            add_query_arg(
                                [
                                    'ff_view'   => $current_view,
                                    'ff_action' => 'clear_cache',
                                    'group_id'  => $group_id,
                                ],
                                $list_base_url
                            ),
                            'ff_group_action_clear_cache_' . $group_id
                        );
                    }
                    ?>
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox"
                                   name="ff_group_ids[]"
                                   value="<?php echo esc_attr( $group_id ); ?>" />
                        </th>

                        <td class="column-primary">
                            <strong>
                                <?php if ( $current_view === 'trash' ) : ?>
                                    <?php echo esc_html( $title ); ?>
                                <?php else : ?>
                                    <a href="<?php echo esc_url( $edit_url ); ?>">
                                        <?php echo esc_html( $title ); ?>
                                    </a>
                                <?php endif; ?>
                            </strong>

                            <div class="row-actions">
                                <?php if ( $current_view === 'trash' ) : ?>
                                    <span class="restore">
                                        <a href="<?php echo esc_url( $restore_url ); ?>">Restore</a> |
                                    </span>
                                    <span class="delete">
                                        <a href="<?php echo esc_url( $delete_url ); ?>" class="submitdelete">
                                            Delete Permanently
                                        </a>
                                    </span>
                                <?php else : ?>
                                    <span class="edit">
                                        <a href="<?php echo esc_url( $edit_url ); ?>">Edit</a> |
                                    </span>
                                    <span class="duplicate">
                                        <a href="<?php echo esc_url( $dup_url ); ?>">Duplicate</a> |
                                    </span>
                                   <span class="deactivate">
                                        <a href="<?php echo esc_url( $toggle_url ); ?>">
                                            <?php echo ( $status === 'active' ) ? 'Deactivate' : 'Activate'; ?>
                                        </a> |
                                    </span>
                                    <span class="trash">
                                        <a href="<?php echo esc_url( $trash_url ); ?>" class="submitdelete">
                                            Trash
                                        </a> |
                                    </span>
                                    <span class="clear-cache">
                                        <a href="<?php echo esc_url( $clear_url ); ?>">Clear Cache</a>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                        <code class="ff-group-key" data-key="<?php echo esc_attr( $group_key ); ?>">
                            <?php echo esc_html( $group_key ); ?>
                        </code>
                        <button
                            type="button"
                            class="button-link ff-copy-key"
                            data-key="<?php echo esc_attr( $group_key ); ?>"
                            aria-label="Copy key"
                            title="Copy to clipboard"
                        >
                            <span class="screen-reader-text">Copy</span>
                        </button>
                    </td>

                        <td>
    <?php
    $location_target = isset( $group['location_target'] ) ? (string) $group['location_target'] : '';

    if ( $location === 'page' ) {
        $label = 'Page';

        if ( $location_target !== '' ) {
            $target_post = get_post( (int) $location_target );
            $target_text = $target_post ? get_the_title( $target_post ) : 'Missing Page';
        } else {
            $target_text = 'All Pages';
        }

        echo '<strong>' . esc_html( $label ) . '</strong> ';
        echo '<span class="ff-location-target">(' . esc_html( $target_text ) . ')</span>';

    } elseif ( $location === 'post' ) {
        $label = 'Post';

        if ( $location_target !== '' ) {
            $target_post = get_post( (int) $location_target );
            $target_text = $target_post ? get_the_title( $target_post ) : 'Missing Post';
        } else {
            $target_text = 'All Posts';
        }

        echo '<strong>' . esc_html( $label ) . '</strong> ';
        echo '<span class="ff-location-target">(' . esc_html( $target_text ) . ')</span>';

    } elseif ( $location === 'global' ) {
        echo '<strong>Global</strong>';
    } else {
        echo esc_html( ucfirst( (string) $location ) );
    }
    ?>
</td>

                        <td>
    <?php
    $count_html = '<span class="ff-chip" title="Fields">'. intval($field_cnt) .'</span>';
    if ( $current_view === 'trash' ) {
        echo $count_html;
    } else {
        echo '<a href="'. esc_url( $edit_url ) .'" class="ff-count-link">'. $count_html .'</a>';
    }
    ?>
</td>

                     <td>
    <?php
    // map status to pill class + label
    $status_key   = strtolower( $status );
    $status_label = ( $status_key === 'active' ) ? 'Active' : ( $status_key === 'inactive' ? 'Inactive' : ucfirst( $status_key ) );
    $status_class = 'ff-status ff-status--' . esc_attr( $status_key );
    echo '<span class="'. $status_class .'">'. esc_html( $status_label ) .'</span>';
    ?>
</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                 <tfoot class="ff-list-bar">
                    <?php $render_header_row(); ?>
                </tfoot>
            </table>
             <?php
                // Bottom bulk actions bar.
                $render_bulk( 'bottom' );
                ?>
            </form>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * GLOBAL OPTIONS SCREEN
 * -------------------------------------------------------------------------
 * Renders all field groups where location === 'global' and status !== 'trash'.
 * Values are stored in a single option: ff_global_fields ( [field_name => value] ).
 */

        function ff_render_global_options_page() {

            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $groups        = ff_get_all_groups();
            $global_groups = [];

            // Collect only non-trashed global groups
            foreach ( $groups as $group_id => $group ) {
                $status   = isset( $group['status'] ) ? $group['status'] : 'active';
                $location = isset( $group['location'] ) ? $group['location'] : 'page';

                if ( $status === 'trash' ) {
                    continue;
                }

                if ( $location === 'global' ) {
                    $global_groups[ $group_id ] = $group;
                }
            }

            // Load stored values
            $stored = get_option( 'ff_global_fields', [] );
            if ( ! is_array( $stored ) ) {
                $stored = [];
            }

            $notices = [];

            // Handle save
            // Handle save
        if ( isset( $_POST['ff_save_global'] ) ) {

            check_admin_referer( 'ff_save_global' );

            $raw = isset( $_POST['ff_global'] ) && is_array( $_POST['ff_global'] )
                ? $_POST['ff_global']
                : [];

            // Build a name => type map from the defined global fields
            $type_map = [];
            foreach ( $global_groups as $group ) {
                if ( empty( $group['fields'] ) || ! is_array( $group['fields'] ) ) {
                    continue;
                }
                foreach ( $group['fields'] as $f ) {
                    if ( empty( $f['name'] ) ) {
                        continue;
                    }
                    $type_map[ $f['name'] ] = isset( $f['type'] ) ? $f['type'] : 'text';
                }
            }

            $new_values = [];

            foreach ( $raw as $field_name => $value_raw ) {
                $name = sanitize_key( $field_name );
                $type = isset( $type_map[ $name ] ) ? $type_map[ $name ] : 'text';

                switch ( $type ) {
            case 'number':
                $new_values[ $name ] = is_array( $value_raw ) ? 0 : floatval( $value_raw );
                break;

            case 'range':
                $new_values[ $name ] = is_array( $value_raw ) ? 0 : intval( $value_raw );
                break;

            case 'email':
                $new_values[ $name ] = is_array( $value_raw ) ? '' : sanitize_email( $value_raw );
                break;

            case 'url':
                $new_values[ $name ] = is_array( $value_raw ) ? '' : esc_url_raw( $value_raw );
                break;

            case 'password':
                $new_values[ $name ] = is_array( $value_raw )
                    ? ''
                    : ff_sanitize_type_password( wp_unslash( $value_raw ), [ 'name' => $name, 'type' => 'password' ], 0 );
                break;

            case 'text':
            case 'textarea':
            case 'wysiwyg':
                $new_values[ $name ] = is_array( $value_raw )
                    ? ''
                    : sanitize_text_field( wp_unslash( $value_raw ) );
                break;

            case 'image': // attachment ID
            case 'file':  // attachment ID
                $new_values[ $name ] = is_array( $value_raw ) ? 0 : absint( $value_raw );
                break;

            // NEW: choice types (just sanitize, no HTML here)
            case 'select':
            case 'radio':
            case 'button_group':
                $new_values[ $name ] = is_array( $value_raw ) ? '' : sanitize_key( (string) $value_raw );
                break;

            case 'checkbox':
                if ( is_array( $value_raw ) ) {
                    $vals = array_map( static function( $v ) { return sanitize_key( (string) $v ); }, $value_raw );
                    $vals = array_values( array_filter( $vals, static fn( $v ) => $v !== '' ) );
                    $new_values[ $name ] = $vals;
                } else {
                    $new_values[ $name ] = [];
                }
                break;

            case 'true_false':
                $new_values[ $name ] = empty( $value_raw ) ? 0 : 1;
                break;

            default:
                $new_values[ $name ] = is_array( $value_raw ) ? '' : sanitize_text_field( wp_unslash( $value_raw ) );
                break;
        }
            }

            update_option( 'ff_global_fields', $new_values );
            $stored  = $new_values; // use fresh values for display
            $notices[] = [
                'type'    => 'updated',
                'message' => 'Global fields saved.',
            ];
        }


            ?>
            <div class="wrap ff-admin ff-global ff-has-brandbar">
            <?php foreach ( $notices as $notice ) :
            $cls = 'notice inline';
            switch ( $notice['type'] ?? '' ) {
                case 'error':   $cls .= ' notice-error';   break;
                case 'updated': $cls .= ' notice-success'; break;
                case 'warning': $cls .= ' notice-warning'; break;
                default:        $cls .= ' notice-info';    break;
            }
        ?>
            <div class="<?php echo esc_attr( $cls ); ?> is-dismissible">
                <p><?php echo esc_html( $notice['message'] ); ?></p>
            </div>
        <?php endforeach; ?>


        <?php if ( empty( $global_groups ) ) : ?>
            <p>No global field groups found. Create a field group with
               <strong>Location = Global</strong> first.</p>
        <?php else : ?>
            <form method="post" action="" id="ff-global-form">
                <?php wp_nonce_field( 'ff_save_global' ); ?>

                <?php foreach ( $global_groups as $group_id => $group ) :

                    $title  = ! empty( $group['title'] ) ? $group['title'] : '(no title)';
                    $fields = isset( $group['fields'] ) && is_array( $group['fields'] )
                        ? $group['fields']
                        : [];
                    ?>

                    <h2><?php echo esc_html( $title ); ?></h2>

                    <?php if ( empty( $fields ) ) : ?>
                        <p><em>No fields defined in this group.</em></p>
                    <?php else : ?>
                        <div class="ff-options-card">
                        <table class="form-table" role="presentation">
                            <tbody>
                            <?php foreach ( $fields as $field ) :

                                if ( empty( $field['name'] ) ) {
                                    continue;
                                }

                                $name  = $field['name'];
                                $label = isset( $field['label'] ) ? $field['label'] : $name;
                                $type  = isset( $field['type'] )  ? $field['type']  : 'text';

                                $id    = 'ff_global_' . esc_attr( $name );
                                $value = isset( $stored[ $name ] ) ? $stored[ $name ] : '';
                                ?>
                                <tr>
                                    <th scope="row">
                                        <label for="<?php echo $id; ?>">
                                            <?php echo esc_html( $label ); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <?php
switch ( $type ) {
    case 'textarea':
        printf(
            '<textarea name="ff_global[%1$s]" id="%2$s" rows="3" class="large-text">%3$s</textarea>',
            esc_attr( $name ),
            esc_attr( $id ),
            esc_textarea( $value )
        );
        break;

    case 'number':
        printf(
            '<input type="number" name="ff_global[%1$s]" id="%2$s" value="%3$s" class="regular-text" />',
            esc_attr( $name ),
            esc_attr( $id ),
            esc_attr( $value )
        );
        break;

    case 'range':
        $min  = isset( $field['min'] )  ? (int) $field['min']  : 0;
        $max  = isset( $field['max'] )  ? (int) $field['max']  : 100;
        $step = isset( $field['step'] ) ? (int) $field['step'] : 1;
        $val  = ($value === '' ? $min : (int) $value);

        $slider_id = $id . '_slider';
        $num_id    = $id . '_num';
        ?>
        <div class="ff-range-wrap">
            <input
                type="range"
                class="ff-range-slider"
                id="<?php echo esc_attr( $slider_id ); ?>"
                name="ff_global[<?php echo esc_attr( $name ); ?>]"
                min="<?php echo esc_attr( $min ); ?>"
                max="<?php echo esc_attr( $max ); ?>"
                step="<?php echo esc_attr( $step ); ?>"
                value="<?php echo esc_attr( $val ); ?>"
                data-target="#<?php echo esc_attr( $num_id ); ?>"
            />
            <input
                type="number"
                class="small-text ff-range-number"
                id="<?php echo esc_attr( $num_id ); ?>"
                min="<?php echo esc_attr( $min ); ?>"
                max="<?php echo esc_attr( $max ); ?>"
                step="<?php echo esc_attr( $step ); ?>"
                value="<?php echo esc_attr( $val ); ?>"
                data-target="#<?php echo esc_attr( $slider_id ); ?>"
            />
        </div>
        <?php
        break;

    case 'password':
    echo '<div class="ff-password-wrap">';
    echo '<input type="password" name="ff_global[' . esc_attr( $name ) . ']" id="' . esc_attr( $id ) . '" value="' . esc_attr( (string) $value ) . '" class="regular-text ff-password-input" />';
    echo '<button type="button" class="ff-password-toggle" data-target="#' . esc_attr( $id ) . '" aria-label="Show password">';
    echo '<span class="dashicons dashicons-hidden" aria-hidden="true"></span>';
    echo '</button>';
    echo '</div>';
    break;

    case 'email':
    case 'url':
    case 'text':
        $input_type = in_array( $type, [ 'email', 'url' ], true ) ? $type : 'text';
        printf(
            '<input type="%4$s" name="ff_global[%1$s]" id="%2$s" value="%3$s" class="regular-text" />',
            esc_attr( $name ),
            esc_attr( $id ),
            esc_attr( (string) $value ),
            esc_attr( $input_type )
        );
        break;

    case 'wysiwyg':
        echo '<div class="ff-editor-card">';
        wp_editor(
            is_string( $value ) ? $value : '',
            $id,
            [
                'textarea_name' => "ff_global[$name]",
                'textarea_rows' => 12,
                'media_buttons' => true,
                'tinymce'       => [
                    'branding'  => false,
                    'menubar'   => false,
                    'statusbar' => false,
                    'toolbar1'  => 'formatselect,bold,italic,underline,|,bullist,numlist,blockquote,|,link,unlink,|,alignleft,aligncenter,alignright,|,removeformat',
                    'toolbar2'  => '',
                ],
                'quicktags'     => true,
                'editor_height' => 260,
                'editor_class'  => 'ff-editor',
            ]
        );
        echo '</div>';
        break;

    case 'image':
        $img_src = '';
        if ( $value ) {
            $src = wp_get_attachment_image_src( (int) $value, 'thumbnail' );
            if ( $src ) { $img_src = $src[0]; }
        }
        ?>
        <div class="ff-media-wrap" data-type="image">
            <input type="hidden" name="ff_global[<?php echo esc_attr( $name ); ?>]" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>">
            <div class="ff-media-preview-wrap" style="margin-bottom:8px;">
                <img class="ff-media-preview" src="<?php echo esc_url( $img_src ); ?>" style="<?php echo $img_src ? '' : 'display:none;'; ?>max-height:80px;border-radius:4px;">
            </div>
            <button type="button" class="button ff-media-select" data-target="<?php echo esc_attr( $id ); ?>">Select Image</button>
            <button type="button" class="button ff-media-clear" data-target="<?php echo esc_attr( $id ); ?>" style="<?php echo $value ? '' : 'display:none;'; ?>">Clear</button>
        </div>
        <?php
        break;

    case 'file':
        $file_url  = $value ? wp_get_attachment_url( (int) $value ) : '';
        $file_name = $file_url ? wp_basename( $file_url ) : '';
        ?>
        <div class="ff-media-wrap" data-type="file">
            <input type="hidden" name="ff_global[<?php echo esc_attr( $name ); ?>]" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $value ); ?>">
            <div class="ff-media-fileline" style="margin-bottom:8px;">
                <span class="dashicons dashicons-media-document" aria-hidden="true"></span>
                <a class="ff-media-fileurl" href="<?php echo esc_url( $file_url ); ?>" target="_blank" style="<?php echo $file_url ? '' : 'display:none;'; ?>"><?php echo esc_html( $file_name ); ?></a>
                <span class="ff-media-nofile" style="<?php echo $file_url ? 'display:none;' : ''; ?>">No file selected.</span>
            </div>
            <button type="button" class="button ff-media-select" data-target="<?php echo esc_attr( $id ); ?>">Select File</button>
            <button type="button" class="button ff-media-clear" data-target="<?php echo esc_attr( $id ); ?>" style="<?php echo $value ? '' : 'display:none;'; ?>">Clear</button>
        </div>
        <?php
        break;

    case 'select':
        $choices_map = ff_parse_choices_string( $field['choices'] ?? '' );
        $current     = is_scalar( $value ) ? (string) $value : '';
        echo '<div class="ff-select-wrap">';
        echo '<select name="ff_global[' . esc_attr( $name ) . ']" id="' . esc_attr( $id ) . '">';
        foreach ( $choices_map as $v => $lbl ) {
            printf(
                '<option value="%1$s"%3$s>%2$s</option>',
                esc_attr( $v ),
                esc_html( $lbl ),
                selected( $current, (string) $v, false )
            );
        }
        echo '</select>';
        echo '</div>';
        break;

    case 'radio':
    $choices_map = ff_parse_choices_string( $field['choices'] ?? '' );
    $current     = is_scalar( $value ) ? (string) $value : '';

    foreach ( $choices_map as $v => $lbl ) {
        $field_id = $id . '_' . sanitize_key( (string) $v );
        printf(
            '<label for="%1$s" style="display:inline-block;margin-right:12px;">
                <input type="radio" name="ff_global[%2$s]" id="%1$s" value="%3$s" %4$s>
                %5$s
            </label>',
            esc_attr( $field_id ),
            esc_attr( $name ),
            esc_attr( $v ),
            checked( $current, (string) $v, false ),
            esc_html( $lbl )
        );
    }
    break;

case 'button_group':
    $choices_map = ff_parse_choices_string( $field['choices'] ?? '' );
    $current     = is_scalar( $value ) ? (string) $value : '';

    echo '<div class="ff-button-group" role="radiogroup">';
    foreach ( $choices_map as $v => $lbl ) {
        $field_id = $id . '_' . sanitize_key( (string) $v );
        printf(
            '<label class="ff-button-group__btn" for="%1$s">
                <input class="ff-button-group__input" type="radio" name="ff_global[%2$s]" id="%1$s" value="%3$s" %4$s>
                <span class="ff-button-group__label">%5$s</span>
            </label>',
            esc_attr( $field_id ),
            esc_attr( $name ),
            esc_attr( $v ),
            checked( $current, (string) $v, false ),
            esc_html( $lbl )
        );
    }
    echo '</div>';
    break;


    case 'checkbox':
        $choices_map = ff_parse_choices_string( $field['choices'] ?? '' );
        $current     = is_array( $value ) ? array_map( 'strval', $value ) : [];
        foreach ( $choices_map as $v => $lbl ) {
            $field_id = $id . '_' . sanitize_key( (string) $v );
            printf(
                '<label for="%1$s" style="display:inline-block;margin-right:12px;">
                    <input type="checkbox" name="ff_global[%2$s][]" id="%1$s" value="%3$s" %4$s>
                    %5$s
                </label>',
                esc_attr( $field_id ),
                esc_attr( $name ),
                esc_attr( $v ),
                in_array( (string) $v, $current, true ) ? 'checked' : '',
                esc_html( $lbl )
            );
        }
        break;

    case 'true_false':
        $checked = ! empty( $value );
        printf(
            '<label>
                <input type="checkbox" name="ff_global[%1$s]" id="%2$s" value="1" %3$s>
                %4$s
            </label>',
            esc_attr( $name ),
            esc_attr( $id ),
            checked( $checked, true, false ),
            esc_html__( 'Enabled', 'forge-fields' )
        );
        break;
}
?>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>

                <?php endforeach; ?>

            </form>
        <?php endif; ?>
    </div>
    <?php
}
// echo ff_get_field( 'heading2', 'global' ); -- to read global on frontend
// e.g. echo esc_attr( ff_get_field( 'meta_description', 'global' ) );
// e.g.  $ga_id = ff_get_field( 'ga_measurement_id', 'global' );
/** gut block function ff_global_shortcode( $atts ) {
    return ff_get_field( $atts['name'] ?? '', 'global' );
}
add_shortcode( 'ff_global', 'ff_global_shortcode' ); **/

/**
 * Normalize a choices textarea into a canonical stored string.
 *
 * Option A behavior:
 * - "value" stays "value" (one token line)
 * - "value|Label" becomes "value : Label"
 * - "value : Label" stays "value : Label"
 *
 * Returns: [ $normalized_string, $warnings_array, $errors_array ]
 */
function ff_normalize_choices_textarea( $raw_text ) {
    $warnings = [];
    $errors   = [];

    $lines = preg_split( '/\r\n|\r|\n/', (string) $raw_text );
    $out_lines = [];
    $seen_values = [];

    foreach ( $lines as $i => $line ) {
        $original = trim( (string) $line );
        if ( $original === '' ) {
            continue;
        }

        $has_pipe  = ( strpos( $original, '|' ) !== false );
        $has_colon = ( strpos( $original, ':' ) !== false );

        // Parse into $value_raw and $label_raw only if delimiter exists.
        if ( $has_pipe ) {
            list( $value_raw, $label_raw ) = array_map( 'trim', explode( '|', $original, 2 ) );
        } elseif ( $has_colon ) {
            list( $value_raw, $label_raw ) = array_map( 'trim', explode( ':', $original, 2 ) );
        } else {
            $value_raw = $original;
            $label_raw = '';
        }

        $value_sanitized = sanitize_key( $value_raw );

        // If they used a delimiter but value becomes empty after sanitizing, that's invalid.
        if ( ( $has_pipe || $has_colon ) && $value_sanitized === '' ) {
            $errors[] = sprintf(
                'Line %d: Invalid choice value "%s". Use letters/numbers/underscores/dashes before the ":" or "|".',
                $i + 1,
                $value_raw
            );
            continue;
        }

        // If it was a "bare" line (no delimiter), still require a usable value.
        if ( ! $has_pipe && ! $has_colon && $value_sanitized === '' ) {
            $errors[] = sprintf(
                'Line %d: Invalid choice "%s". Use at least one letter or number.',
                $i + 1,
                $original
            );
            continue;
        }

        // Warn if we had to clean the value.
        if ( $value_raw !== $value_sanitized ) {
            $warnings[] = sprintf(
                'Line %d: Value "%s" was cleaned to "%s".',
                $i + 1,
                $value_raw,
                $value_sanitized
            );
        }

        // Default label if delimiter used but label is empty.
        $label_clean = sanitize_text_field( (string) $label_raw );
        if ( ( $has_pipe || $has_colon ) && $label_clean === '' ) {
            $label_clean = $value_sanitized;
        }

        // Duplicate values are ambiguous; treat as error.
        if ( isset( $seen_values[ $value_sanitized ] ) ) {
            $errors[] = sprintf(
                'Line %d: Duplicate choice value "%s". Each choice value must be unique.',
                $i + 1,
                $value_sanitized
            );
            continue;
        }
        $seen_values[ $value_sanitized ] = true;

        // OPTION A: bare lines stay bare.
        if ( ! $has_pipe && ! $has_colon ) {
            $out_lines[] = $value_sanitized;
        } else {
            $out_lines[] = $value_sanitized . ' : ' . $label_clean;
        }
    }

    return [ implode( "\n", $out_lines ), $warnings, $errors ];
}

/**
 * EDIT / ADD-NEW SCREEN
 * -------------------------------------------------------------------------
 * - If ?group=<id> is present and found: edit that group.
 * - Otherwise: "Add New" (blank group).
 * - Title is REQUIRED.
 */
function ff_render_field_group_edit() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $groups   = ff_get_all_groups();
    $group_id = isset( $_GET['group'] ) ? sanitize_text_field( wp_unslash( $_GET['group'] ) ) : '';
    $is_new   = true;
    $notices  = [];

    // Base empty group for "Add New"
    $group = [
        'id'       => '',
        'title'    => '',
        'location' => 'page',
        'location_target' => '',
        'fields'   => [],
        'status'   => 'active',
    ];

    // If editing existing and it exists, load it as the starting point
    if ( $group_id && isset( $groups[ $group_id ] ) && is_array( $groups[ $group_id ] ) ) {
        $group   = $groups[ $group_id ];
        $is_new  = false;
    }

    // Handle SAVE
    if ( isset( $_POST['ff_save_field_group'] ) ) {

        check_admin_referer( 'ff_save_field_group' );

        $posted_group_id = isset( $_POST['ff_group_id'] )
            ? sanitize_text_field( wp_unslash( $_POST['ff_group_id'] ) )
            : '';

        $title = isset( $_POST['ff_group_title'] )
            ? sanitize_text_field( wp_unslash( $_POST['ff_group_title'] ) )
            : '';

        $location = isset( $_POST['ff_location'] )
            ? sanitize_text_field( wp_unslash( $_POST['ff_location'] ) )
            : 'page';

        $location_target = '';

        if ( $location === 'page' ) {
            $location_target = isset( $_POST['ff_location_target_page'] )
                ? absint( $_POST['ff_location_target_page'] )
                : 0;
        } elseif ( $location === 'post' ) {
            $location_target = isset( $_POST['ff_location_target_post'] )
                ? absint( $_POST['ff_location_target_post'] )
                : 0;
        }

        $fields_raw = isset( $_POST['ff_fields'] ) && is_array( $_POST['ff_fields'] )
            ? $_POST['ff_fields']
            : [];

                $fields = [];

        foreach ( $fields_raw as $field_raw ) {
            $name  = isset( $field_raw['name'] )  ? sanitize_key( $field_raw['name'] )  : '';
            $label = isset( $field_raw['label'] ) ? sanitize_text_field( $field_raw['label'] ) : '';
            $type  = isset( $field_raw['type'] )  ? sanitize_text_field( $field_raw['type'] )  : 'text';

            // Skip completely empty rows
            if ( $name === '' && $label === '' ) {
                continue;
            }

            // If name blank but label set, derive slug from label
            if ( $name === '' && $label !== '' ) {
                $name = sanitize_key( strtolower( str_replace( ' ', '_', $label ) ) );
            }

            // Capture the raw choices textarea (only relevant types will use it)
            $choice_types = [ 'select', 'checkbox', 'radio', 'button_group', 'true_false' ];
            $choices_raw  = '';
            if ( in_array( $type, $choice_types, true ) ) {
                $choices_raw = isset( $field_raw['choices'] )
                    ? trim( wp_unslash( (string) $field_raw['choices'] ) )
                    : '';
            }

            
            // Normalize + validate ONLY for real choice lists (true_false ignores choices)
            $choices_to_save = $choices_raw;

            if ( in_array( $type, [ 'select', 'checkbox', 'radio', 'button_group' ], true ) ) {

                $result = ff_normalize_choices_string( $choices_raw );

                if ( ! empty( $result['errors'] ) ) {
                    $field_label_for_error = $label !== '' ? $label : $name;

                    foreach ( $result['errors'] as $msg ) {
                        $notices[] = [
                            'type'    => 'error',
                            'message' => sprintf( 'Field "%s": %s', $field_label_for_error, $msg ),
                        ];
                    }

                    // Keep raw so user can fix it (we also block saving later because $notices not empty)
                    $choices_to_save = $choices_raw;

                } else {
                    // Save normalized canonical "value : Label"
                    $choices_to_save = $result['normalized'];

                    // One warning per field if we had to clean anything
                    if ( ! empty( $result['warnings'] ) ) {
                        $field_label_for_error = $label !== '' ? $label : $name;

                        $notices[] = [
                            'type'    => 'warning',
                            'message' => sprintf(
                                'Field "%s": choices were cleaned and normalized on save.',
                                $field_label_for_error
                            ),
                        ];
                    }
                }
            }

            // Build row
            $row = [
                'name'  => $name,
                'label' => $label,
                'type'  => $type,
            ];

            // Store choices only for types that need them (prevents junk being stored on non-choice types)
            if ( in_array( $type, [ 'select', 'checkbox', 'radio', 'button_group' ], true ) ) {
                $row['choices'] = $choices_to_save;
            }

            $fields[] = $row;

        }


        // Basic validation: title required
        if ( $title === '' ) {
            $notices[] = [
                'type'    => 'error',
                'message' => 'Please enter a Field Group title.',
            ];
        }

        if ( empty( $notices ) ) {

            // Determine status: preserve existing if editing, else "active".
            $current_status = 'active';
            if ( $posted_group_id && isset( $groups[ $posted_group_id ]['status'] ) ) {
                $current_status = $groups[ $posted_group_id ]['status'];
            }

            // New group? Generate ID.
            if ( $posted_group_id === '' || $posted_group_id === 'new' ) {
                $posted_group_id = ff_generate_group_id();
                $is_new          = false;
            }

            $group = [
                'id'       => $posted_group_id,
                'title'    => $title,
                'location' => $location,
                'location_target' => $location_target ? (string) $location_target : '',
                'fields'   => $fields,
                'status'   => $current_status,
            ];

            $groups[ $posted_group_id ] = $group;
            ff_save_all_groups( $groups );

            $group_id = $posted_group_id;

            $notices[] = [
                'type'    => 'updated',
                'message' => 'Field group saved.',
            ];
        } else {
            // Keep posted values in the $group variable so the form is repopulated
            $group = [
                'id'       => $posted_group_id,
                'title'    => $title,
                'location' => $location,
                'location_target' => $location_target ? (string) $location_target : '',
                'fields'   => $fields,
                'status'   => isset( $group['status'] ) ? $group['status'] : 'active',
            ];
            $is_new = ( $posted_group_id === '' || $posted_group_id === 'new' );
        }
    }

    // Final values for the form
        $title           = isset( $group['title'] ) ? $group['title'] : '';
        $location        = isset( $group['location'] ) ? $group['location'] : 'page';
        $location_target = isset( $group['location_target'] ) ? (string) $group['location_target'] : '';
        $fields          = isset( $group['fields'] ) && is_array( $group['fields'] ) ? $group['fields'] : [];

        $page_options = get_posts( [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );

        $post_options = get_posts( [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

    // --- NEW: grouped type list (future-proof if you add more groups) ---
    $type_groups = [
        'Basic' => [ 'text', 'textarea', 'number', 'email', 'url', 'range', 'password' ],
        'Content' => [ 'image', 'file', 'wysiwyg' ],
        'Choice'  => [ 'select', 'checkbox', 'radio', 'button_group', 'true_false' ],
    ];

    ?>
    <div class="wrap ff-admin ff-edit ff-has-brandbar">
      

        <?php foreach ( $notices as $n ) :
            $cls = 'notice inline';
            switch ( $n['type'] ?? '' ) {
                case 'error':   $cls .= ' notice-error';   break;
                case 'updated': $cls .= ' notice-success'; break;
                case 'warning': $cls .= ' notice-warning'; break;
                default:        $cls .= ' notice-info';    break;
            }
        ?>
            <div class="<?php echo esc_attr( $cls ); ?> is-dismissible">
                <p><?php echo esc_html( $n['message'] ); ?></p>
            </div>
        <?php endforeach; ?>


        <form method="post" action="" id="ff-edit-form">
            <?php wp_nonce_field( 'ff_save_field_group' ); ?>
            <input type="hidden" name="ff_group_id"
                   value="<?php echo esc_attr( isset( $group['id'] ) ? $group['id'] : '' ); ?>">

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="ff_group_title">Group Title</label></th>
                    <td>
                        <input type="text"
                               id="ff_group_title"
                               name="ff_group_title"
                               class="regular-text"
                               value="<?php echo esc_attr( $title ); ?>">
                        <p class="description">e.g. “Landing Page – Hero Section”.</p>
                    </td>
                </tr>

                <tr>
    <th scope="row"><label for="ff_location">Location</label></th>
    <td>
        <div class="ff-location-row" style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <div class="ff-select-wrap">
                <select id="ff_location" name="ff_location">
                    <option value="page" <?php selected( $location, 'page' ); ?>>Page</option>
                    <option value="post" <?php selected( $location, 'post' ); ?>>Post</option>
                    <option value="global" <?php selected( $location, 'global' ); ?>>Global</option>
                </select>
            </div>

            <div class="ff-select-wrap" id="ff_location_target_page_wrap" <?php echo $location === 'page' ? '' : 'style="display:none;"'; ?>>
                <select id="ff_location_target_page" name="ff_location_target_page">
                    <option value="">All Pages</option>
                    <?php foreach ( $page_options as $page_post ) : ?>
                        <option
                            value="<?php echo esc_attr( $page_post->ID ); ?>"
                            <?php selected( $location === 'page' ? $location_target : '', (string) $page_post->ID ); ?>
                        >
                            <?php echo esc_html( get_the_title( $page_post ) ?: '(no title)' ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="ff-select-wrap" id="ff_location_target_post_wrap" <?php echo $location === 'post' ? '' : 'style="display:none;"'; ?>>
                <select id="ff_location_target_post" name="ff_location_target_post">
                    <option value="">All Posts</option>
                    <?php foreach ( $post_options as $single_post ) : ?>
                        <option
                            value="<?php echo esc_attr( $single_post->ID ); ?>"
                            <?php selected( $location === 'post' ? $location_target : '', (string) $single_post->ID ); ?>
                        >
                            <?php echo esc_html( get_the_title( $single_post ) ?: '(no title)' ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <p class="description">
            Choose whether this field group appears on all pages/posts, or only one specific item. Global ignores the second dropdown.
        </p>
    </td>
</tr>
            </table>

            <h2>Fields</h2>
            <p>Define your fields (label, name, type). Add or remove rows as needed.</p>

            <table class="widefat fixed striped">
                <thead>
                <tr>
                    <th style="width:30px;"></th>
                    <th class="ff-bar-title">Label</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th style="width:80px;"></th>
                </tr>
                </thead>

                <tbody id="ff-fields-body">
                <?php
                // Ensure at least one row.
                if ( empty( $fields ) ) {
                    $fields = [
                        [
                            'label' => '',
                            'name'  => '',
                            'type'  => 'text',
                        ],
                    ];
                }

                foreach ( $fields as $index => $field ) :
                    $label = isset( $field['label'] ) ? $field['label'] : '';
                    $name  = isset( $field['name'] )  ? $field['name']  : '';
                    $type  = isset( $field['type'] )  ? $field['type']  : 'text';
                    ?>
                    <?php
                    $choice_types = ['select','checkbox','radio','button_group'];
                    $choices_raw  = isset($field['choices']) ? (string) $field['choices'] : '';
                    $is_choice    = in_array($type, $choice_types, true);
                    ?>
                    <tr class="ff-field-row" data-index="<?php echo esc_attr( $index ); ?>">
                        <td class="ff-field-handle" aria-label="Drag" title="Drag"></td>

                        <td>
                            <input type="text"
                                name="ff_fields[<?php echo $index; ?>][label]"
                                value="<?php echo esc_attr( $label ); ?>"
                                class="regular-text ff-field-label"
                                data-index="<?php echo esc_attr( $index ); ?>"
                                data-field-part="label">
                        </td>

                        <td>
                            <input type="text"
                                name="ff_fields[<?php echo $index; ?>][name]"
                                value="<?php echo esc_attr( $name ); ?>"
                                class="regular-text ff-field-name"
                                data-index="<?php echo esc_attr( $index ); ?>"
                                data-field-part="name">
                        </td>

                        <td>
                            <div class="ff-select-wrap">
                                            <select name="ff_fields[<?php echo $index; ?>][type]" class="ff-field-type" data-field-part="type">
                                                <?php foreach ( $type_groups as $group_label => $opts ) : ?>
                                                    <optgroup label="<?php echo esc_attr( $group_label ); ?>">
                                                        <?php foreach ( $opts as $t ) : ?>
                                                            <?php
                                                                $pretty = [
                                                                    'text'     => 'Text',
                                                                    'textarea' => 'Textarea',
                                                                    'number'   => 'Number',
                                                                    'email'    => 'Email',
                                                                    'url'      => 'URL',
                                                                    'range'    => 'Range',
                                                                    'password' => 'Password',
                                                                    'image'    => 'Image',
                                                                    'file'     => 'File',
                                                                    'wysiwyg'  => 'WYSIWYG Editor',
                                                                    'select'  => 'Select',
                                                                    'checkbox'  => 'Checkbox',
                                                                    'radio'  => 'Radio',
                                                                    'button_group'  => 'Button Group',
                                                                    'true_false'  => 'True/False',
                                                                ];
                                                                ?>
                                                                <option value="<?php echo esc_attr( $t ); ?>" <?php selected( $type, $t ); ?>>
                                                                    <?php echo esc_html( $pretty[ $t ] ?? ucfirst( $t ) ); ?>
                                                                </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                        </td>

                        <td>
                            <a href="#" class="ff-field-remove">Remove</a>
                        </td>
                    </tr>
                    <tr
                    class="ff-field-settings<?php echo $is_choice ? '' : ' is-hidden'; ?>"
                    data-index="<?php echo esc_attr( $index ); ?>"
                    data-ff-settings
                    <?php echo $is_choice ? '' : 'style="display:none"'; ?>
                >

                        <td colspan="5">
                            <div class="ff-field-setting ff-setting-choices">
                                <label style="display:block;font-weight:600;margin:6px 0;">Choices (one per line)</label>
                                <textarea name="ff_fields[<?php echo $index; ?>][choices]" rows="3" class="large-text" placeholder="value : Label&#10;pro : Pro Plan&#10;enterprise : Enterprise"><?php echo esc_textarea( $choices_raw ); ?></textarea>
                                <p class="description" style="margin-top:6px;">
                                    Supported formats: <code>value : Label</code> or <code>value</code>.
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>


            <!-- Template for new rows -->
            <script type="text/html" id="ff-field-row-template">
            <tr class="ff-field-row" data-index="__INDEX__">
                <td class="ff-field-handle" aria-label="Drag" title="Drag"></td>

                <td>
                    <input type="text"
                        name="ff_fields[__INDEX__][label]"
                        value=""
                        class="regular-text ff-field-label"
                        data-field-part="label">
                </td>

                <td>
                    <input type="text"
                        name="ff_fields[__INDEX__][name]"
                        value=""
                        class="regular-text ff-field-name"
                        data-field-part="name">
                </td>

                <td>
                    <div class="ff-select-wrap">
                        <select name="ff_fields[__INDEX__][type]" class="ff-field-type" data-field-part="type">
                            <?php foreach ( $type_groups as $group_label => $opts ) : ?>
                                <optgroup label="<?php echo esc_attr( $group_label ); ?>">
                                    <?php foreach ( $opts as $t ) : ?>
                                        <?php
                                            $pretty = [
                                                'text'     => 'Text',
                                                'textarea' => 'Textarea',
                                                'number'   => 'Number',
                                                'email'    => 'Email',
                                                'url'      => 'URL',
                                                'range'    => 'Range',
                                                'password' => 'Password',
                                                'image'    => 'Image',
                                                'file'     => 'File',
                                                'wysiwyg'  => 'WYSIWYG Editor',
                                                'select'  => 'Select',
                                                'checkbox'  => 'Checkbox',
                                                'radio'  => 'Radio',
                                                'button_group'  => 'Button Group',
                                                'true_false'  => 'True/False',
                                            ];
                                        ?>
                                        <option value="<?php echo esc_attr( $t ); ?>">
                                            <?php echo esc_html( $pretty[$t] ?? ucfirst($t) ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </td>

                <td class="ff-field-actions">
                    <a href="#" class="ff-field-remove">Remove</a>
                </td>
            </tr>

            <tr class="ff-field-settings is-hidden" data-index="__INDEX__" data-ff-settings style="display:none">
                <td colspan="5">
                    <div class="ff-field-setting ff-setting-choices">
                        <label style="display:block;font-weight:600;margin:6px 0;">Choices (one per line)</label>
                        <textarea name="ff_fields[__INDEX__][choices]"
                                rows="3"
                                class="large-text"
                                placeholder="value : Label&#10;"></textarea>
                        <p class="description" style="margin-top:6px;">
                            Supported formats: <code>value : Label</code>, <code>value|Label</code> or <code>value</code>.
                        </p>
                    </div>
                </td>
            </tr>
            </script>

            
                    </form>
                    <!-- FF confirm modal -->
                    <div id="ff-confirm" class="ff-confirm is-hidden" role="dialog" aria-modal="true" aria-labelledby="ff-confirm-title" aria-describedby="ff-confirm-desc">
                    <div class="ff-confirm__overlay" data-ff-close></div>
                    <div class="ff-confirm__dialog" role="document" tabindex="-1">
                        <h2 id="ff-confirm-title" class="ff-confirm__title">Remove field?</h2>
                        <p id="ff-confirm-desc" class="ff-confirm__desc">
                        This will remove <strong class="ff-confirm__field-name">this field</strong> from the group.
                        </p>
                        <div class="ff-confirm__actions">
                        <button type="button" class="button" data-ff-close>Cancel</button>
                        <button type="button" class="button ff-button-danger" id="ff-confirm-yes">Remove</button>
                        </div>
                        <button type="button" class="ff-confirm__x" aria-label="Close" data-ff-close>×</button>
                    </div>
                    </div>
                </div>
                <?php
}
