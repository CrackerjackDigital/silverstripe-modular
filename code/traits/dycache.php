<?php
namespace Modular\Traits;

trait dycache {
	protected static $dycache_enabled = true;
	
	/**
	 * Dynamically cache a callable, each time it is fetched by key the callable will be called and the value returned.
	 * The result from the call itself is not cached, only the callable.
	 *
	 * @param string     $key
	 * @param callable $callable
	 * @return null|mixed
	 */
	public static function dycache($key, callable $callable = null) {
		static $dycache = [];
		
		$value = $callable;
		
		if (self::dycache_enabled()) {
			if (func_num_args() == 1) {
				// getter
				if (array_key_exists($key, $dycache)) {
					$callable = $dycache[ $key ];
					
					if (is_callable($callable)) {
						$value = $callable();
					}
				}
			} else {
				// setter
				if (is_callable($callable)) {
					$value = $callable();
				}
				$dycache[$key] = $callable;
			}
		} else {
			if (is_callable($callable)) {
				$value = $callable();
			}
		}
		return $value;
	}
	public static function dycache_enabled() {
		return \Config::inst()->get(get_called_class(), 'dycache_enabled');
	}
}