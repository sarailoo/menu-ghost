=== Menu Ghost ===
Contributors: sarailoo
Tags: menu visibility, conditional menus, user roles, navigation, personalization
Requires at least: 6.6
Tested up to: 6.8
Stable tag: 1.0.1
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Target menu items by role, device, schedule, and campaign rules using a fast, native conditions interface inside the menu editor.

== Description ==

Menu Ghost lets you conditionally display each menu item based on factors like user role, device, date, pages, and more.

Design frictionless navigation experiences and boost conversions by only showing relevant menu items to each visitor. Menu Ghost integrates with the native WordPress menu editor and adds a modern conditions interface that feels like it belongs in core. Everything happens inside **Appearance → Menus** no shortcodes or template edits required.

== What does Menu Ghost do? ==

Menu Ghost gives you total control over which navigation links appear and when. Build unlimited rule sets per menu item, combining audience, page, schedule, and campaign conditions. Hide seasonal promotions after a sale ends, display member-only dashboards, or show localized links based on browser language.

== Key features ==

* **Audience targeting** – Show or hide menu items based on user role, login status, browser language, or detected device (desktop, tablet, mobile).
* **Page & post rules** – Limit links to specific pages, post types, taxonomies, archives, author archives, search results, or 404 pages.
* **Scheduling controls** – Activate items on selected weekdays, within date ranges, or during daily time windows.
* **Campaign awareness** – React to query-string values, UTM parameters, or custom marketing links for landing pages.
* **No-code workflow** – React-powered UI inside the existing menu screen. Toggle rules with familiar WordPress components.
* **Unlimited conditions** – Stack include/exclude rules to create precise logic for every menu item.
* **Performance friendly** – All logic runs server-side with cached WordPress APIs. No front-end scripts added to your theme.

== Popular use cases ==

* Display a “Members Area” link only to logged-in customers on desktop.
* Promote a holiday sale on every page until a specific date/time is reached.
* Replace “Book a call” with “Download brochure” on mobile visitors to boost conversions.
* Show “Return to checkout” when users view product archives, but hide it elsewhere.
* Localize navigation labels and URLs based on browser language or UTM campaigns.

== Why choose Menu Ghost? ==

1. **Native experience** – Built with the WordPress components library for a seamless admin UI.
2. **Clean code & extensibility** – PHP logic follows core standards and stores data in post meta.
3. **Marketing ready** – Understand traffic sources and tailor menus without page builders.
4. **SEO aware** – Hide links for humans while keeping friendly markup for search engines (links still exist, only visibility changes).

The interface is built with React and uses WordPress components so it remains responsive, accessible, and familiar. All rule data is stored in post meta to keep compatibility with exports, migrations, and backups.

== Rule reference ==


= Pages tab =

* **Include / Exclude** – Decide whether a matching condition should show or hide the menu item.
* **Entire Site** – Create a catch-all rule that always fires. Place it below granular rules when you need fallback behavior.
* **Archive scopes** – Target date archives, author archives, taxonomy listings, WooCommerce product archives, search results, or even the 404 template.
* **Singular scopes** – Limit links to specific posts, pages, custom post types, parent/child relationships, or taxonomy terms.
* **Add Condition** – Stack unlimited rules. Menu Ghost evaluates them from top to bottom, so keep broad rules toward the bottom of the list.

= Advanced tab =

* **User Role** – Match any WordPress role, including custom roles added by eCommerce or membership plugins.
* **User Device** – Detect desktop, tablet, or mobile visitors to tailor calls to action per device.
* **Login Status** – Serve different menu links to logged-in versus logged-out visitors.
* **Signup Date** – Target members who registered before or after a specific date—ideal for onboarding flows.
* **Browser Language** – Show localized links when the visitor’s browser shares a preferred language.
* **Days of the Week** – Toggle links on specific weekdays (e.g., "Weekend brunch menu").
* **Within Date Range** – Schedule seasonal links to appear between two calendar dates without manual edits.
* **Within Time Window** – Display links only during a daily time slice, such as support hours or flash sales.
* **URL Query Parameter** – Require a query-string key/value (like `ref=partner`) before the menu item appears.
* **UTM Campaign / Content / Medium / Source / Term** – React to marketing URLs so visitors see campaign-specific navigation when arriving from ads or emails.


== Installation ==

1. Upload the `menu-ghost` folder to `/wp-content/plugins/` or install via the Plugins screen.
2. Activate Menu Ghost through **Plugins → Installed Plugins**.
3. Go to **Appearance → Menus**, open a menu item, and click **Display Conditions** to start adding rules.


== Frequently Asked Questions ==

= Does this plugin slow down my site? =
No. Menu Ghost only evaluates rules when `wp_nav_menu()` renders, using cached WordPress APIs. Nothing is injected on the front end, and there are no extra tables or cron jobs.

= Will it work with my theme or page builder? =
Yes. Any theme or builder that leverages the core menu system automatically benefits from Menu Ghost. No template edits, hooks, or shortcodes required.

= Can I show or hide links for logged-in customers only? =
Absolutely. Enable “Login Status” rules to create separate navigation items for logged-in and logged-out visitors.

= Can I schedule menu links for seasonal campaigns? =
Use the date range or time window rules to automatically activate/deactivate links without touching menus again.

= How do I localize menus for different languages? =
Use the browser language condition or query-string parameters (e.g., `?lang=fr`) to tailor navigation per locale.

= Does Menu Ghost work with WooCommerce or membership plugins? =
Yes. As long as the menu item exists in WordPress, you can apply Menu Ghost rules to it—ideal for WooCommerce account links, LMS dashboards, community forums, and more.

= Where are the translations stored? =
If you upload the plugin to WordPress.org, translations are managed by GlotPress. Custom translations can be placed in `wp-content/languages/plugins/menu-ghost-*.mo`.

= Can I migrate settings between sites? =
All data is stored in post meta, so it travels with standard WordPress export/import tools, backup plugins, or site migration services.


== Development ==

The source code for Menu Ghost lives in the public repository at https://github.com/sarailoo/menu-ghost.

== Screenshots ==

1. Menu item meta box with the Display Conditions button inside Appearance ▸ Menus.
2. Pages tab focused on a single "Exclude date archive" rule for quick toggles.
3. Pages tab showing layered include/exclude rules for archives, authors, and WooCommerce products.
4. Advanced Rules User section overview: User Role, User Device, Login Status, Signup Date and Browser Language.
5. Advanced Rules Date & Time section overview: Days of the Week, Within Date Range and Within Time Window.
6. URL & Campaign summary list highlighting query string and UTM parameter switches.
7. Expanded User panel with role/device/login toggles enabled for desktop, tablet, and mobile.
8. Date & Time editor displaying weekday picker, date range, and time window controls.
9. URL & Campaign detail editor configuring a query parameter and UTM campaign value.

== Changelog ==

= 1.0.0 =
* Initial public release with menu item display rules, advanced campaign targeting, and REST-powered async selectors.

== Upgrade Notice ==

= 1.0.0 =
First release. Configure your menus after activation to start controlling visibility.
