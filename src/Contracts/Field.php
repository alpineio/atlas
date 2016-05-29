<?php


namespace AlpineIO\Atlas\Contracts;


use Illuminate\Contracts\Support\Arrayable;

interface Field extends Arrayable {
	public function __toString();
	
	public function getFieldType();
	public function getScope();
}