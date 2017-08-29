<?php
namespace Modular\Interfaces;

interface Executable {

	/**
	 * Get the implementor to do something.
	 *
	 * @param array|\ArrayAccess $params e.g. to merge into fields or configure service execution
	 * @param string             $resultMessage
	 *
	 * @return mixed
	 */
	public function execute( $params = [], &$resultMessage = '' );

}