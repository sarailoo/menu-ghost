<?php
/**
 * Factory helpers for async select configuration.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Admin\Data;

/**
 * Builds async configuration payloads used by the admin UI.
 *
 * @since 1.1.0
 */
class AsyncConfigFactory {
	/**
	 * Create an async configuration definition.
	 *
	 * @since 1.1.0
	 *
	 * @param string $type   Entity type that should be fetched.
	 * @param array  $params Additional parameters that will be passed to the REST endpoint.
	 *
	 * @return array<string,mixed>
	 */
	public static function make( string $type, array $params = array() ): array {
		return array(
			'async'    => array(
				'type'   => $type,
				'params' => $params,
			),
			'selected' => '',
		);
	}
}
