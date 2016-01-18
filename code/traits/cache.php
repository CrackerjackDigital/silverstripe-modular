<?php
namespace Modular;

trait cache {
	/**
	 * @param      $key
	 * @param mixed|callable $value
	 * @return null
	 */
	public static function cache($key, $value = null) {
		static $cache = [];

		if (func_num_args() == 1) {
			if (array_key_exists($key, $cache)) {
				$value = $cache[$key];
			}
		} else {
			if (is_callable($value)) {
				$value = $value();
			}
		}
		if (self::enabled()) {
			$cache[$key] = $value;
		}

		return $value;
	}

	private static function enabled() {
		return \Config::inst()->get(get_called_class(), 'cache_enabled');
	}
}