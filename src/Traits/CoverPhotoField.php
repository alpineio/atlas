<?php


namespace AlpineIO\Atlas\Traits;


use AlpineIO\Atlas\Contracts\PhotoField;

trait CoverPhotoField {
	
	public function getCoverPhotoField() {
		return $this->coverPhotoField;
	}
	public function getCoverPhotoUrl($size) {
		/** @var PhotoField $property */	
		$property = $this->getCoverPhotoField();
		if ( isset( $this->$property )) {
			return $this->$property->getUrl($size);
		}
		//$property->getUrl( $size );
	}
}