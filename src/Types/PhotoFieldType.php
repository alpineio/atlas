<?php


namespace AlpineIO\Atlas\Types;


use AlpineIO\Atlas\Contracts\PhotoField;

class PhotoFieldType extends PostRelationFieldType implements PhotoField {
	
	protected static $fieldType = 'file';
	
	public function getUrl( $size ) {
		return wp_get_attachment_image_url($this->id, $size);
	}

}