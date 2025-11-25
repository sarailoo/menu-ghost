<?php
/**
 * Tests for NavigationSettingsRepository.
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit;

use Brain\Monkey\Functions;
use MenuGhost\NavigationSettingsRepository;

class NavigationSettingsRepositoryTest extends AbstractUnitTestcase {
	public function test_key_generation_prefers_specific_identifiers(): void {
		$this->assertSame( 'id:12', NavigationSettingsRepository::key_from_attributes( array( 'id' => 12 ) ) );
		$this->assertSame( 'ref:abc', NavigationSettingsRepository::key_from_attributes( array( 'ref' => 'abc' ) ) );

		$url_key = NavigationSettingsRepository::key_from_attributes( array( 'url' => 'https://example.com' ) );
		$this->assertStringStartsWith( 'url:', $url_key );

		$label_key = NavigationSettingsRepository::key_from_attributes( array( 'label' => 'My Link' ) );
		$this->assertStringStartsWith( 'label:', $label_key );
	}

	public function test_save_and_get_all_persist_settings(): void {
		$navigation_id = 321;
		$link_key      = 'id:55';
		$pages         = array( array( 'type' => 'exclude', 'scope' => 'entire_site' ) );
		$advanced      = array( array( 'key' => 'login_status', 'enabled' => true ) );

		Functions\expect( 'get_post_meta' )
			->once()
			->with( $navigation_id, NavigationSettingsRepository::META, true )
			->andReturn( array() );

		Functions\expect( 'update_post_meta' )
			->once()
			->with(
				$navigation_id,
				NavigationSettingsRepository::META,
				array(
					$link_key => array(
						'pages'    => $pages,
						'advanced' => $advanced,
					),
				)
			)
			->andReturn( true );

		NavigationSettingsRepository::save( $navigation_id, $link_key, $pages, $advanced );
	}
}
