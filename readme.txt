=== WP Menu Control ===
Contributors: sarailoo
Tags: menu visibility, conditional menus, user roles, navigation, personalization
Requires at least: 6.6
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Target menu items by role, device, schedule, and campaign rules using a fast, native conditions interface inside the menu editor.

== Description ==

Design frictionless navigation experiences and boost conversions by only showing relevant menu items to each visitor. WP Menu Control integrates with the built-in WordPress menu editor and adds a modern conditions interface that's fast, intuitive, and keyboard friendly.

= Fine-grained menu targeting =

* Create unlimited include or exclude rules per menu item.
* Target by user role, login status, browser language, or device type.
* Schedule menus for specific days, date ranges, or time windows.
* Display items on singular, archive, author, or custom taxonomy screens.
* React to UTM campaign parameters or individual query-string values.

The interface is built with React and uses WordPress components so it feels native. All data is stored in standard post meta and respects the WordPress coding standards.

== Installation ==

1. Upload the `wp-menu-control` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress Plugins screen.
2. Activate the plugin through the “Plugins” screen in WordPress.
3. Navigate to **Appearance → Menus**, select a menu item, and click **Display Conditions** to configure visibility rules.

== Frequently Asked Questions ==

= Does this plugin slow down my site? =
No. Visibility checks run only when menus are rendered and use cached WordPress APIs. There are no external requests or front-end assets.

= Will it work with my theme or page builder? =
Yes. WP Menu Control extends the default WordPress menus, so any theme or builder that uses `wp_nav_menu()` automatically benefits from the rules you configure.

= Where are the translations stored? =
If you upload the plugin to WordPress.org, translations are managed by GlotPress. Any custom translations can be placed in `wp-content/languages/plugins/wp-menu-control-*.mo`.

== Screenshots ==

1. Modal with page-based visibility rules.
2. Advanced tab showcasing audience and campaign conditions.

== Changelog ==

= 1.0.0 =
* Initial public release with menu item display rules, advanced campaign targeting, and REST-powered async selectors.

== Upgrade Notice ==

= 1.0.0 =
First release. Configure your menus after activation to start controlling visibility.
