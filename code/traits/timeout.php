<?php

namespace Modular\Traits;

/**
 * timeout configure and manage timeouts of scripts and sockets in milli seconds
 *
 * @package Modular\Traits
 */
trait timeout {
	/**
	 * Set execution time limit in seconds to either configured or passed value. Updates config.timeout (or value of constant named TimeoutConfigVar).
	 *
	 * @param null|int $timeoutMS value in milliseconds or null to reset to default seconds from php.ini
	 *
	 * @return bool|int either timeout as int or false if no timeout configured
	 * @throws \Exception if timeout is not null or not an int
	 */
	public function timeout( $timeoutMS = null, $setSocketTimeout = true ) {
		$configVarName = defined( 'static::TimeoutConfigVar' )
			? static::TimeoutConfigVar
			: 'timeout';

		$lastTimeoutMS = \Config::inst()->get( get_called_class(), $configVarName );
		if ( func_num_args() ) {
			if (is_numeric($timeoutMS)) {
				$timeoutMS = floor( $timeoutMS);
			}
			if ( ! ( is_int( $timeoutMS ) || is_null( $timeoutMS ) ) ) {
				throw new \InvalidArgumentException( "Invalid timeout '$timeoutMS'" );
			}
			\Config::inst()->update( get_called_class(), $configVarName, $timeoutMS );
		}
		$timeoutMS =  \Config::inst()->get( get_called_class(), $configVarName );

		if ( is_null( $timeoutMS ) ) {
			if (!is_null($lastTimeoutMS)) {
				// only update if we've changed previously, use ini_restore to get default value
				ini_restore( 'max_execution_time');
				set_time_limit( ini_get( 'max_execution_time' ) );
				if ($setSocketTimeout) {
					ini_restore('default_socket_timeout');
				}
			}
		} else {
			$timeoutSeconds = floor($timeoutMS);
			// set to configured value in seconds
			set_time_limit( $timeoutSeconds);
			ini_set('max_execution_time', $timeoutSeconds);
			if ($setSocketTimeout) {
				ini_set('default_socket_timeout', $timeoutSeconds);
			}
		}

		return is_null( $timeoutMS )
			? false
			: (int) $timeoutMS;

	}

}