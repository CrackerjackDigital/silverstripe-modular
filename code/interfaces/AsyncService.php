<?php
namespace Modular\Interfaces;

interface AsyncService extends Service {
	/**
	 * Asynchronous call, service is not local in time or space, either call it remotely or queued it for later.
	 *
	 * @param null $params fields to be merged with the queued instance of the Task
	 *
	 * @return mixed
	 */
	public function dispatch( $params = null );

}