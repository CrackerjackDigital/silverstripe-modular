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
	 * Sets or returns a value for passing e.g. between page views etc. ('transient values')
	 *
	 * @param string $fieldName
	 * @param mixed  $value if parameter is provided will be set, if null then value will be unset
	 *
	 * @return mixed
	 */
	public static function transient_value( $fieldName, $value = null) {
		static $transients = [];

		$key = self::transient_key( $fieldName );

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
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public static function transient_key( $fieldName ) {
		return get_called_class() . '.' . $fieldName;
	}
}