<?php
/**
 * AJAX controller for saving menu visibility settings.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Admin;

use MenuGhost\SettingsRepository;
use function absint;

/**
 * Handles persisting menu-level visibility options from the admin UI.
 */
final class SettingsController {
	/**
	 * Shared controller instance.
	 *
	 * @var SettingsController|null
	 */
	private static ?SettingsController $instance = null;

	/**
	 * Retrieve a shared instance.
	 *
	 * @since 1.1.0
	 *
	 * @return SettingsController
	 */
	public static function instance(): SettingsController {
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
	private function init(): void {
		add_action( 'wp_ajax_mghost_save_menu_settings', array( $this, 'save_menu_settings' ) );
	}

	/**
	 * Persist menu settings submitted over AJAX.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function save_menu_settings(): void {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Forbidden.', 'menu-ghost' ) ), 403 );
		}

		check_ajax_referer( 'menu_ghost', 'nonce' );

		$item_id = isset( $_POST['itemId'] )
			? absint( wp_unslash( (string) $_POST['itemId'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			: 0;

		if ( 0 === $item_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid menu item.', 'menu-ghost' ) ), 400 );
		}

		$pages_raw = array_key_exists( 'pages', $_POST )
			? wp_unslash( $_POST['pages'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();

		if ( is_string( $pages_raw ) ) {
			$decoded   = json_decode( $pages_raw, true );
			$pages_raw = is_array( $decoded ) ? $decoded : array();
		} elseif ( ! is_array( $pages_raw ) ) {
			$pages_raw = array();
		}

		$advanced_raw = array_key_exists( 'advanced', $_POST )
			? wp_unslash( $_POST['advanced'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();

		if ( is_string( $advanced_raw ) ) {
			$decoded      = json_decode( $advanced_raw, true );
			$advanced_raw = is_array( $decoded ) ? $decoded : array();
		} elseif ( ! is_array( $advanced_raw ) ) {
			$advanced_raw = array();
		}

		$pages_clean = array_values(
			array_filter(
				array_map(
					static function ( $condition ) {
						if ( ! is_array( $condition ) ) {
							return array();
						}

						return array(
							'type'            => sanitize_key( $condition['type'] ?? '' ),
							'scope'           => sanitize_key( $condition['scope'] ?? '' ),
							'subScope'        => sanitize_key( $condition['subScope'] ?? '' ),
							'additional'      => sanitize_text_field( (string) ( $condition['additional'] ?? '' ) ),
							'additionalLabel' => sanitize_text_field( (string) ( $condition['additionalLabel'] ?? '' ) ),
						);
					},
					$pages_raw
				),
				static fn( $condition ) => ! empty( $condition['type'] ) && ! empty( $condition['scope'] )
			)
		);

		$advanced_clean = array_map(
			static function ( $rule ) {
				if ( ! is_array( $rule ) ) {
					return array();
				}

				return array_map(
					static fn( $value ) => is_string( $value ) ? sanitize_text_field( $value ) : $value,
					$rule
				);
			},
			$advanced_raw
		);

		SettingsRepository::save_pages( $item_id, $pages_clean );
		SettingsRepository::save_advanced( $item_id, $advanced_clean );

		wp_send_json_success( array( 'message' => __( 'Settings saved.', 'menu-ghost' ) ) );
	}
}
