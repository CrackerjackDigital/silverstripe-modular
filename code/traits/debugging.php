<?php
namespace Modular;

trait debugging {
	public static function debugger($shared = true) {
		static $debugger;
		if (!$debugger) {
			if ($shared) {
				$debugger = \Injector::inst()->get('Modular\Debugger');
			} else {
				$debugger = \Injector::inst()->create('Modular\Debugger');
			}
		}
		return $debugger;
	}
	/**
	 * @param string $message
	 * @param string  $source
	 */
	public static function log_error($message, $source = '') {
		static::debugger()->error($message, $source);
	}

	/**
	 * @param string $message
	 * @param mixed  $level
	 * @param string $source
	 */
	public static function log_message($message, $level = Debugger::DebugInfo, $source = '') {
		static::debugger()->log($message, $level, $source);
	}
}