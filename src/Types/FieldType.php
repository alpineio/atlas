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
	public function setFieldType( $fieldType ) {
		$this->fieldType = $fieldType;

		return $this;
	}

	/**
	 * @param $scope
	 *
	 * @return $this
	 */
	public function setScope( $scope ) {
		$this->scope = $scope;

		return $this;
	}

	public function getScope() {
		return $this->scope;
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