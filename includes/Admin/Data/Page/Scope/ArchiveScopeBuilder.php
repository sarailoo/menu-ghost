<?php
/**
 * Builds archive scope configuration for page conditions.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Admin\Data\Page\Scope;

use WP_Post_Type;
use WP_Taxonomy;
use MenuGhost\Admin\Data\AsyncConfigFactory;

/**
 * Builds the Archive scope definition along with its option metadata.
 *
 * @since 1.1.0
 */
class ArchiveScopeBuilder {
	/**
	 * Create the archive scope data structure.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,WP_Post_Type> $post_types Public post types.
	 *
	 * @return array<string,mixed>
	 */
	public function build( array $post_types ): array {
		return array(
			'value'          => 'archive',
			'label'          => 'Archive',
			'options'        => array_merge(
				$this->static_options(),
				$this->post_type_options( $post_types )
			),
			'additionalData' => $this->additional_data( $post_types ),
		);
	}

	/**
	 * Base archive options available for every installation.
	 *
	 * @since 1.1.0
	 *
	 * @return array<int,array<string,string>>
	 */
	private function static_options(): array {
		return array(
			array(
				'value' => 'archive_all',
				'label' => 'All Archives',
			),
			array(
				'value' => 'archive_author',
				'label' => 'Author Archive',
			),
			array(
				'value' => 'archive_date',
				'label' => 'Date Archive',
			),
			array(
				'value' => 'archive_search',
				'label' => 'Search Results',
			),
		);
	}

	/**
	 * Dynamic archive options per post type and taxonomy.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,WP_Post_Type> $post_types Post types to inspect.
	 *
	 * @return array<int,array<string,string>>
	 */
	private function post_type_options( array $post_types ): array {
		$options = array();

		foreach ( $post_types as $post_type ) {
			if ( ! $this->is_eligible_post_type( $post_type ) ) {
				continue;
			}

			$options[] = array(
				'value' => "archive_{$post_type->name}",
				'label' => "{$post_type->labels->name}: Archive",
			);

			foreach ( $this->taxonomies_for( $post_type ) as $taxonomy ) {
				$label = "{$post_type->labels->name}: {$taxonomy->labels->name}";
				$value = "archive_{$post_type->name}_{$taxonomy->name}";

				$options[] = array(
					'value' => $value,
					'label' => $label,
				);

				if ( ! $taxonomy->hierarchical ) {
					continue;
				}

				$options[] = array(
					'value' => "child_of_{$post_type->name}_{$taxonomy->name}",
					'label' => "{$post_type->labels->name}: Direct Child {$taxonomy->labels->name} Of",
				);

				$options[] = array(
					'value' => "any_child_of_{$post_type->name}_{$taxonomy->name}",
					'label' => "{$post_type->labels->name}: Any Child {$taxonomy->labels->name} Of",
				);
			}
		}

		return $options;
	}

	/**
	 * Additional dropdown data keyed by archive option value.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,WP_Post_Type> $post_types Post types to inspect.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function additional_data( array $post_types ): array {
		$additional = array(
			'archive_author' => AsyncConfigFactory::make( 'author' ),
		);

		foreach ( $post_types as $post_type ) {
			if ( ! $this->is_eligible_post_type( $post_type ) ) {
				continue;
			}

			foreach ( $this->taxonomies_for( $post_type ) as $taxonomy ) {
				$key                = "archive_{$post_type->name}_{$taxonomy->name}";
				$additional[ $key ] = AsyncConfigFactory::make(
					'term',
					array(
						'taxonomy' => $taxonomy->name,
					)
				);

				if ( ! $taxonomy->hierarchical ) {
					continue;
				}

				$hierarchical_config = AsyncConfigFactory::make(
					'term',
					array(
						'taxonomy' => $taxonomy->name,
					)
				);

				$additional[ "child_of_{$post_type->name}_{$taxonomy->name}" ]     = $hierarchical_config;
				$additional[ "any_child_of_{$post_type->name}_{$taxonomy->name}" ] = $hierarchical_config;
			}
		}

		return $additional;
	}

	/**
	 * Determine whether a post type should be exposed within archive conditions.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 *
	 * @return bool
	 */
	private function is_eligible_post_type( WP_Post_Type $post_type ): bool {
		return 'post' === $post_type->capability_type
			&& $post_type->show_in_menu
			&& $post_type->show_in_nav_menus;
	}

	/**
	 * Retrieve the taxonomies that are exposed within the UI.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post_Type $post_type Post type to inspect.
	 *
	 * @return array<int,WP_Taxonomy>
	 */
	private function taxonomies_for( WP_Post_Type $post_type ): array {
		$taxonomies = get_object_taxonomies( $post_type->name, 'objects' );

		if ( ! is_array( $taxonomies ) ) {
			return array();
		}

		return array_values(
			array_filter(
				$taxonomies,
				static fn( WP_Taxonomy $taxonomy ): bool => (bool) $taxonomy->show_ui
			)
		);
	}
}
