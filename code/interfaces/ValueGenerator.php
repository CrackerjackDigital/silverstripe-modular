<?php
namespace Modular\Interfaces;

interface ValueGenerator {
	
	/**
	 * Return true if a new value should be generated, false otherwise. e.g. a test if a value is already set on a
	 * model could return false if the value should not be overwritten, or true if a new value should always be
	 * generated.
	 *
	 * @return bool
	 */
	public function shouldGenerate();
	
	/**
	 * Return a new value for the extended model if shouldGenerate returns true,
	 * or the existing value from the extended model if shouldGenerate returned false.
	 *
	 * @param mixed $seed value to generate new value from, value to return etc
	 * @return mixed
	 */
	public static function generator($seed = null);
	
}