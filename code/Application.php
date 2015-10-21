<?php

class ModularApp extends ModularModule {
	private static $requirements_path;

	/**
	 * Override to provide current theme folder if requirements_path not set.
	 * @return string
	 */
	public static function requirements_path() {
		return static::config()->get('requirements_path') ?: SSViewer::get_theme_folder();
	}
}