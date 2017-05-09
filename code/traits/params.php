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
	 * Return the first parameter which matches the name.
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
	 * Return parameter as an array of values
	 *
	 * @param array  $params
	 * @param string $incomingName
	 * @param null   $default
	 *
	 * @return array
	 */
	public function paramArray( $params, $incomingName, $default = null ) {
		return $this->parseParam(
			array_key_exists( $incomingName, $params )
				? $params[ $incomingName ]
				: ( is_array( $default ) ? $default : [ $default ] )
		);
	}

	/**
	 * Parse passed parameter by config.parameter_separator (like a csv string).
	 *
	 * @param string|array $value
	 *
	 * @return array
	 */
	protected function parseParam( $value ) {
		return is_array( $value )
			? $value
			: array_filter(
				explode(
					$this->config()->get( 'parameter_separator' ),
					$value
				)
			);
	}
}