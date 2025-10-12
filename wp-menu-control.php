<?php
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
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

/**
 * Bootstrap for the WP Menu Control plugin.
 *
 * @package WPMenuControl
 */

namespace WPMenuControl;

if ( ! class_exists( WPMenuControl::class ) && is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Plugin version.
if ( ! defined( 'WP_MENU_CONTROL_VERSION' ) ) {
	define( 'WP_MENU_CONTROL_VERSION', '1.0.0' );
}

// Plugin directory.
if ( ! defined( 'WP_MENU_CONTROL_DIR' ) ) {
	define( 'WP_MENU_CONTROL_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin url.
if ( ! defined( 'WP_MENU_CONTROL_URL' ) ) {
	define( 'WP_MENU_CONTROL_URL', plugin_dir_url( __FILE__ ) );
}

// Build directory.
if ( ! defined( 'WP_MENU_CONTROL_BUILD_DIR' ) ) {
	define( 'WP_MENU_CONTROL_BUILD_DIR', WP_MENU_CONTROL_DIR . 'build/' );
}

// Build url.
if ( ! defined( 'WP_MENU_CONTROL_BUILD_URL' ) ) {
	define( 'WP_MENU_CONTROL_BUILD_URL', WP_MENU_CONTROL_URL . 'build/' );
}

class_exists( WPMenuControl::class ) && WPMenuControl::instance();
