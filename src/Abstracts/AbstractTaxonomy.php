<?php


namespace AlpineIO\Atlas\Abstracts;


use AlpineIO\Atlas\Contracts\Reflectable;
use AlpineIO\Atlas\Traits\AttributeStorage;
use Illuminate\Support\Str;

/**
 * Class AbstractTaxonomy
 * @package AlpineIO\Atlas\Abstracts
 * @property string $name;
 * @property string $description;
 * @property string $slug;
 */
abstract class AbstractTaxonomy implements Reflectable {
	
	use AttributeStorage;

	protected static $postTypes = [];
	protected static $labels;
	protected static $taxonomy;
	protected static $settings = [ ];
	protected static $unguarded = [];
	protected $attributes = [];
	protected $id;
	protected $term;

	public function __construct( $object = null ) {
		if ( $object ) {

			if ( is_numeric( $object ) ) {
				$this->id   = abs(intval( $object ));
				$this->term = static::getTerm( $this->id );
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
			$meta     = get_term_meta( $this->id );
			$defaults = [
				'post_status' => $this->term->post_status,
				'post_title'  => $this->term->post_title,
				'post_type'   => $this->term->post_type,
			];
			$this->fill( array_merge( $defaults, $meta ) );
		}
	}

	/**
	 * @param array $args
	 *
	 * @return self[]
	 */
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

	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->getAttribute( $key );
	}

	/**
	 * Get an attribute from the model.
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function getAttribute( $key ) {
		if ( property_exists( $this->term, $key )  || $this->hasGetMutator( $key ) ) {
			return $this->getAttributeValue( $key );
		}

		//return $this->getRelationValue($key);
		return null;
	}
	/**
	 * Determine if a get mutator exists for an attribute.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	public function hasGetMutator( $key ) {
		return method_exists( $this, 'get' . Str::studly( $key ) . 'Attribute' );
	}

	/**
	 * Get a plain attribute (not a relationship).
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	public function getAttributeValue( $key ) {
		$value = $this->getAttributeFromArray( $key );

		if ( $this->hasGetMutator( $key ) ) {
			return $this->mutateAttribute( $key, $value );
		}
		return $value;
	}

	/**
	 * Get the value of an attribute using its mutator.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 *
	 * @return mixed
	 */
	protected function mutateAttribute( $key, $value ) {
		return $this->{'get' . Str::studly( $key ) . 'Attribute'}( $value );
	}

	protected function getAttributeFromArray( $key ) {
		if ( property_exists( $this->term, $key ) ) {
			return $this->term->$key;
		}

		return null;
	}
	
	

	public function getPermalink() {
		return get_term_link($this->term, $this->getTaxonomy());
	}

	public function getFieldType( $field, $value = null ) {
		$method = sprintf('get%sField', Str::studly($field));
		if (method_exists($this,$method)) {
			return $this->$method($value);
		}
	}
	public static function getTerm( $term, $taxonomy = null ) {
		if (! function_exists('get_term')) {
			return null;	
		}
		return get_term($term, $taxonomy);
	}
	
	
	
}