<?php

namespace Modular;

use Modular\Exceptions\Exception;
use Modular\Interfaces\Debugger as DebuggerInterface;
use Modular\Interfaces\Logger as LoggerInterface;
use Modular\Traits\bitfield;
use Modular\Traits\enabler;
use Modular\Traits\logging;
use SS_LogEmailWriter;
use SS_LogFileWriter;

class Debugger extends Object implements LoggerInterface, DebuggerInterface {
	use bitfield;
	use enabler;
	use logging;

	const DefaultSendEmailsFrom = 'servers@moveforward.co.nz';

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

	private static $send_emails_from = self::DefaultSendEmailsFrom;

	private $safe_paths = [];

	// when destructor is called on the logger email the log file to this address
	private $emailLogFileTo;

	// where are messages coming from?
	private $source;

	// what level will we trigger at
	private $level;

	public function __construct( $level = self::LevelFromEnv, $source = '' ) {
		parent::__construct();
		$this->init( $level, $source );
	}

	/**
	 * If emailLogFileTo and logFilePathName is set then email the logFilePathName content if not empty
	 */
	public function __destruct() {
		if ( $this->emailLogFileTo && $this->logFilePathName ) {
			$this->info( "End of logging at " . date( 'Y-m-d h:i:s' ) );

			if ( $body = file_get_contents( $this->logFilePathName ) ) {
				$email = new \Email(
					$this->config()->get( 'send_emails_from' ),
					$this->emailLogFileTo,
					'Debug log from: ' . \Director::protocolAndHost(),
					$body
				);
				$email->sendPlain();
			}
		}
	}

	/**
	 * Return instance of a logger to log things to.
	 * @return \Modular\Logger
	 */
	public function logger() {
		if (!$this->logger) {
			$this->logger = new Logger();
		}
		return $this->logger;
	}

	/**
	 * Return instance of a debugger class to do debugging calls on (including logging).
	 * @param int|null $level
	 * @param string   $source
	 *
	 * @return mixed
	 */
	public static function debugger( $level = self::LevelFromEnv, $source = '' ) {
		$class = get_called_class();
		return new $class( $level, $source );
	}

	/**
	 * @inheritdoc
	 */
	public function level( $level = self::LevelFromEnv ) {
		if ( func_num_args() ) {
			if ( $level === self::LevelFromEnv ) {
				$this->level = $this->env();
			} else {
				$this->level = $level;
			}

			return $this;
		} else {
			return $this->level;
		}
	}

	/**
	 * @param null $source
	 *
	 * @return $this|string
	 * @fluent-setter
	 */
	public function source( $source = null ) {
		if ( func_num_args() ) {
			$this->source = $source;

			return $this;
		} else {
			return $this->source;
		}
	}

	/**
	 * Return the level for a given environment.
	 *
	 * @param string $env 'dev', 'test', 'live'
	 *
	 * @return $this
	 * @fluent
	 */
	public function env( $env = SS_ENVIRONMENT_TYPE ) {
		return $this->config()->get( 'environment_levels' )[ $env ];
	}

	/**
	 * Set levels and source and if flags indicate debugging to file screen or email initialise those aspects of debugging using defaults from config.
	 *
	 * @param        $level
	 * @param string $source
	 *
	 * @return $this
	 * @throws \Zend_Log_Exception
	 */
	protected function init( $level, $source = null ) {
		$this->logger()->clearWriters();

		$this->level( $level );
		$this->source( $source );

		// get the level arrived at
		$level = $this->level();

		if ( $this->testbits( $level, self::DebugFile ) ) {
			$this->toFile( $level );
		}
		if ( $this->testbits( $level, self::DebugScreen ) ) {
			$this->toScreen( $level );
		}
		if ( $this->testbits( $level, self::DebugEmail ) ) {
			if ( $email = static::log_email() ) {
				static::toEmail( $email, $level );
			}
		}

		return $this;
	}

	/**
	 *
	 * @param string $message
	 * @param string $severity e.g. 'ERR', 'TRC'
	 * @param string $source
	 *
	 * @return mixed
	 */
	public function formatMessage( $message, $severity, $source = '' ) {
		$source = $source ?: ( $this->source() ?: get_called_class() );

		return implode( "\t", [
				date( 'Y-m-d' ),
				date( 'h:i:s' ),
				"$severity:",
				$source,
				static::digest( $message, $source ),
			] ) . ( \Director::is_cli() ? '' : '<br/>' ) . PHP_EOL;
	}

	/**
	 * Return level if level from facilities less than current level otherwise false.
	 *
	 * @param $facilities
	 *
	 * @return bool|int
	 */
	protected function lvl( $facilities, $compareToLevel = null ) {
		// strip out non-level facilities
		$level          = $facilities & ( self::DebugErr | self::DebugWarn | self::DebugNotice | self::DebugInfo | self::DebugTrace );
		$compareToLevel = is_null( $compareToLevel ) ? $this->level() : $compareToLevel;

		return $level <= $compareToLevel ? $level : false;
	}

	/**
	 *
	 * @param string $message either message or a language file key
	 * @param int    $facilities
	 * @param string $source
	 * @param array  $tokens  to replace in message
	 *
	 * @return $this
	 */
	public function log( $message, $facilities, $source = '', $tokens = [] ) {
		$source = $source ?: ( $this->source() ?: get_called_class() );

		$message = static::digest( $message, $source, $tokens );

		if ( $level = $this->lvl( $facilities ) ) {
			$this->logger()->log( ( $source ? "$source: " : '' ) . $message . PHP_EOL, $level );
		}

		return $this;
	}

	/**
	 * Try to look up message in lang files by message and source as keys (max 20 characters, camelcased and spaces removed) or just return the message.
	 *
	 * @param string $message
	 * @param array  $source
	 * @param array  $tokens to replace in message
	 *
	 * @return string
	 */
	public static function digest( $message, $source, $tokens = [] ) {
		$key    = str_replace( ' ', '', ucwords( substr( $message, 0, 20 ) ) );
		$source = str_replace( ' ', '', ucwords( substr( $source, 0, 20 ) ) );

		return _t( "$source.$key", _t( $key, $message, $tokens ), $tokens );
	}

	/**
	 * @param string $message or a lang file key
	 * @param string $source
	 * @param array  $tokens  to replace in message
	 *
	 * @return $this
	 */
	public function info( $message, $source = '', $tokens = [] ) {
		$this->log( $message, self::DebugInfo, $source, $tokens );

		return $this;
	}

	public function trace( $message, $source = '', $tokens = [] ) {
		$this->log( $message, self::DebugTrace, $source, $tokens );

		return $this;
	}

	public function notice( $message, $source = '', $tokens = [] ) {
		$this->log( $message, self::DebugNotice, $source, $tokens );

		return $this;
	}

	public function warn( $message, $source = '', $tokens = [] ) {
		$this->log( $message, self::DebugWarn, $source, $tokens );

		return $this;
	}

	public function error( $message, $source = '', $tokens = [] ) {
		$this->log( $message, self::DebugErr, $source, $tokens );

		return $this;
	}

	/**
	 * @param string|int           $message
	 * @param string               $source
	 * @param \Exception|Exception $exception if passed will be thrown
	 *
	 * @return $this
	 * @throws \Modular\Exceptions\Exception
	 */
	public function fail( $message, $source = '', \Exception $exception = null ) {
		if ( $exception instanceof \Exception ) {
			$this->log( $message, self::DebugErr, $source, [
				'file'      => $exception->getFile(),
				'line'      => $exception->getLine(),
				'code'      => $exception->getCode(),
				'backtrace' => $exception->getTraceAsString(),
			] );
			if ( $exception instanceof Exception ) {
				$exception->setMessage( $message );
			}
			throw $exception;
		}
		$this->log( $message, self::DebugErr, $source );

		return $this;
	}

	/**
	 * Set the email address to send emails to
	 *
	 * @param int    $address
	 * @param string $level
	 *
	 * @return $this
	 * @throws \Zend_Log_Exception
	 */
	public function toEmail( $address, $level ) {
		if ( $address ) {
			$this->logger()->addWriter(
				new SS_LogEmailWriter( $address ),
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
	 *
	 * @return $this
	 * @throws \Zend_Log_Exception
	 */
	public function toFile( $level, $filePathName = '' ) {
		$originalFilePathName = $filePathName;

		if ( ! $filePathName ) {
			$filePathName = static::log_path_name( '.log' );
		}
		if ( substr( $filePathName, - 4 ) != '.log' ) {
			$filePathName .= ".log";
		}

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

		// log an warning if we got an invalid path above so we know this and can fix
		if ( $filePathName && ! Application::make_safe_path( dirname( $originalFilePathName ) ) ) {
			$this->warn( "Invalid file path outside of web root '$filePathName' using '$this->logFilePathName' instead" );
		}
		if ( $filePathName && ! is_dir( dirname( $originalFilePathName ) ) ) {
			$this->warn( "Path for '$filePathName' does not exist, using '$this->logFilePathName' instead" );
		}

		return $this;
	}

	/**
	 * @param int|null $level
	 *
	 * @return $this
	 * @throws \Zend_Log_Exception
	 */
	public function toScreen( $level = self::LevelFromEnv ) {
		if ( is_null( $level ) || $level === self::LevelFromEnv ) {
			$level = $this->config()->get( 'environment_levels' )[ SS_ENVIRONMENT_TYPE ];
		}
		$this->logger()->addWriter( new \LogOutputWriter( $level ) );

		return $this;
	}

	/**
	 * At end of Debugger lifecycle file set by toFile will be sent to this email address.
	 *
	 * @param $emailAddress
	 *
	 * @return $this
	 */
	public function sendFile( $emailAddress ) {
		$this->emailLogFileTo = $emailAddress;

		return $this;
	}

	/**
	 * Sets an error handler which will throw an exception of the same class as the passed exception
	 * or just base \Exception if null.
	 *
	 * @param string     $message will receive the error message
	 * @param null       $code    will receive the error code
	 * @param \Exception $exception
	 *
	 * @return callable the previous error handler
	 */
	public static function set_error_exception(\Exception $exception = null) {
		return set_error_handler(
			function ( $errorCode, $errorMessage ) use ( &$message, &$code, $exception ) {
				$exceptionClass = $exception ? get_class( $exception ) : \Exception::class;
				throw new $exceptionClass( $message, $code);
			}
		);
	}

	public static function log_email() {
		return static::config()->get( 'log_email' );
	}
}
