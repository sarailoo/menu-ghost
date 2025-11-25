<?php
/**
 * Builds metadata consumed by the advanced conditions UI.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Admin\Data\Advanced;

use MenuGhost\Admin\Data\Advanced\Languages\LanguageOptions;
use function translate_user_role;
use function wp_roles;
use function __;

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
				'label' => __( 'Sunday', 'menu-ghost' ),
			),
			array(
				'value' => 1,
				'label' => __( 'Monday', 'menu-ghost' ),
			),
			array(
				'value' => 2,
				'label' => __( 'Tuesday', 'menu-ghost' ),
			),
			array(
				'value' => 3,
				'label' => __( 'Wednesday', 'menu-ghost' ),
			),
			array(
				'value' => 4,
				'label' => __( 'Thursday', 'menu-ghost' ),
			),
			array(
				'value' => 5,
				'label' => __( 'Friday', 'menu-ghost' ),
			),
			array(
				'value' => 6,
				'label' => __( 'Saturday', 'menu-ghost' ),
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
				'label' => __( 'Mobile', 'menu-ghost' ),
			),
			array(
				'value' => 'tablet',
				'label' => __( 'Tablet', 'menu-ghost' ),
			),
			array(
				'value' => 'desktop',
				'label' => __( 'Desktop', 'menu-ghost' ),
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
					// Use WordPress' role translations when available.
					'label' => isset( $role['name'] ) ? translate_user_role( (string) $role['name'] ) : $role_key,
				),
				array_keys( $roles ),
				$roles
			)
		);
	}
}
