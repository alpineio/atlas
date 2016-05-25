<?php


namespace AlpineIO\Atlas\Types;



class EditorFieldType extends FieldType {
	
	protected static $fieldType = 'editor';

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * EditorFieldType constructor.
	 *
	 * @param $content
	 */
	public function __construct( $content ) {
		$this->content = (string) $content;
	}

	public function __toString()
	{
		return $this->content;
	}
}