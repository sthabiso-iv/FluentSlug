# FluentSlug

A WordPress plugin that lets you assign clean, custom URL slugs to [FluentForms](https://wordpress.org/plugins/fluentform/) conversational forms — replacing the default parameter-based URLs.

**Requires:** WordPress 6.0+, PHP 8.0+, FluentForms (any version with conversational forms)

---

## Features

- Assign a custom slug to any conversational form (e.g. `/contact-us/` instead of `/?ff_conv=...`)
- Full-page, theme-independent render of the conversational form at the custom URL
- Slug collision detection — two forms cannot share the same slug
- Reserved WordPress endpoint protection
- Themes can override the form template
- Clean uninstall — removes all plugin data on deletion

---

## Installation

1. Download or clone this repository.
2. Place the `FluentSlug` folder in your `wp-content/plugins/` directory.
3. Activate **FluentSlug** via *Plugins → Installed Plugins*.
4. Go to *FluentForms → Custom Slugs* to assign slugs to your forms.

> **Note:** FluentForms must be installed and active. If it is not, an admin notice will appear and the plugin will do nothing until the dependency is met.

---

## Usage

1. Navigate to **FluentForms → Custom Slugs** in the WordPress admin.
2. You will see a table of all your conversational forms.
3. Enter a slug in the text field next to the form you want (e.g. `contact-us`).
4. Click **Save**.
5. Visit `https://yoursite.com/contact-us/` — the conversational form renders full-page.

### Slug rules

- Lowercase letters, numbers, and hyphens only
- Must start and end with a letter or number
- Must be unique across all forms
- Cannot be a reserved WordPress endpoint (`feed`, `wp-admin`, `wp-json`, etc.)

### Troubleshooting

If a saved slug returns a 404, go to **Settings → Permalinks** and click **Save Changes** to manually flush WordPress rewrite rules.

---

## Template Override

Themes can override the full-page form template by placing a file at:

```
{your-theme}/fluent-slug/conversational-form.php
```

The template receives `$args['form_id']` (int) as its only variable.

---

## File Structure

```
FluentSlug/
├── fluent-slug.php                      # Plugin bootstrap, constants, dependency check
├── includes/
│   ├── class-fluent-slug-core.php       # Rewrite rules, query vars, template redirect
│   └── class-fluent-slug-admin.php      # Admin page, save/delete handlers
├── templates/
│   └── conversational-form.php          # Full-page form template (theme-overridable)
├── admin/css/
│   └── admin.css                        # Admin styles
└── uninstall.php                        # Cleanup on plugin deletion
```

---

## License

GPL-2.0-or-later — see [LICENSE](LICENSE).

---

## Author

[Mthokozisi Dhlamini](https://mthokozisi.link)
