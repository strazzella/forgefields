<?php
// Minimal WordPress bootstrap for static analysis only.

// Common WP functions used in plugins (declare as stubs if not picked up).
if (!function_exists('add_action')) { function add_action(...$args) {} }
if (!function_exists('add_filter')) { function add_filter(...$args) {} }
if (!function_exists('do_action')) { function do_action(...$args) {} }
if (!function_exists('apply_filters')) { function apply_filters($tag, $value, ...$args) { return $value; } }

if (!function_exists('get_option')) { function get_option($name, $default = false) { return $default; } }
if (!function_exists('update_option')) { function update_option($name, $value, $autoload = null) { return true; } }
if (!function_exists('delete_option')) { function delete_option($name) { return true; } }

if (!function_exists('sanitize_key')) { function sanitize_key(mixed $key): string { return (string) $key; } }
if (!function_exists('sanitize_text_field')) { function sanitize_text_field(mixed $str): string { return (string) $str; } }

if (!function_exists('esc_attr')) { function esc_attr($text) { return (string) $text; } }
if (!function_exists('esc_html')) { function esc_html($text) { return (string) $text; } }
if (!function_exists('esc_url')) { function esc_url($url) { return (string) $url; } }
if (!function_exists('esc_textarea')) { function esc_textarea($text) { return (string) $text; } }

if (!function_exists('esc_url_raw')) { function esc_url_raw(mixed $url): string { return (string) $url; } }

if (!function_exists('sanitize_email')) { function sanitize_email(mixed $email): string { return (string) $email; } }


if (!function_exists('admin_url')) { function admin_url($path = '', $scheme = 'admin') { return $path; } }

if (!function_exists('wp_check_invalid_utf8')) { function wp_check_invalid_utf8(mixed $string, bool $strip = false): string { return (string) $string; } }
if (!function_exists('wp_parse_args')) { function wp_parse_args(mixed $args, array $defaults = []): array { return is_array($args) ? array_merge($defaults, $args) : $defaults; } }

if (!function_exists('selected')) { function selected(mixed $selected, mixed $current = true, bool $display = true): string { return ''; } }
if (!function_exists('checked')) { function checked(mixed $checked, mixed $current = true, bool $display = true): string { return ''; } }

if (!function_exists('wp_nonce_field')) { function wp_nonce_field(...$args): string { return ''; } }
if (!function_exists('wp_verify_nonce')) { function wp_verify_nonce(...$args): bool { return true; } }
if (!function_exists('wp_enqueue_media')) { function wp_enqueue_media(...$args): void {} }
if (!function_exists('wp_enqueue_style')) { function wp_enqueue_style(...$args): void {} }
if (!function_exists('wp_enqueue_script')) { function wp_enqueue_script(...$args): void {} }

if (!function_exists('wp_editor')) { function wp_editor(...$args): void {} }
if (!function_exists('add_meta_box')) { function add_meta_box(...$args): void {} }

if (!function_exists('get_post_meta')) { function get_post_meta(...$args): mixed { return null; } }
if (!function_exists('update_post_meta')) { function update_post_meta(...$args): bool { return true; } }
if (!function_exists('delete_post_meta')) { function delete_post_meta(...$args): bool { return true; } }

if (!function_exists('wp_get_attachment_image_src')) { function wp_get_attachment_image_src(...$args): mixed { return null; } }
if (!function_exists('wp_get_attachment_url')) { function wp_get_attachment_url(...$args): string { return ''; } }

if (!function_exists('wp_basename')) { function wp_basename(mixed $path, string $suffix = ''): string { return basename((string) $path, $suffix); } }

if (!function_exists('wp_kses_post')) { function wp_kses_post(mixed $content): string { return (string) $content; } }
if (!function_exists('wp_unslash')) { function wp_unslash(mixed $value): mixed { return $value; } }

if (!function_exists('plugin_dir_path')) { function plugin_dir_path(string $file): string { return ''; } }
if (!function_exists('plugin_dir_url')) { function plugin_dir_url(string $file): string { return ''; } }
if (!function_exists('is_admin')) { function is_admin(): bool { return true; } }

if (!function_exists('add_menu_page')) { function add_menu_page(...$args): string { return ''; } }
if (!function_exists('add_submenu_page')) { function add_submenu_page(...$args): string { return ''; } }

if (!function_exists('add_query_arg')) { function add_query_arg(...$args): string { return ''; } }
if (!function_exists('remove_query_arg')) { function remove_query_arg(...$args): string { return ''; } }
if (!function_exists('wp_safe_redirect')) { function wp_safe_redirect(string $location, int $status = 302): void {} }
if (!function_exists('wp_cache_delete')) { function wp_cache_delete(...$args): bool { return true; } }
if (!function_exists('check_admin_referer')) { function check_admin_referer(...$args): bool { return true; } }
if (!function_exists('absint')) { function absint(mixed $maybeint): int { return (int) $maybeint; } }

if (!function_exists('get_the_ID')) { function get_the_ID(): int { return 0; } }

if (!function_exists('wp_nonce_url')) { function wp_nonce_url($actionurl, $action = -1, $name = '_wpnonce') { return (string) $actionurl; } }
if (!function_exists('esc_html__')) { function esc_html__($text, $domain = 'default') { return (string) $text; } }

if (!function_exists('add_menu_page')) { function add_menu_page(...$args) { return ''; } }
if (!function_exists('add_submenu_page')) { function add_submenu_page(...$args) { return ''; } }
