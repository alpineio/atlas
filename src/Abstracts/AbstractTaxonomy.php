<?php


namespace AlpineIO\Atlas\Abstracts;


use AlpineIO\Atlas\Contracts\Reflectable;
use Illuminate\Support\Str;

abstract class AbstractTaxonomy implements Reflectable {

	protected static $postTypes = [];
	protected static $labels;
	protected static $taxonomy;
	protected static $settings = [ ];

	public static function getPostTypeSlugs() {
		return array_map( function ( $postType ) {
			if ( class_exists( $postType ) && ( new \ReflectionClass( $postType ) )->hasMethod( 'getSlug' ) ) {
				/** @var $postType AbstractTaxonomy */
				return $postType::getSlug();
			}

			return $postType;
		}, static::getPostTypes() );
		//return Str::camel( static::getReflection()->getShortName() );
	}

	/**
	 * @return string
	 */
	public static function getSlug() {
		if ( isset( static::$taxonomy ) ) {
			return static::$taxonomy;
		}

		return Str::slug( static::getReflection()->getShortName() );
	}

	public static function getReflection() {
		return new \ReflectionClass( get_called_class() );
	}

	/**
	 * @return string
	 */
	public static function getPostTypes() {
		if ( isset( static::$postTypes ) ) {
			return static::$postTypes;
		}

		return Str::camel( static::getReflection()->getShortName() );
	}

	public static function getLabels() {
		if ( ! function_exists( 'piklist' ) ) {
			// TODO some error or defaults
		}
		//return piklist( 'taxonomy_labels', Str::studly( static::getTaxonomy() ) );
		//return piklist( 'taxonomy_labels', ucwords(str_replace( '-', ' ', static::getTaxonomy() ) ) );
		$labels = piklist( 'taxonomy_labels', ucwords( str_replace( '-', ' ', static::getTaxonomy() ) ) );
		if ( isset( static::$labels ) ) {
			return array_merge( $labels, static::$labels );
		}

		return $labels;
	}

	/**
	 * @return string
	 */
	public static function getTaxonomy() {
		if ( isset( static::$taxonomy ) ) {
			return static::$taxonomy;
		}

		return Str::snake( static::getReflection()->getShortName(), '-' );
	}

	/**
	 * @return mixed
	 */
	public static function getSettings() {
		return self::$settings;
	}

	public static function hasSettings() {
		if ( isset( static::$settings ) ) {
			return ! empty( static::$settings );
		}

		return false;
	}


}