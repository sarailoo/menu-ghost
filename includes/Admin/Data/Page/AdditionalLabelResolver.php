<?php
/**
 * Resolve saved condition labels for presentation in the UI.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Admin\Data\Page;

use function __;

/**
 * Resolves and assigns additional labels for saved conditions.
 *
 * @since 1.1.0
 */
class AdditionalLabelResolver {
	/**
	 * Populate missing additional labels on persisted conditions.
	 *
	 * @since 1.1.0
	 *
	 * @param array<int,array<string,mixed>>                  $conditions Saved conditions.
	 * @param array<string,array<string,array<string,mixed>>> $lookup Lookup data generated from scopes.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function populate( array $conditions, array $lookup ): array {
		return array_map(
			static function ( array $condition ) use ( $lookup ): array {
				if ( ! empty( $condition['additionalLabel'] ) ) {
					return $condition;
				}

				$label = self::resolve(
					(string) ( $condition['scope'] ?? '' ),
					(string) ( $condition['subScope'] ?? '' ),
					(string) ( $condition['additional'] ?? '' ),
					$lookup
				);

				if ( '' !== $label ) {
					$condition['additionalLabel'] = $label;
				}

				return $condition;
			},
			$conditions
		);
	}

	/**
	 * Resolve a label for a specific saved condition.
	 *
	 * @since 1.1.0
	 *
	 * @param string                                          $scope     Scope slug.
	 * @param string                                          $sub_scope Sub-scope slug.
	 * @param string                                          $value     Additional value identifier.
	 * @param array<string,array<string,array<string,mixed>>> $lookup Lookup data.
	 *
	 * @return string
	 */
	public static function resolve( string $scope, string $sub_scope, string $value, array $lookup ): string {
		if ( '' === $scope || '' === $value ) {
			return '';
		}

		$config = $lookup[ $scope ][ $sub_scope ] ?? null;

		if ( null === $config ) {
			return '';
		}

		if ( 'list' === ( $config['mode'] ?? '' ) ) {
			foreach ( (array) ( $config['list'] ?? array() ) as $item ) {
				if ( ! isset( $item['value'] ) ) {
					continue;
				}

				if ( (string) $item['value'] === $value ) {
					return (string) ( $item['label'] ?? '' );
				}
			}

			return '';
		}

		if ( 'async' === ( $config['mode'] ?? '' ) ) {
			return self::resolve_async_label(
				(string) ( $config['type'] ?? '' ),
				(array) ( $config['params'] ?? array() ),
				$value
			);
		}

		return '';
	}

	/**
	 * Resolve a label by querying WordPress for the referenced entity.
	 *
	 * @since 1.1.0
	 *
	 * @param string              $type   Async entity type.
	 * @param array<string,mixed> $params Async parameters.
	 * @param string              $value  Entity identifier.
	 *
	 * @return string
	 */
	private static function resolve_async_label( string $type, array $params, string $value ): string {
		$id = (int) $value;

		if ( 0 >= $id ) {
			return '';
		}

		switch ( $type ) {
			case 'author':
				$user = get_user_by( 'id', $id );
				return $user instanceof \WP_User ? $user->display_name : '';

			case 'post':
				$post = get_post( $id );

				if ( ! $post instanceof \WP_Post ) {
					return '';
				}

				$post_type = $params['post_type'] ?? null;
				if ( $post_type && $post->post_type !== $post_type ) {
					return '';
				}

				if ( '' !== $post->post_title ) {
					return $post->post_title;
				}

				/* translators: %d: post ID. */
				return sprintf( __( 'Untitled (%d)', 'menu-control' ), $post->ID );

			case 'term':
				$taxonomy = $params['taxonomy'] ?? '';

				if ( '' === $taxonomy ) {
					return '';
				}

				$term = get_term( $id, (string) $taxonomy );

				return $term instanceof \WP_Term ? $term->name : '';
		}

		return '';
	}
}
