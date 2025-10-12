<?php
/**
 * Handles saving advanced visibility rules from the admin UI.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Admin;

use WPMenuControl\SettingsRepository;

/**
 * Controller that persists advanced visibility rules over AJAX.
 */
class AdvancedController {
	/**
	 * Shared controller instance.
	 *
	 * @var AdvancedController|null
	 */
	private static ?AdvancedController $instance = null;

	/**
	 * Retrieve a shared controller instance and bootstrap hooks.
	 *
	 * @since 1.1.0
	 *
	 * @return AdvancedController
	 */
	public static function instance(): AdvancedController {
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
	public static function init(): void {
		add_action( 'wp_ajax_save_menu_rules', array( __CLASS__, 'save_menu_rules' ) );
	}

	/**
	 * Persist advanced rules for a menu item via AJAX.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public static function save_menu_rules(): void {
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

		$raw_rules = isset( $_POST['rules'] )
			? wp_unslash( $_POST['rules'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();

		if ( is_string( $raw_rules ) ) {
			$decoded   = json_decode( $raw_rules, true );
			$raw_rules = is_array( $decoded ) ? $decoded : array();
		}

		if ( ! is_array( $raw_rules ) || 0 === $item_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request payload.', 'menu-control' ) ), 400 );
		}

		$clean_rules = array();

		foreach ( $raw_rules as $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}

			$clean_rules[] = array(
				'key'     => sanitize_key( $rule['key'] ?? '' ),
				'enabled' => ! empty( $rule['enabled'] ),
				'params'  => isset( $rule['params'] ) && is_array( $rule['params'] )
					? self::sanitize_params( $rule['params'] )
					: array(),
			);
		}

		SettingsRepository::save_advanced( $item_id, $clean_rules );

		wp_send_json_success( array( 'message' => __( 'Rules saved.', 'menu-control' ) ) );
	}

	/**
	 * Recursively sanitize rule parameters.
	 *
	 * @since 1.1.0
	 *
	 * @param array<mixed> $params Raw parameters.
	 *
	 * @return array<mixed>
	 */
	private static function sanitize_params( array $params ): array {
		$clean = array();

		foreach ( $params as $key => $value ) {
			$clean_key = is_string( $key ) ? sanitize_key( $key ) : $key;

			if ( is_array( $value ) ) {
				$clean[ $clean_key ] = self::sanitize_params( $value );
			} elseif ( is_scalar( $value ) ) {
				$clean[ $clean_key ] = sanitize_text_field( (string) $value );
			}
		}

		return $clean;
	}
}
