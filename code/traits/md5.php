<?php

namespace Modular\Traits;

use Director;

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
	 * @param mixed  $salt   prepended to value before hashing if provided
	 * @param string $method used to calculate the hash
	 * @param bool   $raw    see md5()
	 *
	 * @return string
	 */
	public static function hash( $value, $salt = '', &$method = 'md5', $raw = false ) {
		$method = 'md5';

		return md5( $salt . $value, $raw );
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
	public static function hash_file( $fileName, $salt = '', &$method = 'md5', $raw = false ) {
		$method = 'md5';

		return md5( $salt . md5_file( Director::getAbsFile( $fileName ) ), $raw );
	}

	/**
	 * Return the length of a hashed value if known, or false if not known/predictable.
	 *
	 * @return int
	 */
	public static function hash_length() {
		return 32;
	}

	/**
	 * Return the name of method used to get the hash.
	 *
	 * @param mixed $for an identifier e.g. if for mysql then could pass 'mysql' or if same in php and mysql just return same value
	 *                   in this case it is ignored as mysql and php do both use 'md5' as the method name.
	 *
	 * @return string
	 */
	public static function hash_method_name($for = null) {
		return 'md5';
	}
}
