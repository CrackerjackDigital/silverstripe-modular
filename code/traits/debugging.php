<?php
namespace Modular;

use Config;
use Modular\Helpers\Debugger;

trait debugging {
	private static $debugging_level = Debugger::DebugOff;

	/**
	 * @param int  $level
	 * @param null $prefix
	 * @return mixed
	 */
	public static function debugging($level = Debugger::DefaultDebugLevel, $prefix = null) {
		return Debugger::debugger($level, is_null($prefix) ? get_called_class() : $prefix);
	}

	public static function set_debugging_level($level) {
		Config::inst()->update(get_called_class(), 'debugging_level', $level);
	}
}