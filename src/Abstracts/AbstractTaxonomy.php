<?php


namespace AlpineIO\Atlas\Abstracts;


use AlpineIO\Atlas\Contracts\Reflectable;
use Illuminate\Support\Str;

abstract class AbstractTaxonomy implements Reflectable {

	protected static $postTypes = [];
	protected static $labels;
	protected static $taxonomy;
	protected static $settings = [ ];
	protected $id;
	protected $term;

	public function __construct( $object ) {
		if ( $object ) {

			if ( is_numeric( $object ) ) {
				$this->id   = abs(intval( $object ));
				$this->term = get_term( $this->id );
			} elseif ( $object instanceof WP_Term ) {
				$this->id   = abs(intval( $object->ID ));
				$this->term = $object;
			} elseif ( isset( $object->term_id ) ) {
				$this->id   = abs(intval( $object->term_id ));
				$this->term = $object;
			}
			if ( $this->term->taxonomy != $this->getTaxonomy() ) {
				throw new \DomainException( 'Invalid taxonomy for this term. Can\'t assign taxonomy: '
				                            . $this->term->taxonomy . ' to ' . get_class( $this ) );
			}
			/*
			$meta     = get_post_meta( $this->id );
			$defaults = [
				'post_status' => $this->post->post_status,
				'post_title'  => $this->post->post_title,
				'post_type'   => $this->post->post_type,
			];
			$this->fill( array_merge( $defaults, $meta ) );
			*/
		}
	}

	public static function all( $args = [] ) {
		$default = [
			'taxonomy' => static::getTaxonomy(),
			'hide_empty' => false,
		];
		$args = wp_parse_args($args, $default);
		//$columns  = is_array( $columns ) ? $columns : func_get_args();

		$terms = get_terms($default);
		$object = static::class;
		return array_map( function ( $term ) use ($object) {
			// Late static binding does not work on WPE's version of php 5.5.9 in closures
			//return self::newInstance($post);
			//return new static($post);
			return new $object($term);
		}, $terms );
	}

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