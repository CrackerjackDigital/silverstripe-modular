<?php

namespace Modular\Traits;

use Session;

/**
 * Trait transients are values which persist in the session but once their value is fetched they are cleared from the session though cached within
 * the request for re-use. In subsequent requests they will no longer be in the session.
 *
 * @package Modular\Traits
 */
trait transients {
	/**
	 * Sets all passed values as transients, e.g. from a form post so returning to form will restore values.
	 *
	 * @param array  $values        map of [ name => value ] pairs to set
	 * @param bool   $clearExisting all transient values for the object before setting new ones.
	 * @param string $className     optional to filter what fields are returned, e.g. the className against which the transient was stored.
	 *                              if this is set to '' then a 'global' setting is selected.
	 *
	 * @return array map of all transient [ name => value ] pairs currently set which match the key of those passed in
	 *
	 */
	public static function transient_values( array $values = [], $clearExisting = false, $className = null ) {
		if ( $clearExisting ) {
			self::transient_values_clear( $className );
		}
		foreach ( $values as $key => $value ) {
			self::transient_value( $key, $value, $className );
		}
		$prefix     = explode( '.', self::transient_key( '', $className ) );
		$transients = [];

		$sessionValues = Session::get_all();

		foreach ( $sessionValues as $key => $value ) {
			$pfx = $prefix;
			if ( ( $key == array_shift( $pfx ) ) && is_array( $value ) ) {
				// e.g. key is 'TXV' and value is [ 'FormName' => [ 'FieldName' => value ]]
				// then key is 'FormName' and value is [ 'FieldName' => value ]
				// then key is 'FieldName' and value is value
				while ( $part = array_shift( $pfx ) ) {
					if ( ! array_key_exists( $part, $value ) ) {
						break;
					}
					$transients = $value[ $part ];
				}
			}
		}

		return $transients ?: [];
	}

	/**
	 * Clear matching transient values from session.
	 *
	 * @param null $className
	 */
	public static function transient_values_clear( $className = null ) {
		$prefix = self::transient_key( '', $className );
		Session::clear( $prefix );
	}

	/**
	 * Sets or returns a value for passing e.g. between page views etc. ('transient values')
	 *
	 * @param string $fieldName
	 * @param mixed  $value if parameter is provided will be set, if null then value will be unset
	 * @param string $className
	 *
	 * @return mixed
	 */
	public static function transient_value( $fieldName, $value = null, $className = null ) {
		static $transients = [];

		$key = self::transient_key( $fieldName, $className );

		if ( func_num_args() > 1 ) {
			if ( is_null( $value ) ) {
				unset( $transients[ $key ] );
				Session::clear( $key );
			} else {
				$transients[ $key ] = $value;
				Session::set( $key, $value );
			}
		} else {
			if ( array_key_exists( $key, $transients ) ) {
				$value = $transients[ $key ];
			} else {
				$value              = Session::get( $key );
				$transients[ $key ] = $value;
			}
			Session::clear( $key );
		}

		return $value;
	}

	/**
	 * Return a unique key for stashing things ('transient values') in the session between e.g. pages or form renders.
	 *
	 * @param string $name      , e.g. of a field
	 * @param string $className if not passed (or default null) then called class will be used, if '' then no class name will be used.
	 *
	 * @return string
	 */
	public static function transient_key( $name, $className = null ) {
		$className = is_null( $className ) ? get_called_class() : '';

		return rtrim( "TXV.$className.$name", '.' );
	}
}
