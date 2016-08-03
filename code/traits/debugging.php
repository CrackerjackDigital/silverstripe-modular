<?php
namespace Modular;

use Config;
use Modular\Helpers\Debugger;
use SS_Log;
use SS_LogFileWriter;
use SS_LogEmailWriter;

trait debugging {
	/**
	 * Writes to config.errorlog_path_name if set and
	 * sends email to config.errorlog_email_address if set
	 * as well as writing to 'normal' log by log method.
	 *
	 * @param string $message
	 * @param mixed  $extras
	 */
	public static function log_error($message, $extras = null) {
		static $log;

		if (!$log) {
			$log = new SS_Log();

			// if config.log_file_name set then log to this file in assets/logs/
			if ($logFilePathName = static::config()->get('errorlog_path_name')) {
				$log->add_writer(
					new SS_LogFileWriter(ASSETS_PATH . "/$logFilePathName")
				);
			}
			if ($emailErrorAddress = static::config()->get('errorlog_email_address')) {
				$log->add_writer(
					new SS_LogEmailWriter($emailErrorAddress)
				);
			}
		}
		static::log_message($message, SS_Log::ERR, $extras);
		$log->log($message, SS_Log::ERR, $extras);
	}

	/**
	 * Writes to config.log_path_name if set
	 *
	 * @param string $message
	 * @param mixed  $level
	 * @param mixed  $extras
	 */
	public static function log_message($message, $level = SS_Log::INFO, $extras = null) {
		static $log;

		if (!$log) {
			$log = new SS_Log();
			// if config.log_file_name set then log to this file in assets/logs/
			if ($logFilePathName = static::config()->get('log_path_name')) {
				$log->add_writer(
					new SS_LogFileWriter(
						ASSETS_PATH . "/$logFilePathName"
					)
				);
			}
		}
		$log->log($message, $level, $extras);
	}
}