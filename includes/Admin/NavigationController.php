<?php
/**
 * REST controller for navigation block link visibility settings.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Admin;

use MenuGhost\NavigationSettingsRepository;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use function absint;
use function current_user_can;
use function json_decode;
use function sanitize_key;
use function sanitize_text_field;
use function wp_json_encode;

/**
 * Handles persisting and fetching visibility settings for navigation links (block themes).
 */
final class NavigationController {
	/**
	 * Register REST endpoints.
	 *
	 * @return void
	 */
	public static function register(): void {
		$instance = new self();

		add_action(
			'rest_api_init',
			static function () use ( $instance ) {
				register_rest_route(
					'menu-ghost/v1',
					'/navigation/(?P<navigation_id>\\d+)/(?P<link_key>[A-Za-z0-9:_\\-]+)/settings',
					array(
						array(
							'methods'             => WP_REST_Server::READABLE,
							'callback'            => array( $instance, 'get_settings' ),
							'permission_callback' => array( $instance, 'can_edit' ),
						),
						array(
							'methods'             => WP_REST_Server::CREATABLE,
							'callback'            => array( $instance, 'save_settings' ),
							'permission_callback' => array( $instance, 'can_edit' ),
						),
					)
				);
			}
		);
	}

	/**
	 * Permission callback for navigation settings.
	 *
	 * @return bool
	 */
	public function can_edit(): bool {
		return current_user_can( 'edit_theme_options' );
	}

	/**
	 * Fetch saved settings for a navigation link.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings( WP_REST_Request $request ): WP_REST_Response {
		$navigation_id = absint( $request->get_param( 'navigation_id' ) );
		$link_key      = sanitize_text_field( (string) $request->get_param( 'link_key' ) );

		$settings = NavigationSettingsRepository::get( $navigation_id, $link_key );

		return new WP_REST_Response(
			array(
				'pages'    => $settings['pages'] ?? array(),
				'advanced' => $settings['advanced'] ?? array(),
			)
		);
	}

	/**
	 * Save settings for a navigation link.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function save_settings( WP_REST_Request $request ): WP_REST_Response {
		$navigation_id = absint( $request->get_param( 'navigation_id' ) );
		$link_key      = sanitize_text_field( (string) $request->get_param( 'link_key' ) );

		$payload = json_decode( $request->get_body(), true );
		$payload = is_array( $payload ) ? $payload : array();

		$pages_raw    = $payload['pages'] ?? array();
		$advanced_raw = $payload['advanced'] ?? array();

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
			is_array( $advanced_raw ) ? $advanced_raw : array()
		);

		NavigationSettingsRepository::save( $navigation_id, $link_key, $pages_clean, $advanced_clean );

		return new WP_REST_Response(
			array(
				'message' => __( 'Settings saved.', 'menu-ghost' ),
				'echo'    => wp_json_encode(
					array(
						'pages'    => $pages_clean,
						'advanced' => $advanced_clean,
					)
				),
			)
		);
	}
}
