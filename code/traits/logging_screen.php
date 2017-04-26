<?php
namespace Modular\Traits;

use Modular\Logger;

trait logging_screen {

	/**
	 * @return Logger
	 */
	abstract public function logger();

	/**
	 * @param int|null $level
	 *
	 * @return $this
	 * @throws \Zend_Log_Exception
	 */
	public function toScreen( $level = self::LevelFromEnv ) {
		if ( is_null( $level ) || $level === self::LevelFromEnv ) {
			$level = $this->config()->get( 'environment_levels' )[ self::EnvType ];
		}
		$this->logger()->addWriter( new \LogOutputWriter( $level ) );

		return $this;
	}

}