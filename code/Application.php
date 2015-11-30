<?php

class ModularApp extends ModularModule {
	const DeviceMobile = 'mobile';
	const DeviceDesktop = 'desktop';

	// base dir for loading requirements from, if not set then theme folder will be used (e.g. themes/default/)
	private static $requirements_path;

	/**
	 * Override to provide current theme folder if requirements_path not set.
	 * @return string
	 */
	public static function requirements_path() {
		return static::config()->get('requirements_path') ?: SSViewer::get_theme_folder();
	}

	/**
	 * Use DEVICE_ABC constants to figure out 'device mode' ('mobile' or 'desktop').
	 *
	 * @return string 'mobile' or 'desktop'
	 */
	public static function device_mode() {
		if (defined('DEVICE_MOBILE')) {
			return self::DeviceMobile;
		} else {
			return self::DeviceDesktop;
		}
	}
}