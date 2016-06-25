<?php


namespace AlpineIO\Atlas\Contracts;


interface PhotoField  extends Field {
	public function getUrl( $size );
	public function getHTML( $size = 'post-thumbnail', $attr = '' );
}