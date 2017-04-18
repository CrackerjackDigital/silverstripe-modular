<?php

namespace Modular;

use Director;
use Modular\Interfaces\Service as ServiceInterface;
use Modular\Interfaces\Task as TaskInterface;
use Modular\Traits\debugging;
use Modular\Traits\enabler;
use Modular\Traits\trackable;
use SS_HTTPRequest;

abstract class Task extends \BuildTask implements ServiceInterface, TaskInterface {
	use enabler;
	use debugging;
	use trackable;

	const EnablerConfigVar = 'task_enabled';

	// can't use 'enabled' as that is a member var on BuildTask
	private static $task_enabled = true;

	/**
	 * Service interface method.
	 *
	 * @param array|\ArrayAccess $params
	 * @param string             $resultMessage
	 *
	 * @return mixed
	 */
	abstract public function execute( $params = [], &$resultMessage = '' );

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
		if ( ! $instance ) {
			$instance = new static( $options, $env );
		}

		return $instance;
	}

	/**
	 *
	 * @param SS_HttpRequest $request
	 */
	final public function run( $request ) {
		$this->debugger()->toScreen( Debugger::DebugAll );

		$taskName = get_class( $this );
		$resultMessage = '';

		$this->trackable_start( __METHOD__ );

		$runnable = static::enabled() && ( Director::isDev() || Director::is_cli() || \Permission::check( 'ADMIN' ) );
		if ( $runnable ) {
			$this->execute( $request->requestVars(), $resultMessage );
		} else {
			$this->debug_info( "Task $taskName not allowed to run" );
		}
		$this->trackable_end( $resultMessage );
	}

}