<?php
/**
 * Tests for the SettingsController AJAX handler.
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use MenuGhost\Admin\SettingsController;
use MenuGhost\SettingsRepository;
use MenuGhost\Tests\Unit\AbstractUnitTestcase;
use MenuGhost\Tests\Unit\JsonResponse;

final class SettingsControllerTest extends AbstractUnitTestcase {
	/**
	 * Backup of $_POST so we can restore after each test.
	 *
	 * @var array<mixed>
	 */
	private array $post_backup = array();

	protected function setUp(): void {
		parent::setUp();
		$this->post_backup = $_POST;
	}

	protected function tearDown(): void {
		$_POST = $this->post_backup;
		parent::tearDown();
	}

	public function test_save_menu_settings_requires_valid_item(): void {
		$_POST = array( 'itemId' => '0' );

		Functions\expect( 'check_ajax_referer' )
			->once()
			->with( 'menu_ghost', 'nonce' )
			->andReturn( true );

		try {
			$controller = new SettingsController();
			$controller->save_menu_settings();
			$this->fail( 'Expected JsonResponse exception.' );
		} catch ( JsonResponse $response ) {
			$this->assertSame( 'error', $response->kind );
			$this->assertSame( 400, $response->status );
			$this->assertSame( 'Invalid menu item.', $response->payload['message'] ?? '' );
		}
	}

	public function test_save_menu_settings_normalizes_payloads_before_storage(): void {
		$pages_payload = array(
			array(
				'type'            => ' include ',
				'scope'           => ' archive ',
				'subScope'        => ' archive_author ',
				'additional'      => ' 5 , 10 ',
				'additionalLabel' => '<h1>Label</h1>',
			),
			array( 'type' => '', 'scope' => '' ),
		);

		$advanced_payload = array(
			array(
				'key'   => 'utm_source',
				'value' => '  Email ',
			),
			'not-array',
		);

		$_POST = array(
			'nonce'    => '123',
			'itemId'   => '99',
			'pages'    => json_encode( $pages_payload ),
			'advanced' => json_encode( $advanced_payload ),
		);

		$expected_pages = array(
			array(
				'type'            => 'include',
				'scope'           => 'archive',
				'subScope'        => 'archive_author',
				'additional'      => '5 , 10',
				'additionalLabel' => 'Label',
			),
		);

		$expected_advanced = array(
			array(
				'key'   => 'utm_source',
				'value' => 'Email',
			),
			array(),
		);

		Functions\expect( 'check_ajax_referer' )
			->once()
			->with( 'menu_ghost', 'nonce' )
			->andReturn( true );

		Functions\expect( 'get_post_meta' )
			->once()
			->with( 99, SettingsRepository::META, true )
			->andReturn( array( 'advanced' => array( array( 'existing' => 'rule' ) ) ) );

		Functions\expect( 'update_post_meta' )
			->once()
			->ordered()
			->with(
				99,
				SettingsRepository::META,
				array(
					'advanced' => array( array( 'existing' => 'rule' ) ),
					'pages'    => $expected_pages,
				)
			);

		Functions\expect( 'get_post_meta' )
			->once()
			->ordered()
			->with( 99, SettingsRepository::META, true )
			->andReturn( array( 'pages' => $expected_pages ) );

		Functions\expect( 'update_post_meta' )
			->once()
			->ordered()
			->with(
				99,
				SettingsRepository::META,
				array(
					'pages'    => $expected_pages,
					'advanced' => $expected_advanced,
				)
			);

		try {
			$controller = new SettingsController();
			$controller->save_menu_settings();
			$this->fail( 'Expected JsonResponse exception.' );
		} catch ( JsonResponse $response ) {
			$this->assertSame( 'success', $response->kind );
			$this->assertSame( 'Settings saved.', $response->payload['message'] ?? '' );
		}
	}
}
