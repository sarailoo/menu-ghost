<?php

declare(strict_types=1);

namespace WPMenuControl;

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
	 * Add hooks.
	 *
	 * @return void.
	 */
	public function init() {
		add_action( 'init', array( $this, 'loadTextDomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAssets' ) );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'hideMnuItem' ), 10, 3 );
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

		$buildDir = WP_MENU_CONTROL_DIR . 'build/';
		$buildUrl = WP_MENU_CONTROL_URL . 'build/';
	
		$assetFile = include( $buildDir . 'index.asset.php' );
	
		wp_enqueue_script(
			'wp-menu-control-script',
			$buildUrl . 'index.js',
			$assetFile['dependencies'],
			$assetFile['version'],
			true
		);
	
		if ( ! is_rtl() ) {
			wp_enqueue_style(
				'wp-menu-control-style',
				$buildUrl . 'style-index.css',
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

	public function hideMnuItem( $items, $menu, $args ) {
		foreach ( $items as $key => $item ) {
			// @todo Add conditions
			//$pageIsValid = Page::isValid($item->ID);
			//$triggerIsValid = Trigger::isValid($item->ID);

			if ( ! $pageCondition || ! $triggerIsValid ) {
				unset( $items[ $key ] );
			}
		}

		return $items;
	}
}
