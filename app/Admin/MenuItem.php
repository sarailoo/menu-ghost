<?php

declare(strict_types=1);

namespace WPMenuControl\Admin;

/**
 * MenuItem class handles custom fields and visibility logic for menu items.
 */
class MenuItem {
	/**
	 * Holds the class instance.
	 */
	private static ?MenuItem $instance = null;

	/**
	 * Return an instance of the MenuItem Class.
	 *
	 * @return MenuItem class instance.
	 */
	public static function instance(): MenuItem {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
     * Add hooks.
     *
     * @return void
     */
	public function init(): void {
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menuItemFields' ), 10, 2 );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'hideMenuItem' ), 10, 3 );
	}

	/**
	 * Add a custom field to each menu item.
	 *
	 * @param int $item_id Menu item ID.
	 * @param object $item Menu item object.
	 * @return void
	 */
	public function menuItemFields( $item_id, $item ) {
		$uniqueId = 'wp-menu-control-' . $item_id;
		echo '<div id="' . esc_attr( $uniqueId ) . '" class="wp-menu-control-item-button-wrap"></div>';
	}

	/**
	 * Conditionally hide menu items.
	 *
	 * @param array $items List of menu items.
	 * @param object $menu The menu object.
	 * @param array $args Arguments passed to the menu.
	 * @return array Filtered list of menu items.
	 */
	public function hideMenuItem( $items, $menu, $args ) {
		foreach ( $items as $key => $item ) {
			// Example: Logic to hide certain items based on conditions.
			// Replace with your custom conditions.
			$hideCondition = false; // Replace with actual condition logic.

			if ( $hideCondition ) {
				unset( $items[ $key ] );
			}
		}

		return $items;
	}
}
