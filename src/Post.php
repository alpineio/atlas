<?php


namespace AlpineIO\Atlas;


use AlpineIO\Atlas\Abstracts\AbstractPost;
use AlpineIO\Atlas\Contracts\Reflectable;

class Post extends AbstractPost implements Reflectable {
	//protected static $post_type = 'post';

	public function getPostTitleAttribute( $value ) {
		$value = str_replace( "'", "\'", $value );	
		return $value;
	}
}