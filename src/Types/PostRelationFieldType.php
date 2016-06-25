<?php


namespace AlpineIO\Atlas\Types;


use AlpineIO\Atlas\Contracts\ScopedRelationship;
use AlpineIO\Atlas\Post;

class PostRelationFieldType extends FieldType implements ScopedRelationship {

	protected $fieldType = 'object-relate';

	protected $scope = 'post';
	protected $template = 'field';

	/**
	 * @var \WP_Post
	 */
	protected $post;
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * PhotoFieldType constructor.
	 *
	 * @param \WP_Post|int $post
	 * @param null $parentPost
	 */
	public function __construct( $post, $parentPost = null ) {
		if ( is_numeric( $post ) ) {
			$this->id   = absint( $post );
			$this->post = get_post( $this->id );
		} elseif ( $post instanceof \WP_Post ) {
			$this->id   = absint( $post->ID );
			$this->post = $post;
		} elseif ( isset( $post->ID ) ) {
			$this->id   = absint( $post->ID );
			$this->post = $post;
		}
		
		if ($parentPost) {
			$this->setParent($parentPost);	
		}

		return $this;
	}
	
	

	

	public function __toString() {
		return $this->post->post_title;
	}


}