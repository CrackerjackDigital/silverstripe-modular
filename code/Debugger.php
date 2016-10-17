<?php
namespace Modular;

use Controller;
use \Modular\Interfaces\Debugger as DebugInterface;
use Filesystem;
use Modular\Exceptions\Debug;
use Modular\Exceptions\Exception;
use SS_Log;
use SS_LogEmailWriter;
use SS_LogFileWriter;

class Debugger extends Object implements DebugInterface
{
	use bitfield;
	use enabler;

	const DefaultSendEmailsFrom = 'servers@moveforward.co.nz';

	// options in numerically increasing order, IMO Zend did this the wrong way, 0 should always be 'no' or least
	const DebugNone   = -1;
	const DebugErr    = SS_Log::ERR;        // 3
	const DebugWarn   = SS_Log::WARN;       // 4
	const DebugNotice = SS_Log::NOTICE;     // 5
	const DebugInfo   = SS_Log::INFO;       // 6
	const DebugTrace  = SS_Log::DEBUG;      // 7
	const DebugAll    = self::DebugTrace;   // easier to remember

	// disable all debugging
	const DebugOff = 16;
	
	const DebugFile   = 32;
	const DebugScreen = 64;
	const DebugEmail  = 128;
	
	private static $levels = [
		self::DebugErr    => 'ERROR',
		self::DebugWarn   => 'WARN',
		self::DebugNotice => 'NOTICE',
		self::DebugInfo   => 'INFO',
		self::DebugTrace  => 'TRACE',
	];
	
	// TODO implement writing a log file per class as well as global log, may need to move this into trait
	// as we need to get the class name for the file maybe, though SS_Log already handles backtrace it doesn't
	// og back far enough
	const DebugPerClass = 256;

	// debug warning level and debug to file and screen
	const DefaultDebugLevel = 102;

	private static $send_emails_from = self::DefaultSendEmailsFrom;

	// name of log file to create if none supplied to toFile
	private static $log_file = '';

	// path to create log file in relative to base folder
	private static $log_path = '';

	// set when toFile is called.
	private $logFilePathName;

	// when destructor is called on the logger email the log file to this address
	private $emailLogFileTo;

	// where are messages coming from?
	private $source;

	// what level will we trigger at
	private $level;

	public function __construct($level = self::DefaultDebugLevel, $source = '') {
		parent::__construct();
		$this->init($level, $source);
	}

	/**
	 * If emailLogFileTo and logFilePathName is set then email the logFilePathName content if not empty
	 */
	public function __destruct() {
		if ($this->emailLogFileTo && $this->logFilePathName) {
			if ($body = file_get_contents($this->logFilePathName)) {
				$email = new \Email(
					$this->config()->get('send_emails_from'),
					$this->emailLogFileTo,
					'Debug log from: ' . \Director::protocolAndHost(),
					$body
				);
				$email->sendPlain();
			}
		}
	}

	public static function debugger($level = self::DefaultDebugLevel, $source = '') {
		$class = get_called_class();
		return new $class($level, $source);
	}
	
	public function level($level = null)
	{
		if (func_num_args()) {
			$this->level = $level;
			return $this;
		} else {
			return $this->level;
		}
	}

	public function source($source = null) {
		if (func_num_args()) {
			$this->source = $source;
			return $this;
		} else {
			return $this->source;
		}
	}

	/**
	 * Set levels and source and if flags indicate debugging to file screen or email initialise those aspects of debugging using defaults from config.
	 *
	 * @param      $level
	 * @param null $source
	 * @return $this
	 */
	protected function init($level, $source = null) {
		SS_Log::clear_writers();
		
		$this->level($level);
		$this->source($source);

		if ($this->bitfieldTest($level, self::DebugFile)) {
			if ($logFile = $this->makeLogFileName()) {
				$this->toFile($level, $logFile);
			}
		}
		if ($this->bitfieldTest($level, self::DebugScreen)) {
			$this->toScreen($level);
		}
		if ($this->bitfieldTest($level, self::DebugEmail)) {
			if ($email = $this->config()->get('log_email')) {
				static::toEmail($email, $level);
			}
		}
		
		return $this;
	}

	/**
	 *
	 * @param string $message
	 * @param string $severity e.g. 'ERR', 'TRC'
	 * @param string $source
	 * @return mixed
	 */
	public function formatMessage($message, $severity, $source = '') {
		return implode("\t", [
			date('Y-m-d'),
			date('h:i:s'),
			"$severity:",
			$source,
			$message,
		]) . (\Director::is_cli() ? '' : '<br/>') . PHP_EOL;
	}

	/**
	 * Return level if level from facilities less than current level otherwise false.
	 *
	 * @param $facilities
	 * @return bool|int
	 */
	protected function lvl($facilities, $compareToLevel = null) {
		// strip out non-level facilities
		$level = $facilities & (self::DebugErr | self::DebugWarn | self::DebugNotice | self::DebugInfo | self::DebugTrace);
		$compareToLevel = is_null($compareToLevel) ? $this->level() : $compareToLevel;
		return $level <= $compareToLevel ? $level : false;
	}

	public function log($message, $facilities, $source = '') {
		$source = $source ?: $this->source();

		if ($level = $this->lvl($facilities)) {
			SS_Log::log(($source ? "$source: " : '') . $message . PHP_EOL, $level);
		}
		return $this;
	}

	public function info($message, $source = '') {
		$this->log($message, self::DebugInfo, $source);
		
		return $this;
	}

	public function trace($message, $source = '') {
		if ($this->lvl(self::DebugTrace)) {
			echo $message . (\Director::is_cli() ? '' : "<br/>") . PHP_EOL;
			ob_flush();
		}
		$this->log($message, self::DebugTrace, $source);
		
		return $this;
	}

	public function notice($message, $source = '') {
		$this->log($message, self::DebugNotice, $source);
		
		return $this;
	}

	public function warn($message, $source = '') {
		$this->log($message, self::DebugWarn, $source);
		
		return $this;
	}

	public function error($message, $source = '') {
		$this->log($message, self::DebugErr, $source);
		
		return $this;
	}

	public function fail($message, $source = '', Exception $exception) {
		$this->log($message, self::DebugErr, $source);
		if ($exception) {
			$exception->setMessage($message);
			throw $exception;
		}
		return $this;
	}

	/**
	 * Set the email address to send emails to
	 *
	 * @param $address
	 * @param $level
	 * @return $this
	 */
	public function toEmail($address, $level) {
		if ($address) {
			SS_Log::add_writer(
				new SS_LogEmailWriter($address),
				$level
			);
		};
		
		return $this;
	}

	/**
	 * Log to provided file or to a generated file. Filename is relative to site root if it starts with a '/' otherwise is interpreted as relative
	 * to assets folder. Checks to make sure final log file path is inside the web root.
	 *
	 * @param  int    $level        only log above this level
	 * @param  string $filePathName log to this file or if not supplied generate one
	 * @return $this
	 */
	public function toFile($level, $filePathName = '') {
		if ($filePathName) {
			if (substr($filePathName, -4) != '.log') {
				$filePathName .= ".log";
			}
			if ($path = Application::make_safe_path(dirname($filePathName))) {
				$this->logFilePathName = Controller::join_links(
					$path,
					$this->config()->get('log_path'),
					basename($filePathName)
				);
			};
		} else {
			$this->logFilePathName = $this->makeLogFileName();
		}

		SS_Log::add_writer(
			new SS_LogFileWriter($this->logFilePathName),
			$this->lvl($level)
		);

		// log an warning if we got an invalid path above so we know this and can fix
		if ($filePathName && !Application::make_safe_path(dirname($filePathName))) {
			$this->warn("Invalid file path outside of web root '$filePathName' using '$this->logFilePathName' instead");
		}
		if ($filePathName && !is_dir(dirname($filePathName))) {
			$this->warn("Path for '$filePathName' does not exist, using '$this->logFilePathName' instead");
		}
		return $this;
	}
	
	/**
	 * @param $level
	 * @return $this
	 */
	public function toScreen($level) {
		SS_Log::add_writer(new \LogOutputWriter($level));
		return $this;
	}

	/**
	 * At end of Debugger lifecycle file set by toFile will be sent to this email address.
	 *
	 * @param $emailAddress
	 * @return $this
	 */
	public function sendFile($emailAddress) {
		$this->emailLogFileTo = $emailAddress;
		return $this;
	}

	/**
	 * Returns a log file path and name relative to the assets folder using config.log_path. If path doesn't exist
	 * and is in the assets folder then will try and create it (recursively). If it is outside
	 * the assets folder then will not try and create the path.
	 *
	 * @return string
	 * @throws \Modular\Exceptions\Application
	 */
	protected function makeLogFileName() {
		if ($filePathName = static::config()->get('log_file')) {
			// if no path then dirname returns '.' we don't want that but empty path instead
			$path = trim(dirname($filePathName), '.');
			if (!$path) {
				$path = static::config()->get('log_path');
			}
			$fileName = basename($filePathName, '.log');
		} else {
			$path = static::config()->get('log_path');
			$date = date('Ymd_his');

			$prefix = $this->source ?: "$date-";

			$fileName = basename(tempnam($path, "silverstripe-$prefix")) . ".log";
		}
		$path = Application::make_safe_path($path, false);
		return "$path/$fileName.log";
	}
}
