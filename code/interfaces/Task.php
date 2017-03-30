<?php
namespace Modular\Interfaces;

interface Task {
	/**
	 * Get the task to do something.
	 *
	 * @param null   $params
	 * @param string $resultMessage
	 *
	 * @return mixed
	 */
	public function execute($params = null, &$resultMessage = '');

	/**
	 * Finish the task and perhaps delete it or update it to an 'archived' state.
	 * @return mixed
	 */
	public function archive();
}