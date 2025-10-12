<?php
/**
 * Builds lookup tables for additional condition metadata.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Admin\Data\Page;

/**
 * Builds a lookup table mapping scope/sub-scope to renderer metadata.
 *
 * @since 1.1.0
 */
class AdditionalLookupBuilder {
	/**
	 * Build the lookup structure from scope definitions.
	 *
	 * @since 1.1.0
	 *
	 * @param array<int,array<string,mixed>> $scopes Scope definitions.
	 *
	 * @return array<string,array<string,array<string,mixed>>>
	 */
	public static function from_scopes( array $scopes ): array {
		$lookup = array();

		foreach ( $scopes as $scope ) {
			$scope_value = isset( $scope['value'] ) ? (string) $scope['value'] : '';

			if ( '' === $scope_value ) {
				continue;
			}

			$additional_data = isset( $scope['additionalData'] ) && is_array( $scope['additionalData'] )
				? $scope['additionalData']
				: array();

			foreach ( $additional_data as $sub_key => $config ) {
				if ( isset( $config['async'] ) && is_array( $config['async'] ) ) {
					$lookup[ $scope_value ][ (string) $sub_key ] = array(
						'mode'   => 'async',
						'type'   => $config['async']['type'] ?? '',
						'params' => $config['async']['params'] ?? array(),
					);
					continue;
				}

				if ( isset( $config['list'] ) && is_array( $config['list'] ) ) {
					$lookup[ $scope_value ][ (string) $sub_key ] = array(
						'mode' => 'list',
						'list' => $config['list'],
					);
				}
			}
		}

		return $lookup;
	}
}
