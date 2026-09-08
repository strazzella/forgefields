<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Build a Forge Fields status pill.
 *
 * Converts a status value into a styled HTML label used throughout
 * the Forge Fields admin interface.
 *
 * Supported statuses commonly include:
 * - active
 * - inactive
 * - trash
 *
 * @param string $status Status value to display.
 *
 * @return string Escaped HTML for the status pill.
 */
function ff_status_pill(string $status): string
{
    $status = strtolower($status);

    $label  = ($status === 'active')
        ? 'Active'
        : (($status === 'inactive') ? 'Inactive' : ucfirst($status));

    $cls = 'ff-status ff-status--' . esc_attr($status);

    return '<span class="' . $cls . '">' . esc_html($label) . '</span>';
}

/**
 * Build a Forge Fields numeric count chip.
 *
 * Used to display compact counts, such as the number of fields
 * contained within a field group.
 *
 * @param int    $count Numeric value to display.
 * @param string $title Optional tooltip/title for the chip.
 *
 * @return string Escaped HTML for the count chip.
 */
function ff_count_chip(int $count, string $title = 'Fields'): string
{
    return '<span class="ff-chip" title="' . esc_attr($title) . '">' . intval($count) . '</span>';
}
