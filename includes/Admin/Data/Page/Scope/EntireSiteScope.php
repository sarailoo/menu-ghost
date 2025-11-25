<?php
/**
 * Scope definition for entire site matches.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Admin\Data\Page\Scope;

use function __;

/**
 * Provides the "Entire Site" scope definition.
 *
 * @since 1.1.0
 */
class EntireSiteScope {
	/**
	 * Retrieve the scope payload.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string,mixed>
	 */
	public static function definition(): array {
		return array(
			'value'   => 'entire_site',
			'label'   => __( 'Entire Site', 'menu-ghost' ),
			'options' => array(),
		);
	}
}
