<?php
namespace Modular;

use Controller;
use \Modular\Interfaces\Debugger as DebugInterface;
use Filesystem;
use Modular\Exceptions\Debug;
use SS_Log;
use SS_LogEmailWriter;
use SS_LogFileWriter;

class Debugger extends Object implements DebugInterface
{
	use bitfield;
	use enabler;
	
	// options in numerically increasing order, IMO Zend did this the wrong way, 0 should always be 'no' or least
	const DebugErr    = SS_Log::ERR;           // 3
	const DebugWarn   = SS_Log::WARN;
	const DebugNotice = SS_Log::NOTICE;
	const DebugInfo   = SS_Log::INFO;
	const DebugTrace  = SS_Log::DEBUG;          // 7
	const DebugFail   = SS_Log::ERR;
	
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
	
	const DefaultDebugLevel = 48; // self::DebugOff | self::DebugFile
	
	// name of log file to create
	private static $log_file = 'silverstripe.log';
	
	// path to create log file in relative to assets folder
	private static $log_path = 'logs';
	
	private $level;
	
	public function __construct($level = self::DefaultDebugLevel, $prefix = '')
	{
		parent::__construct();
		$this->init($level, $prefix);
	}
	
	public static function debugger($level = self::DefaultDebugLevel, $prefix = '')
	{
		$class = get_called_class();
		
		return new $class($level, $prefix);
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
	
	protected function init($level, $prefix = null)
	{
		SS_Log::clear_writers();
		
		$this->level($level);
		
		if ($this->bitfieldTest($level, self::DebugFile)) {
			$logFile = static::config()
			                 ->get('log_file_name');
			
			static::toFile($logFile, $level, $prefix);
			
			if ($this->bitfieldTest($level, self::DebugPerClass)) {
				// TODO Implement per-class debug logging, could handle by prefix for now
				// SS_Log::add_writer(new SS_LogFileWriter(self::log_path())
			}
		}
		if ($this->bitfieldTest($level, self::DebugEmail)) {
			if ($email = $this->config()
			                  ->get('email')
			) {
				static::toEmail($email, $level);
			}
		}
		
		return $this;
	}
	
	public function formatMessage($message, $severity, $source = '')
	{
		return implode("\t", [
			date('Y-m-d'),
			date('h:i:s'),
			"$severity:",
			$source,
			$message,
		]);
	}
	
	public function log($message, $facilities, $source = '')
	{
		// strip out non-level facilities
		$level = $facilities & ( self::DebugErr | self::DebugWarn | self::DebugNotice | self::DebugInfo | self::DebugTrace );
		
		if ($level <= $this->level()) {
			$levels = $this->config()->get('levels');
			
			if (!isset( $levels[ $level ] )) {
				$this->log("Bad debug level '$level'", self::DebugWarn);
				$level = self::DebugErr;
			}
			$message = $this->formatMessage($message, $levels[ $level ], $source);
			SS_Log::log($message, $level);
		}
		
		return $this;
	}
	
	public function info($message, $source = '')
	{
		$message = $this->formatMessage($message, 'INF', $source);
		$this->log($message, self::DebugInfo, $source);
		
		return $this;
	}
	
	public function trace($message, $source = '')
	{
		$message = $this->formatMessage($message, 'TRC', $source);
		$this->log($message, self::DebugTrace, $source);
		
		return $this;
	}
	
	public function notice($message, $source = '')
	{
		$message = $this->formatMessage($message, 'NTC ', $source);
		$this->log($message, self::DebugNotice, $source);
		
		return $this;
	}
	
	public function warn($message, $source = '')
	{
		$message = $this->formatMessage($message, 'WRN ', $source);
		$this->log($message, self::DebugWarn, $source);
		
		return $this;
	}
	
	public function error($message, $source = '')
	{
		$message = $this->formatMessage($message, 'ERR', $source);
		$this->log($message, self::DebugErr, $source);
		
		return $this;
	}
	
	public function fail($message, $source = '')
	{
		$message = $this->formatMessage($message, 'ERR', $source);
		$this->log($message, self::DebugErr, $source);
		throw new Debug($message);
	}
	
	public function toEmail($address, $level)
	{
		if ($address) {
			SS_Log::add_writer(
				new SS_LogEmailWriter($address),
				$level
			);
		};
		
		return $this;
	}
	
	public function toFile($path, $level, $source = '')
	{
		if ($path) {
			SS_Log::add_writer(
				new SS_LogFileWriter($path
					?: static::log_file_name($source)),
				$level
			);
		}
		
		return $this;
	}
	
	/**
	 * Returns a log file path and name relative to the assets folder using config.log_path. If path doesn't exist
	 * and is in the assets folder then will try and create it (recursively). If it is outside
	 * the assets folder then will not try and create the path.
	 *
	 * @param $prefix
	 * @return string
	 */
	public static function log_file_name($prefix = 'silverstripe')
	{
		if (!$filePathName = static::config()
		                           ->get('log_file')
		) {
			$path = static::config()
			              ->get('log_path');
			$date = date('Ymd_his');
			
			$prefix = $prefix
				? ( "$prefix-$date-" )
				: ( "$date-" );
			
			$fileName = basename(tempnam($path, $prefix));
		} else {
			$path     = dirname($filePathName);
			$fileName = basename($filePathName, '.log');
		}
		$path = realpath(Controller::join_links(
			ASSETS_PATH,
			$path
		));
		if (substr($path, 0, strlen(ASSETS_PATH)) == ASSETS_PATH) {
			// we only try and make a logging directory if we are inside the assets folder
			Filesystem::makeFolder($path);
		}
		
		return "$path/$fileName.log";
	}
}
