<?php
/**
 * REST API search endpoints powering the admin selectors.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Admin;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function __;
use function absint;

/**
 * Registers REST API endpoints used by the admin async search controls.
 */
class SearchController {
	/**
	 * Bootstrap the REST endpoints.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Register REST routes for searching authors, posts, and terms.
	 *
	 * @return void
	 */
	public static function register_routes(): void {
		register_rest_route(
			'wp-menu-control/v1',
			'/search',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'handle_search' ),
				'permission_callback' => static function (): bool {
					return current_user_can( 'edit_theme_options' );
				},
				'args'                => array(
					'type'      => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
					),
					'id'        => array(
						'sanitize_callback' => 'absint',
					),
					'page'      => array(
						'sanitize_callback' => 'absint',
					),
					'per_page'  => array(
						'sanitize_callback' => 'absint',
					),
					'post_type' => array(
						'sanitize_callback' => 'sanitize_key',
					),
					'taxonomy'  => array(
						'sanitize_callback' => 'sanitize_key',
					),
					'search'    => array(
						'sanitize_callback' => '\\WPMenuControl\\Admin\\SearchController::sanitize_search',
					),
				),
			)
		);
	}

	/**
	 * Sanitize user-entered search terms.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return string
	 */
	public static function sanitize_search( $value ): string {
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Handle search requests for authors, posts, and terms.
	 *
	 * @param WP_REST_Request $request Incoming request.
	 *
	 * @return WP_REST_Response
	 */
	public static function handle_search( WP_REST_Request $request ): WP_REST_Response {
		$type      = (string) $request->get_param( 'type' );
		$search    = (string) $request->get_param( 'search' );
		$page      = max( 1, (int) $request->get_param( 'page' ) );
		$per_page  = (int) $request->get_param( 'per_page' );
		$post_type = $request->get_param( 'post_type' );
		$taxonomy  = $request->get_param( 'taxonomy' );
		$item_id   = absint( $request->get_param( 'id' ) );

		if ( $per_page <= 0 ) {
			$per_page = 20;
		}

		if ( 0 !== $item_id ) {
			$item = self::get_single_item(
				$type,
				$item_id,
				is_string( $post_type ) ? $post_type : null,
				is_string( $taxonomy ) ? $taxonomy : null
			);

			$items = array();

			if ( null !== $item ) {
				$items[] = $item;
			}

			return new WP_REST_Response( array( 'items' => $items ) );
		}

		switch ( $type ) {
			case 'author':
				$items = self::search_authors( $search, $page, $per_page );
				break;
			case 'post':
				$items = self::search_posts( is_string( $post_type ) ? $post_type : null, $search, $page, $per_page );
				break;
			case 'term':
				$items = self::search_terms( is_string( $taxonomy ) ? $taxonomy : null, $search, $page, $per_page );
				break;
			default:
				$items = array();
		}

		return new WP_REST_Response( array( 'items' => $items ) );
	}

	/**
	 * Fetch a single entity by ID.
	 *
	 * @param string      $type      Entity type (author, post, term).
	 * @param int         $id        Entity ID.
	 * @param string|null $post_type Optional post type.
	 * @param string|null $taxonomy  Optional taxonomy.
	 *
	 * @return array<string,string>|null
	 */
	private static function get_single_item( string $type, int $id, ?string $post_type, ?string $taxonomy ): ?array {
		switch ( $type ) {
			case 'author':
				$user = get_user_by( 'id', $id );
				if ( ! $user instanceof \WP_User ) {
					return null;
				}
				return array(
					'value' => (string) $user->ID,
					'label' => $user->display_name,
				);
			case 'post':
				$post = get_post( $id );
				if ( ! $post instanceof \WP_Post ) {
					return null;
				}
				if ( $post_type && $post->post_type !== $post_type ) {
					return null;
				}

				$label = $post->post_title;
				if ( '' === $label ) {
					/* translators: %d: post ID. */
					$label = sprintf( __( 'Untitled (%d)', 'wp-menu-control' ), $post->ID );
				}

				return array(
					'value' => (string) $post->ID,
					'label' => $label,
				);
			case 'term':
				$term = get_term( $id, $taxonomy ? $taxonomy : '' );
				if ( ! $term instanceof \WP_Term ) {
					return null;
				}
				return array(
					'value' => (string) $term->term_id,
					'label' => $term->name,
				);
		}

		return null;
	}

	/**
	 * Search users with author roles.
	 *
	 * @param string $search   Optional search string.
	 * @param int    $page     Results page.
	 * @param int    $per_page Items per page.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function search_authors( string $search, int $page, int $per_page ): array {
		$args = array(
			'number'  => $per_page,
			'paged'   => $page,
			'orderby' => 'display_name',
			'order'   => 'ASC',
			'who'     => 'authors',
		);

		if ( '' !== $search ) {
			$args['search']         = '*' . esc_attr( $search ) . '*';
			$args['search_columns'] = array( 'display_name', 'user_login', 'user_email' );
		}

		$query = new \WP_User_Query( $args );
		$users = $query->get_results();

		return array_map(
			static function ( \WP_User $user ): array {
				return array(
					'value' => (string) $user->ID,
					'label' => $user->display_name,
				);
			},
			$users
		);
	}

	/**
	 * Search posts within a post type.
	 *
	 * @param string|null $post_type Post type slug.
	 * @param string      $search    Optional search string.
	 * @param int         $page      Results page.
	 * @param int         $per_page  Items per page.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function search_posts( ?string $post_type, string $search, int $page, int $per_page ): array {
		$resolved_post_type = $post_type ? $post_type : 'post';

		$args = array(
			'post_type'      => $resolved_post_type,
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		);

		if ( '' !== $search ) {
			$args['s'] = $search;
		}

		$query = new \WP_Query( $args );

		return array_map(
			static function ( \WP_Post $post ): array {
				$label = $post->post_title;

				if ( '' === $label ) {
					/* translators: %d: post ID. */
					$label = sprintf( __( 'Untitled (%d)', 'wp-menu-control' ), $post->ID );
				}

				return array(
					'value' => (string) $post->ID,
					'label' => $label,
				);
			},
			$query->posts
		);
	}

	/**
	 * Search taxonomy terms.
	 *
	 * @param string|null $taxonomy Taxonomy slug.
	 * @param string      $search   Optional search string.
	 * @param int         $page     Results page.
	 * @param int         $per_page Items per page.
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function search_terms( ?string $taxonomy, string $search, int $page, int $per_page ): array {
		if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			return array();
		}

		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'number'     => $per_page,
			'offset'     => ( $page - 1 ) * $per_page,
			'orderby'    => 'name',
			'order'      => 'ASC',
		);

		if ( '' !== $search ) {
			$args['search'] = $search;
		}

		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		return array_map(
			static function ( \WP_Term $term ): array {
				return array(
					'value' => (string) $term->term_id,
					'label' => $term->name,
				);
			},
			$terms
		);
	}
}
