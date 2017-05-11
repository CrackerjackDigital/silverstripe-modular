<?php

namespace Modular\Traits;

/**
 * Trait adds simple md5 hashing functions
 *
 * @package Modular
 */
trait md5 {
	/**
	 * Return md5 hash of value.
	 *
	 * @param mixed  $value
	 * @param null   $seed not used
	 * @param string $method used to calculate the hash
	 * @param bool   $raw
	 *
	 * @return string
	 */
	public static function hash( $value, $seed = null, &$method = 'md5', $raw = false ) {
		$method = 'md5';
		return md5( $value, $raw );
	}

	/**
	 *
	 * @param string $fileName
	 *
	 * @param null   $seed not used
	 *
	 * @param string $method
	 *
	 * @return string
	 */
	public static function hash_file( $fileName, $seed = null, &$method = 'md5' ) {
		$method = 'md5';
		return md5_file( $fileName );
	}

	/**
	 * Return the length of a hashed value if known, or false if not known/predictable.
	 *
	 * @return int
	 */
	public static function hash_length() {
		return 32;
	}
}