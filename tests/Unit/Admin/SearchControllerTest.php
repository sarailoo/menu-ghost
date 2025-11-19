<?php
/**
 * Tests for the SearchController helper endpoints.
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use MenuGhost\Admin\SearchController;
use MenuGhost\Tests\Unit\AbstractUnitTestcase;

final class SearchControllerTest extends AbstractUnitTestcase {
	public function test_sanitize_search_trims_tags(): void {
		$this->assertSame( 'apples', SearchController::sanitize_search( '<b>apples</b> ' ) );
	}

	public function test_handle_search_returns_single_post_when_id_present(): void {
		$post = new \WP_Post(
			array(
				'ID'         => 45,
				'post_title' => 'Landing Page',
				'post_type'  => 'page',
			)
		);

		Functions::expect( 'get_post' )
			->once()
			->with( 45 )
			->andReturn( $post );

		$request  = new \WP_REST_Request(
			array(
				'type'      => 'post',
				'id'        => 45,
				'post_type' => 'page',
			)
		);
		$response = SearchController::handle_search( $request );
		$data     = $response->get_data();

		$this->assertSame(
			array(
				'items' => array(
					array(
						'value' => '45',
						'label' => 'Landing Page',
					),
				),
			),
			$data
		);
	}

	public function test_handle_search_returns_single_term(): void {
		$term = new \WP_Term(
			array(
				'term_id' => 12,
				'name'    => 'News',
				'taxonomy' => 'category',
			)
		);

		Functions::expect( 'get_term' )
			->once()
			->with( 12, 'category' )
			->andReturn( $term );

		$request  = new \WP_REST_Request(
			array(
				'type'     => 'term',
				'id'       => 12,
				'taxonomy' => 'category',
			)
		);
		$response = SearchController::handle_search( $request );
		$data     = $response->get_data();

		$this->assertSame( '12', $data['items'][0]['value'] );
		$this->assertSame( 'News', $data['items'][0]['label'] );
	}
}
