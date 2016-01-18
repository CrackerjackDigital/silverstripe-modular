<?php
namespace Modular;

trait debugging {
	private static $debugging_level = \ModularDebugger::DebugOff;

	/**
	 * @param int $level
	 * @return mixed
	 */
	public static function debugging($level = \ModularDebugger::DefaultDebugLevel, $prefix = null) {
		return \ModularDebugger::debugger($level, is_null($prefix) ? get_called_class() : $prefix);
	}
	public static function set_debugging_level($level) {
		\Config::inst()->update(get_called_class(), 'debugging_level', $level);
	}
}