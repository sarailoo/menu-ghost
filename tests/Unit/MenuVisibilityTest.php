<?php
/**
 * Tests for front-end menu visibility filtering.
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit;

use Brain\Monkey\Functions;
use MenuGhost\Frontend\MenuVisibility;
use MenuGhost\SettingsRepository;

class MenuVisibilityTest extends AbstractUnitTestcase {
	public function test_filter_menu_items_respects_include_and_exclude_rules(): void {
		$items = array(
			(object) array( 'ID' => 10 ),
			(object) array( 'ID' => 11 ),
		);

		$settings = array(
			10 => array(
				'pages'    => array(
					array(
						'type'  => 'include',
						'scope' => 'entire_site',
					),
				),
				'advanced' => array(),
			),
			11 => array(
				'pages'    => array(
					array(
						'type'  => 'exclude',
						'scope' => 'entire_site',
					),
				),
				'advanced' => array(),
			),
		);

		Functions\expect( 'get_post_meta' )
			->twice()
			->andReturnUsing(
				static function ( $item_id, $key, $single ) use ( $settings ) {
					return $settings[ $item_id ] ?? array();
				}
			);

		$visibility = new MenuVisibility();
		$result     = $visibility->filter_menu_items( $items, (object) array(), array() );

		$this->assertCount( 1, $result );
		$this->assertSame( 10, $result[0]->ID );
	}

	public function test_filter_menu_items_applies_advanced_rules_after_pages(): void {
		$items = array( (object) array( 'ID' => 50 ) );

		Functions\expect( 'get_post_meta' )
			->once()
			->with( 50, SettingsRepository::META, true )
			->andReturn(
				array(
					'pages'    => array(),
					'advanced' => array(
						array(
							'key'     => 'login_status',
							'enabled' => true,
							'params'  => array( 'state' => 'logged_in' ),
						),
					),
				)
			);

		$visibility = new MenuVisibility();
		$result     = $visibility->filter_menu_items( $items, (object) array(), array() );

		$this->assertSame( array(), $result );
	}
}
