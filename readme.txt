/**
 * Forge Fields Usage Examples
 *
 * These examples show common ways to retrieve and safely output
 * Forge Fields values in a WordPress theme or plugin.
 */


/*
|--------------------------------------------------------------------------
| Get a field from the current post/page
|--------------------------------------------------------------------------
*/

$value = ff_get_field('field_name');


/*
|--------------------------------------------------------------------------
| Output a text field safely
|--------------------------------------------------------------------------
*/

<h1>
    <?php echo esc_html(ff_get_field('hero_title')); ?>
</h1>


/*
|--------------------------------------------------------------------------
| Get a field from a specific post/page
|--------------------------------------------------------------------------
*/

$value = ff_get_field('field_name', 123);


/*
|--------------------------------------------------------------------------
| Output WYSIWYG content
|--------------------------------------------------------------------------
|
| wp_kses_post() allows standard WordPress-safe HTML while removing
| potentially unsafe markup.
|
*/

<?php echo wp_kses_post(ff_get_field('content')); ?>


/*
|--------------------------------------------------------------------------
| Output an Image field
|--------------------------------------------------------------------------
|
| Forge Fields stores WordPress attachment IDs for image fields.
|
*/

<?php
$image_id = absint(ff_get_field('hero_image'));

if ($image_id) {
    echo wp_get_attachment_image(
        $image_id,
        'full'
    );
}
?>


/*
|--------------------------------------------------------------------------
| Get the URL for an Image field
|--------------------------------------------------------------------------
*/

<?php
$image_id = absint(ff_get_field('hero_image'));

$image_url = wp_get_attachment_image_url(
    $image_id,
    'full'
);
?>

<?php if ($image_url) : ?>
    <img
        src="<?php echo esc_url($image_url); ?>"
        alt=""
    >
<?php endif; ?>


/*
|--------------------------------------------------------------------------
| Get a Global Field
|--------------------------------------------------------------------------
*/

$value = ff_get_global('field_name');


/*
|--------------------------------------------------------------------------
| Output a Global text field safely
|--------------------------------------------------------------------------
*/

<h2>
    <?php echo esc_html(ff_get_global('company_name')); ?>
</h2>


/*
|--------------------------------------------------------------------------
| Get a Global Field using ff_get_field()
|--------------------------------------------------------------------------
|
| Global Fields can also be retrieved by passing "global"
| as the second argument.
|
*/

$value = ff_get_field('company_name', 'global');


/*
|--------------------------------------------------------------------------
| Output a Global Field inside normal HTML
|--------------------------------------------------------------------------
*/

<title>
    <?php echo esc_html(ff_get_global('home')); ?>
</title>


/*
|--------------------------------------------------------------------------
| Output a Global Field inside an HTML attribute
|--------------------------------------------------------------------------
|
| Use esc_attr() when inserting a Forge Fields value into
| an HTML attribute.
|
*/

<meta
    name="description"
    content="<?php echo esc_attr(
        ff_get_global('home_meta_description')
    ); ?>"
>


/*
|--------------------------------------------------------------------------
| Output a Global Image field
|--------------------------------------------------------------------------
*/

<?php
$image_id = absint(
    ff_get_global('site_logo')
);

if ($image_id) {
    echo wp_get_attachment_image(
        $image_id,
        'full'
    );
}
?>


/*
|--------------------------------------------------------------------------
| Common escaping rules
|--------------------------------------------------------------------------
|
| Text:
| esc_html()
|
| HTML attributes:
| esc_attr()
|
| URLs:
| esc_url()
|
| WYSIWYG / allowed WordPress HTML:
| wp_kses_post()
|
| Image/File attachment IDs:
| absint()
|
*/

/*
|--------------------------------------------------------------------------
| True or False
|--------------------------------------------------------------------------
*/

<?php if (ff_get_field('show_banner')) : ?>
    <div class="banner">
        Banner content here
    </div>
<?php endif; ?>


<?php
$show_banner = ff_get_field('show_banner');

if ($show_banner) {
    echo '<p>Enabled</p>';
}
?>

/*
|--------------------------------------------------------------------------
| Duplicate Field Key - Use Group Key
|--------------------------------------------------------------------------
*/

<?php
$show_banner = ff_get_field(
    'show_banner',
    null,
    'ff_group_6a997bba1fdae'
);

if ($show_banner) {
    echo '<div class="banner">Banner is enabled.</div>';
}
?>


<?php if (ff_get_field('show_banner', null, 'ff_group_6a997bba1fdae')) : ?>
    <div class="banner">
        Banner is enabled.
    </div>
<?php endif; ?>