# Forge Fields Import and Export

Forge Fields provides JSON import and export tools for moving Field Group definitions between WordPress installations.

These tools are intended for Field Group configuration rather than transferring the content stored inside individual fields.

## Export Field Groups

Open:

**Forge Fields → Import / Export**

Use the export tools to generate a Forge Fields JSON file containing Field Group definitions.

An export can be useful for:

- Moving Field Groups from development to production
- Transferring configuration between websites
- Maintaining reusable Field Group definitions
- Backing up Field Group configuration
- Sharing configuration between Forge Fields installations

## What an Export Contains

Forge Fields exports Field Group definitions and their configuration.

Depending on the group, this can include information such as:

- Field Group title
- Location
- Location target
- Fields
- Field types
- Field names
- Default values
- Required settings
- Character limits
- Prepend and append settings
- Configured choices

Runtime-only metadata that should not be transferred between installations is excluded from the export.

## What an Export Does Not Represent

Field Group exports are not intended to be a complete content migration system.

Values already stored on:

- Pages
- Posts
- Global Fields

are separate from the Field Group definitions themselves.

Use normal WordPress migration or database tools when moving complete website content.

## Import Field Groups

Open:

**Forge Fields → Import / Export**

Under **Import Field Groups**:

1. Choose a Forge Fields JSON export.
2. Select how existing Field Groups should be handled.
3. Click **Import JSON**.

Forge Fields validates imported data before saving it.

## Existing Field Groups

The import screen allows you to choose how existing Field Groups should be handled.

For example, the available behavior may include skipping groups that already exist.

Review the selected import behavior before importing into an existing website.

## Validation

Forge Fields validates imported Field Group data to reduce the risk of malformed configuration being saved.

Validation includes checks around:

- Expected import structure
- Supported Field Group data
- Valid field definitions
- Supported field types
- Field options
- Choice values

Newer Forge Fields exports may contain additional validation information used to verify that the file was produced by Forge Fields.

Older supported export formats can remain compatible where possible.

## Choice Fields

Choice-field definitions are validated during import.

Supported choice formats include:

```text
value : Label
```

or:

```text
value
```

Choice values should be unique.

## Before Importing

Before importing configuration into an important or production WordPress website:

1. Back up the website.
2. Review the JSON file being imported.
3. Confirm the desired existing-group behavior.
4. Import the Field Groups.
5. Review the imported groups before using them in production.

## Moving Configuration Between Environments

A common development workflow is:

1. Build and test Field Groups locally or on staging.
2. Export the Field Groups as JSON.
3. Import the JSON into the destination WordPress installation.
4. Verify the imported Field Groups.
5. Test the relevant Page/Post or Global Field interfaces.

## Security

Only import Forge Fields JSON files from sources you trust.

Forge Fields performs validation and sanitization during import, but configuration files should still be treated as application data rather than arbitrary files.

## Related Documentation

- [Developer Usage](usage.md)
- [Field Types](field-types.md)
