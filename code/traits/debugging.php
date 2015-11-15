<?php
namespace Modular;

trait debugging {
	private static $debugging_level = \ModularDebugger::DebugOff;

	/**
	 * @param int $level
	 * @return mixed
	 */
	public static function debugging($level = \ModularDebugger::DefaultDebugLevel) {
		return \Strings::debugger($level);
	}
	public static function set_debugging_level($level) {
		\Config::inst()->update(get_called_class(), 'debugging_level', $level);
	}

	/**
	 * Create a ModularDebugger for provided level or get it from the per-level cache.
	 *
	 * @param $level
	 * @return mixed
	 */
	public static function debugger($level) {
		static $cache = [];
		if (!isset($cache[$level])) {
			$cache[$level] = new \ModularDebugger($level);
		}
		return $cache[$level];
	}
}