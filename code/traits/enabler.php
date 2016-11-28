<?php
namespace Modular;
/**
 * Manage enable/disable status through configuration.
 *
 * @package Modular
 */
trait enabler {
	/**
	 * Is extension enabled? This should be checked before doing processing checks, augmenting SQL etc, it is not
	 * 'magical' so will need to be called on a case-by-case basis.
	 *
	 * @return boolean
	 */
	public static function enabled() {
		return (bool)\Config::inst()->get(get_called_class(), 'enabled');
	}

	/**
	 * Enable the extension (generally if previously disabled to e.g. skip checks etc). Ephemeral so will only affect
	 * the currently running process.
	 */
	public static function enable($enable = true) {
		\Config::inst()->update(get_called_class(), 'enabled', $enable);
	}

	/**
	 * Disable the extension (skip any checks being made, SQL augmentation etc). This is not 'magical' and will
	 * still need to be checked for on a case-by-case basis.
	 * @return bool previous state
	 */
	public static function disable() {
		$old = static::enabled();
		\Config::inst()->update(get_called_class(), 'enabled', false);
		return $old;
	}

}