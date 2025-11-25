<?php
/**
 * Tests for navigation block link visibility.
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit;

use Brain\Monkey\Functions;
use MenuGhost\Frontend\MenuVisibility;
use MenuGhost\NavigationSettingsRepository;

class NavigationBlockVisibilityTest extends AbstractUnitTestcase {
	protected function setUp(): void {
		parent::setUp();
	}

	public function test_link_hidden_with_context_navigation_settings(): void {
		$nav_id   = 99;
		$link_key = 'id:5';

		Functions\expect( 'get_post_meta' )
			->with( $nav_id, NavigationSettingsRepository::META, true )
			->andReturn(
				array(
					$link_key => array(
						'pages'    => array(
							array(
								'type'  => 'exclude',
								'scope' => 'entire_site',
							),
						),
						'advanced' => array(),
					),
				)
			);

		$block = array(
			'attrs'   => array(
				'id'   => 5,
				'url'  => 'https://example.com',
				'label'=> 'Example',
			),
			'context' => array(
				'navigationId' => $nav_id,
			),
		);

		$visibility = new MenuVisibility();
		$result     = $visibility->filter_navigation_link( '<a>link</a>', $block );

		$this->assertSame( '', $result );
	}

	public function test_link_hidden_via_fallback_lookup_when_context_missing(): void {
		$link_key = 'id:15';

		Functions\expect( 'get_post_meta' )
			->andReturnUsing(
				static function ( $post_id, $key ) use ( $link_key ) {
					if ( 0 === (int) $post_id ) {
						return array();
					}

					return array(
						$link_key => array(
							'pages'    => array(
								array(
									'type'  => 'exclude',
									'scope' => 'entire_site',
								),
							),
							'advanced' => array(),
						),
					);
				}
			);

		Functions\expect( 'get_posts' )
			->once()
			->andReturn( array( 777 ) );

		$block = array(
			'attrs'   => array(
				'id' => 15,
			),
			'context' => array(), // missing navigationId/postId.
		);

		$visibility = new MenuVisibility();
		$result     = $visibility->filter_navigation_link( '<a>link</a>', $block );

		$this->assertSame( '', $result );
	}
}
