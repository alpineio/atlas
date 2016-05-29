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
	protected $fieldType = 'text';

	/**
	 * @return string
	 */
	public function getFieldType() {
		return $this->fieldType;
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