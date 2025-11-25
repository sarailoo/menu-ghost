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
use MenuGhost\Admin\NavigationController;
use MenuGhost\Frontend\MenuVisibility;
use function add_action;
use function add_filter;
use function determine_locale;
use function load_textdomain;
use function plugin_basename;
use function dirname;
use function is_admin;
use function md5;

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
		add_filter( 'load_script_translation_file', array( $this, 'ensure_script_translations' ), 10, 3 );

		AdminAssets::register();
		SearchController::register();
		NavigationController::register();

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
		$locale = determine_locale();
		$mofile = sprintf( '%1$slanguages/menu-ghost-%2$s.mo', MNGH_DIR, $locale );

		if ( is_readable( $mofile ) ) {
			load_textdomain( 'menu-ghost', $mofile );
		}
	}

	/**
	 * Ensure script translations are loaded when WordPress cannot locate the JSON file.
	 *
	 * This is primarily needed for bundled translations such as fa_IR that live inside
	 * the plugin instead of the global languages directory.
	 *
	 * @since 1.0.2
	 *
	 * @param string $file   Path to the translation file WordPress located.
	 * @param string $handle Script handle requesting translations.
	 * @param string $domain Translation text domain.
	 *
	 * @return string
	 */
	public function ensure_script_translations( string $file, string $handle, string $domain ): string {
		if ( $file || 'mngh-admin-script' !== $handle || 'menu-ghost' !== $domain ) {
			return $file;
		}

		$locale    = determine_locale();
		$json_file = sprintf(
			'%1$slanguages/menu-ghost-%2$s-%3$s.json',
			MNGH_DIR,
			$locale,
			md5( 'build/index.js' )
		);

		return is_readable( $json_file ) ? $json_file : $file;
	}
}
