<?php


namespace AlpineIO\Atlas\Contracts;


use Illuminate\Contracts\Support\Arrayable;

interface Field extends Arrayable {
	public function __toString();
	
	public static function getFieldType();
}