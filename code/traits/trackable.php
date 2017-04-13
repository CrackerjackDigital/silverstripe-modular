<?php
namespace Modular\Traits;

use Modular\Interfaces\Debugger;

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
		$this->debugger()->info("Starting" . ($message ? (" with message '$message'") : ''));
	}

	/**
	 * @param string $result will be output through print_r, so dont' pass anything sensitive
	 */
	public function trackable_end($result = '') {
		$this->debugger()->info("Ending" . ($result ? (" with result '" . print_r($result, true)) . "'" : ''));
	}

}