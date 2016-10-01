<?php
namespace Modular\Exceptions;

/**
 * Assist in determining where problems arose.
 */
class Exception extends \Exception {
	/**
	 * Sometimes we want to set the message before throwing the exception, e.g. if it is passed as a parameter to a call then that call could throw it and
	 * the correct exception class would be thrown (i.e. from the instance of the passed reception).
	 * @param $message
	 */
	public function setMessage($message) {
		$this->message = $message;
	}
}