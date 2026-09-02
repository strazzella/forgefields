<?php echo ff_get_field('field_name'); ?>

<h1><?php echo ff_get_field('hero_title'); ?></h1>

<h1><?php echo esc_html(ff_get_field('hero_title')); ?></h1>

<?php
$image_id = ff_get_field('hero_image');

if ($image_id) {
    echo wp_get_attachment_image($image_id, 'full');
}
?>

<?php echo wp_kses_post(ff_get_field('content')); ?>

<?php echo ff_get_field('field_name', 'global'); ?>

<h2><?php echo esc_html(ff_get_field('company_name', 'global')); ?></h2>

<?php echo ff_get_global('field_name'); ?>

ff_get_field('company_name', 'global');

ff_get_global('company_name');

ff_get_field('field_name');

ff_get_field('field_name', 123);

<title><?php echo esc_html(ff_get_global('home')); ?></title>

<meta
    name="description"
    content="<?php echo esc_attr(ff_get_global('home_meta_description')); ?>"
>