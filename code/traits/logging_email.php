<?php
namespace Modular\Traits;


use SS_LogEmailWriter;

trait logging_email {
	// when destructor is called on the logger email the log file to this address
	private $emailLogFileTo;

	/**
	 * @return \Modular\Logger
	 */
	abstract public function logger();

	public static function log_email() {
		return static::config()->get( 'log_email' );
	}

	/**
	 * At end of Debugger lifecycle file set by toFile will be sent to this email address.
	 *
	 * @param $emailAddress
	 *
	 * @return $this
	 */
	public function emailLogTo( $emailAddress ) {
		$this->emailLogFileTo = $emailAddress;

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

}