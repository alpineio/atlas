<?php


namespace AlpineIO\Atlas\Services;


use AlpineIO\Atlas\Abstracts\AbstractPost;
use AlpineIO\Atlas\Contracts\Field;
use AlpineIO\Atlas\Contracts\Reflectable;
use AlpineIO\Atlas\Contracts\ScopedRelationship;
use AlpineIO\Atlas\Post;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;

class PiklistAutofields {
	private static $object;
	private static $reflection;
	public static function generate( Reflectable $object ) {
		static::$object = $object;
		static::$reflection = $object::getReflection();
		
		$factory    = DocBlockFactory::createInstance();
		$docblock   = $factory->create( static::$reflection->getDocComment() );
		if ( $docblock->hasTag( 'property' ) ) {
			$properties = $docblock->getTagsByName( 'property' );
			foreach ( $properties as $property ) {
				static::renderField( $property );
			}
		}
	}

	public static function renderField( Property $property ) {
		$description = (string) $property->getDescription();
		$settings = [
			'type'       => static::getFieldType( $property ),
			'field'      => $property->getVariableName(),
			'label'      => static::removeJson( $description ),
			// 'description' => 'This is a description of the field.',
			'columns' => '12',
			'attributes' => [
				'class' => 'text'
			]	
		];

		$isCollection = static::isCollection($property);
		if ( $isCollection ) {
			$settings['add_more'] = true;	
		}
		
		$isScoped = static::isScopedRelationship($property);
		if ( $isScoped ) {
			$variableName = $property->getVariableName();
<<<<<<< HEAD
			$settings['scope'] = static::$object->getFieldType($variableName)->getScope();
=======
			$settings['scope'] = static::$object->$variableName->getScope();
>>>>>>> 5b3c35d39ef1606aa4beb789f4fdeec433e873dd
		}

		$hasJson     = static::hasJsonString( (string) $description );
		if ( $hasJson ) {
			$settings = wp_parse_args( $hasJson, $settings );
		}
		piklist( 'field', $settings );
	}
	
	public static function isCollection( Property $property ) {
		if ( $property->getType() instanceof Array_ ) {
			return true;
		}
	}
	
	public static function isScopedRelationship( Property $property ) {
		$variableName = $property->getVariableName();
<<<<<<< HEAD
		if(static::$object->getFieldType($variableName) instanceof ScopedRelationship) {
=======
		if(static::$object->$variableName instanceof ScopedRelationship) {
			static::$object->$variableName->getScope();
>>>>>>> 5b3c35d39ef1606aa4beb789f4fdeec433e873dd
			return true;
		}
		return false;
	}
	
	

	public static function hasJsonString(  $description ) {
		$description = trim(preg_replace('/\s+/', ' ', $description));
		if ( preg_match( '/\{.+\}/', $description, $matches ) ) {
			return json_decode( $matches[0], true );
		}

		return false;
	}
	public static function removeJson(  $string ) {
		return preg_replace('/{[\s\S]+?}/', '', $string);
	}

	/**
	 * If the property is a instance of Field then we can get the field type of it, otherwise return text
	 * @param Property $property
	 *
	 * @return string
	 */
	public static function getFieldType( Property $property ) {
		$variableName = $property->getVariableName();
<<<<<<< HEAD
		//dd(static::$object->getFieldType($variableName));
		if ( static::$object->getFieldType($variableName) instanceof Field) {
			return static::$object->getFieldType($variableName)->getFieldType();
=======
		if ( isset(static::$object->$variableName) && static::$object->$variableName instanceof Field) {
			return static::$object->$variableName->getFieldType();
>>>>>>> 5b3c35d39ef1606aa4beb789f4fdeec433e873dd
		}
		return 'text';
	}
}