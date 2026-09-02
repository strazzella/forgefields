<?php

if (!defined('ABSPATH')) {
    exit;
}

function ff_status_pill(string $status): string
{
    $status = strtolower($status);
    $label  = ($status === 'active') ? 'Active' : (($status === 'inactive') ? 'Inactive' : ucfirst($status));
    $cls    = 'ff-status ff-status--' . esc_attr($status);
    return '<span class="' . $cls . '">' . esc_html($label) . '</span>';
}

function ff_count_chip(int $count, string $title = 'Fields'): string
{
    return '<span class="ff-chip" title="' . esc_attr($title) . '">' . intval($count) . '</span>';
}
