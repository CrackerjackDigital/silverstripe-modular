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

		if (!self::enabled()) {
			return null;
		}

		if (func_num_args() == 1) {
			if (array_key_exists($key, $cache)) {
				$value = $cache[$key];
			}
		} else {
			if (is_null($value)) {

				unset($cache[$key]);
			} elseif (is_callable($value)) {

				$cache[$key] = $value();
			} else {

				$cache[$key] = $value;
			}
		}
		return $value;
	}

	private static function enabled() {
		$enabled = \Config::inst()->get(get_called_class(), 'memo_enabled');

		return is_null($enabled)
			? true
			: $enabled;
	}
}