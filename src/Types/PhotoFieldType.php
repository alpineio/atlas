<?php


namespace AlpineIO\Atlas\Types;


use AlpineIO\Atlas\Contracts\PhotoField;

class PhotoFieldType extends PostRelationFieldType implements PhotoField {

	protected $fieldType = 'file';
	protected $scope = 'post_meta';

	public function getUrl( $size ) {
		return wp_get_attachment_image_url( $this->id, $size );
	}

	/**
	 * @param string|array $size
	 * @param string|array $attr
	 *
	 * @return string
	 */
	public function getHTML( $size = 'thumbnail', $attr = '' ) {
		$size = apply_filters( 'post_thumbnail_size', $size );

		if ( $this->id ) {
			do_action( 'begin_fetch_post_thumbnail_html', $this->parent->id, $this->id, $size );
			if ( in_the_loop() ) {
				update_post_thumbnail_cache();
			}
			$html = wp_get_attachment_image( $this->id, $size, false, $attr );
			do_action( 'end_fetch_post_thumbnail_html', $this->parent->id, $this->id, $size );
		} else {
			$html = '';
		}

		return apply_filters( 'post_thumbnail_html', $html, $this->parent->id, $this->id, $size, $attr );

	}

}
