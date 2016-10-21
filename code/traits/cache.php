<?php
namespace Modular;

trait cache {
	/**
	 * @param                $key
	 * @param mixed|callable $value
	 * @return null
	 */
	public static function cache($key, $value = null) {
		static $cache = [];

		if (func_num_args() == 1) {
			// get by key
			if (array_key_exists($key, $cache)) {
				$value = $cache[ $key ];
			}
		} else {
			// set key = value
			if (is_callable($value)) {
				$value = $value();
			}
		}
		if (!self::cache_disabled()) {
			$cache[ $key ] = $value;
		}

		return $value;
	}

	private static function cache_disabled() {
		return \Config::inst()->get(get_called_class(), 'cache_disabled');
	}
}