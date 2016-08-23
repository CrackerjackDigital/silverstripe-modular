<?php
namespace Modular;

use Quaff\Exceptions\Exception;

trait debugging {
	/**
	 * @param int $level create debugger with this log level, or set the current log level if already created
	 * @return \Modular\Debugger
	 */
	public static function debugger($level = Debugger::DefaultDebugLevel) {
		/** @var Debugger $debugger */
		static $debugger;
		if ($debugger) {
			if (func_num_args()) {
				$debugger->level($level);
			}
		} else {
			$debugger = \Injector::inst()->get('Modular\Debugger', $level);
		}
		return $debugger;
	}

	public static function debug_message($message, $level) {
		static::debugger()->log($message, $level, get_called_class());
	}

	public static function debug_info($message) {
		static::debugger()->info($message, get_called_class());
	}

	public static function debug_trace($message) {
		static::debugger()->trace($message, get_called_class());
	}

	public static function debug_warn($message) {
		static::debugger()->warn($message, get_called_class());
	}

	public static function debug_error($message) {
		static::debugger()->error($message, get_called_class());
	}

	/**
	 * @param      $message
	 * @param null $httpResponceCode if provided then will force and httpError with this code.
	 * @throws \Modular\Exceptions\Debug
	 * @throws \SS_HTTPResponse_Exception
	 */
	public function debug_fail($message, $httpResponceCode = null) {
		$this->debugger()->error($message, get_called_class());
		if ($httpResponceCode && !\Director::is_cli()) {
			if (\Director::isLive()) {
				Controller::curr()->httpError($httpResponceCode, get_called_class());
			} else {
				Controller::curr()->httpError($httpResponceCode, get_called_class() . ":" . $message);
			}
		} else {
			throw new Exception($message, $httpResponceCode);
		}
	}
}