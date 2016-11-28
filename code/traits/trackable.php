<?php
namespace Modular;

trait trackable {
	/**
	 * @param int|null $level  create debugger with this log level, or set the current log level if already created
	 * @param string   $source used in output, if not provided then the called class is used
	 * @return \Modular\Debugger
	 */
	abstract public function debugger($level = Debugger::LevelFromEnv, $source = '');

	public function trackable_start($what, $message = '') {
		$className = get_called_class();
		$signature = md5(microtime());
		$this->debugger()->source("$className:$what@$signature");
		$this->debug_info("Starting with message '$message'");
	}

	public function trackable_end($result = '') {
		$this->debug_info("Ending with result '$result'");
	}

}