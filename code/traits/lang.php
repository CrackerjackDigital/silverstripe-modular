<?php
namespace Modular;

require_once 'config.php';

trait lang {
	function lang($key, $default = '', array $tokens = []) {
		return _t(get_called_class(), ".$key", $default ?: $key, $tokens);
	}

	/**
	 * Return a string from siteConfig.{$source$name} tokeised with $data, otherwise pass through to
	 * get_localised_config_string to look in lang file and config.
	 *
	 * @param       $source
	 * @param       $name
	 * @param       $default
	 * @param array $data
	 * @param null  $configOptions
	 * @return string
	 */
	public static function get_site_localised_config_setting($source, $name, $default, array $data = [], $configOptions = null) {
		if ($value = \SiteConfig::current_site_config()->{"$source$name"}) {
			return _t($value, $value, $data);
		}
		return self::get_localised_config_string($source, $name, $default, $data, $configOptions);
	}

	/**
	 * Return a string from localised language files or config or default in order of checking existence.
	 *
	 * @param       $source        - classname localised too or config classname
	 * @param       $name          - e.g. fieldname on object or message name in lang
	 * @param       $default       - default to use if not found in lang or config
	 * @param array $data          - data for tokens in resulting string
	 * @param null  $configOptions - options for config, e.g. Config.UNINHERITED
	 * @return string
	 */
	public static function get_localised_config_string($source, $name, $default, array $data = [], $configOptions = null) {
		if ($value = _t("$source.$name", $default, $data)) {
			return $value;
		}

		if ($value = self::get_config_setting($source, strtolower($name), null, $configOptions)) {
			if (is_string($value)) {
				return _t($value, $value, $data);
			}
		}
		return _t($default, $default, $data);
	}
}