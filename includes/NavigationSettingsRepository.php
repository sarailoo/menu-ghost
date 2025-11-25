<?php
/**
 * Persistence helpers for navigation block link visibility settings.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost;

use function get_post_meta;
use function is_array;
use function sanitize_key;
use function sanitize_text_field;
use function update_post_meta;
use function wp_json_encode;

/**
 * Repository for reading and writing navigation link settings stored on wp_navigation posts.
 */
final class NavigationSettingsRepository {
	public const META = '_mngh_navigation_settings';

	/**
	 * Build a deterministic key for a navigation link.
	 *
	 * @param array<string,mixed> $attrs Navigation link block attributes.
	 *
	 * @return string
	 */
	public static function key_from_attributes( array $attrs ): string {
		if ( ! empty( $attrs['id'] ) ) {
			return 'id:' . sanitize_key( (string) $attrs['id'] );
		}

		if ( ! empty( $attrs['ref'] ) ) {
			return 'ref:' . sanitize_key( (string) $attrs['ref'] );
		}

		if ( ! empty( $attrs['url'] ) ) {
			return 'url:' . self::hash_value( (string) $attrs['url'] );
		}

		if ( ! empty( $attrs['label'] ) ) {
			return 'label:' . self::hash_value( sanitize_text_field( (string) $attrs['label'] ) );
		}

		$key = $attrs['kind'] ?? $attrs['type'] ?? 'nav-link';

		return sanitize_key( (string) $key ) . ':' . self::hash_value( wp_json_encode( $attrs ) );
	}

	/**
	 * Generate a URL-safe deterministic hash.
	 *
	 * @param string $value Value to hash.
	 *
	 * @return string
	 */
	private static function hash_value( string $value ): string {
		return rtrim(
			strtr(
				// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				base64_encode( $value ),
				'+/',
				'-_'
			),
			'='
		);
	}

	/**
	 * Retrieve settings for a navigation link inside a navigation post.
	 *
	 * @param int    $navigation_id Navigation post ID (wp_navigation).
	 * @param string $link_key      Deterministic link key.
	 *
	 * @return array<string,array>
	 */
	public static function get( int $navigation_id, string $link_key ): array {
		$all = self::get_all( $navigation_id );

		return $all[ $link_key ] ?? array(
			'pages'    => array(),
			'advanced' => array(),
		);
	}

	/**
	 * Persist settings for a single navigation link.
	 *
	 * @param int              $navigation_id Navigation post ID (wp_navigation).
	 * @param string           $link_key      Deterministic link key.
	 * @param array<int,array> $pages         Sanitized page conditions.
	 * @param array<int,array> $advanced      Sanitized advanced rules.
	 *
	 * @return void
	 */
	public static function save( int $navigation_id, string $link_key, array $pages, array $advanced ): void {
		$all = self::get_all( $navigation_id );

		$all[ $link_key ] = array(
			'pages'    => array_values( $pages ),
			'advanced' => array_values( $advanced ),
		);

		update_post_meta( $navigation_id, self::META, $all );
	}

	/**
	 * Retrieve all navigation link settings for a navigation post.
	 *
	 * @param int $navigation_id Navigation post ID (wp_navigation).
	 *
	 * @return array<string,array>
	 */
	public static function get_all( int $navigation_id ): array {
		$settings = get_post_meta( $navigation_id, self::META, true );

		return is_array( $settings ) ? $settings : array();
	}
}
