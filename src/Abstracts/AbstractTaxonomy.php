<?php


namespace AlpineIO\Atlas\Abstracts;


use AlpineIO\Atlas\Contracts\Reflectable;
use AlpineIO\Atlas\Traits\AttributeStorage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class AbstractTaxonomy
 * @package AlpineIO\Atlas\Abstracts
 * @property string $name;
 * @property string $description;
 * @property string $slug;
 */
abstract class AbstractTaxonomy implements Reflectable {
	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'post_date_gmt';
	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
	 */
	const UPDATED_AT = 'post_modified_gmt';	
	use AttributeStorage;

	protected static $postTypes = [];
	protected static $labels;
	protected static $taxonomy;
	protected static $settings = [ ];
	protected static $unguarded = [];
	protected $id;
	protected $term;

	/**
	 * The attributes that should be visible in arrays.
	 *
	 * @var array
	 */
	protected $visible = [ ];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [ '_edit_last', '_edit_lock' ];

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [ ];

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
				'name' => $this->term->name,
				'description'  => $this->term->description,
				'slug'  => $this->term->slug,
				'count'   => $this->term->count,
			];
			$this->fill( array_merge( $defaults, $meta ) );
		}
	}

	/**
	 * @param array $args
	 *
	 * @return Collection|$this[]
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
		return collect(array_map( function ( $term ) use ($object) {
			// Late static binding does not work on WPE's version of php 5.5.9 in closures
			//return self::newInstance($post);
			//return new static($post);
			return new $object($term);
		}, $terms ));
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
		if ( array_key_exists( $key, $this->attributes ) || $this->hasGetMutator( $key ) ) {
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
		if ( array_key_exists( $key, $this->attributes ) ) {
			return $this->attributes[$key ];
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

	/**
	 * Convert the model to its string representation.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return sprintf('<a href="%s">%s</a>', $this->getPermalink(), $this->name);
		//return $this->toJson();
	}

	/**
	 * Convert the model instance to JSON.
	 *
	 * @param  int $options
	 *
	 * @return string
	 */
	public function toJson( $options = 0 ) {
		return json_encode( $this->jsonSerialize(), $options );
	}

	/**
	 * Convert the object into something JSON serializable.
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}
	
	/**
	 * Convert the model instance to an array.
	 *
	 * @return array
	 */
	public function toArray() {
		$attributes = $this->attributesToArray();

		//return array_merge($attributes, $this->relationsToArray());
		return $attributes;
	}

	/**
	 * Convert the model's attributes to an array.
	 *
	 * @return array
	 */
	public function attributesToArray() {
		$attributes = $this->getArrayableAttributes();
		/*
		// If an attribute is a date, we will cast it to a string after converting it
		// to a DateTime / Carbon instance. This is so we will get some consistent
		// formatting while accessing attributes vs. arraying / JSONing a model.
		foreach ( $this->getDates() as $key ) {
			if ( ! isset( $attributes[ $key ] ) ) {
				continue;
			}
			$attributes[ $key ] = $this->serializeDate(
				$this->asDateTime( $attributes[ $key ], $key )
			);
		}

		$mutatedAttributes = $this->getMutatedAttributes();
		// We want to spin through all the mutated attributes for this model and call
		// the mutator for the attribute. We cache off every mutated attributes so
		// we don't have to constantly check on attributes that actually change.
		foreach ( $mutatedAttributes as $key ) {
			if ( ! array_key_exists( $key, $attributes ) ) {
				continue;
			}
			$attributes[ $key ] = $this->mutateAttributeForArray(
				$key, $attributes[ $key ]
			);
		}
		// Next we will handle any casts that have been setup for this model and cast
		// the values to their appropriate type. If the attribute has a mutator we
		// will not perform the cast on those attributes to avoid any confusion.
		foreach ( $this->getCasts() as $key => $value ) {
			if ( ! array_key_exists( $key, $attributes ) ||
			     in_array( $key, $mutatedAttributes )
			) {
				continue;
			}
			$attributes[ $key ] = $this->castAttribute(
				$key, $attributes[ $key ]
			);
			if ( $attributes[ $key ] && ( $value === 'date' || $value === 'datetime' ) ) {
				$attributes[ $key ] = $this->serializeDate( $attributes[ $key ] );
			}
		}
		// Here we will grab all of the appended, calculated attributes to this model
		// as these attributes are not really in the attributes array, but are run
		// when we need to array or JSON the model for convenience to the coder.
		foreach ( $this->getArrayableAppends() as $key ) {
			$attributes[ $key ] = $this->mutateAttributeForArray( $key, null );
		}
		*/
		return $attributes;
	}

	/**
	 * Get an attribute array of all arrayable attributes.
	 *
	 * @return array
	 */
	protected function getArrayableAttributes() {
		return $this->getArrayableItems( $this->attributes );
	}

	/**
	 * Get an attribute array of all arrayable values.
	 *
	 * @param  array $values
	 *
	 * @return array
	 */
	protected function getArrayableItems( array $values ) {
		if ( count( $this->getVisible() ) > 0 ) {
			return array_intersect_key( $values, array_flip( $this->getVisible() ) );
		}

		return array_diff_key( $values, array_flip( $this->getHidden() ) );
	}

	/**
	 * Get the visible attributes for the model.
	 *
	 * @return array
	 */
	public function getVisible() {
		return $this->visible;
	}

	/**
	 * Get the hidden attributes for the model.
	 *
	 * @return array
	 */
	public function getHidden() {
		return $this->hidden;
	}


}