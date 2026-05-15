=== Atomic Newsletter For Elementor ===
Contributors: mongsinghai
Donate link:
Tags: newsletter, elementor, email, subscribers, lead-generation
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Captures subscriber emails via Elementor Atomic Forms. Manage, search, export to CSV. Works free with Pro Elements — no paid license needed.

== Description ==

**Atomic Newsletter For Elementor** automatically collects email addresses submitted through Elementor Atomic Forms and stores them in your WordPress database. No third-party email service required — all subscriber data stays on your own server.

This plugin works with both **Elementor Pro** and the free **[Pro Elements](https://proelements.org/)** plugin. Pro Elements is a free, open-source alternative that provides Elementor Pro features — including Atomic Forms — at no cost. You do not need to purchase Elementor Pro to use this plugin.

= Features =
* Automatically capture emails from Elementor Atomic Forms
* Compatible with Elementor Pro and the free Pro Elements plugin
* View all subscribers in a clean WordPress admin dashboard
* Paginated subscriber table — works smoothly with any list size
* Search and filter subscribers by email
* Delete individual, selected, or all filtered subscribers
* Duplicate email prevention — the same email is never stored twice
* Export subscriber list to CSV file
* WordPress Multisite compatible — each site gets its own subscriber table
* Zero external dependencies — no API keys, no third-party services

= Compatibility =

This plugin is compatible with:

* **Elementor Pro** (version 4.0+ with Atomic Form support)
* **[Pro Elements](https://proelements.org/)** — a free alternative to Elementor Pro that includes Atomic Forms. Install Pro Elements to use all features of this plugin at no cost.

= Requirements =
* WordPress 6.0 or higher
* PHP 7.4 or higher
* Elementor (free) — available from the WordPress plugin directory
* Elementor Pro 4.0+ **OR** Pro Elements (free) — for Atomic Form support

== Installation ==

1. Install and activate the free **Elementor** plugin from the WordPress plugin directory.
2. Install either **Elementor Pro** or the free **[Pro Elements](https://proelements.org/)** plugin.
3. Upload the `atomic-newsletter-for-elementor` folder to the `/wp-content/plugins/` directory, or install directly through the WordPress plugin screen.
4. Activate the plugin through the **Plugins** screen in WordPress.
5. Go to **Subscribers** in the WordPress admin menu to view and export collected emails.

== Frequently Asked Questions ==

= Do I need to buy Elementor Pro? =
No. This plugin works with the free **[Pro Elements](https://proelements.org/)** plugin, which provides Elementor Pro features — including Atomic Forms — at no cost. Install Elementor (free) and Pro Elements (free), and this plugin will work fully.

= What is Pro Elements? =
Pro Elements (https://proelements.org/) is a free, open-source plugin that unlocks Elementor Pro features without a paid license. It is a community-maintained project and is not affiliated with Elementor or this plugin.

= Does this work with the old Elementor Form widget? =
This plugin is specifically built for the **Elementor Atomic Form** (available in Elementor Pro 4.0+ or Pro Elements). For the classic Elementor Form widget, other plugins are available.

= Where is subscriber data stored? =
All data is stored locally in your WordPress database. Nothing is sent to external servers.

= How do I export my subscribers? =
Go to the **Subscribers** page in your WordPress admin and click **Export CSV**. This downloads your full subscriber list as a CSV file compatible with Excel, Google Sheets, and any email marketing platform.

= Does it prevent duplicate emails? =
Yes. The database enforces a unique constraint on the email column. If the same email is submitted more than once it is silently ignored — no duplicates are ever stored.

= Is this GDPR compliant? =
The plugin only stores email addresses that users voluntarily submit through your forms. You are responsible for adding appropriate consent language to your forms and maintaining a privacy policy on your site.

= Does this work on WordPress Multisite? =
Yes. Each site in a multisite network gets its own separate subscriber table.

= Is a license required? =
No. All plugin features are completely free with no license key required.

= What happens to my data when I delete the plugin? =
The plugin includes an uninstall routine that removes the subscriber database table and all plugin options when you delete the plugin from the WordPress admin.

== Screenshots ==

1. Subscriber list page showing collected emails with search, pagination, and delete options.
2. CSV export — download your full subscriber list with one click.

== Changelog ==

= 1.0.3 =
* Compatibility: added full support for the free Pro Elements plugin (https://proelements.org/).
* Performance: subscriber table now paginated (20 per page) — no full-table loads.
* Performance: CSV export streams in 1,000-row chunks — safe for any list size.
* Performance: bulk delete processed in 500-ID batches to avoid MySQL packet limits.
* Performance: DB version check replaces SHOW TABLES query on every page load.
* Architecture: all plugin components deferred consistently to plugins_loaded hook.
* Architecture: unified email extraction pipeline replaces four duplicate methods.
* Compatibility: full WordPress Multisite support via per-site table prefix.

= 1.0.2 =
* Removed Google Sheets export feature.
* Removed all vendor dependencies — plugin is now dependency-free.
* Fixed: admin Export button now downloads CSV directly with one click.

= 1.0.1 =
* Security: removed publicly accessible debug panel.
* Security: added nonce verification to all admin form actions.
* Security: AJAX handlers now verify Elementor's own nonce before processing.
* Compliance: added index.php to all directories to prevent directory listing.
* Compliance: added uninstall.php to clean up table and options on plugin deletion.
* Compliance: added UNIQUE constraint on email column.
* Code: simplified email validation and fixed duplicate email prevention.

= 1.0.0 =
* Initial release.
* Capture emails from Elementor Atomic Forms.
* Subscriber management dashboard.
* CSV export feature.

== Upgrade Notice ==

= 1.0.3 =
Adds free Pro Elements compatibility, major performance improvements, and Multisite support. Recommended for all users.
