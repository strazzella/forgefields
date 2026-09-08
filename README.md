# Forge Fields

Forge Fields is a lightweight custom fields plugin for WordPress.

Create reusable Field Groups for Pages and Posts, define Global Fields, and retrieve saved values from your theme using simple developer-friendly functions.

> Forge Fields is currently in beta.

## Features

- Field Groups for Pages and Posts
- Global Fields
- Specific Page/Post targeting
- Text
- Textarea
- Number
- Email
- URL
- Range
- Password
- Image
- File
- WYSIWYG Editor
- Select
- Checkbox
- Radio
- Button Group
- True/False
- Tab layout fields
- Required fields
- Default values
- Character limits
- Prepend and append values
- Import and export
- Forge Group Keys for resolving duplicate field names

## Requirements

- WordPress 6.8.2 or newer
- PHP 8.0 or newer

## Installation

1. Download Forge Fields.
2. Upload the `forge-fields` folder to `/wp-content/plugins/`.
3. Activate Forge Fields through the WordPress Plugins screen.
4. Open **Forge Fields** in the WordPress admin menu.
5. Create your first Field Group.

## Developer Usage

Retrieve a field from the current Page or Post:

```php
$value = ff_get_field('field_name');
