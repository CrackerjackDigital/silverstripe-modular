<?php
namespace Modular;

use Director;
use Modular\Interfaces\Service;
use Modular\Traits\debugging;
use Modular\Traits\enabler;
use Modular\Traits\trackable;
use SS_HTTPRequest;

abstract class Task extends \BuildTask implements Service {
	use enabler;
	use debugging;
	use trackable;

	const EnablerConfigVar = 'task_enabled';

	// can't use 'enabled' as that is a member var on BuildTask
	private static $task_enabled = true;

	/**
	 * Service interface method.
	 *
	 * @param null   $params
	 *
	 * @param string $resultMessage
	 *
	 * @return mixed
	 */
	abstract public function execute($params = null, &$resultMessage = '');

	/**
	 * Simple singleton
	 *
	 * @param null   $options
	 * @param string $env
	 *
	 * @return static
	 */
	public static function get( $options = null, $env = '' ) {
		static $instance;
		if (!$instance) {
			$instance = new static( $options, $env );
		}
		return $instance;
	}

	/**
	 * Task can run if enabled and either is_cli or logged in as ADMIN.
	 * @return bool
	 */
	public function canRun() {
		return static::enabled() && (Director::isDev() || Director::is_cli() || \Permission::check('ADMIN'));
	}

	/**
	 *
	 * @param SS_HttpRequest $request
	 */
	final public function run($request) {
		$this->debugger()->toScreen(Debugger::DebugAll);

		$this->trackable_start( __METHOD__);

		$taskName = get_class($this);

		$message = '';
		if ($this->canRun()) {
			$this->execute($request->requestVars(), $message);
		} else {
			$this->debug_info("Task $taskName not allowed to run");
		}
		$this->trackable_end($message);
	}

}