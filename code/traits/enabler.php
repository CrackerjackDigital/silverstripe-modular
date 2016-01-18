<?php
namespace Modular;

trait enabler {

	/**
	 * Is extension enabled? This should be checked before doing processing checks, augmenting SQL etc, it is not
	 * 'magical' so will need to be called on a case-by-case basis.
	 *
	 * @return boolean
	 */
	public static function enabled() {
		return \Config::inst()->get(get_called_class(), 'enabled');
	}

	/**
	 * Enable the extension (generally if previously disabled to e.g. skip checks etc). Ephemeral so will only affect
	 * the currently running process.
	 */
	public static function enable() {
		\Config::inst()->update(get_called_class(), 'enabled', true);
	}

	/**
	 * Disable the extension (skip any checks being made, SQL augmentation etc). This is not 'magical' and will
	 * still need to be checked for on a case-by-case basis.
	 */
	public static function disable() {
		\Config::inst()->update(get_called_class(), 'enabled', false);
	}


}