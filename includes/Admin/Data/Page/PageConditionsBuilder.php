<?php
/**
 * Builds the various page condition scopes exposed to the admin UI.
 *
 * @package WPMenuControl
 */

declare(strict_types=1);

namespace WPMenuControl\Admin\Data\Page;

use WP_Post_Type;
use WPMenuControl\Admin\Data\Page\Scope\ArchiveScopeBuilder;
use WPMenuControl\Admin\Data\Page\Scope\EntireSiteScope;
use WPMenuControl\Admin\Data\Page\Scope\SingularScopeBuilder;

/**
 * Coordinates page-related condition data for the admin UI.
 *
 * @since 1.1.0
 */
class PageConditionsBuilder {
	/**
	 * Build the complete page conditions payload.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string,mixed>
	 */
	public function build(): array {
		$post_types = $this->public_post_types();

		return array(
			'conditionTypes' => ConditionTypes::all(),
			'scopes'         => array(
				EntireSiteScope::definition(),
				( new ArchiveScopeBuilder() )->build( $post_types ),
				( new SingularScopeBuilder() )->build( $post_types ),
			),
		);
	}

	/**
	 * Retrieve all public post types keyed by slug.
	 *
	 * @since 1.1.0
	 *
	 * @return array<string,WP_Post_Type>
	 */
	private function public_post_types(): array {
		$post_types = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		if ( ! is_array( $post_types ) ) {
			return array();
		}

		// Guarantee associative array keyed by post type slug.
		return array_filter(
			$post_types,
			static fn( $type ): bool => $type instanceof WP_Post_Type
		);
	}
}
