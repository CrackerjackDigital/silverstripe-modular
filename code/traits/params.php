<?php

namespace Modular\Traits;

/**
 * Trait params add to class which deals with parameters, e.g. those coming
 * from query string. Provides handy functions for retrieving, parsing etc.
 *
 * @package Modular\Traits
 */
trait params {
	/**
	 * @return \Config_ForClass
	 */
	abstract public function config();

	/**
	 * Return the first value of the parameter which matches the name after splitting it (so if not a csv-like value will just return the value).
	 *
	 * @param array  $params
	 * @param string $incomingName
	 * @param null   $default
	 *
	 * @return mixed
	 */
	public function param( $params, $incomingName, $default = null ) {
		$array = $this->paramArray( $params, $incomingName, $default ) ?: [];

		return current( $array );
	}

	/**
	 * Return all values (split) which match the name.
	 *
	 * @param array       $params
	 * @param string      $incomingName
	 * @param array|mixed $default if not an array is converted to an array, pay attention to keys. null means no default
	 *
	 * @return array
	 */
	public function paramArray( $params, $incomingName, $default = null ) {
		$value = [];
		if (array_key_exists( $incomingName, $params)) {
			$value = $this->splitParam( $params[ $incomingName] );
		}
		return $value ?: $this->splitParam( $default);
	}

	/**
	 * Parse passed parameter out into values if it's not an array, otherwise return the value as is, caters for csv-live parameters being passed.
	 *
	 * @param string|array $value
	 *
	 * @param string       $separator if empty then config.parameter_separator will be used
	 *
	 * @return array
	 */
	protected function splitParam( $value, $separator = '' ) {
		return is_array( $value )
			? $value
			: array_filter(
				explode(
					((strlen($separator) == 0) ? $this->config()->get( 'parameter_separator' ) : $separator),
					$value
				)
			);
	}
}