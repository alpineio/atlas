<?php


namespace AlpineIO\Atlas\Contracts;


use Illuminate\Contracts\Support\Arrayable;

interface Field extends Arrayable {
	public function __toString();
	
<<<<<<< HEAD
	public function getFieldType();
	public function getScope();
=======
	public static function getFieldType();
>>>>>>> 5b3c35d39ef1606aa4beb789f4fdeec433e873dd
}