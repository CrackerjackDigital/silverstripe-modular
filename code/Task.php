<?php
namespace Modular;

use Director;

abstract class Task extends \BuildTask {
	use enabler;
	use debugging;

	const EnablerConfigVar = 'task_enabled';

	// can't use 'enabled' as that is a member var on BuildTask
	private static $task_enabled = true;

	abstract public function execute($request);

	/**
	 * Task can run if enabled and either is_cli or logged in as ADMIN.
	 * @return bool
	 */
	public function canRun() {
		return static::enabled() && (Director::isDev() || Director::is_cli() || \Permission::check('ADMIN'));
	}

	final public function run($request) {
		$taskName = get_class($this);

		if ($this->canRun()) {
			if (!Director::is_cli()) {
				ob_start('nl2br');
			}
			$this->println("Starting task $taskName");

			$this->execute($request);

			$this->println("End of task $taskName");

			ob_end_flush();
		} else {
			$this->println("Task $taskName not allowed to run");
		}
	}

	public static function println($line) {
		$prefix = Director::is_cli() ? "\t" : "&nbsp;-&nbsp;";
		echo date('Y-m-d_h:i:s') . "$prefix$line" . PHP_EOL;
	}
}