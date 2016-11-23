<?php
namespace Modular;

use Modular\Interfaces\Exception as ExceptionInterface;
use Modular\Exceptions\Exception;

trait debugging {
	/**
	 * @param int $level create debugger with this log level, or set the current log level if already created
	 * @return \Modular\Debugger
	 */
	public static function debugger($level = Debugger::LevelFromEnv) {
		/** @var Debugger $debugger */
		static $debugger;
		if ($debugger) {
			if (func_num_args()) {
				$debugger->level($level);
			}
		} else {
			// 'Debugger' is a service name set on Injector
			$debugger = \Injector::inst()->get('Debugger', $level);
		}
		return $debugger;
	}

	public static function debug_read_log($nl2br = false) {
		return static::debugger()->readLog($nl2br);
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

	/**
	 * @param string $message
	 * @throws null
	 */
	public static function debug_error($message) {
		static::debugger()->error($message, get_called_class());
	}

	/**
	 * @param ExceptionInterface $exception to log message from
	 * @throws Exception
	 */
	public function debug_fail(ExceptionInterface $exception) {
		$this->debugger()->fail($exception->getMessage(), $exception->getFile() . ':' . $exception->getLine(), $exception);
	}
}