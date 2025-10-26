<?php
/**
 * Menu visibility filtering for the front-end.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Frontend;

use MenuGhost\Conditions\Pages;
use MenuGhost\Conditions\Advanced;
use MenuGhost\SettingsRepository;

/**
 * Registers the frontend-only filter that hides menu items based on saved conditions.
 */
class MenuVisibility {
	/**
	 * Shared instance of the handler.
	 *
	 * @var MenuVisibility|null
	 */
	private static ?MenuVisibility $instance = null;

	/**
	 * Retrieve a shared instance of the visibility handler.
	 *
	 * @since 1.1.0
	 *
	 * @return MenuVisibility
	 */
	public static function instance(): MenuVisibility {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function init(): void {
		add_filter( 'wp_get_nav_menu_items', array( $this, 'filter_menu_items' ), 10, 3 );
	}

	/**
	 * Filter nav menu items according to saved visibility rules.
	 *
	 * @since 1.1.0
	 *
	 * @param array<mixed>        $items Menu items for the current menu.
	 * @param \WP_Term|\stdClass  $menu  Menu object.
	 * @param array<string,mixed> $args  Menu arguments.
	 *
	 * @return array<mixed>
	 */
	public function filter_menu_items( $items, $menu, $args ) {
		unset( $menu, $args );

		foreach ( $items as $key => $item ) {
			$settings = SettingsRepository::get( (int) $item->ID );
			$pages    = $settings['pages'] ?? array();
			$advanced = $settings['advanced'] ?? array();

			$visible = $this->evaluate_pages( $pages );
			if ( $visible ) {
				$visible = Advanced::match( $advanced );
			}

			if ( ! $visible ) {
				unset( $items[ $key ] );
			}
		}
		return array_values( $items );
	}

	/**
	 * Evaluate page scope visibility conditions for the current user.
	 *
	 * @since 1.1.0
	 *
	 * @param array<mixed> $conditions List of saved page conditions.
	 *
	 * @return bool
	 */
	private function evaluate_pages( $conditions ): bool {
		if ( empty( $conditions ) || ! is_array( $conditions ) ) {
			return true;
		}

		$include_matched = null;
		$exclude_matched = false;

		foreach ( $conditions as $condition ) {
			$type       = $condition['type'] ?? '';
			$scope      = $condition['scope'] ?? '';
			$sub_scope  = $condition['subScope'] ?? '';
			$additional = $condition['additional'] ?? '';

			$matched = Pages::match( $scope, $sub_scope, $additional );

			if ( 'include' === $type ) {
				if ( null === $include_matched ) {
					$include_matched = $matched;
				} else {
					$include_matched = $include_matched || $matched;
				}
			} elseif ( 'exclude' === $type ) {
				$exclude_matched = $exclude_matched || $matched;
			}
		}

		$visible = true;

		if ( null !== $include_matched ) {
			$visible = (bool) $include_matched;
		}

		if ( $visible && $exclude_matched ) {
			$visible = false;
		}

		return $visible;
	}
}
