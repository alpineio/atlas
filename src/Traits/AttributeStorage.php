<?php


namespace AlpineIO\Atlas\Traits;


use Illuminate\Support\Str;

trait AttributeStorage {

	/**
	 * @var array Fields to mutate to date object
	 */
	protected $dates = [ ];
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ ];
	/**
	 * The Post's meta values.
	 *
	 * @var array
	 */
	protected $attributes = [ ];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [ ];
	
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
}