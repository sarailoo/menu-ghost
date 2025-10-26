<?php
/**
 * Persistence helpers for menu visibility settings.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost;

/**
 * Repository for reading and writing menu settings stored in post meta.
 */
final class SettingsRepository {
	public const META = '_mngh_menu_settings';

	/**
	 * Retrieve settings for a menu item.
	 *
	 * @param int $item_id Menu item ID.
	 *
	 * @return array<string,array>
	 */
	public static function get( int $item_id ): array {
		$settings = get_post_meta( $item_id, self::META, true );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		if ( ! isset( $settings['pages'] ) || ! is_array( $settings['pages'] ) ) {
			$settings['pages'] = array();
		}
		if ( ! isset( $settings['advanced'] ) || ! is_array( $settings['advanced'] ) ) {
			$settings['advanced'] = array();
		}
		return $settings;
	}

	/**
	 * Overwrite the page conditions for a menu item.
	 *
	 * @param int   $item_id Menu item ID.
	 * @param array $pages   Sanitized page conditions.
	 *
	 * @return void
	 */
	public static function save_pages( int $item_id, array $pages ): void {
		$settings          = self::get( $item_id );
		$settings['pages'] = array_values( $pages );
		update_post_meta( $item_id, self::META, $settings );
	}

	/**
	 * Overwrite the advanced rules for a menu item.
	 *
	 * @param int   $item_id Menu item ID.
	 * @param array $rules   Sanitized advanced rules.
	 *
	 * @return void
	 */
	public static function save_advanced( int $item_id, array $rules ): void {
		$settings             = self::get( $item_id );
		$settings['advanced'] = array_values( $rules );
		update_post_meta( $item_id, self::META, $settings );
	}

	/**
	 * Retrieve settings for multiple menu items.
	 *
	 * @param array<int> $item_ids Menu item IDs.
	 *
	 * @return array<int,array>
	 */
	public static function get_many( array $item_ids ): array {
		$out = array();
		foreach ( $item_ids as $id ) {
			$out[ (int) $id ] = self::get( (int) $id );
		}
		return $out;
	}
}
