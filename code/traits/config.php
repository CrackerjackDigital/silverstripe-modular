<?php
namespace Modular;

trait config {
	public static function config($className = null) {
		return \Config::inst()->forClass($className ?: get_called_class());
	}

	/**
	 * @param      $name
	 * @param null $key if value is an array and key is supplied return this key or null
	 * @param null $className class name to get config of or null for get_called_class()
	 * @param null $sourceOptions SilverStripe config.get options e.g. Config::UNINHERITED
	 * @return array|null|string
	 */
	public static function get_config_setting($name, $key = null, $className = null, $sourceOptions = null) {
		$value = static::config($className ?: get_called_class())->get($name, $sourceOptions);

		if ($key && is_array($value)) {
			if (array_key_exists($key, $value)) {
				$value = $value[$key];
			} else {
				$value = null;
			}
		}
		return $value;
	}

	/**
	 * Return multiple config settings for class as an array in provided order with null as value where not found.
	 *
	 * @param $className
	 * @param array $names either names as values or names as key and key into value as value
	 * @param null $sourceOptions
	 * @return array
	 */
	public static function get_config_settings(array $names, $className, $sourceOptions = null) {
		$values = array();
		foreach ($names as $key => $name) {
			if (is_int($key)) {
				$values[] = static::get_config_setting($name, null, $className, $sourceOptions);
			} else {
				$values[] = static::get_config_setting($key, $name, $className, $sourceOptions);
			}
		}
		return $values;
	}
}