<?php
/**
 * Builds singular scope configuration for page conditions.
 *
 * @package MenuGhost
 */

declare(strict_types=1);

namespace MenuGhost\Admin\Data\Page\Scope;

use WP_Post_Type;
use WP_Taxonomy;
use MenuGhost\Admin\Data\AsyncConfigFactory;

/**
 * Builds the Singular scope definition the admin UI consumes.
 *
 * @since 1.1.0
 */
class SingularScopeBuilder {
	/**
	 * Create the singular scope data structure.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,WP_Post_Type> $post_types Public post types.
	 *
	 * @return array<string,mixed>
	 */
	public function build( array $post_types ): array {
		return array(
			'value'          => 'singular',
			'label'          => 'Singular',
			'options'        => array_merge(
				$this->static_options(),
				$this->post_type_options( $post_types ),
				$this->global_options()
			),
			'additionalData' => $this->additional_data( $post_types ),
		);
	}

	/**
	 * Options present for every environment.
	 *
	 * @since 1.1.0
	 *
	 * @return array<int,array<string,string>>
	 */
	private function static_options(): array {
		return array(
			array(
				'value' => 'singular_all',
				'label' => 'All Singular',
			),
			array(
				'value' => 'front_page',
				'label' => 'Front Page',
			),
		);
	}

	/**
	 * Options generated per post type and taxonomy.
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
				'value' => "singular_{$post_type->name}",
				'label' => "{$post_type->labels->name}: Single",
			);

			foreach ( $this->taxonomies_for( $post_type ) as $taxonomy ) {
				$options[] = array(
					'value' => "singular_{$post_type->name}_in_{$taxonomy->name}",
					'label' => "{$post_type->labels->name}: In {$taxonomy->labels->name}",
				);

				if ( ! $taxonomy->hierarchical ) {
					continue;
				}

				$options[] = array(
					'value' => "singular_{$post_type->name}_in_child_{$taxonomy->name}",
					'label' => "{$post_type->labels->name}: In Child {$taxonomy->labels->name}",
				);
			}

			$options[] = array(
				'value' => "singular_{$post_type->name}_by_author",
				'label' => "{$post_type->labels->name}: By Author",
			);
		}

		return $options;
	}

	/**
	 * Global options appended to the end of the list.
	 *
	 * @since 1.1.0
	 *
	 * @return array<int,array<string,string>>
	 */
	private function global_options(): array {
		return array(
			array(
				'value' => 'child_of',
				'label' => 'Direct Child Of',
			),
			array(
				'value' => 'any_child_of',
				'label' => 'Any Child Of',
			),
			array(
				'value' => 'by_author',
				'label' => 'By Author',
			),
			array(
				'value' => 'not_found404',
				'label' => '404 Page',
			),
		);
	}

	/**
	 * Additional dropdown data keyed by singular option value.
	 *
	 * @since 1.1.0
	 *
	 * @param array<string,WP_Post_Type> $post_types Post types to inspect.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function additional_data( array $post_types ): array {
		$additional = array(
			'child_of'     => AsyncConfigFactory::make(
				'post',
				array(
					'post_type' => 'page',
				)
			),
			'any_child_of' => AsyncConfigFactory::make(
				'post',
				array(
					'post_type' => 'page',
				)
			),
			'by_author'    => AsyncConfigFactory::make( 'author' ),
		);

		foreach ( $post_types as $post_type ) {
			if ( ! $this->is_eligible_post_type( $post_type ) ) {
				continue;
			}

			$additional[ "singular_{$post_type->name}" ] = AsyncConfigFactory::make(
				'post',
				array(
					'post_type' => $post_type->name,
				)
			);

			foreach ( $this->taxonomies_for( $post_type ) as $taxonomy ) {
				$key                = "singular_{$post_type->name}_in_{$taxonomy->name}";
				$additional[ $key ] = AsyncConfigFactory::make(
					'term',
					array(
						'taxonomy' => $taxonomy->name,
					)
				);

				if ( ! $taxonomy->hierarchical ) {
					continue;
				}

				$child_key                = "singular_{$post_type->name}_in_child_{$taxonomy->name}";
				$additional[ $child_key ] = AsyncConfigFactory::make(
					'term',
					array(
						'taxonomy' => $taxonomy->name,
					)
				);
			}

			$additional[ "singular_{$post_type->name}_by_author" ] = AsyncConfigFactory::make( 'author' );
		}

		return $additional;
	}

	/**
	 * Determine whether a post type should be exposed for singular rules.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Post_Type $post_type Post type object.
	 *
	 * @return bool
	 */
	private function is_eligible_post_type( WP_Post_Type $post_type ): bool {
		return 'attachment' === $post_type->name || $post_type->show_in_nav_menus;
	}

	/**
	 * Retrieve visible taxonomies for a post type.
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
