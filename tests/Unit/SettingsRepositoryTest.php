<?php
/**
 * Tests for the SettingsRepository helper.
 *
 * @package MenuGhost\Tests
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit;

use Brain\Monkey\Functions;
use MenuGhost\SettingsRepository;

class SettingsRepositoryTest extends AbstractUnitTestcase {
	public function test_get_returns_default_shape_when_meta_missing(): void {
		Functions\expect( 'get_post_meta' )
			->once()
			->with( 42, SettingsRepository::META, true )
			->andReturn( 'not-an-array' );

		$settings = SettingsRepository::get( 42 );

		$this->assertSame(
			array(
				'pages'    => array(),
				'advanced' => array(),
			),
			$settings
		);
	}

	public function test_save_pages_normalizes_indexes_before_persisting(): void {
		Functions\expect( 'get_post_meta' )
			->once()
			->with( 7, SettingsRepository::META, true )
			->andReturn(
				array(
					'advanced' => array( array( 'rule' => 'existing' ) ),
				)
			);

		Functions\expect( 'update_post_meta' )
			->once()
			->with(
				7,
				SettingsRepository::META,
				array(
					'advanced' => array( array( 'rule' => 'existing' ) ),
					'pages'    => array(
						array( 'type' => 'include' ),
						array( 'type' => 'exclude' ),
					),
				)
			);

		SettingsRepository::save_pages(
			7,
			array(
				'custom' => array( 'type' => 'include' ),
				array( 'type' => 'exclude' ),
			)
		);
	}

	public function test_save_advanced_overwrites_ruleset(): void {
		Functions\expect( 'get_post_meta' )
			->once()
			->with( 11, SettingsRepository::META, true )
			->andReturn(
				array(
					'pages' => array( array( 'foo' => 'bar' ) ),
				)
			);

		$rules = array(
			array( 'key' => 'login_status' ),
			array( 'key' => 'device' ),
		);

		Functions\expect( 'update_post_meta' )
			->once()
			->with(
				11,
				SettingsRepository::META,
				array(
					'pages'    => array( array( 'foo' => 'bar' ) ),
					'advanced' => array(
						array( 'key' => 'login_status' ),
						array( 'key' => 'device' ),
					),
				)
			);

		SettingsRepository::save_advanced( 11, $rules );
	}

	public function test_get_many_returns_settings_indexed_by_id(): void {
		$call_map = array(
			5  => array( 'pages' => array( array( 'a' => 1 ) ) ),
			10 => array( 'advanced' => array( array( 'b' => 2 ) ) ),
		);

		Functions\expect( 'get_post_meta' )
			->times( 2 )
			->andReturnUsing(
				static function( $id ) use ( $call_map ) {
					return $call_map[ $id ] ?? array();
				}
			);

		$results = SettingsRepository::get_many( array( 5, 10 ) );

		$this->assertSame( array( 'a' => 1 ), $results[5]['pages'][0] );
		$this->assertSame( array( 'b' => 2 ), $results[10]['advanced'][0] );
	}
}
