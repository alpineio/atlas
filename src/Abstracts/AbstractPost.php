<?php


namespace AlpineIO\Atlas\Abstracts;

use AlpineIO\Atlas\Post;
use Carbon\Carbon;
use DateTime;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use WP_Post;

/**
 * Class AbstractPost
 * @package CORA\Abstracts
 *
 * @property int $id ID of project.
 * @property string $post_title Title.
 * @property string $post_status Status.
 */
abstract class AbstractPost {
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

	protected static $contentAutoAppend = true;
	
	public static $icon = 'dashicons-admin-site';

	/**
	 * Indicates whether attributes are snake cased on arrays.
	 *
	 * @var bool
	 */
	public static $snakeAttributes = true;
	/**
	 * Indicates if all mass assignment is enabled.
	 *
	 * @var bool
	 */
	protected static $unguarded = true;
	/**
	 * The cache of the mutated attributes for each class.
	 *
	 * @var array
	 */
	protected static $mutatorCache = [ ];
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected static $post_type;
	/**
	 * The product (post) ID.
	 *
	 * @var int
	 */
	public $id = 0;
	/**
	 * $post Stores post data.
	 *
	 * @var $post WP_Post
	 */
	public $post = null;
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = true;
	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = [ ];
	/**
	 * The loaded relationships for the model.
	 *
	 * @var array
	 */
	protected $relations = [ ];

	/**
	 * The taxonomies.
	 *
	 * @var array
	 */
	protected $taxonomies = [ ];
	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [ ];
	/**
	 * The Post's meta values.
	 *
	 * @var array
	 */
	protected $attributes = [ ];
	/**
	 * @var array Fields to mutate to date object
	 */
	protected $dates = [ ];
	/**
	 * @var string Prefix for meta values
	 */
	protected $meta_prefix = '';
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ ];

	/**
	 * The storage format of the model's date columns.
	 *
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [ ];

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
	 * Constructor gets the post object and sets the ID for the loaded product.
	 *
	 * @param int|WP_Post|object $object Object ID, WP_Post object
	 */
	public function __construct( $object ) {
		if ( $object ) {

			if ( is_numeric( $object ) ) {
				$this->id   = absint( $object );
				$this->post = get_post( $this->id );
			} elseif ( $object instanceof WP_Post ) {
				$this->id   = absint( $object->id );
				$this->post = $object->post;
			} elseif ( isset( $object->ID ) ) {
				$this->id   = absint( $object->ID );
				$this->post = $object;
			}
			if ( $this->post->post_type != $this->getPostType() ) {
				throw new \DomainException( 'Invalid post type for this object. Can\'t assign post_type: '
				                            . $this->post->post_type . ' to ' . get_class( $this ) );
			}
			$meta     = get_post_meta( $this->id );
			$defaults = [
				'post_status' => $this->post->post_status,
				'post_title'  => $this->post->post_title,
				'post_type'   => $this->post->post_type,
			];
			$this->fill( array_merge( $defaults, $meta ) );
		}
	}
	
	public static function addFilters() {
		if (static::$contentAutoAppend) {
			add_filter( 'the_content', [ static::class, 'contentFilter'] );
		}
	}

	public static function contentFilter( $content ) {
		if ( is_singular( static::getPostType() ) ) {
			return $content . static::getAppendContent(new static(get_the_ID()));
		}
		return $content;	
	}

	public static function getAppendContent( Post $object) {
		if ( ! empty($object->toArray() ) ) {
			$content = null;	
			foreach($object->toArray() as $attribute => $value) {
				// skip relates for now
				if ( Str::startsWith($attribute, '__relate')) {
					continue;	
				}
				
				$content .= sprintf('<dt>%s</dt>', Str::title(str_replace('_', ' ', $attribute)));				
				if ( is_array($value)) {
					foreach ($value as $row ) {
						$content .= sprintf('<dd>%s</dd>', $row);
					}
				} else {
					$content .= sprintf('<dd>%s</dd>', $value);
				}
			}
			return sprintf('<dl>%s</dl>', $content);
		}	
		
	}
	
	/**
	 * @return string
	 */
	public static function getPostType() {
		if ( isset( static::$post_type ) ) {
			return static::$post_type;
		}

		return Str::snake( static::getReflection()->getShortName(), '-' );
	}

	public static function getReflection() {
		return new \ReflectionClass( get_called_class() );
	}

	public function getFieldType( $field, $value = null ) {
		$method = sprintf('get%sField', Str::studly($field));
		if (method_exists($this,$method)) {
			return $this->$method($value);
		}
	}
	
	/**
	 * Fill the model with an array of attributes.
	 *
	 * @param  array $attributes
	 *
	 * @return $this
	 *
	 */
	public function fill( array $attributes ) {
		//$totallyGuarded = $this->totallyGuarded();
		foreach ( $this->fillableFromArray( $attributes ) as $key => $value ) {
			//$key = $this->removeTableFromKey($key);
			// The developers may choose to place some attributes in the "fillable"
			// array, which means only those attributes may be set through mass
			// assignment to the model, and all others will just be ignored.
			if ( $this->isFillable( $key ) ) {
				if ( is_array( $value ) && count( $value ) == 1 ) {
					$value = $value[0];
				}
				$this->setAttribute( $key, $value );
			}
		}

		return $this;
	}

	/**
	 * Get the fillable attributes of a given array.
	 *
	 * @param  array $attributes
	 *
	 * @return array
	 */
	protected function fillableFromArray( array $attributes ) {
		if ( count( $this->getFillable() ) > 0 && ! static::$unguarded ) {
			return array_intersect_key( $attributes, array_flip( $this->getFillable() ) );
		}

		return $attributes;
	}

	/**
	 * Get the fillable attributes for the model.
	 *
	 * @return array
	 */
	public function getFillable() {
		return $this->fillable;
	}

	/**
	 * Determine if the given attribute may be mass assigned.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	public function isFillable( $key ) {
		if ( static::$unguarded ) {
			return true;
		}
		// If the key is in the "fillable" array, we can of course assume that it's
		// a fillable attribute. Otherwise, we will check the guarded array when
		// we need to determine if the attribute is black-listed on the model.
		if ( in_array( $key, $this->getFillable() ) ) {
			return true;
		}
		if ( $this->isGuarded( $key ) ) {
			return false;
		}

		return empty( $this->getFillable() ) && ! Str::startsWith( $key, '_' );
	}

	/**
	 * Determine if the given key is guarded.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	public function isGuarded( $key ) {
		return in_array( $key, $this->getGuarded() ) || $this->getGuarded() == [ '*' ];
	}

	/**
	 * Get the guarded attributes for the model.
	 *
	 * @return array
	 */
	public function getGuarded() {
		return $this->guarded;
	}

	/**
	 * Set a given attribute on the model.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 *
	 * @return $this
	 */
	public function setAttribute( $key, $value ) {
		// First we will check for the presence of a mutator for the set operation
		// which simply lets the developers tweak the attribute as it is set on
		// the model, such as "json_encoding" an listing of data for storage.
		if ( $this->hasSetMutator( $key ) ) {
			$method = 'set' . Str::studly( $key ) . 'Attribute';

			return $this->{$method}( $value );
		}
		// If an attribute is listed as a "date", we'll convert it from a DateTime
		// instance into a form proper for storage on the database tables using
		// the connection grammar's date format. We will auto set the values.
		elseif ( $value && ( in_array( $key, $this->getDates() ) || $this->isDateCastable( $key ) ) ) {
			$value = $this->fromDateTime( $value, $key );
		}
		if ( $this->isJsonCastable( $key ) && ! is_null( $value ) ) {
			$value = $this->asJson( $value );
		}
		$this->attributes[ $key ] = $value;

		return $this;
	}

	/**
	 * Determine if a set mutator exists for an attribute.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	public function hasSetMutator( $key ) {
		return method_exists( $this, 'set' . Str::studly( $key ) . 'Attribute' );
	}

	/**
	 * Get the attributes that should be converted to dates.
	 *
	 * @param bool $withFormats
	 *
	 * @return array
	 */
	public function getDates( $withFormats = false ) {
		$defaults = [ static::CREATED_AT, static::UPDATED_AT ];
		if ( $withFormats ) {
			$dates = $this->dates;
		} else {
			$dates = array_keys( $this->dates );
		}

		return $this->timestamps ? array_merge( $dates, $defaults ) : $dates;
	}

	/**
	 * Determine whether a value is Date / DateTime castable for inbound manipulation.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	protected function isDateCastable( $key ) {
		return $this->hasCast( $key, [ 'date', 'datetime' ] );
	}

	/**
	 * Determine whether an attribute should be cast to a native type.
	 *
	 * @param  string $key
	 * @param  array|string|null $types
	 *
	 * @return bool
	 */
	public function hasCast( $key, $types = null ) {
		if ( array_key_exists( $key, $this->getCasts() ) ) {
			return $types ? in_array( $this->getCastType( $key ), (array) $types, true ) : true;
		}

		return false;
	}

	/**
	 * Get the casts array.
	 *
	 * @return array
	 */
	public function getCasts() {
		/*
		if ( $this->getIncrementing() ) {
			return array_merge( [
				$this->getKeyName() => 'int',
			], $this->casts );
		}
		*/

		return $this->casts;
	}

	/**
	 * Get the type of cast for a model attribute.
	 *
	 * @param  string $key
	 *
	 * @return string
	 */
	protected function getCastType( $key ) {
		return trim( strtolower( $this->getCasts()[ $key ] ) );
	}

	/**
	 * Convert a DateTime to a storable string.
	 *
	 * @param  \DateTime|int $value
	 *
	 * @param null $key
	 *
	 * @return string
	 */
	public function fromDateTime( $value, $key = null ) {
		$format = $this->getDateFormat( $key );
		$value  = $this->asDateTime( $value, $key );

		return $value->format( $format );
	}

	/**
	 * Get the format for database stored dates.
	 *
	 * @param null $key
	 *
	 * @return string
	 */
	protected function getDateFormat( $key = null ) {
		if ( $key && ! empty( $dates = $this->getDates( true ) ) ) {
			if ( ! empty( $format = $dates[ $key ] ) ) {
				return $format;
			}
		}

		return $this->dateFormat ?: 'Y-m-d H:i:s';
	}

	/**
	 * Return a timestamp as DateTime object.
	 *
	 * @param  mixed $value
	 *
	 * @param null $key
	 *
	 * @return Carbon
	 */
	protected function asDateTime( $value, $key = null ) {
		if ( empty($value)) {
			return;	
		}
		// If this value is already a Carbon instance, we shall just return it as is.
		// This prevents us having to re-instantiate a Carbon instance when we know
		// it already is one, which wouldn't be fulfilled by the DateTime check.
		if ( $value instanceof Carbon ) {
			return $value;
		}
		// If the value is already a DateTime instance, we will just skip the rest of
		// these checks since they will be a waste of time, and hinder performance
		// when checking the field. We will just return the DateTime right away.
		if ( $value instanceof DateTimeInterface ) {
			return new Carbon(
				$value->format( 'Y-m-d H:i:s.u' ), $value->getTimezone()
			);
		}
		// If this value is an integer, we will assume it is a UNIX timestamp's value
		// and format a Carbon object from this timestamp. This allows flexibility
		// when defining your date fields as they might be UNIX timestamps here.
		if ( is_numeric( $value ) ) {
			return Carbon::createFromTimestamp( $value );
		}

		// If the value is in simply year, month, day format, we will instantiate the
		// Carbon instances from that format. Again, this provides for simple date
		// fields on the database, while still supporting Carbonized conversion.
		if ( preg_match( '/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value ) ) {
			return Carbon::createFromFormat( 'Y-m-d', $value )->startOfDay();
		}
		// Finally, we will just assume this date is in the format used by default on
		// the database connection and use that format to create the Carbon object
		// that is returned back out to the developers after we convert it here.
		$format = $this->getDateFormat( $key );
		$timezone = get_option('timezone_string');
		//var_dump($format);
		//var_dump($value);
		$date   = Carbon::createFromFormat( $format, $value, $timezone );
		// If date doesn't have hours then make it start of day
		if ( empty( $date->hour ) ) {
			return $date->startOfDay();
		}

		return $date;
	}

	/**
	 * Determine whether a value is JSON castable for inbound manipulation.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	protected function isJsonCastable( $key ) {
		return $this->hasCast( $key, [ 'array', 'json', 'object', 'collection' ] );
	}

	/**
	 * Encode the given value as JSON.
	 *
	 * @param  mixed $value
	 *
	 * @return string
	 */
	protected function asJson( $value ) {
		return json_encode( $value );
	}

	/**
	 * @param WP_Post|null $post
	 *
	 * @return self
	 */
	public static function newInstance( $post = null ) {
		$class  = static::class;
		$object = new $class( $post );
		dd( $object );
	}

	/**
	 * Get all of the models from the database.
	 *
	 * @return self[]
	 */
	public static function all() {
		//$columns  = is_array( $columns ) ? $columns : func_get_args();

		$posts  = get_posts( [ 'post_type' => static::getPostType(), 'posts_per_page' => - 1 ] );
		$object = static::class;

		return array_map( function ( $post ) use ( $object ) {
			// Late static binding does not work on WPE's version of php 5.5.9 in closures
			//return self::newInstance($post);
			//return new static($post);
			return new $object( $post );
		}, $posts );
	}

	/**
	 * @return string
	 */
	public static function getSlug() {
		if ( isset( static::$post_type ) ) {
			return static::$post_type;
		}

		return Str::slug( Str::snake( static::getReflection()->getShortName(), ' ' ) );
	}

	public function getPermalink() {
		return get_permalink( $this->getId() );
	}

	/**
	 * Return the object ID
	 *
	 * @return int object (post) ID
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Append attributes to query when building a query.
	 *
	 * @param  array|string $attributes
	 *
	 * @return $this
	 */
	public function append( $attributes ) {
		if ( is_string( $attributes ) ) {
			$attributes = func_get_args();
		}
		$this->appends = array_unique(
			array_merge( $this->appends, $attributes )
		);

		return $this;
	}

	/**
	 * Set the accessors to append to model arrays.
	 *
	 * @param  array $appends
	 *
	 * @return $this
	 */
	public function setAppends( array $appends ) {
		$this->appends = $appends;

		return $this;
	}

	/**
	 * __isset function.
	 *
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		if ( isset( $this->attributes[ $key ] ) || isset( $this->relations[ $key ] ) ) {
			return true;
		}
		if ( method_exists( $this, $key ) && $this->$key && isset( $this->relations[ $key ] ) ) {
			return true;
		}

		return $this->hasGetMutator( $key ) && ! is_null( $this->getAttributeValue( $key ) );
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
		// If the attribute has a get mutator, we will call that then return what
		// it returns as the value, which is useful for transforming values on
		// retrieval from the model to a form that is more useful for usage.
		if ( $this->hasGetMutator( $key ) ) {
			return $this->mutateAttribute( $key, $value );
		}
		// If the attribute exists within the cast array, we will convert it to
		// an appropriate native PHP type dependant upon the associated value
		// given with the key in the pair. Dayle made this comment line up.
		if ( $this->hasCast( $key ) ) {
			return $this->castAttribute( $key, $value );
		}
		// If the attribute is listed as a date, we will convert it to a DateTime
		// instance on retrieval, which makes it quite convenient to work with
		// date fields without having to create a mutator for each property.
		if ( in_array( $key, $this->getDates() ) && ! is_null( $value ) ) {
			return $this->asDateTime( $value, $key );
		}

		return $value;
	}

	/**
	 * Get an attribute from the $attributes array.
	 *
	 * @param  string $key
	 *
	 * @return mixed
	 */
	protected function getAttributeFromArray( $key ) {
		if ( array_key_exists( $key, $this->attributes ) ) {
			return $this->attributes[ $key ];
		}

		return null;
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

	/**
	 * Cast an attribute to a native PHP type.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	protected function castAttribute( $key, $value ) {
		if ( is_null( $value ) ) {
			return $value;
		}
		switch ( $this->getCastType( $key ) ) {
			case 'int':
			case 'integer':
				return (int) $value;
			case 'real':
			case 'float':
			case 'double':
				return (float) $value;
			case 'string':
				return (string) $value;
			case 'bool':
			case 'boolean':
				return (bool) $value;
			case 'object':
				return $this->fromJson( $value, true );
			case 'array':
			case 'json':
				return $this->fromJson( $value );
			case 'collection':
				//return new BaseCollection( $this->fromJson( $value ) );
				throw new \Exception( 'Not implemented' );
			case 'date':
			case 'datetime':
				return $this->asDateTime( $value );
			case 'timestamp':
				throw new \Exception( 'Not implemented' );
			//return $this->asTimeStamp( $value );
			default:
				return $value;
		}
	}

	/**
	 * Decode the given JSON back into an array or object.
	 *
	 * @param  string $value
	 * @param  bool $asObject
	 *
	 * @return mixed
	 */
	public function fromJson( $value, $asObject = false ) {
		return json_decode( $value, ! $asObject );
	}

	/**
	 * Convert the model to its string representation.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->toJson();
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

	/**
	 * Prepare a date for array / JSON serialization.
	 *
	 * @param  \DateTime $date
	 *
	 * @return string
	 */
	protected function serializeDate( DateTime $date ) {
		return $date->format( $this->getDateFormat() );
	}

	/**
	 * Get the mutated attributes for a given instance.
	 *
	 * @return array
	 */
	public function getMutatedAttributes() {
		$class = static::class;
		if ( ! isset( static::$mutatorCache[ $class ] ) ) {
			static::cacheMutatedAttributes( $class );
		}

		return static::$mutatorCache[ $class ];
	}

	/**
	 * Extract and cache all the mutated attributes of a class.
	 *
	 * @param  string $class
	 *
	 * @return void
	 */
	public static function cacheMutatedAttributes( $class ) {
		$mutatedAttributes = [ ];
		// Here we will extract all of the mutated attributes so that we can quickly
		// spin through them after we export models to their array form, which we
		// need to be fast. This'll let us know the attributes that can mutate.
		if ( preg_match_all( '/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode( ';', get_class_methods( $class ) ),
			$matches ) ) {
			foreach ( $matches[1] as $match ) {
				if ( static::$snakeAttributes ) {
					$match = Str::snake( $match );
				}
				$mutatedAttributes[] = lcfirst( $match );
			}
		}
		static::$mutatorCache[ $class ] = $mutatedAttributes;
	}

	/**
	 * Get the value of an attribute using its mutator for array conversion.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 *
	 * @return mixed
	 */
	protected function mutateAttributeForArray( $key, $value ) {
		$value = $this->mutateAttribute( $key, $value );

		return $value instanceof Arrayable ? $value->toArray() : $value;
	}

	/**
	 * __get function.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	/*
	public function __get( $key ) {
		$value = get_post_meta( $this->id, $this->meta_prefix . $key, true );

		// Mutate date objects
		if ( in_array( $key, $this->getDates() ) ) {
			$value = new Carbon( $value );
		}
		if ( false !== $value ) {
			$this->$key = $value;
		}

		return $value;
	}
	*/

	/**
	 * Get all of the appendable values that are arrayable.
	 *
	 * @return array
	 */
	protected function getArrayableAppends() {
		if ( ! count( $this->appends ) ) {
			return [ ];
		}

		return $this->getArrayableItems(
			array_combine( $this->appends, $this->appends )
		);
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
	 * Get the product's post data.
	 *
	 * @return object
	 */
	public function get_post_data() {
		return $this->post;
	}

	/**
	 * Get all of the current attributes on the model.
	 *
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * Get the title of the post.
	 *
	 * @return string
	 */
	public function getTitle() {
		return get_the_title( $this->post );
	}

}