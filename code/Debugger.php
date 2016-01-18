<?php
use \Modular\ModularObject as Object;

class ModularDebugger extends Object
{
	use \Modular\bitfield;

	// options in numerically increasing order, IMO Zend did this the wrong way, 0 should always be 'no'
	const DebugErr    = SS_Log::ERR;           // 3
	const DebugWarn   = SS_Log::WARN;
	const DebugInfo   = SS_Log::INFO;
	const DebugNotice = SS_Log::NOTICE;
	const DebugTrace  = SS_Log::DEBUG;       // 7

	// disable all debugging
	const DebugOff    = 16;

	const DebugFile   = 32;
	const DebugScreen = 64;
	const DebugEmail  = 128;

	// TODO implement writing a log file per class as well as global log, may need to move this into trait
	// as we need to get the class name for the file maybe, though SS_Log already handles backtrace it doesn't
	// og back far enough
	const DebugPerClass = 256;

	const DefaultDebugLevel = 48; // self::DebugOff | self::DebugFile

	protected $level = self::DefaultDebugLevel;

	// prefix for log file names, email subjects etc
	protected $prefix = '';

	private static $log_path = 'logs';

	private static $default_prefix = 'debug-';

	private static $email = '';

	public function __construct($level = self::DefaultDebugLevel, $prefix = null) {
		parent::__construct();
		$this->configure($level, is_null($prefix) ? static::config()->get('default_prefix') : $prefix);
	}

	public function level($level = null) {
		return $this->configure($level);
	}
	public function prefix($prefix) {
		return $this->configure($this->level, $prefix);
	}

	/**
	 * Enable a feature or features.
	 *
	 * @param $features bitfield of features to enable/turn on
	 * @return $this|ModularDebugger
	 */
	public function enable($features) {
		return $this->configure(
			$this->level | $features
		);
	}

	/**
	 * Disable a feature or features
	 *
	 * level:               1001
	 * features (disable):  0101
	 * ~features:           1010
	 * level & ~features:   1000
	 *
	 * @param $features bitfield of features to disable/turn off
	 * @return $this|ModularDebugger
	 */
	public function disable($features) {
		return $this->configure(
			$this->level & ~$features
		);
	}

	protected function configure($level, $prefix = null) {
		$this->level = $level;
		$this->prefix = is_null($prefix) ? $this->prefix : $prefix;

		SS_Log::clear_writers();

		if ($this->bitfieldTest($level, self::DebugFile)) {
			SS_Log::add_writer(new SS_LogFileWriter(self::log_file_name($this->prefix), $this->level));

			if ($this->bitfieldTest($level, self::DebugPerClass)) {
				// TODO Implement per-class debug logging, could handle by prefix for now
				// SS_Log::add_writer(new SS_LogFileWriter(self::log_path())
			}
		}
		if ($this->bitfieldTest($level, self::DebugEmail)) {
			$email = $this->config()->get('email') ?: Email::config()->get('admin_email');
			if ($email) {
				SS_Log::add_writer(new SS_LogEmailWriter($email));
			} else {
				$this->warn("No debug email recipient found");
			}
		}
		return $this;
	}

	public function formatMessage($message, $severity, $source = '') {
		return implode("\t", [
			date('Y-m-d'),
			date('h:i:s'),
			"$severity:",
			$source,
		    $message
		]);
	}

	public function trace($message, $source = '') {
		$message = $this->formatMessage($message, 'TRACE', $source);
		SS_Log::log($message, self::DebugTrace);
		return $this;
	}

	public function notice($message, $source = '') {
		$message = $this->formatMessage($message, 'WARN ', $source);
		SS_Log::log($message, self::DebugNotice);
		return $this;
	}

	public function warn($message, $source = '') {
		$message = $this->formatMessage($message, 'WARN ', $source);
		SS_Log::log($message, self::DebugWarn);
		return $this;
	}

	public function error($message, $source = '') {
		$message = $this->formatMessage($message, 'ERROR', $source);
		SS_Log::log($message, self::DebugErr);
		return $this;
	}

	/**
	 * Returns a log file path and name relative to the assets folder using config.log_path. If path doesn't exist
	 * and is in the assets folder then will try and create it (recursively).
	 *
	 * @param $prefix
	 * @return string
	 */
	public function log_file_name($prefix) {
		$path = realpath(Controller::join_links(
			ASSETS_PATH,
			static::config()->get('log_path'))
		);
		if (substr($path, 0, strlen(ASSETS_PATH)) == ASSETS_PATH) {
			// we only try and make a logging directory if we are inside the assets folder
			Filesystem::makeFolder($path);
		}
		$pathName = tempnam(
			$path,
			$prefix . '-' . date('Ymd_his') . '-'
		);
		return "$pathName.log";
	}
}