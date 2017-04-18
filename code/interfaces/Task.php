<?php

namespace Modular\Interfaces;

use Modular\Fields\QueuedState;

interface Task {
	/**
	 * Get the task to do something, this is the default method called
	 * when a task is called as a BuildTask or as a QueuedTask.
	 *
	 * @param array|\ArrayAccess $params
	 * @param string $resultMessage
	 *
	 * @return mixed
	 */
	public function execute( $params = [], &$resultMessage = '' );

}