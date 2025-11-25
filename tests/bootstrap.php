<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package MenuGhost\Tests
 */

declare(strict_types=1);


if ( ! class_exists( 'WP_User' ) ) {
	/**
	 * Lightweight stand-in for WP_User used in tests.
	 */
	class WP_User {
		/**
		 * User roles for the stubbed account.
		 *
		 * @var array<int,string>
		 */
		public $roles = array();

		/**
		 * Registration datetime string.
		 *
		 * @var string
		 */
		public $user_registered = '';

		/**
		 * Hydrate properties for convenience.
		 *
		 * @param array<string,mixed> $props Optional properties.
		 */
		public function __construct( array $props = array() ) {
			foreach ( $props as $key => $value ) {
				$this->$key = $value;
			}
		}
	}
}


if ( ! class_exists( 'WP_Post' ) ) {
	/**
	 * Basic representation of WP_Post for tests.
	 */
	class WP_Post {
		/**
		 * Post ID.
		 *
		 * @var int
		 */
		public $ID = 0;

		/**
		 * Post title.
		 *
		 * @var string
		 */
		public $post_title = '';

		/**
		 * Post type slug.
		 *
		 * @var string
		 */
		public $post_type = 'post';

		/**
		 * Hydrate properties for convenience.
		 *
		 * @param array<string,mixed> $props Optional properties.
		 */
		public function __construct( array $props = array() ) {
			foreach ( $props as $key => $value ) {
				$this->$key = $value;
			}
		}
	}
}

if ( ! class_exists( 'WP_Term' ) ) {
	/**
	 * Simple stand-in for WP_Term.
	 */
	class WP_Term {
		/**
		 * Term ID.
		 *
		 * @var int
		 */
		public $term_id = 0;

		/**
		 * Term name.
		 *
		 * @var string
		 */
		public $name = '';

		/**
		 * Term slug.
		 *
		 * @var string
		 */
		public $slug = '';

		/**
		 * Taxonomy slug.
		 *
		 * @var string
		 */
		public $taxonomy = '';

		/**
		 * Parent term ID.
		 *
		 * @var int
		 */
		public $parent = 0;

		/**
		 * Hydrate the stubbed term properties.
		 *
		 * @param array<string,mixed> $props Optional properties.
		 */
		public function __construct( array $props = array() ) {
			foreach ( $props as $key => $value ) {
				if ( property_exists( $this, $key ) ) {
					$this->$key = $value;
				}
			}
		}
	}
}


if ( ! class_exists( 'WP_REST_Request' ) ) {
	/**
	 * Minimal WP_REST_Request stub.
	 */
	class WP_REST_Request {
		/**
		 * Raw parameters.
		 *
		 * @var array<string,mixed>
		 */
		private $params;

		/**
		 * Constructor.
		 *
		 * @param array<string,mixed> $params Parameters.
		 */
		public function __construct( array $params = array() ) {
			$this->params = $params;
		}

		/**
		 * Retrieve request parameter.
		 *
		 * @param string $key Parameter name.
		 *
		 * @return mixed
		 */
		public function get_param( string $key ) {
			return $this->params[ $key ] ?? null;
		}
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	/**
	 * Minimal WP_REST_Response stub.
	 */
	class WP_REST_Response {
		/**
		 * Response payload.
		 *
		 * @var array<mixed>
		 */
		private $data;

		/**
		 * Constructor.
		 *
		 * @param array<mixed> $data Response data.
		 */
		public function __construct( array $data = array() ) {
			$this->data = $data;
		}

		/**
		 * Access response data.
		 *
		 * @return array<mixed>
		 */
		public function get_data(): array {
			return $this->data;
		}
	}
}

if ( ! class_exists( 'WP_REST_Server' ) ) {
	/**
	 * Minimal REST server constants.
	 */
	class WP_REST_Server {
		public const READABLE = 'GET';
	}
}

putenv( 'TESTS_PATH=' . __DIR__ ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
putenv( 'LIBRARY_PATH=' . dirname( __DIR__ ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_putenv
$vendor = dirname( __DIR__ ) . '/vendor/';
if ( ! realpath( $vendor ) ) {
	die( 'Please install via Composer before running tests.' );
}
if ( ! defined( 'PHPUNIT_COMPOSER_INSTALL' ) ) {
	define( 'PHPUNIT_COMPOSER_INSTALL', $vendor . 'autoload.php' );
}

require_once $vendor . '/antecedent/patchwork/Patchwork.php';
require_once $vendor . 'autoload.php';
unset( $vendor );
