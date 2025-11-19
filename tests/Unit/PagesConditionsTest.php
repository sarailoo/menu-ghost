<?php
/**
 * Tests for the page-based condition evaluator.
 *
 * @package MenuGhost\Tests
 */

declare(strict_types=1);

namespace MenuGhost\Tests\Unit;

use Brain\Monkey\Functions;
use MenuGhost\Conditions\Pages;

class PagesConditionsTest extends AbstractUnitTestcase {
	public function test_match_returns_true_for_entire_site_scope(): void {
		$this->assertTrue( Pages::match( 'entire_site', '', '' ) );
	}

	public function test_match_singular_defaults_to_is_singular(): void {
		Functions\expect( 'is_singular' )
			->once()
			->withNoArgs()
			->andReturn( true );

		$this->assertTrue( Pages::match( 'singular', '', '' ) );
	}

	public function test_match_singular_specific_post_type_filters_by_id(): void {
		Functions\expect( 'is_singular' )
			->once()
			->with( 'product' )
			->andReturn( true );

		Functions\expect( 'get_queried_object_id' )
			->once()
			->andReturn( 55 );

		$this->assertTrue( Pages::match( 'singular', 'singular_product', '55' ) );
	}

	public function test_match_archive_author_with_specific_ids(): void {
		Functions\expect( 'is_author' )
			->once()
			->with( array( 3, 8 ) )
			->andReturn( true );

		$this->assertTrue( Pages::match( 'archive', 'archive_author', '3,8' ) );
	}
}
