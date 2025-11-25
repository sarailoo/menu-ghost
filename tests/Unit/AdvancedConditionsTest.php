<?php
/**
 * Tests for the advanced conditions evaluator.
 *
 * @package MenuGhost\Tests
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit;

use Brain\Monkey\Functions;
use MenuGhost\Conditions\Advanced;

class AdvancedConditionsTest extends AbstractUnitTestcase {
	/**
	 * Backups of super globals to restore between tests.
	 *
	 * @var array
	 */
	private array $original_get = array();

	/**
	 * @var array
	 */
	private array $original_server = array();

	protected function setUp(): void {
		parent::setUp();
		$this->original_get    = $_GET;
		$this->original_server = $_SERVER;
	}

	protected function tearDown(): void {
		$_GET    = $this->original_get;
		$_SERVER = $this->original_server;
		parent::tearDown();
	}

	public function test_match_returns_true_when_browser_language_matches(): void {
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr,en-US;q=0.8,en;q=0.7';

		$rules = array(
			array(
				'enabled' => true,
				'key'     => 'browser_language',
				'params'  => array( 'langs' => array( 'en', 'de' ) ),
			),
		);

		$this->assertTrue( Advanced::match( $rules ) );
	}

	public function test_match_returns_false_when_login_state_is_not_met(): void {
		Functions\when( 'is_user_logged_in' )->justReturn( false );

		$rules = array(
			array(
				'enabled' => true,
				'key'     => 'login_status',
				'params'  => array( 'state' => 'logged_in' ),
			),
		);

		$this->assertFalse( Advanced::match( $rules ) );
	}

	public function test_match_user_role_requires_login(): void {
		Functions\when( 'is_user_logged_in' )->justReturn( false );

		$rules = array(
			array(
				'enabled' => true,
				'key'     => 'user_role',
				'params'  => array( 'roles' => array( 'administrator' ) ),
			),
		);

		$this->assertFalse( Advanced::match( $rules ) );
	}

	public function test_match_user_role_passes_when_role_overlaps(): void {
		Functions\when( 'is_user_logged_in' )->justReturn( true );

		$user        = new \WP_User();
		$user->roles = array( 'subscriber', 'shop_manager' );
		Functions\when( 'wp_get_current_user' )->alias( static fn() => $user );

		$rules = array(
			array(
				'enabled' => true,
				'key'     => 'user_role',
				'params'  => array( 'roles' => array( 'shop_manager' ) ),
			),
		);

		$this->assertTrue( Advanced::match( $rules ) );
	}

	public function test_match_url_query_key_equals_mode_must_match_value(): void {
		$_GET['campaign'] = 'fall-sale';

		$rules = array(
			array(
				'enabled' => true,
				'key'     => 'url_query_key',
				'params'  => array(
					'mode'  => 'equals',
					'key'   => 'campaign',
					'value' => 'fall-sale',
				),
			),
		);

		$this->assertTrue( Advanced::match( $rules ) );
	}

	public function test_match_url_query_key_equals_mode_fails_for_mismatched_value(): void {
		$_GET['campaign'] = 'spring';

		$rules = array(
			array(
				'enabled' => true,
				'key'     => 'url_query_key',
				'params'  => array(
					'mode'  => 'equals',
					'key'   => 'campaign',
					'value' => 'fall',
				),
			),
		);

		$this->assertFalse( Advanced::match( $rules ) );
	}

	public function test_match_utm_parameter_equals_mode_checks_query_string(): void {
		$_GET['utm_source'] = 'newsletter';

		$rules = array(
			array(
				'enabled' => true,
				'key'     => 'utm_source',
				'params'  => array(
					'mode'  => 'equals',
					'value' => 'newsletter',
				),
			),
		);

		$this->assertTrue( Advanced::match( $rules ) );
	}

	public function test_match_device_detects_tablet_user_agents(): void {
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X)';
		Functions\when( 'wp_is_mobile' )->justReturn( false );

		$rules = array(
			array(
				'enabled' => true,
				'key'     => 'device',
				'params'  => array( 'devices' => array( 'tablet' ) ),
			),
		);

		$this->assertTrue( Advanced::match( $rules ) );
	}

	public function test_match_signup_date_honors_before_operator(): void {
		Functions\when( 'is_user_logged_in' )->justReturn( true );

		$user                  = new \WP_User();
		$user->user_registered = '2024-01-05 12:00:00';
		Functions\when( 'wp_get_current_user' )->alias( static fn() => $user );
		Functions\expect( 'get_date_from_gmt' )
			->once()
			->with( '2024-01-05 12:00:00', 'Y-m-d' )
			->andReturn( '2024-01-05' );

		$rules = array(
			array(
				'enabled' => true,
				'key'     => 'signup_date',
				'params'  => array(
					'operator' => 'before',
					'date'     => '2024-02-01',
				),
			),
		);

		$this->assertTrue( Advanced::match( $rules ) );
	}
}
