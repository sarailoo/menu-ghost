<?php
/**
 * Admin data providers for the JavaScript application.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Admin;

use WPMenuControl\Admin\Data\Advanced\AdvancedMetaBuilder;
use WPMenuControl\Admin\Data\Page\AdditionalLabelResolver;
use WPMenuControl\Admin\Data\Page\AdditionalLookupBuilder;
use WPMenuControl\Admin\Data\Page\PageConditionsBuilder;
use function wp_get_nav_menu_items;
use function wp_get_nav_menus;

/**
 * Exposes data sets consumed by the JavaScript application.
 *
 * @since 1.0.0
 */
class DataProvider {
	/**
	 * Retrieve all menu item IDs and titles from every registered menu.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int,array<string,int|string>>
	 */
	public static function generate_menu_items_data(): array {
		$menu_items = array();
		$menus      = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );

			if ( ! is_array( $items ) ) {
				continue;
			}

			foreach ( $items as $item ) {
				$menu_items[] = array(
					'id'    => (int) $item->ID,
					'title' => (string) $item->title,
				);
			}
		}

		return $menu_items;
	}

	/**
	 * Generate the dynamic data for page conditions.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string,mixed>
	 */
	public static function generate_page_conditions(): array {
		return ( new PageConditionsBuilder() )->build();
	}

	/**
	 * Build an additional lookup payload for async/list-driven selects.
	 *
	 * @since 1.1.0
	 *
	 * @param array<int,array<string,mixed>> $scopes Scope definitions.
	 *
	 * @return array<string,array<string,array<string,mixed>>>
	 */
	public static function build_additional_lookup( array $scopes ): array {
		return AdditionalLookupBuilder::from_scopes( $scopes );
	}

	/**
	 * Populate missing additional labels on saved conditions.
	 *
	 * @since 1.1.0
	 *
	 * @param array<int,array<string,mixed>>                  $conditions Saved conditions.
	 * @param array<string,array<string,array<string,mixed>>> $lookup Lookup data built from scopes.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function populate_additional_labels( array $conditions, array $lookup ): array {
		return AdditionalLabelResolver::populate( $conditions, $lookup );
	}

	/**
	 * Generate advanced condition metadata.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string,mixed>
	 */
	public static function generate_advanced_meta(): array {
		return ( new AdvancedMetaBuilder() )->build();
	}
}
