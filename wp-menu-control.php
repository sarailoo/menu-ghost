<?php

declare(strict_types=1);

/**
 * Plugin Name: WP Menu Control
 * Plugin URI: https://github.com/sarailoo/wp-menu-control
 * Description: WP Menu Control lets you conditionally display each menu item based on factors like user role, device, date, pages, and more.
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Version: 1.0.0
 * Author: Reza Sarailoo
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-menu-control
 */

namespace WPMenuControl;

if (! class_exists(WPMenuControl::class) && is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Plugin version.
if (! defined('WP_MENU_CONTROL_VERSION')) {
    define('WP_MENU_CONTROL_VERSION', '1.0.0');
}

// Define plugin main file path.
if (! defined('WP_MENU_CONTROL_PLUGIN_FILE')) {
    define('WP_MENU_CONTROL_PLUGIN_FILE', __FILE__);
}

// Plugin directory.
if (! defined('WP_MENU_CONTROL_DIR')) {
    define('WP_MENU_CONTROL_DIR', plugin_dir_path(__FILE__));
}

// Plugin url.
if (! defined('WP_MENU_CONTROL_URL')) {
    define('WP_MENU_CONTROL_URL', plugin_dir_url(__FILE__));
}

// Assets url.
if (! defined('WP_MENU_CONTROL_ASSETS_URL')) {
    define('WP_MENU_CONTROL_ASSETS_URL', WP_MENU_CONTROL_URL . '/assets');
}

class_exists(WPMenuControl::class) && WPMenuControl::instance();
?>
