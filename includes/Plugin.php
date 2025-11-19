<?php
/**
 * Main plugin bootstrap.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost;

use MenuGhost\Admin\Assets\AdminAssets;
use MenuGhost\Admin\MenuItem;
use MenuGhost\Admin\AdvancedController;
use MenuGhost\Admin\SettingsController;
use MenuGhost\Admin\SearchController;
use MenuGhost\Frontend\MenuVisibility;
use function add_action;
use function load_plugin_textdomain;
use function plugin_basename;
use function dirname;

/**
 * Primary plugin bootstrap class.
 */
class Plugin {
	/**
	 * Shared instance of the plugin bootstrap.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Return an instance of the plugin bootstrap.
	 *
	 * @return Plugin class instance
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initialize plugin.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		AdminAssets::register();
		SearchController::register();

		if ( ! is_admin() ) {
			MenuVisibility::instance();

			return;
		}

		MenuItem::instance();
		AdvancedController::instance();
		SettingsController::instance();
	}

	/**
	 * Load the plugin textdomain so bundled translations are available immediately.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'menu-ghost', false, dirname( plugin_basename( MNGH_PLUGIN_FILE ) ) . '/languages' );
	}
}
