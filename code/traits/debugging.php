<?php
namespace Modular\Traits;

use Modular\Debugger;
use Modular\Interfaces\Exception as ExceptionInterface;
use Modular\Exceptions\Exception;
use Modular\ScopedReference;

trait debugging {
	/**
	 * @param int|null $level create debugger with this log level, or set the current log level if already created
	 * @param string   $source used in output, if not provided then the called class is used
	 * @return \Modular\Debugger
	 */
	public static function debugger($level = Debugger::LevelFromEnv, $source = '') {
		/** @var Debugger $debugger */
		static $debugger;
		if ($debugger) {
			if (func_num_args()) {
				$debugger->level($level);
				if (func_num_args() >= 2) {
					$debugger->source($source);
				}
			}
		} else {
			// 'Debugger' is a service name set on Injector which defaults to Modular\Debugger
			$debugger = \Injector::inst()->get('Debugger', $level, $source ?: get_called_class());
		}
		return $debugger;
	}

	/**
	 * Output the log so far to screen/page. $formatter can be:
	 *  -   null in which case if command line is used then no post-processing, otherwise nl2br for web
	 *  -   true for always nl2br, false for never nl2br
	 *  -   a function/function name to apply to the log before outputing (via ob_start)
	 *
	 * @param null $formatter
	 */
	public static function debug_output_log($formatter = null) {
		$formatter = is_bool(is_null($formatter) ? !\Director::is_cli() : $formatter)
			? ( $formatter ? 'nl2br' : null)
			: $formatter;

		ob_start($formatter);
		echo static::debug_read_log();
		ob_end_flush();
	}

	/**
	 * Return the contents of the current log (file).
	 * @return null|string
	 */
	public static function debug_read_log() {
		return static::debugger()->readLog();
	}

	/**
	 * @param $message
	 * @param $level
	 * @return void
	 */
	public static function debug_message($message, $level) {
		static::debugger()->log($message, $level, get_called_class());
	}

	/**
	 * @param $message
	 * @return void
	 */
	public static function debug_info($message) {
		static::debugger()->info($message, get_called_class());
	}

	/**
	 * @param $message
	 * @return void
	 */
	public static function debug_trace($message) {
		static::debugger()->trace($message, get_called_class());
	}

	/**
	 * @param $message
	 * @return void
	 */
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
	 * @param \Exception $exception to log message from
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function debug_fail(\Exception $exception) {
		$this->debugger()->fail($exception->getMessage(), ($exception->getFile() . ':' . $exception->getLine()), $exception);
		return false;
	}

	/**
	 * Sets the debugger current source to provided source or to the function that called this function, then resets when the function exits to what it was
	 * before.
	 *
	 * @param string $source to use as the debug 'source', if not supplied then the caller function name will be used (using debug_backtrace so may be slow)
	 * @return ScopedReference which will set the source back to original source when it is destroyed
	 */
	public static function debug_source($source = null) {
		$debugger = static::debugger();
		$oldSource = $debugger->source();
		$debugger->source($source ?: debug_backtrace(false, 1)[1]['function']);

		return new ScopedReference($debugger, function() use ($debugger, $oldSource) { $debugger->source($oldSource); } );
	}

}