<?php


namespace AlpineIO\Atlas\Traits;


trait AttributeStorage {
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [ ];
	
	private static $unguarded;
	
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
}