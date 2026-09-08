=== Forge Fields ===
Contributors: strazzella
Tags: custom fields, metadata, field groups, global fields, developer tools
Requires at least: 6.8.2
Tested up to: 6.8.2
Requires PHP: 8.0
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create and manage custom fields, field groups, global fields, media fields, and developer-friendly template functions for WordPress.

== Description ==

Forge Fields is a lightweight custom fields plugin for WordPress.

Create reusable Field Groups for Pages and Posts, define Global Fields, and retrieve saved values in your theme or plugin using simple developer-friendly functions.

Features include:

* Field Groups for Pages and Posts
* Global Fields
* Specific Page/Post targeting
* Text
* Textarea
* Number
* Email
* URL
* Range
* Password
* Image
* File
* WYSIWYG Editor
* Select
* Checkbox
* Radio
* Button Group
* True/False
* Tab layout fields
* Required fields
* Default values
* Character limits
* Prepend and append values
* Import and export
* Forge Group Keys for resolving duplicate field names

Developer documentation is available on GitHub:
https://github.com/strazzella/forgefields

== Installation ==

1. Upload the `forge-fields` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress Plugins screen.
2. Activate Forge Fields through the WordPress Plugins screen.
3. Open Forge Fields from the WordPress admin menu.
4. Create your first Field Group.
5. Add fields and assign the group to Pages, Posts, or Global Fields as needed.

== Frequently Asked Questions ==

= Where are Forge Fields values stored? =

Page and Post field values are stored in WordPress post meta.

Global Fields are stored separately using WordPress options.

= How do I retrieve a field in my theme? =

Use:

`ff_get_field('field_name')`

For Global Fields:

`ff_get_global('field_name')`

More developer examples are available in the GitHub documentation.

= What happens if the same field name exists in multiple Field Groups? =

Forge Fields can use the Forge Group Key to identify the intended Field Group.

= Are Image and File fields stored as URLs? =

No. Forge Fields stores WordPress attachment IDs for Image and File fields.

= Does Forge Fields support importing and exporting Field Groups? =

Yes. Field Group definitions can be exported and imported as JSON.

Saved Page, Post, and Global Field values are not included in Field Group exports.

== Screenshots ==

1. Field Groups overview.
2. Add or edit a Field Group.
3. Global Fields.
4. Settings and Import/Export tools.

== Changelog ==

= 0.2.0 =
* Initial public beta release.

== Upgrade Notice ==

= 0.2.0 =
Initial public beta release.