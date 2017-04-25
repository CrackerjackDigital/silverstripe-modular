<?php
namespace Modular\Traits;

use Modular\Application;
use Modular\Exceptions\Exception;
use Modular\Logger;
use Modular\Module;

trait logging {

	// prefix log file names with the date in format self.LogFilePrefixDateFormat
	private static $log_file_prefix_date = true;

	// name of log file to create if none supplied to toFile
	private static $log_file = 'silverstripe.log';

	// path to create log file in relative to base folder
	private static $log_path = ASSETS_PATH;

	private $logger;

	// set when toFile is called.
	private $logFilePathName;

	/**
	 * @param null $forClass
	 *
	 * @return \Config_ForClass
	 */
	abstract public function config($forClass = null);

	/**
	 * Return log path from config.log_path and config.log_file or return something sensible
	 *
	 * @param string $extension
	 * @param string $useFileName
	 * @param string $usePath
	 *
	 * @return string
	 * @throws \Modular\Exceptions\Exception
	 */
	public static function log_target( $extension = '.log', $useFileName = '', $usePath = '' ) {
		$path     = $usePath ?: static::log_path();
		$fileName = $useFileName ?: static::log_filename();

		if ( $extension && ( substr( $fileName, - strlen( $extension ) ) != $extension ) ) {
			$fileName .= $extension;
		}

		return $path . DIRECTORY_SEPARATOR . $fileName;
	}

	/**
	 * Return line by line content of log file as a generator can be iterated over.
	 *
	 * @return \Generator|null
	 */
	public function readLog() {
		if ( $this->logFilePathName ) {
			if ($fp = fopen($this->logFilePathName, 'r')) {
				while (!feof($fp)) {
					yield fgets($fp);
				}
				fclose($fp);
			}
		}
		return null;
	}

	/**
	 * Return a directory to put logs in from supplied classes config or module's.
	 *
	 * @return string
	 * @throws \Modular\Exceptions\Exception
	 */
	protected static function log_path() {
		$path = static::config()->get('log_path');
		if ( substr( $path, 0, 1 ) == '/' || substr( $path, 0, 2 ) == '..' ) {
			// relative to docroot, make absolute to directory, must already exist (realpath returns false if it doesn't)
			$path = realpath( BASE_PATH . DIRECTORY_SEPARATOR . trim( $path, '/' ) );
			if ( false === $path ) {
				$path = '';
			}
		}
		if ( ! $path ) {
			if ( defined( 'SS_ERROR_LOG' ) ) {
				$path = basename( SS_ERROR_LOG );
			} else {
				// relative to assets, make absolute to directory in assets, create it doesn't exist
				$path = ASSETS_PATH . DIRECTORY_SEPARATOR . $path;
				if ( ! is_dir( $path ) ) {
					\Filesystem::makeFolder( $path );
				}
			}
		}
		if (!\Injector::inst()->get('Application')->in_safe_path( $path)) {
			throw new Exception("Not a safe path '$path'");
		}
		return $path;
	}

	/**
	 * Return a filename without a path to use for logging from the supplied class or module's.
	 */
	protected static function log_filename() {
		$fileName = static::config()->get( 'log_file' );

		if ( ! $fileName) {
			if ( defined( 'SS_ERROR_LOG' ) ) {
				$fileName = basename( SS_ERROR_LOG );
			} else {
				$fileName = 'silverstripe.log';
			}
		}
		if (static::config()->get('log_file_prefix_date')) {
			$fileName = date(Logger::LogFilePrefixDateFormat);
		}

		return $fileName;
	}

}