<?php
/**
 * Builds metadata consumed by the advanced conditions UI.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Admin\Data\Advanced;

use WPMenuControl\Admin\Data\Advanced\Languages\LanguageOptions;
use function wp_roles;

/**
 * Builds advanced condition metadata consumed by the admin UI.
 *
 * @since 1.1.0
 */
class AdvancedMetaBuilder {
	/**
	 * Generate all advanced metadata.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string,mixed>
	 */
	public function build(): array {
		return array(
			'languages' => LanguageOptions::all(),
			'weekdays'  => $this->weekdays(),
			'devices'   => $this->devices(),
			'roles'     => $this->roles(),
		);
	}

	/**
	 * Weekday options.
	 *
	 * @since 1.1.0
	 *
	 * @return array<int,array<string,int|string>>
	 */
	private function weekdays(): array {
		return array(
			array(
				'value' => 0,
				'label' => 'Sunday',
			),
			array(
				'value' => 1,
				'label' => 'Monday',
			),
			array(
				'value' => 2,
				'label' => 'Tuesday',
			),
			array(
				'value' => 3,
				'label' => 'Wednesday',
			),
			array(
				'value' => 4,
				'label' => 'Thursday',
			),
			array(
				'value' => 5,
				'label' => 'Friday',
			),
			array(
				'value' => 6,
				'label' => 'Saturday',
			),
		);
	}

	/**
	 * Device options.
	 *
	 * @since 1.1.0
	 *
	 * @return array<int,array<string,string>>
	 */
	private function devices(): array {
		return array(
			array(
				'value' => 'mobile',
				'label' => 'Mobile',
			),
			array(
				'value' => 'tablet',
				'label' => 'Tablet',
			),
			array(
				'value' => 'desktop',
				'label' => 'Desktop',
			),
		);
	}

	/**
	 * Role options.
	 *
	 * @since 1.1.0
	 *
	 * @return array<int,array<string,string>>
	 */
	private function roles(): array {
		$roles = wp_roles()->roles;

		return array_values(
			array_map(
				static fn( string $role_key, array $role ): array => array(
					'value' => $role_key,
					'label' => isset( $role['name'] ) ? (string) $role['name'] : $role_key,
				),
				array_keys( $roles ),
				$roles
			)
		);
	}
}
