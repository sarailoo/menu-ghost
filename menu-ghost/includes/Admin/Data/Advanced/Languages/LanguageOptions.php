<?php
/**
 * Helper utilities for language select options.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Admin\Data\Advanced\Languages;

use function locale_get_display_language;
use function locale_get_display_name;

/**
 * Generates a list of language options suitable for select controls.
 *
 * @since 1.1.0
 */
class LanguageOptions {
	/**
	 * ISO 639-1 base language codes.
	 *
	 * @var array<int,string>
	 */
	private const BASE_CODES = array(
		'aa',
		'ab',
		'ae',
		'af',
		'ak',
		'am',
		'an',
		'ar',
		'as',
		'av',
		'ay',
		'az',
		'ba',
		'be',
		'bg',
		'bh',
		'bi',
		'bm',
		'bn',
		'bo',
		'br',
		'bs',
		'ca',
		'ce',
		'ch',
		'co',
		'cr',
		'cs',
		'cu',
		'cv',
		'cy',
		'da',
		'de',
		'dv',
		'dz',
		'ee',
		'el',
		'en',
		'eo',
		'es',
		'et',
		'eu',
		'fa',
		'ff',
		'fi',
		'fj',
		'fo',
		'fr',
		'fy',
		'ga',
		'gd',
		'gl',
		'gn',
		'gu',
		'gv',
		'ha',
		'he',
		'hi',
		'ho',
		'hr',
		'ht',
		'hu',
		'hy',
		'hz',
		'ia',
		'id',
		'ie',
		'ig',
		'ii',
		'ik',
		'io',
		'is',
		'it',
		'iu',
		'ja',
		'jv',
		'ka',
		'kg',
		'ki',
		'kj',
		'kk',
		'kl',
		'km',
		'kn',
		'ko',
		'kr',
		'ks',
		'ku',
		'kv',
		'kw',
		'ky',
		'la',
		'lb',
		'lg',
		'li',
		'ln',
		'lo',
		'lt',
		'lu',
		'lv',
		'mg',
		'mh',
		'mi',
		'mk',
		'ml',
		'mn',
		'mr',
		'ms',
		'mt',
		'my',
		'na',
		'nb',
		'nd',
		'ne',
		'ng',
		'nl',
		'nn',
		'no',
		'nr',
		'nv',
		'ny',
		'oc',
		'oj',
		'om',
		'or',
		'os',
		'pa',
		'pi',
		'pl',
		'ps',
		'pt',
		'qu',
		'rm',
		'rn',
		'ro',
		'ru',
		'rw',
		'sa',
		'sc',
		'sd',
		'se',
		'sg',
		'si',
		'sk',
		'sl',
		'sm',
		'sn',
		'so',
		'sq',
		'sr',
		'ss',
		'st',
		'su',
		'sv',
		'sw',
		'ta',
		'te',
		'tg',
		'th',
		'ti',
		'tk',
		'tl',
		'tn',
		'to',
		'tr',
		'ts',
		'tt',
		'tw',
		'ty',
		'ug',
		'uk',
		'ur',
		'uz',
		've',
		'vi',
		'vo',
		'wa',
		'wo',
		'xh',
		'yi',
		'yo',
		'za',
		'zh',
		'zu',
	);

	/**
	 * Common language variants to include.
	 *
	 * @var array<int,string>
	 */
	private const VARIANTS = array(
		'ar-AE',
		'ar-EG',
		'ar-SA',
		'de-AT',
		'de-CH',
		'de-DE',
		'en-AU',
		'en-CA',
		'en-GB',
		'en-IN',
		'en-NZ',
		'en-US',
		'es-419',
		'es-ES',
		'es-MX',
		'fa-IR',
		'fr-CA',
		'fr-FR',
		'pt-BR',
		'pt-PT',
		'zh-Hans',
		'zh-Hant',
		'zh-HK',
		'zh-CN',
		'zh-TW',
	);

	/**
	 * Generate all language options, sorted alphabetically by label.
	 *
	 * @since 1.1.0
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function all(): array {
		$seen    = array();
		$options = array();

		foreach ( self::BASE_CODES as $code ) {
			$code = strtolower( $code );

			if ( isset( $seen[ $code ] ) ) {
				continue;
			}

			$options[]     = array(
				'value' => $code,
				'label' => sprintf( '%s (%s)', self::label_for( $code ), $code ),
			);
			$seen[ $code ] = true;
		}

		foreach ( self::VARIANTS as $code ) {
			$normalized = strtolower( $code );

			if ( isset( $seen[ $normalized ] ) ) {
				continue;
			}

			$options[]           = array(
				'value' => $code,
				'label' => sprintf( '%s (%s)', self::label_for( $code, 'name' ), $code ),
			);
			$seen[ $normalized ] = true;
		}

		usort(
			$options,
			static fn( array $a, array $b ): int => strcasecmp( (string) $a['label'], (string) $b['label'] )
		);

		return $options;
	}

	/**
	 * Determine a display label for a locale.
	 *
	 * @since 1.1.0
	 *
	 * @param string $locale Locale code.
	 * @param string $format Format to request ('language' or 'name').
	 *
	 * @return string
	 */
	private static function label_for( string $locale, string $format = 'language' ): string {
		if ( 'name' === $format && function_exists( 'locale_get_display_name' ) ) {
			$label = locale_get_display_name( $locale, 'en' );
			if ( is_string( $label ) && '' !== $label ) {
				return $label;
			}
		}

		if ( function_exists( 'locale_get_display_language' ) ) {
			$label = locale_get_display_language( $locale, 'en' );

			if ( is_string( $label ) && '' !== $label ) {
				return $label;
			}
		}

		return strtoupper( $locale );
	}
}
