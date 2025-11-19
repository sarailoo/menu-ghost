<?php
/**
 * Tests for the AdvancedController AJAX handler.
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use MenuGhost\Admin\AdvancedController;
use MenuGhost\SettingsRepository;
use MenuGhost\Tests\Unit\AbstractUnitTestcase;
use MenuGhost\Tests\Unit\JsonResponse;

class AdvancedControllerTest extends AbstractUnitTestcase {
	/**
	 * Backup of the global POST array.
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

	public function test_save_menu_rules_requires_capability(): void {
		Functions::expect( 'current_user_can' )
			->once()
			->with( 'edit_theme_options' )
			->andReturn( false );

		try {
			AdvancedController::save_menu_rules();
			$this->fail( 'Expected JsonResponse exception.' );
		} catch ( JsonResponse $response ) {
			$this->assertSame( 'error', $response->kind );
			$this->assertSame( 403, $response->status );
			$this->assertSame( 'Forbidden.', $response->payload['message'] ?? '' );
		}
	}

	public function test_save_menu_rules_sanitizes_rules_before_persisting(): void {
		$_POST = array(
			'nonce'  => 'abc123',
			'itemId' => '12',
			'rules'  => json_encode(
				array(
					array(
						'key'     => ' user_role ',
						'enabled' => 1,
						'params'  => array(
							'roles' => array( ' administrator ', 'customer ' ),
							'mode'  => ' equals ',
						),
					),
					'not-an-array',
				)
			),
		);

		Functions::expect( 'wp_verify_nonce' )
			->once()
			->with( 'abc123', 'menu_ghost' )
			->andReturn( true );

		Functions::expect( 'get_post_meta' )
			->once()
			->with( 12, SettingsRepository::META, true )
			->andReturn( array( 'pages' => array() ) );

		$expected_rules = array(
			array(
				'key'     => 'user_role',
				'enabled' => true,
				'params'  => array(
					'roles' => array( 'administrator', 'customer' ),
					'mode'  => 'equals',
				),
			),
			array(
				'key'     => '',
				'enabled' => false,
				'params'  => array(),
			),
		);

		Functions::expect( 'update_post_meta' )
			->once()
			->with(
				12,
				SettingsRepository::META,
				array(
					'pages'    => array(),
					'advanced' => $expected_rules,
				)
			);

		try {
			AdvancedController::save_menu_rules();
			$this->fail( 'Expected JsonResponse exception.' );
		} catch ( JsonResponse $response ) {
			$this->assertSame( 'success', $response->kind );
			$this->assertSame( 200, $response->status );
			$this->assertSame( 'Rules saved.', $response->payload['message'] ?? '' );
		}
	}
}
