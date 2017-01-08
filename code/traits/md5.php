<?php
namespace Modular\Traits;
/**
 * Trait adds simple md5 hashing functions
 * @package Modular
 */
trait md5 {
	/**
	 * Return md5 hash of value.
	 *
	 * @param      $value
	 * @param bool $raw
	 * @return string
	 */
	public static function md5($value, $raw = false) {
		return md5($value, $raw);
	}
	
	/**
	 * Return the length of a hashed value if known, or false if not known/predictable.
	 * @return int
	 */
	public static function hash_length() {
		return 32;
	}
}