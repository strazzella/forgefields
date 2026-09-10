# Forge Fields Developer Usage

These examples show common ways to retrieve and safely output Forge Fields values in a WordPress theme or plugin.

## Basic Field Retrieval

Retrieve a field from the current Page or Post:

```php
$value = ff_get_field('field_name');
```

Retrieve a Global Field:

```php
$value = ff_get_global('field_name');
```

## Retrieve a Field From a Specific Page or Post

Pass a post ID as the second argument:

```php
$value = ff_get_field('field_name', 123);
```

## Output Text Safely

```php
<h1>
    <?php echo esc_html(ff_get_field('hero_title')); ?>
</h1>
```

## Global Fields

Retrieve a Global Field:

```php
$value = ff_get_global('company_name');
```

Global Fields can also be retrieved with `ff_get_field()`:

```php
$value = ff_get_field('company_name', 'global');
```

Output a Global text value safely:

```php
<h2>
    <?php echo esc_html(ff_get_global('company_name')); ?>
</h2>
```

## HTML Attributes

Use `esc_attr()` when inserting a Forge Fields value inside an HTML attribute:

```php
<meta
    name="description"
    content="<?php echo esc_attr(
        ff_get_global('home_meta_description')
    ); ?>"
>
```

## URLs

Use `esc_url()` when outputting a URL:

```php
<?php
$url = ff_get_field('website_url');
?>

<?php if ($url) : ?>
    <a href="<?php echo esc_url($url); ?>">
        Visit website
    </a>
<?php endif; ?>
```

## WYSIWYG Fields

For WordPress-safe HTML:

```php
<?php echo wp_kses_post(ff_get_field('content')); ?>
```

If the content should behave like normal WordPress post content:

```php
<?php
$content = ff_get_field('content');

if ($content) {
    echo apply_filters('the_content', $content);
}
?>
```

## Image Fields

Forge Fields stores WordPress attachment IDs for Image fields.

Output an image using WordPress image functions:

```php
<?php
$image_id = absint(ff_get_field('hero_image'));

if ($image_id) {
    echo wp_get_attachment_image(
        $image_id,
        'large',
        false,
        [
            'alt' => get_post_meta(
                $image_id,
                '_wp_attachment_image_alt',
                true
            ),
        ]
    );
}
?>
```

Get the image URL directly:

```php
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
```

## File Fields

Forge Fields stores WordPress attachment IDs for File fields.

Open a selected file in a new tab:

```php
<?php
$file_id = absint(ff_get_field('file'));

if ($file_id) {
    $file_url   = wp_get_attachment_url($file_id);
    $file_title = get_the_title($file_id);

    if ($file_url) :
        ?>
        <a
            href="<?php echo esc_url($file_url); ?>"
            target="_blank"
            rel="noopener">
            <?php echo esc_html($file_title ?: 'Open file'); ?>
        </a>
        <?php
    endif;
}
?>
```

Provide a download link:

```php
<?php
$file_id = absint(ff_get_field('file'));

if ($file_id) {
    $file_url   = wp_get_attachment_url($file_id);
    $file_title = get_the_title($file_id);

    if ($file_url) :
        ?>
        <a
            href="<?php echo esc_url($file_url); ?>"
            download>
            <?php echo esc_html($file_title ?: 'Download file'); ?>
        </a>
        <?php
    endif;
}
?>
```

## True / False Fields

Use a True/False field directly in a conditional:

```php
<?php if (ff_get_field('show_banner')) : ?>
    <div class="banner">
        Banner content here
    </div>
<?php endif; ?>
```

Or convert the result into display text:

```php
<p>
    <?php echo ff_get_field('show_banner') ? 'Yes' : 'No'; ?>
</p>
```

## Choice Fields

Forge Fields can return both the saved value and the configured choices for choice-based fields.

### Select

```php
<?php
$selected = ff_get_field('select');
$choices  = ff_get_field_choices('select');
?>

<select name="forge-test-select">
    <option value="">
        Select one:
    </option>

    <?php foreach ($choices as $value => $label) : ?>
        <option
            value="<?php echo esc_attr($value); ?>"
            <?php selected($selected, $value); ?>>
            <?php echo esc_html($label); ?>
        </option>
    <?php endforeach; ?>
</select>
```

### Radio

```php
<?php
$selected = ff_get_field('radio');
$choices  = ff_get_field_choices('radio');
?>

<div class="forge-radio-group">
    <?php foreach ($choices as $value => $label) : ?>
        <label>
            <input
                type="radio"
                name="forge-test-radio"
                value="<?php echo esc_attr($value); ?>"
                <?php checked($selected, $value); ?>
            >

            <?php echo esc_html($label); ?>
        </label>
    <?php endforeach; ?>
</div>
```

### Button Group

```php
<?php
$selected = ff_get_field('button_group');
$choices  = ff_get_field_choices('button_group');
?>

<div class="forge-button-group">
    <?php foreach ($choices as $value => $label) : ?>
        <label>
            <input
                type="radio"
                name="forge-test-button-group"
                value="<?php echo esc_attr($value); ?>"
                <?php checked($selected, $value); ?>
            >

            <span>
                <?php echo esc_html($label); ?>
            </span>
        </label>
    <?php endforeach; ?>
</div>
```

## Duplicate Field Names

If the same field name exists in multiple Field Groups, provide the Forge Group Key as the third argument:

```php
$value = ff_get_field(
    'show_banner',
    null,
    'ff_group_example'
);
```

Example:

```php
<?php if (ff_get_field('show_banner', null, 'ff_group_example')) : ?>
    <div class="banner">
        Banner is enabled.
    </div>
<?php endif; ?>
```

## Escaping Values

Forge Fields returns stored values. Escape values when outputting them based on the context in which they are used.

- Text: `esc_html()`
- HTML attributes: `esc_attr()`
- URLs: `esc_url()`
- Allowed WordPress HTML: `wp_kses_post()`
- Image/File attachment IDs: `absint()`

## Notes

- Image and File fields return WordPress attachment IDs.
- True/False fields can be used directly in conditionals.
- Global Fields are separate from normal Page/Post Field Groups.
- Forge Group Keys can be used to resolve duplicate field-name ambiguity.

## Related Documentation

- [Field Types](field-types.md)
- [Import Export](import-export.md)
