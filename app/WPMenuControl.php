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
			$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void.
	 */
	public function init() {
		add_action( 'init', array( $this, 'loadTextDomain' ) );
		add_filter( 'wp_get_nav_menu_items', 'hideMnuItem', 10, 3 );
	}

	/**
	 * Load text domain.
	 *
	 * @return void.
	 */
	public function loadTextDomain(): void {
		load_plugin_textdomain( 'wp-menu-control', false, WP_MENU_CONTROL_DIR . 'languages' );
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
