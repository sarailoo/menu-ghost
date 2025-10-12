<?php
/**
 * Advanced condition evaluation helpers.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Conditions;

/**
 * Evaluate non-page "advanced" visibility rules for a request.
 *
 * @since 1.1.0
 */
class Advanced {
	/**
	 * Determine whether all enabled rules pass.
	 *
	 * @since 1.1.0
	 *
	 * @param array<int,array<string,mixed>> $rules Saved ruleset for a menu item.
	 *
	 * @return bool
	 */
	public static function match( array $rules ): bool {
		foreach ( $rules as $rule ) {
			if ( empty( $rule['enabled'] ) ) {
				continue;
			}

			$key    = (string) ( $rule['key'] ?? '' );
			$params = (array) ( $rule['params'] ?? array() );

			$passes = match ( $key ) {
				'browser_language' => self::match_browser_language( $params ),
				'days_of_week'     => self::match_days_of_week( $params ),
				'login_status'     => self::match_login_status( $params ),
				'signup_date'      => self::match_signup_date( $params ),
				'url_query_key'    => self::match_url_query_key( $params ),
				'device'           => self::match_device( $params ),
				'user_role'        => self::match_user_role( $params ),
				'utm_campaign'     => self::match_utm_parameter( 'utm_campaign', $params ),
				'utm_content'      => self::match_utm_parameter( 'utm_content', $params ),
				'utm_medium'       => self::match_utm_parameter( 'utm_medium', $params ),
				'utm_source'       => self::match_utm_parameter( 'utm_source', $params ),
				'utm_term'         => self::match_utm_parameter( 'utm_term', $params ),
				'within_date_range'=> self::match_within_date_range( $params ),
				'within_time'      => self::match_within_time( $params ),
				default            => true,
			};

			if ( ! $passes ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Match against allowed browser languages.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_browser_language( array $params ): bool {
		$languages = array_filter(
			array_map(
				static fn( $language ): string => strtolower( trim( (string) $language ) ),
				(array) ( $params['langs'] ?? array() )
			)
		);

		if ( empty( $languages ) ) {
			return true;
		}

		$raw_accept = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] )
			? wp_unslash( (string) $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: '';
		$accept     = strtolower( sanitize_text_field( $raw_accept ) );

		foreach ( $languages as $language_code ) {
			$pattern = sprintf( '~\b%s(?:-|;|,|\s|$)~', preg_quote( $language_code, '~' ) );

			if ( 1 === preg_match( $pattern, $accept ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Match against allowed days of the week.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_days_of_week( array $params ): bool {
		$days = array_map( 'intval', (array) ( $params['days'] ?? array() ) );

		if ( empty( $days ) ) {
			return true;
		}

		$current   = new \DateTimeImmutable( 'now', wp_timezone() );
		$day_value = (int) $current->format( 'w' );

		return in_array( $day_value, $days, true );
	}

	/**
	 * Match the current user's login status.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_login_status( array $params ): bool {
		$state = (string) ( $params['state'] ?? 'any' );

		return match ( $state ) {
			'logged_in'  => is_user_logged_in(),
			'logged_out' => ! is_user_logged_in(),
			default      => true,
		};
	}

	/**
	 * Match against the user's registration date.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_signup_date( array $params ): bool {
		if ( ! is_user_logged_in() ) {
			return true;
		}

		$operator = (string) ( $params['operator'] ?? '' );
		$date     = (string) ( $params['date'] ?? '' );

		if ( '' === $operator || '' === $date ) {
			return true;
		}

		$user = wp_get_current_user();
		if ( ! $user instanceof \WP_User ) {
			return true;
		}

		$registered = get_date_from_gmt( $user->user_registered, 'Y-m-d' );
		if ( ! $registered ) {
			return true;
		}

		return 'before' === $operator ? ( $registered < $date ) : ( $registered > $date );
	}

	/**
	 * Match against a specific query-string key.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_url_query_key( array $params ): bool {
		$mode = (string) ( $params['mode'] ?? 'exists' );
		$key  = sanitize_key( $params['key'] ?? '' );

		if ( '' === $key ) {
			return true;
		}

		if ( 'equals' === $mode ) {
			if ( ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return false;
			}

			$value   = sanitize_text_field( wp_unslash( (string) $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$desired = (string) ( $params['value'] ?? '' );

			return $value === $desired;
		}

		return isset( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Match against detected device types.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_device( array $params ): bool {
		$devices = array_map( 'strval', (array) ( $params['devices'] ?? array() ) );

		if ( empty( $devices ) ) {
			return true;
		}

		return in_array( self::detect_device(), $devices, true );
	}

	/**
	 * Match the current user's roles.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_user_role( array $params ): bool {
		$roles = array_map( 'strval', (array) ( $params['roles'] ?? array() ) );

		if ( empty( $roles ) ) {
			return true;
		}

		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user = wp_get_current_user();

		return (bool) array_intersect( $roles, (array) $user->roles );
	}

	/**
	 * Match against a specific UTM query parameter.
	 *
	 * @since 1.1.0
	 *
	 * @param string              $key    The UTM parameter name.
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_utm_parameter( string $key, array $params ): bool {
		$mode = (string) ( $params['mode'] ?? 'exists' );

		if ( 'equals' === $mode ) {
			if ( ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return false;
			}

			$value   = sanitize_text_field( wp_unslash( (string) $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$desired = (string) ( $params['value'] ?? '' );

			return $value === $desired;
		}

		return isset( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Match the current request against a date range.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_within_date_range( array $params ): bool {
		$start = (string) ( $params['start'] ?? '' );
		$end   = (string) ( $params['end'] ?? '' );

		if ( '' === $start && '' === $end ) {
			return true;
		}

		$timezone = wp_timezone();
		$now      = new \DateTimeImmutable( 'now', $timezone );

		if ( '' !== $start ) {
			$start_date = \DateTimeImmutable::createFromFormat( 'Y-m-d', $start, $timezone );
			if ( $start_date instanceof \DateTimeImmutable && $now < $start_date ) {
				return false;
			}
		}

		if ( '' !== $end ) {
			$end_date = \DateTimeImmutable::createFromFormat( 'Y-m-d', $end, $timezone );
			if ( $end_date instanceof \DateTimeImmutable && $now > $end_date->setTime( 23, 59, 59 ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Match the current request against a time-of-day window.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,mixed> $params Rule parameters.
	 *
	 * @return bool
	 */
	private static function match_within_time( array $params ): bool {
		$start = (string) ( $params['start'] ?? '' );
		$end   = (string) ( $params['end'] ?? '' );

		if ( '' === $start && '' === $end ) {
			return true;
		}

		$timezone         = wp_timezone();
		$now              = new \DateTimeImmutable( 'now', $timezone );
		$now_in_minutes   = ( (int) $now->format( 'H' ) * 60 ) + (int) $now->format( 'i' );
		$start_in_minutes = self::time_string_to_minutes( $start );
		$end_in_minutes   = self::time_string_to_minutes( $end );

		if ( null === $start_in_minutes && null === $end_in_minutes ) {
			return true;
		}

		if ( null !== $start_in_minutes && null !== $end_in_minutes ) {
			if ( $start_in_minutes <= $end_in_minutes ) {
				return $now_in_minutes >= $start_in_minutes && $now_in_minutes <= $end_in_minutes;
			}

			return $now_in_minutes >= $start_in_minutes || $now_in_minutes <= $end_in_minutes;
		}

		if ( null !== $start_in_minutes ) {
			return $now_in_minutes >= $start_in_minutes;
		}

		return $now_in_minutes <= ( $end_in_minutes ?? 0 );
	}

	/**
	 * Convert a time string (HH:MM) into minutes since midnight.
	 *
	 * @since 1.1.0
	 *
	 * @param string $time_string Time string in HH:MM format.
	 *
	 * @return int|null Minutes since midnight or null on failure.
	 */
	private static function time_string_to_minutes( string $time_string ): ?int {
		if ( ! preg_match( '/^\d{2}:\d{2}$/', $time_string ) ) {
			return null;
		}

		list( $hours, $minutes ) = array_map( 'intval', explode( ':', $time_string, 2 ) );

		return ( $hours * 60 ) + $minutes;
	}

	/**
	 * Detect the current visitor's device class.
	 *
	 * @since 1.1.0
	 *
	 * @return string One of mobile, tablet, or desktop.
	 */
	private static function detect_device(): string {
		$raw_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
			? wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: '';
		$user_agent     = strtolower( sanitize_text_field( $raw_user_agent ) );

		if ( '' === $user_agent ) {
			return 'desktop';
		}

		if ( 1 === preg_match( '~ipad|tablet|kindle|silk|playbook|nexus 7|sm-t\d+|tab~', $user_agent ) ) {
			return 'tablet';
		}

		if ( wp_is_mobile() ) {
			return 'mobile';
		}

		return 'desktop';
	}
}
