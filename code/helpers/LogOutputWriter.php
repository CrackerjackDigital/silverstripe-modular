<?php
class LogOutputWriter extends Zend_Log_Writer_Abstract {

	private $messageType;

	public function __construct($messageType) {
		$this->messageType = $messageType;
	}

	/**
	 * Write a message to the output buffer, if cli append an eol, if not append a html break then eol.
	 *
	 * @param  array $event log data event
	 * @return void
	 */
	protected function _write($event) {
		echo $event . (Director::is_cli() ? '' : '<br/>') . PHP_EOL;
	}

	/**
	 * Construct a Zend_Log driver
	 *
	 * @param  int $messageType type of message to log
	 * @return Zend_Log_FactoryInterface
	 */
	static public function factory($messageType = 3) {
		return new self($messageType);
	}
}