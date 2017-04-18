<?php

namespace Modular\Interfaces;

interface Service {

	/**
	 * Return a configured instance of the service.
	 *
	 * @param null|mixed $options to create instance with
	 * @param string     $env     to run in (e.g. for testing force a particular environment)
	 *
	 * @return Service
	 */
	public static function get( $options = null, $env = '' );

	/**
	 * Get the service to do something.
	 *
	 * @param array|\ArrayAccess $params e.g. to merge into fields or configure service execution
	 * @param string $resultMessage
	 *
	 * @return mixed
	 */
	public function execute( $params = [], &$resultMessage = '' );

}