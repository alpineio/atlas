<?php


namespace AlpineIO\Atlas\Types;


use AlpineIO\Atlas\Contracts\Field;

/**
 * Class FieldType
 * @package AlpineIO\Atlas\Types
 */
abstract class FieldType implements Field {
	/**
	 * @var string
	 */
	protected static $fieldType = 'text';

	/**
	 * @return string
	 */
	public static function getFieldType() {
		return static::$fieldType;	
	}

	/**
	 * @param string $fieldType
	 */
	public static function setFieldType( $fieldType ) {
		self::$fieldType = $fieldType;
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray() {
		return (array) $this;
	}
}