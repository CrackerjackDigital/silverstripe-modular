<?php
namespace Modular;

use Modular\Exceptions\Exception;
use Modular\Interfaces\Logger;
use SS_Log;
use SS_LogEmailWriter;
use SS_LogFileWriter;

class Debugger extends Object implements Logger {
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
	const DebugAll    = self::DebugTrace;   // 7 alias for trace

	// disable all debugging
	const DebugOff = 16;

	const DebugFile     = 32;
	const DebugScreen   = 64;
	const DebugEmail    = 128;
	const DebugTruncate = 256;     // truncate log files

	const DebugEnvDev  = 103;      // screen | file | trace
	const DebugEnvTest = 165;      // file | email | notice
	const DebugEnvLive = 164;      // file | email | warn

	const LevelFromEnv = null;

	private static $environment_levels = [
		'dev'  => self::DebugEnvDev,
		'test' => self::DebugEnvTest,
		'live' => self::DebugEnvLive,
	];

	private static $levels = [
		self::DebugErr    => 'ERROR ',
		self::DebugWarn   => 'WARN  ',
		self::DebugNotice => 'NOTICE',
		self::DebugInfo   => 'INFO  ',
		self::DebugTrace  => 'TRACE ',
	];

	// TODO implement writing a log file per class as well as global log, may need to move this into trait
	// as we need to get the class name for the file maybe, though SS_Log already handles backtrace it doesn't
	// og back far enough
	const DebugPerClass = 256;

	private static $send_emails_from = self::DefaultSendEmailsFrom;

	// name of log file to create if none supplied to toFile
	private static $log_file = '';

	// path to create log file in relative to base folder
	private static $log_path = '';

	private $logger;

	// set when toFile is called.
	private $logFilePathName;

	private $safe_paths = [];

	// when destructor is called on the logger email the log file to this address
	private $emailLogFileTo;

	// where are messages coming from?
	private $source;

	// what level will we trigger at
	private $level;

	public function __construct($level = self::LevelFromEnv, $source = '') {
		parent::__construct();
		$this->logger = new \Modular\Logger();
		$this->init($level, $source);
	}

	/**
	 * If emailLogFileTo and logFilePathName is set then email the logFilePathName content if not empty
	 */
	public function __destruct() {
		if ($this->emailLogFileTo && $this->logFilePathName) {
			$this->info("End of logging at " . date('Y-m-d h:i:s'));

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

	public static function debugger($level = self::LevelFromEnv, $source = '') {
		$class = get_called_class();
		return new $class($level, $source);
	}

	/**
	 * @inheritdoc
	 */
	public function level($level = null) {
		if (func_num_args()) {
			if ($level === self::LevelFromEnv) {
				$this->env();
			} else {
				$this->level = $level;
			}
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
	 * @return null|string
	 */
	public function readLog() {
		if ($this->logFilePathName) {
			return file_get_contents($this->logFilePathName);
		}
		return null;
	}

	/**
	 * Set level from config.environment_levels for passed type
	 *
	 * @param string $env 'dev', 'test', 'live'
	 * @return $this
	 * @fluent
	 */
	public function env($env = SS_ENVIRONMENT_TYPE) {
		$this->level = $this->config()->get('environment_levels')[ $env ];
		return $this;
	}

	/**
	 * Set levels and source and if flags indicate debugging to file screen or email initialise those aspects of debugging using defaults from config.
	 *
	 * @param      $level
	 * @param null $source
	 * @return $this
	 */
	protected function init($level, $source = null) {
		$this->logger->clearWriters();

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

	/**
	 * @inheritdoc
	 *
	 */
	public function log($message, $facilities, $source = '') {
		$source = $source ?: ($this->source() ?: get_called_class());

		if ($level = $this->lvl($facilities)) {
			$this->logger->log(($source ? "$source: " : '') . $message . PHP_EOL, $level);
		}
		return $this;
	}

	public function info($message, $source = '') {
		$this->log($message, self::DebugInfo, $source);
		return $this;
	}

	public function trace($message, $source = '') {
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
			$this->logger->addWriter(
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
		$originalFilePathName = $filePathName;

		if ($filePathName) {
			if (substr($filePathName, -4) != '.log') {
				$filePathName .= ".log";
			}
		} else {
			$filePathName = $this->config()->get('log_file') ?: Application::log_file();
		}

		if (trim(dirname($filePathName), '.') == '') {
			$filePathName = ($this->config()->get('log_path') ?: Application::log_path()) . '/' . $filePathName;
		}
		if ($path = Application::make_safe_path(dirname($filePathName))) {
			$this->logFilePathName = Controller::join_links(
				$path,
				basename($filePathName)
			);
		};
		if (!$this->logFilePathName) {
			$this->logFilePathName = Application::log_path() . '/' . Application::log_file();
		}

		// if truncate is specified then do so on the log file
		if ($level && self::DebugTruncate) {
			if (file_exists($this->logFilePathName)) {
				unlink($this->logFilePathName);
			}
		}

		$this->logger->addWriter(
			new SS_LogFileWriter($this->logFilePathName),
			$this->lvl($level),
			"<="
		);

		$this->info("Start of logging at " . date('Y-m-d h:i:s'));

		// log an warning if we got an invalid path above so we know this and can fix
		if ($filePathName && !Application::make_safe_path(dirname($originalFilePathName))) {
			$this->warn("Invalid file path outside of web root '$filePathName' using '$this->logFilePathName' instead");
		}
		if ($filePathName && !is_dir(dirname($originalFilePathName))) {
			$this->warn("Path for '$filePathName' does not exist, using '$this->logFilePathName' instead");
		}

		return $this;
	}

	/**
	 * @param int $level
	 * @return $this
	 */
	public function toScreen($level = self::LevelFromEnv) {
		if (is_null($level) || $level === self::LevelFromEnv) {
			$level = $this->config()->get('environment_levels')[ SS_ENVIRONMENT_TYPE ];
		}
		$this->logger->addWriter(new \LogOutputWriter($level));
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
