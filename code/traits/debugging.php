<?php
namespace Modular;

trait debugging {
	private static $debugging_level = \ModularDebugger::DebugOff;

	/**
	 * @return mixed
	 */
	public static function debugging($level = \ModularDebugger::DefaultDebugLevel) {
		return \ModularUtils::debugger($level);
	}
	public static function set_debugging_level($level) {
		\Config::inst()->update(get_called_class(), 'debugging_level', $level);
	}
}