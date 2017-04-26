<?php
namespace Modular\Traits;

use Modular\Application;
use Modular\Exceptions\Exception;
use Modular\Logger;
use Modular\Module;
use SS_LogFileWriter;

trait logging_file {

	// prefix log file names with the date in format self.LogFilePrefixDateFormat
	private static $log_file_prefix_date = true;

	// name of log file to create if none supplied to toFile
	private static $log_file = 'silverstripe.log';

	// path to create log file in relative to base folder
	private static $log_path = ASSETS_PATH;

	// set when toFile is called.
	private $logFilePathName;

	/**
	 * @param null $forClass
	 *
	 * @return \Config_ForClass
	 */
	abstract public function config($forClass = null);

	/**
	 * @return Logger
	 */
	abstract public function logger();

	/**
	 * Log to provided file or to a generated file. Filename is relative to site root if it starts with a '/' otherwise is interpreted as relative
	 * to assets folder. Checks to make sure final log file path is inside the web root.
	 *
	 * @param  int   $level only log above this level
	 * @param string $fileName
	 *
	 * @return $this
	 * @throws \Modular\Exceptions\Exception
	 * @throws \Zend_Log_Exception
	 * @internal param string $filePathName log to this file or if not supplied generate one
	 *
	 */
	public function toFile( $level, $fileName = '' ) {
		$this->logFilePathName = static::log_target($fileName);

		// if truncate is specified then do so on the log file
		if ( ( $level && self::DebugTruncate ) == self::DebugTruncate ) {
			if ( file_exists( $this->logFilePathName ) ) {
				unlink( $this->logFilePathName );
			}
		}

		$this->logger()->addWriter(
			new SS_LogFileWriter( $this->logFilePathName ),
			$this->lvl( $level ),
			"<="
		);
		$this->info( "Start of logging at " . date( 'Y-m-d h:i:s' ) );
		return $this;
	}

	/**
	 * Return log path from config.log_path and config.log_file or return something sensible. This will
	 * not be checked to make sure it is a 'safe' path, see safe_paths trait if you want to do this.
	 *
	 * @param string $extension
	 * @param string $useFileName
	 * @param string $usePath
	 *
	 * @return string
	 * @throws \Modular\Exceptions\Exception
	 */
	public static function log_target( $useFileName = '', $usePath = '', $extension = '.log' ) {
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
	}

	/**
	 * Return a directory to put logs in from config.log_path or figure out a safe one
	 * in assets directory. If in assets and doesn't exist already will create it.
	 *
	 * @return string
	 * @throws \Modular\Exceptions\Exception
	 */
	protected static function log_path() {
		$path = static::config()->get('log_path');
		if ( ! $path ) {
			if ( defined( 'SS_ERROR_LOG' ) ) {
				$path = dirname( SS_ERROR_LOG );
			}
		}
		if ( substr( $path, 0, 1 ) == '/' || substr( $path, 0, 2 ) == '..' ) {
			// relative to docroot, make absolute from filesystem root to directory,
			// must already exist (realpath returns false if it doesn't)

			$path = realpath( BASE_PATH . DIRECTORY_SEPARATOR . trim( $path, '/' ) );
			if ( false === $path ) {
				$path = ASSETS_PATH;
			}
		} else {
			// relative to assets, make absolute from filesystem root to directory in assets,
			// create it doesn't exist
			$path = ASSETS_PATH . DIRECTORY_SEPARATOR . $path;
			if ( ! is_dir( $path ) ) {
				\Filesystem::makeFolder( $path );
			}

		}

		return realpath($path);
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