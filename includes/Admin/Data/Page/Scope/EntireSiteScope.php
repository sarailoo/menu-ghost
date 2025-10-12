<?php
/**
 * Scope definition for entire site matches.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Admin\Data\Page\Scope;

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
			'label'   => 'Entire Site',
			'options' => array(),
		);
	}
}
