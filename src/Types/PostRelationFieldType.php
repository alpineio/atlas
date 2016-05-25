<?php


namespace AlpineIO\Atlas\Types;


use AlpineIO\Atlas\Contracts\ScopedRelationship;

class PostRelationFieldType extends FieldType implements ScopedRelationship {

	protected static $fieldType = 'post-relate';

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
	 */
	public function __construct( $post ) {
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

		return $this;
	}
	
	public function getScope() {
		return $this->scope;
	}
	
	public function setScope( $scope ) {
		$this->scope = $scope;
		return $this;
	}

	public function __toString() {
		return $this->post->post_title;
	}


}