<?php


use AlpineIO\Atlas\Abstracts\AbstractPost;
use AlpineIO\Atlas\Post;
use AlpineIO\Atlas\Tests\Samples\Person;
use Faker\Factory as FakerFactory;

class PostTest extends WP_UnitTestCase {

	public function testPostExtendsAbstractPost() {

		$postId = static::insertSamplePost();

		$post = new Post( $postId );

		$this->assertInstanceOf( AbstractPost::class, $post );
	}

	private static function insertSamplePost( $args = [ ] ) {
		$faker    = FakerFactory::create();
		$defaults = array(
			'post_title'   => wp_strip_all_tags( $faker->text( 32 ) ),
			'post_content' => $faker->text,
			'post_status'  => 'publish',
			'post_author'  => 1
		);

		$args = wp_parse_args( $args, $defaults );

		return wp_insert_post( $args );
	}

	public function testPostErrorsOnTypeMismatch() {
		$this->expectException( DomainException::class );

		$postId = static::insertSamplePost( [ 'post_type' => 'foo_post' ] );
		$post   = new Post( $postId );
	}

	public function testPostTitleAttribute() {
		$faker = FakerFactory::create();
		$title = $faker->text;

		$postId = static::insertSamplePost( [ 'post_title' => $title ] );
		$post   = new Post( $postId );

		$this->assertEquals( $title, $post->post_title );
	}

	public function testFetchAllPosts() {
		$samples = 5;
		$postIds = [ ];
		for ( $i = 0; $i < $samples; $i ++ ) {
			$postIds[] = static::insertSamplePost();
		}
		$posts = Post::all();

		$this->assertCount( $samples, $posts );
		foreach ( $posts as $post ) {
			$this->assertInstanceOf( Post::class, $post );
		}
	}

	public function testExtendedPost() {
		include_once 'samples/Person.php';
		$samples = 5;
		$postIds = [ ];
		for ( $i = 0; $i < $samples; $i ++ ) {
			$postIds[] = static::insertSamplePost( [ 'post_type' => Person::getPostType() ] );
		}
		$posts = Person::all();

		$this->assertCount( $samples, $posts );
		foreach ( $posts as $post ) {
			$this->assertInstanceOf( Post::class, $post );
			$this->assertInstanceOf( Person::class, $post );
			$this->assertEquals( Person::getPostType(), $post->getPostType() );
		}
	}
}