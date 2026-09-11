=== Forge Fields ===
Contributors: strazzella
Tags: custom fields, metadata, field groups, global fields, developer tools
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 0.2.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create and manage custom fields, field groups, global fields, media fields, and developer-friendly template functions for WordPress.

== Description ==

Forge Fields is a lightweight custom-fields framework for WordPress focused on straightforward field groups, global fields, group-scoped metadata, and developer-friendly template APIs without the broader content-modeling feature set of larger custom-field suites.

Forge Fields lets you create custom field groups, assign them to posts and pages, define global fields, work with media and choice fields, and retrieve values in templates using simple helper functions.

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

== External services ==

Forge Fields includes an optional feedback form in the WordPress admin area.

When an administrator submits the feedback form, the plugin sends the submitted feedback to the Forge Fields feedback service at:

https://forge-fields-feedback.useforgedev.workers.dev/

The service is hosted using Cloudflare Workers.

The following data is sent when feedback is submitted:

* Feedback type
* Feedback message
* The current WordPress user's email address

For bug reports, the following additional diagnostic information is also sent:

* Forge Fields plugin version
* WordPress version
* PHP version
* Site URL

No feedback data is sent automatically. Data is only transmitted when an administrator explicitly submits the feedback form.

The feedback service uses Postmark to deliver the submitted feedback by email.

Cloudflare:
Terms: https://www.cloudflare.com/website-terms/
Privacy: https://www.cloudflare.com/privacypolicy/

Postmark:
Terms: https://postmarkapp.com/terms-of-service
Privacy: https://postmarkapp.com/privacy-policy

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

= 0.2.7 =
* Initial public beta release.

== Upgrade Notice ==

= 0.2.7 =
Initial public beta release.