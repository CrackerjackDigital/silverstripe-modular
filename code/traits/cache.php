<?php
namespace Modular\Traits;

trait cache {
	protected static $cache_enabled = true;
	
	/**
	 * Lookup (if no value provided) or store value in cache.
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @return mixed
	 */
	public static function cache($key, $value = null) {
		static $cache = [];
		
		if (self::cache_enabled()) {
			if (func_num_args() == 1) {
				// getter
				if (array_key_exists($key, $cache)) {
					$value = $cache[ $key ];
				}
			} else {
				// setter
				$cache[ $key ] = $value;
			}
			return $value;
		}
	}
	
	private static function cache_enabled() {
		return \Config::inst()->get(get_called_class(), 'cache_enabled');
	}
}