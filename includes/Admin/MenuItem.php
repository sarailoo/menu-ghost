<?php
/**
 * Admin hooks for menu-item visibility fields.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Admin;

use WP_Post;
use WPMenuControl\SettingsRepository;

/**
 * MenuItem class handles custom fields and visibility logic for menu items.
 */
class MenuItem {
	/**
	 * Shared controller instance.
	 *
	 * @var MenuItem|null
	 */
	private static ?MenuItem $instance = null;

	/**
	 * Return a shared controller instance.
	 *
	 * @return MenuItem
	 */
	public static function instance(): MenuItem {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'menu_item_fields' ), 10, 2 );
		add_action( 'wp_ajax_save_menu_conditions', array( $this, 'save_menu_conditions' ) );
	}

	/**
	 * Output the placeholder element the React app mounts into.
	 *
	 * @param int     $item_id Menu item ID.
	 * @param WP_Post $item Menu item object.
	 *
	 * @return void
	 */
	public function menu_item_fields( int $item_id, WP_Post $item ): void {
		unset( $item );

		$unique_id = 'menu-control-' . (int) $item_id;
		echo '<div id="' . esc_attr( $unique_id ) . '" class="menu-control-item-button-wrap"></div>';
	}

	/**
	 * Save menu item visibility conditions via AJAX.
	 *
	 * @return void
	 */
	public function save_menu_conditions(): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Forbidden.', 'menu-control' ) ), 403 );
		}

		$nonce = isset( $_POST['nonce'] )
			? sanitize_key( wp_unslash( (string) $_POST['nonce'] ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing
			: '';

		if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'menu_control' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'menu-control' ) ), 400 );
		}

		$item_id = isset( $_POST['itemId'] )
			? (int) wp_unslash( (string) $_POST['itemId'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing
			: 0;

		$raw_conditions = isset( $_POST['conditions'] )
			? wp_unslash( $_POST['conditions'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();

		if ( is_string( $raw_conditions ) ) {
			$decoded        = json_decode( $raw_conditions, true );
			$raw_conditions = is_array( $decoded ) ? $decoded : array();
		}

		if ( 0 === $item_id || ! is_array( $raw_conditions ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid menu item.', 'menu-control' ) ), 400 );
		}

		$sanitized = array();

		foreach ( $raw_conditions as $condition ) {
			if ( ! is_array( $condition ) ) {
				continue;
			}

			$clean = array(
				'type'            => sanitize_key( $condition['type'] ?? '' ),
				'scope'           => sanitize_key( $condition['scope'] ?? '' ),
				'subScope'        => sanitize_key( $condition['subScope'] ?? '' ),
				'additional'      => sanitize_text_field( (string) ( $condition['additional'] ?? '' ) ),
				'additionalLabel' => sanitize_text_field( (string) ( $condition['additionalLabel'] ?? '' ) ),
			);

			if ( '' === $clean['type'] || '' === $clean['scope'] ) {
				continue;
			}

			$sanitized[] = $clean;
		}

		SettingsRepository::save_pages( $item_id, $sanitized );

		wp_send_json_success( array( 'message' => __( 'Conditions saved.', 'menu-control' ) ) );
	}
}
