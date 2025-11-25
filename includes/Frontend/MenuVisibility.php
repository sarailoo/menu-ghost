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
use MenuGhost\NavigationSettingsRepository;
use function add_filter;

/**
 * Registers the frontend-only filter that hides menu items based on saved conditions.
 */
class MenuVisibility {
	/**
	 * Tracks the navigation post ID while rendering a navigation block.
	 *
	 * @var int|null
	 */
	private static ?int $current_navigation_id = null;

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
		add_filter( 'render_block_core/navigation-link', array( $this, 'filter_navigation_link' ), 10, 2 );
		add_filter( 'render_block_core/navigation', array( $this, 'capture_navigation_context' ), 9, 2 );
		add_filter( 'render_block_data', array( $this, 'capture_navigation_context_data' ), 9, 2 );
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
	 * Hide navigation block links when conditions fail.
	 *
	 * @param string $block_content Rendered block HTML.
	 * @param array  $block Block instance.
	 *
	 * @return string
	 */
	public function filter_navigation_link( string $block_content, array $block ): string {
		if ( empty( $block['attrs'] ) || ! is_array( $block['attrs'] ) ) {
			return $block_content;
		}

		$attrs = $block['attrs'];

		// Only apply when the navigation context is present.
		$navigation_id = isset( $block['context']['navigationId'] ) ? (int) $block['context']['navigationId'] : 0;

		if ( $navigation_id <= 0 && isset( $block['context']['postId'] ) ) {
			$navigation_id = (int) $block['context']['postId'];
		}

		if ( $navigation_id <= 0 ) {
			if ( null !== self::$current_navigation_id ) {
				$navigation_id = (int) self::$current_navigation_id;
			}
		}

		if ( $navigation_id <= 0 ) {
			global $post;
			if ( $post && 'wp_navigation' === ( $post->post_type ?? '' ) ) {
				$navigation_id = (int) $post->ID;
			}
		}

		$link_key = NavigationSettingsRepository::key_from_attributes( $attrs );
		$settings = $navigation_id > 0 ? NavigationSettingsRepository::get( $navigation_id, $link_key ) : array(
			'pages'    => array(),
			'advanced' => array(),
		);

		if ( empty( $settings['pages'] ) && empty( $settings['advanced'] ) ) {
			$settings = $this->find_settings_by_link_key( $link_key );
		}

		$pages    = $settings['pages'] ?? array();
		$advanced = $settings['advanced'] ?? array();

		$visible = $this->evaluate_pages( $pages );
		if ( $visible ) {
			$visible = Advanced::match( $advanced );
		}

		return $visible ? $block_content : '';
	}

	/**
	 * Capture navigation context from the navigation block render.
	 *
	 * @param string $block_content Content.
	 * @param array  $block Block data.
	 *
	 * @return string
	 */
	public function capture_navigation_context( string $block_content, array $block ): string {
		self::$current_navigation_id = null;

		if ( ! empty( $block['attrs']['ref'] ) ) {
			self::$current_navigation_id = (int) $block['attrs']['ref'];
		} elseif ( isset( $block['context']['postId'] ) ) {
			self::$current_navigation_id = (int) $block['context']['postId'];
		}

		return $block_content;
	}

	/**
	 * Fallback lookup for a navigation link key across navigation posts.
	 *
	 * @param string $link_key Link key.
	 *
	 * @return array<string,array>
	 */
	private function find_settings_by_link_key( string $link_key ): array {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key,WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$posts = get_posts(
			array(
				'post_type'      => 'wp_navigation',
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => NavigationSettingsRepository::META,
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $posts as $nav_id ) {
			$all = NavigationSettingsRepository::get_all( (int) $nav_id );
			if ( isset( $all[ $link_key ] ) && is_array( $all[ $link_key ] ) ) {
				return $all[ $link_key ];
			}
		}

		return array(
			'pages'    => array(),
			'advanced' => array(),
		);
	}

	/**
	 * Capture navigation context via render_block_data for navigation blocks.
	 *
	 * @param array $parsed_block  Block data.
	 * @param array $_source_block Original source block (unused).
	 *
	 * @return array
	 */
	public function capture_navigation_context_data( array $parsed_block, array $_source_block ): array {
		unset( $_source_block );

		if ( 'core/navigation' === ( $parsed_block['blockName'] ?? '' ) ) {
			$nav_id = null;

			if ( isset( $parsed_block['attrs']['ref'] ) ) {
				$nav_id = (int) $parsed_block['attrs']['ref'];
			} elseif ( isset( $parsed_block['context']['postId'] ) ) {
				$nav_id = (int) $parsed_block['context']['postId'];
			}

			self::$current_navigation_id = $nav_id;
		}

		return $parsed_block;
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
