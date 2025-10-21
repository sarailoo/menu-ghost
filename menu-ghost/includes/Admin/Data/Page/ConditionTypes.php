<?php
/**
 * Condition types available in the page conditions UI.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Admin\Data\Page;

/**
 * Provides available condition types for the menu UI.
 *
 * @since 1.1.0
 */
class ConditionTypes {
	/**
	 * Retrieve all condition types.
	 *
	 * @since 1.1.0
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function all(): array {
		return array(
			array(
				'value' => 'include',
				'label' => 'Include',
			),
			array(
				'value' => 'exclude',
				'label' => 'Exclude',
			),
		);
	}
}
