<?php

declare(strict_types=1);

namespace WPMenuControl;

use WPMenuControl\Admin\MenuItem;

/**
 * WPMenuControl class.
 */
class WPMenuControl {
	/**
	 * Holds the class instance.
	 */
	private static ?WPMenuControl $instance = null;

	/**
	 * Return an instance of the WPMenuControl Class.
	 *
	 * @return WPMenuControl class instance.
	 */
	public static function instance(): WPMenuControl {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initialize plugin.
	 *
	 * @return void.
	 */
	public function init() {
		$this->addHooks();

		MenuItem::instance();
	}

	/**
	 * Add hooks.
	 *
	 * @return void.
	 */
	private function addHooks() {
		add_action( 'init', array( $this, 'loadTextDomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAssets' ) );
	}

	/**
	 * Load text domain.
	 *
	 * @return void.
	 */
	public function loadTextDomain(): void {
		load_plugin_textdomain( 'wp-menu-control', false, WP_MENU_CONTROL_DIR . 'languages' );
	}

	public function enqueueAssets( $hook_suffix ) {
		if ( 'nav-menus.php' !== $hook_suffix ) {
			return;
		}

		$assetFile = include( WP_MENU_CONTROL_BUILD_DIR . 'index.asset.php' );

		$this->enqueueScripts($assetFile);
		$this->enqueueStyles($assetFile);

		$this->localizeScript();
	}

	private function enqueueScripts($assetFile) {
		wp_enqueue_script(
			'wp-menu-control-script',
			WP_MENU_CONTROL_BUILD_URL . 'index.js',
			$assetFile['dependencies'],
			$assetFile['version'],
			true
		);
	}

	private function enqueueStyles($assetFile) {
		if ( ! is_rtl() ) {
			wp_enqueue_style(
				'wp-menu-control-style',
				WP_MENU_CONTROL_BUILD_URL . 'style-index.css',
				array(),
				$assetFile['version']
			);

			return;
		}

		wp_enqueue_style(
			'wp-menu-control-style-rtl',
			$buildUrl . 'style-index-rtl.css',
			array(),
			$assetFile['version']
		);
	}

	/**
	 * Retrieve all menu item IDs from all menus.
	 *
	 * @return array.
	 */
	private function generateMenuItemIds() {
		$menuItemIds = [];
		$menus = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );

			if ( $items ) {
				foreach ( $items as $item ) {
					$menuItemIds[] = $item->ID;
				}
			}
		}

		return $menuItemIds;
	}

	/**
	 * Retrieve all menu item IDs from all menus.
	 *
	 * @return array.
	 */
	private function localizeScript() {
		$menuItemIds = $this->generateMenuItemIds();

		$data = [
			'menu_item_ids' => $menuItemIds,
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wp_menu_control'),
			'conditions' => get_option(WP_MENU_CONTROL_OPTION_NAME),
		];

		wp_localize_script('wp-menu-control-script', 'wp_menu_control', $data);
	}
}
