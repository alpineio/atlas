<?php
use AlpineIO\Atlas\Abstracts\AbstractTaxonomy;
use AlpineIO\Atlas\Tests\Samples\Team;
use Faker\Factory as FakerFactory;

class TaxonomyTest extends WP_UnitTestCase {

	private static function insertSamplePost( $args = [ ] ) {
		$faker    = FakerFactory::create();
		$defaults = array(
			'post_title'   => wp_strip_all_tags( $faker->text( 32 ) ),
			'post_content' => $faker->text,
			'post_status'  => 'publish',
			'post_author'  => 1
		);

		$args = wp_parse_args( $args, $defaults );

		return wp_insert_post( $args, true );
	}

	public function _testTeamExtendsAbstractTaxonomy() {
		include_once 'samples/Team.php';

		$term = new Team();

		$this->assertInstanceOf( AbstractTaxonomy::class, $term );
	}

	

	public function testTaxonomyAll() {
		include_once 'samples/Team.php';
		include_once 'samples/Person.php';
		$faker   = FakerFactory::create();
		$samples = 5;
		$termIds = [ ];
	
		register_taxonomy( Team::getTaxonomy(), Team::getPostTypes() );

		for ( $i = 0; $i < $samples; $i ++ ) {
			$t = self::factory()->term->create(['taxonomy' => Team::getTaxonomy()]);
			clean_term_cache($t, Team::getTaxonomy());
			//$termIds[] = wp_insert_term( $faker->word, Team::getTaxonomy() );
		}

		$teams = Team::all();
		$this->assertCount( $samples, $teams );
		foreach ( $teams as $term ) {
			$this->assertInstanceOf( Team::class, $term );
		}

	}

	/**
	 * Test a taxonomy lookup with a empty result
	 */
	public function testTaxonomyAllEmpty() {
		$this->reset_taxonomies();
		include_once 'samples/Team.php';
		include_once 'samples/Person.php';
		register_taxonomy( Team::getTaxonomy(), Team::getPostTypes() );
		$teams = Team::all();
		$this->assertInternalType( 'array', $teams );
		$this->assertCount( 0, $teams );
	}
	
}
