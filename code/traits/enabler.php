<?php
namespace Modular\Traits;
/**
 * Manage enable/disable status through configuration. By default static config.enabled is checked, if can be overridden
 * by defining a constant 'EnablerConfigVar' on the exhibiting model, e.g. for if the default 'enabled' is being used
 * elsewhere in heirarchy.
 *
 * @package Modular
 */
trait enabler {
	/**
	 * If this trait is added to a field then only return fields if the field is enabled (will only override if the field is a subclass of the
	 * class which has cmsFields method in = trait inheritance. If cmsFields is implemented in class this trait is added to then this check will
	 * need to made in that method).
	 *
	 * @return array
	 */
	public function cmsFields($mode = null) {
		if (static::enabled()) {
			return parent::cmsFields($mode);
		}
		return [];
	}
	/**
	 * Is extension enabled? This should be checked before doing processing checks, augmenting SQL etc, it is not
	 * 'magical' so will need to be called on a case-by-case basis.
	 *
	 * @return bool
	 */
	public static function enabled() {
		$configVarName = defined('static::EnablerConfigVar') ? static::EnablerConfigVar : 'enabled';
		return (bool)\Config::inst()->get(get_called_class(), $configVarName);
	}

	/**
	 * Enable the extension (generally if previously disabled to e.g. skip checks etc). Ephemeral so will only affect
	 * the currently running process. Returns the previous state.
	 *
	 * @param bool $enable
	 * @return bool
	 */
	public static function enable($enable = true) {
		$previous = static::enabled();
		$configVarName = defined( 'static::EnablerConfigVar' ) ? static::EnablerConfigVar : 'enabled';
		\Config::inst()->update(get_called_class(), $configVarName, $enable);
		return $previous;
	}

	/**
	 * Disable the extension (skip any checks being made, SQL augmentation etc). This is not 'magical' and will
	 * still need to be checked for on a case-by-case basis.
	 *
	 * @return bool previous state
	 */
	public static function disable() {
		$old = static::enabled();
		$configVarName = defined('static::EnablerConfigVar') ? static::EnablerConfigVar : 'enabled';
		\Config::inst()->update(get_called_class(), $configVarName, false);
		return $old;
	}

}