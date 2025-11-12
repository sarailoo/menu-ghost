<?php
/**
 * Plugin Name: Menu Ghost
 * Plugin URI: https://github.com/sarailoo/menu-ghost
 * Description: Target menu items by role, device, schedule, and campaign rules using a fast, native conditions interface inside the menu editor.
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Version: 1.0.1
 * Author: Reza Sarailoo
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: menu-ghost
 *
 * @package MenuGhost
 */

declare(strict_types=1);

/**
 * Bootstrap for the Menu Ghost plugin.
 */

namespace MenuGhost;

if ( ! class_exists( Plugin::class ) ) {
	$mngh_autoloader = __DIR__ . '/vendor/autoload.php';

	if ( is_readable( $mngh_autoloader ) ) {
		require_once $mngh_autoloader;
	}
}

// Plugin version.
if ( ! defined( 'MNGH_VERSION' ) ) {
	define( 'MNGH_VERSION', '1.0.0' );
}

// Plugin directory.
if ( ! defined( 'MNGH_DIR' ) ) {
	define( 'MNGH_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin url.
if ( ! defined( 'MNGH_URL' ) ) {
	define( 'MNGH_URL', plugin_dir_url( __FILE__ ) );
}

// Build directory.
if ( ! defined( 'MNGH_BUILD_DIR' ) ) {
	define( 'MNGH_BUILD_DIR', MNGH_DIR . 'build/' );
}

// Build url.
if ( ! defined( 'MNGH_BUILD_URL' ) ) {
	define( 'MNGH_BUILD_URL', MNGH_URL . 'build/' );
}

class_exists( Plugin::class ) && Plugin::instance();
