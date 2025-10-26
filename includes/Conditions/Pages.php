<?php
/**
 * Conditional checks for page-based scopes.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Conditions;

/**
 * Evaluates page visibility scope conditions against the current request.
 *
 * @since 1.1.0
 */
class Pages {
	/**
	 * Determine whether the supplied condition matches the current request.
	 *
	 * @since 1.1.0
	 *
	 * @param string $scope      The top-level scope identifier.
	 * @param string $sub_scope  Optional sub-scope value.
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	public static function match( string $scope, string $sub_scope, string $additional ): bool {
		if ( 'entire_site' === $scope ) {
			return true;
		}

		if ( 'singular' === $scope || str_starts_with( $scope, 'singular_' ) ) {
			return self::match_singular( $sub_scope, $additional );
		}

		if ( 'archive' === $scope || str_starts_with( $scope, 'archive_' ) ) {
			return self::match_archive( $sub_scope, $additional );
		}

		return false;
	}

	/**
	 * Evaluate singular page conditions.
	 *
	 * @since 1.1.0
	 *
	 * @param string $sub_scope  Singular sub-scope.
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_singular( string $sub_scope, string $additional ): bool {
		if ( '' === $sub_scope ) {
			$sub_scope = 'singular_all';
		}

		if ( 'singular_all' === $sub_scope ) {
			return is_singular();
		}

		if ( 'child_of' === $sub_scope ) {
			return self::match_child_of_page( $additional );
		}

		if ( 'any_child_of' === $sub_scope ) {
			return self::match_any_child_of_page( $additional );
		}

		if ( 'by_author' === $sub_scope ) {
			return self::match_by_author( $additional );
		}

		if ( 'not_found404' === $sub_scope ) {
			return is_404();
		}

		if ( 'front_page' === $sub_scope ) {
			return is_front_page();
		}

		if ( str_starts_with( $sub_scope, 'singular_' ) ) {
			$selector = substr( $sub_scope, strlen( 'singular_' ) );

			return self::match_singular_selector( $selector, $additional );
		}

		return false;
	}

	/**
	 * Evaluate archive conditions.
	 *
	 * @since 1.1.0
	 *
	 * @param string $sub_scope  Archive sub-scope.
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_archive( string $sub_scope, string $additional ): bool {
		if ( '' === $sub_scope ) {
			$sub_scope = 'archive_all';
		}

		if ( 'archive_all' === $sub_scope ) {
			return is_archive() || is_home();
		}

		if ( 'archive_author' === $sub_scope ) {
			$ids = self::parse_ids( $additional );

			return ! empty( $ids ) ? is_author( $ids ) : is_author();
		}

		if ( 'archive_date' === $sub_scope ) {
			return is_date();
		}

		if ( 'archive_search' === $sub_scope ) {
			return is_search();
		}

		if ( str_starts_with( $sub_scope, 'child_of_' ) ) {
			$descriptor = substr( $sub_scope, strlen( 'child_of_' ) );

			return self::match_archive_child_of( $descriptor, $additional, false );
		}

		if ( str_starts_with( $sub_scope, 'any_child_of_' ) ) {
			$descriptor = substr( $sub_scope, strlen( 'any_child_of_' ) );

			return self::match_archive_child_of( $descriptor, $additional, true );
		}

		if ( str_starts_with( $sub_scope, 'archive_' ) ) {
			$rest = substr( $sub_scope, strlen( 'archive_' ) );

			return self::match_archive_rest( $rest, $additional );
		}

		return false;
	}

	/**
	 * Handle singular selectors such as singular_post or singular_post_in_category.
	 *
	 * @since 1.1.0
	 *
	 * @param string $selector   Parsed selector (without the singular_ prefix).
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_singular_selector( string $selector, string $additional ): bool {
		if ( '' === $selector ) {
			return false;
		}

		if ( str_contains( $selector, '_by_author' ) ) {
			$post_type = str_replace( '_by_author', '', $selector );

			return self::match_post_type_author( $post_type, $additional );
		}

		if ( str_contains( $selector, '_in_child_' ) ) {
			list( $post_type, $taxonomy ) = explode( '_in_child_', $selector, 2 );

			return self::match_post_type_child_term( $post_type, $taxonomy, $additional );
		}

		if ( str_contains( $selector, '_in_' ) ) {
			list( $post_type, $taxonomy ) = explode( '_in_', $selector, 2 );

			return self::match_post_type_term( $post_type, $taxonomy, $additional );
		}

		return self::match_post_type( $selector, $additional );
	}

	/**
	 * Match a specific post type singular view.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_type  Post type slug.
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_post_type( string $post_type, string $additional ): bool {
		if ( ! is_singular( $post_type ) ) {
			return false;
		}

		$ids = self::parse_ids( $additional );

		if ( empty( $ids ) ) {
			return true;
		}

		return in_array( (int) get_queried_object_id(), $ids, true );
	}

	/**
	 * Match a singular request restricted to specific authors for a post type.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_type  Post type slug.
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_post_type_author( string $post_type, string $additional ): bool {
		$post_id = (int) get_queried_object_id();
		if ( 0 === $post_id ) {
			return false;
		}

		if ( get_post_type( $post_id ) !== $post_type ) {
			return false;
		}

		$author_id = (int) get_post_field( 'post_author', $post_id );
		$ids       = self::parse_ids( $additional );

		if ( empty( $ids ) ) {
			return true;
		}

		return in_array( $author_id, $ids, true );
	}

	/**
	 * Match a singular request within child terms of a taxonomy.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_type  Post type slug.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_post_type_child_term( string $post_type, string $taxonomy, string $additional ): bool {
		$post_id = (int) get_queried_object_id();
		if ( 0 === $post_id ) {
			return false;
		}

		if ( get_post_type( $post_id ) !== $post_type ) {
			return false;
		}

		$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'all' ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return false;
		}

		$ids = self::parse_ids( $additional );

		foreach ( $terms as $term ) {
			if ( ! $term instanceof \WP_Term || 0 === (int) $term->parent ) {
				continue;
			}

			if ( empty( $ids ) ) {
				return true;
			}

			if ( in_array( (int) $term->parent, $ids, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Match a singular request within specific taxonomy terms.
	 *
	 * @since 1.1.0
	 *
	 * @param string $post_type  Post type slug.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_post_type_term( string $post_type, string $taxonomy, string $additional ): bool {
		$post_id = (int) get_queried_object_id();
		if ( 0 === $post_id ) {
			return false;
		}

		if ( get_post_type( $post_id ) !== $post_type ) {
			return false;
		}

		$ids = self::parse_ids( $additional );

		if ( ! empty( $ids ) ) {
			return has_term( $ids, $taxonomy, $post_id );
		}

		if ( has_term( '', $taxonomy, $post_id ) ) {
			return true;
		}

		return is_singular( $post_type );
	}

	/**
	 * Match any child page relative to a set of parent page IDs.
	 *
	 * @since 1.1.0
	 *
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_child_of_page( string $additional ): bool {
		$post_id = (int) get_queried_object_id();
		if ( 0 === $post_id || 'page' !== get_post_type( $post_id ) ) {
			return false;
		}

		$parent_id = (int) get_post_field( 'post_parent', $post_id );
		$ids       = self::parse_ids( $additional );

		if ( ! empty( $ids ) ) {
			return 0 !== $parent_id && in_array( $parent_id, $ids, true );
		}

		return 0 !== $parent_id;
	}

	/**
	 * Match any descendant page relative to a set of ancestor IDs.
	 *
	 * @since 1.1.0
	 *
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_any_child_of_page( string $additional ): bool {
		$post_id = (int) get_queried_object_id();
		if ( 0 === $post_id || 'page' !== get_post_type( $post_id ) ) {
			return false;
		}

		$ancestors = get_post_ancestors( $post_id );
		$ids       = self::parse_ids( $additional );

		if ( ! empty( $ids ) ) {
			$intersection = array_intersect(
				array_map( 'intval', $ancestors ),
				array_map( 'intval', $ids )
			);

			return ! empty( $intersection );
		}

		return ! empty( $ancestors );
	}

	/**
	 * Match a singular request by author for any post type.
	 *
	 * @since 1.1.0
	 *
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_by_author( string $additional ): bool {
		$post_id = (int) get_queried_object_id();
		if ( 0 === $post_id ) {
			return false;
		}

		$author_id = (int) get_post_field( 'post_author', $post_id );
		$ids       = self::parse_ids( $additional );

		if ( empty( $ids ) ) {
			return is_singular();
		}

		return in_array( $author_id, $ids, true );
	}

	/**
	 * Match archive child or descendant taxonomy conditions.
	 *
	 * @since 1.1.0
	 *
	 * @param string $descriptor Combined post type and taxonomy descriptor.
	 * @param string $additional Additional identifier or list of identifiers.
	 * @param bool   $any_depth  Whether to match against any ancestor depth.
	 *
	 * @return bool
	 */
	private static function match_archive_child_of( string $descriptor, string $additional, bool $any_depth ): bool {
		list( , $taxonomy ) = explode( '_', $descriptor, 2 );

		if ( ! self::is_tax_query_for( $taxonomy ) ) {
			return false;
		}

		$term = get_queried_object();
		if ( ! $term instanceof \WP_Term ) {
			return false;
		}

		$ids = self::parse_ids( $additional );

		if ( $any_depth ) {
			$ancestors = get_ancestors( (int) $term->term_id, $term->taxonomy );

			if ( empty( $ids ) ) {
				return ! empty( $ancestors );
			}

			$intersection = array_intersect(
				array_map( 'intval', $ancestors ),
				array_map( 'intval', $ids )
			);

			return ! empty( $intersection );
		}

		if ( empty( $ids ) ) {
			return 0 !== (int) $term->parent;
		}

		return in_array( (int) $term->parent, $ids, true );
	}

	/**
	 * Match archive sub-scopes beginning with archive_.
	 *
	 * @since 1.1.0
	 *
	 * @param string $rest       Remainder of the scope.
	 * @param string $additional Additional identifier or list of identifiers.
	 *
	 * @return bool
	 */
	private static function match_archive_rest( string $rest, string $additional ): bool {
		if ( 'post' === $rest ) {
			return is_home();
		}

		if ( str_starts_with( $rest, 'child_of_' ) ) {
			$descriptor = substr( $rest, strlen( 'child_of_' ) );

			return self::match_archive_child_of( $descriptor, $additional, false );
		}

		if ( str_starts_with( $rest, 'any_child_of_' ) ) {
			$descriptor = substr( $rest, strlen( 'any_child_of_' ) );

			return self::match_archive_child_of( $descriptor, $additional, true );
		}

		if ( str_contains( $rest, '_' ) ) {
			list( , $taxonomy ) = explode( '_', $rest, 2 );
			if ( ! self::is_tax_query_for( $taxonomy ) ) {
				return false;
			}

			$ids = self::parse_ids( $additional );

			return self::match_tax_archive( $taxonomy, $ids );
		}

		return is_post_type_archive( $rest );
	}

	/**
	 * Parse an additional value into an array of integer IDs.
	 *
	 * @since 1.1.0
	 *
	 * @param string|array|null $raw Raw identifier payload.
	 *
	 * @return array<int>
	 */
	private static function parse_ids( string|array|null $raw ): array {
		if ( null === $raw ) {
			return array();
		}

		$tokens = is_array( $raw )
			? $raw
			: preg_split( '/\s*,\s*/', trim( (string) $raw ), -1, PREG_SPLIT_NO_EMPTY );

		if ( empty( $tokens ) ) {
			return array();
		}

		$ids = array();

		foreach ( $tokens as $token ) {
			$token        = trim( (string) $token );
			$lower_token  = strtolower( $token );
			$ignored_list = array( '', 'all', 'any', '*', '0' );

			if ( in_array( $lower_token, $ignored_list, true ) ) {
				continue;
			}

			if ( preg_match( '/^\d+$/', $token ) ) {
				$ids[] = (int) $token;
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Ensure the current query relates to the requested taxonomy.
	 *
	 * @since 1.1.0
	 *
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return bool
	 */
	private static function is_tax_query_for( string $taxonomy ): bool {
		if ( 'category' === $taxonomy ) {
			return is_category();
		}

		if ( 'post_tag' === $taxonomy ) {
			return is_tag();
		}

		return is_tax( $taxonomy );
	}

	/**
	 * Match taxonomy archive conditions.
	 *
	 * @since 1.1.0
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @param array  $ids      Optional list of term IDs.
	 *
	 * @return bool
	 */
	private static function match_tax_archive( string $taxonomy, array $ids ): bool {
		if ( ! empty( $ids ) ) {
			if ( 'category' === $taxonomy ) {
				return is_category( $ids );
			}

			if ( 'post_tag' === $taxonomy ) {
				return is_tag( $ids );
			}

			return is_tax( $taxonomy, $ids );
		}

		if ( 'category' === $taxonomy ) {
			return is_category();
		}

		if ( 'post_tag' === $taxonomy ) {
			return is_tag();
		}

		return is_tax( $taxonomy );
	}
}
