<?php
/**
 * Shared setup/teardown utilities for unit tests.
 *
 * @package MenuGhost\Tests
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Exception used to intercept wp_send_json_* calls inside tests.
 */
class JsonResponse extends \RuntimeException {
	/**
	 * Response type (success or error).
	 *
	 * @var string
	 */
	public string $kind;

	/**
	 * Payload passed to wp_send_json_*
	 *
	 * @var array<mixed>
	 */
	public array $payload;

	/**
	 * HTTP-like status code.
	 *
	 * @var int
	 */
	public int $status;

	/**
	 * Constructor.
	 *
	 * @param string       $kind    Response kind.
	 * @param array<mixed> $payload Response payload.
	 * @param int          $status  HTTP status code.
	 */
	public function __construct( string $kind, array $payload, int $status ) {
		parent::__construct( sprintf( 'JSON %s response', $kind ) );
		$this->kind    = $kind;
		$this->payload = $payload;
		$this->status  = $status;
	}
}

/**
 * Base PHPUnit test case that wires up Brain Monkey.
 */
abstract class AbstractUnitTestcase extends TestCase {
	/**
	 * Adds Mockery expectations to the PHPUnit assertions count.
	 */
	use MockeryPHPUnitIntegration;

	/**
	 * Sets up the environment.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		$this->stub_common_wp_functions();
	}

	/**
	 * Provide defaults for core WordPress helpers referenced in tests.
	 *
	 * @return void
	 */
	private function stub_common_wp_functions(): void {
		Functions\when( '__' )
			->alias( static fn( $text ) => is_scalar( $text ) ? (string) $text : '' );

		Functions\when( '_e' )
			->alias( static fn( $text ) => is_scalar( $text ) ? (string) $text : '' );

		Functions\when( 'sanitize_text_field' )
			->alias( static fn( $value ) => is_scalar( $value ) ? trim( strip_tags( (string) $value ) ) : '' );

		Functions\when( 'sanitize_textarea_field' )
			->alias( static fn( $value ) => is_scalar( $value ) ? trim( strip_tags( (string) $value ) ) : '' );

		Functions\when( 'sanitize_key' )
			->alias( static function( $value ) {
				$value = strtolower( (string) $value );
				return preg_replace( '/[^a-z0-9_\-]/', '', $value );
			} );

		Functions\when( 'wp_unslash' )
			->alias( static fn( $value ) => $value );

		Functions\when( 'absint' )
			->alias( static fn( $value ) => abs( (int) $value ) );

		Functions\when( 'wp_timezone' )
			->alias( static fn() => new \DateTimeZone( 'UTC' ) );

		Functions\when( 'wp_is_mobile' )
			->justReturn( false );

		Functions\when( 'is_admin' )
			->justReturn( false );

		Functions\when( 'is_user_logged_in' )
			->justReturn( false );

		Functions\when( 'wp_get_current_user' )
			->alias( static fn() => new \WP_User() );

		Functions\when( 'is_wp_error' )
			->justReturn( false );

		Functions\when( 'current_user_can' )
			->justReturn( true );

		Functions\when( 'wp_send_json_success' )
			->alias(
				static function ( $data = null, $status = null ) {
					throw new JsonResponse( 'success', (array) $data, $status ?? 200 );
				}
			);

		Functions\when( 'wp_send_json_error' )
			->alias(
				static function ( $data = null, $status = null ) {
					throw new JsonResponse( 'error', (array) $data, $status ?? 400 );
				}
			);
	}

	/**
	 * Tears down the environment.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}
}
