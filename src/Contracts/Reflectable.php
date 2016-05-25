<?php


namespace AlpineIO\Atlas\Contracts;


interface Reflectable {
	/**
	 * @return \ReflectionClass
	 */
	static function getReflection();
}