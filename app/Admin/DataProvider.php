<?php

declare(strict_types=1);

namespace WPMenuControl\Admin;

use DataProvider\Admin\MenuItem;

/**
 * DataProvider class.
 */
class DataProvider {
	/**
	 * Retrieve all menu item IDs and titles from all menus.
	 *
	 * @return array
	 */
	public static function generateMenuItemsData() {
		$menuItems = [];
		$menus = wp_get_nav_menus();

		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );

			if ( $items ) {
				foreach ( $items as $item ) {
					$menuItems[] = [
						'id' => $item->ID,
						'title' => $item->title,
					];
				}
			}
		}

		return $menuItems;
	}

    public static function generatePageConditions() {
        // Fake data for localized conditions
        $localized_data = [
            'conditionTypes' => [
                ['value' => 'Include', 'label' => 'Include'],
                ['value' => 'Exclude', 'label' => 'Exclude'],
            ],
            'scopes' => [
                [
                    'value' => 'Entire Site',
                    'label' => 'Entire Site',
                    'options' => [], // No sub-options for "Entire Site"
                ],
                [
                    'value' => 'Archive',
                    'label' => 'Archive',
                    'options' => [
                        ['value' => 'archive', 'label' => 'All Archives'],
                        ['value' => 'author', 'label' => 'Author Archive'],
                        ['value' => 'date', 'label' => 'Date Archive'],
                        ['value' => 'search', 'label' => 'Search Results'],
                    ],
                    'additionalData' => [
                        'author' => [
                            'list' => [
                                ['value' => '1', 'label' => 'Author 1'],
                                ['value' => '2', 'label' => 'Author 2'],
                            ],
                            'selected' => 'All', // Default value
                        ],
                    ],
                ],
                [
                    'value' => 'Singular',
                    'label' => 'Singular',
                    'options' => [
                        ['value' => 'singular', 'label' => 'All Singular'],
                        ['value' => 'front_page', 'label' => 'Front Page'],
                    ],
                    'additionalData' => [
                        'post_by_author' => [
                            'list' => [
                                ['value' => '1', 'label' => 'Author 1'],
                                ['value' => '2', 'label' => 'Author 2'],
                            ],
                            'selected' => 'All', // Default value
                        ],
                    ],
                ],
            ],
        ];

        return $localized_data;
    }
}
