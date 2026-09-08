# Forge Fields Field Types

Forge Fields provides a collection of field types for building custom data interfaces in WordPress.

Available options vary depending on the selected field type.

## Basic Fields

### Text

A single-line text field.

Common uses:

- Headings
- Names
- Short labels
- Identifiers
- Short text content

Available options may include:

- Default Value
- Character Limit
- Required
- Prepend
- Append

### Textarea

A multi-line plain text field.

Common uses:

- Descriptions
- Notes
- Summaries
- Longer plain-text content

Available options may include:

- Default Value
- Character Limit
- Required

### Number

A numeric input field.

Common uses:

- Quantities
- Prices
- Measurements
- Numeric settings

Available options may include:

- Default Value
- Required
- Prepend
- Append

### Email

An email-address input.

Common uses:

- Contact addresses
- Support addresses
- Account-related email values

Available options may include:

- Default Value
- Character Limit
- Required
- Prepend
- Append

### URL

A URL input field.

Common uses:

- External links
- Website addresses
- CTA destinations

Available options may include:

- Default Value
- Character Limit
- Required

### Range

A slider paired with a numeric value.

Common uses:

- Ratings
- Percentages
- Configurable numeric ranges

The slider and numeric control remain synchronized.

### Password

A password-style input field.

Common uses:

- Values that should not be visibly exposed while editing

The admin control includes a visibility toggle.

Available options may include:

- Default Value
- Character Limit
- Required
- Prepend
- Append

## Content Fields

### Image

Uses the WordPress Media Library to select an image.

Forge Fields stores the WordPress attachment ID rather than the image URL.

Example:

```php
$image_id = ff_get_field('hero_image');
```

Use WordPress attachment functions such as:

```php
wp_get_attachment_image();
wp_get_attachment_image_url();
```

to render the selected image.

### File

Uses the WordPress Media Library to select a file.

Forge Fields stores the WordPress attachment ID.

Example:

```php
$file_id = ff_get_field('document');
```

Use:

```php
wp_get_attachment_url();
```

to retrieve the file URL.

### WYSIWYG Editor

Uses the WordPress visual/text editor interface for formatted content.

Suitable for:

- Formatted text
- Headings
- Links
- Lists
- Other WordPress-supported HTML content

Use `wp_kses_post()` when outputting allowed WordPress HTML, or apply WordPress content filters when appropriate.

## Choice Fields

### Select

A standard dropdown selection field.

Choices can be defined as:

```text
value : Label
```

or:

```text
value
```

Example:

```text
red : Red
green : Green
blue : Blue
```

Retrieve the saved value:

```php
$selected = ff_get_field('color');
```

Retrieve the configured choices:

```php
$choices = ff_get_field_choices('color');
```

### Checkbox

Allows one or more configured choices to be selected.

Choices use the same configuration format as other choice fields.

### Radio

Allows one configured choice to be selected.

Choices can be retrieved with:

```php
$choices = ff_get_field_choices('field_name');
```

### Button Group

Provides choice-field behavior using a button-style interface.

The saved selection can be retrieved the same way as other choice fields.

### True / False

Stores a boolean-style value.

Example:

```php
if (ff_get_field('show_banner')) {
    // Enabled.
}
```

True/False fields do not use the normal Required option.

## Layout Fields

### Tab

Creates a visual tab within a Field Group.

Tab fields are structural and do not store normal field values.

Because they are used for layout:

- They do not use a normal field name
- They do not support Required
- Normal value options do not apply

Tabs can be used to organize larger Field Groups into clearer sections.

## Common Field Options

Depending on the field type, Forge Fields may provide the following options.

### Default Value

Defines the initial value used by the field when appropriate.

### Required

Marks a supported field as required.

True/False and Tab fields do not use the Required option.

### Character Limit

Limits the number of characters accepted by supported text-based fields.

### Prepend

Displays text before the field control.

Common examples:

```text
$
£
https://
```

### Append

Displays text after the field control.

Common examples:

```text
%
px
kg
```

### Choices

Defines available values for:

- Select
- Checkbox
- Radio
- Button Group

Supported formats:

```text
value : Label
```

or:

```text
value
```

Each choice value should be unique.

## Field Names

Field names are used when retrieving values in PHP.

Example:

```php
ff_get_field('hero_title');
```

Forge Fields can automatically generate a field name from a label when the field name is left blank.

Field names should be unique within the same Field Group.

If the same field name exists in multiple Field Groups, a Forge Group Key can be passed to `ff_get_field()` to identify the intended group.

For developer examples, see [Developer Usage](usage.md).
