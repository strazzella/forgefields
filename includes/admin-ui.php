<?php

/**
 * Prevent direct access to this file outside of WordPress.
 */
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
    /**
     * Normalize the status value before building the label and class.
     */
    $status = strtolower($status);

    /**
     * Convert known statuses into user-facing labels.
     */
    $label  = ($status === 'active')
        ? 'Active'
        : (($status === 'inactive') ? 'Inactive' : ucfirst($status));

    /**
     * Build the CSS class used to style the status pill.
     */
    $cls = 'ff-status ff-status--' . esc_attr($status);

    /**
     * Return the completed status pill markup.
     */
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
    /**
     * Return the count as a compact, styled Forge Fields chip.
     */
    return '<span class="ff-chip" title="' . esc_attr($title) . '">' . intval($count) . '</span>';
}
