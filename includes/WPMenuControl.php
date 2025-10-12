<?php
/**
 * Main plugin bootstrap.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl;

use WPMenuControl\Admin\Assets\AdminAssets;
use WPMenuControl\Admin\MenuItem;
use WPMenuControl\Admin\AdvancedController;
use WPMenuControl\Admin\SettingsController;
use WPMenuControl\Admin\SearchController;
use WPMenuControl\Frontend\MenuVisibility;

/**
 * WPMenuControl class.
 */
class WPMenuControl {
	/**
	 * Shared instance of the plugin bootstrap.
	 *
	 * @var WPMenuControl|null
	 */
	private static ?WPMenuControl $instance = null;

	/**
	 * Return an instance of the WPMenuControl Class.
	 *
	 * @return WPMenuControl class instance
	 */
	public static function instance(): WPMenuControl {
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
}
