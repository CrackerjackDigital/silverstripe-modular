<?php
namespace Modular\Interfaces;

interface Logger {
	/**
	 * Return the target we are logging to, e.g. for a file then the log file name.
	 * @return mixed
	 */
	public static function log_target();

	/**
	 * Return a logger instance we can log to.
	 * @return mixed
	 */
	public function logger();

	/**
	 * @param string     $message
	 * @param int        $facilities a level to compare to current set level and any other output options such as Screen etc
	 * @param string     $source
	 * @return $this
	 */
	public function log($message, $facilities, $source = '');

	/**
	 * Set a new level for debugging or return the existing one. If level is LevelFromEnv then
	 * use the config.environment_levels setting for the current environment ('dev', 'test' or 'live')
	 *
	 * @param mixed $level to set and any other options such as to screen etc
	 * @return $this if any params passed, current level if not
	 * @fluid
	 */
	public function level($level = null);
}