<?php
namespace Modular;

trait config {
	public static function config($className = null) {
		return \Config::inst()->forClass($className ?: get_called_class());
	}

	/**
	 * @param      $className
	 * @param      $name
	 * @param null $key
	 * @param null $sourceOptions SilverStripe config.get options e.g. Config::UNINHERITED
	 * @return array|null|string
	 */
	public static function get_config_setting($className, $name, $key = null, $sourceOptions = null) {
		$value = static::config($className)->get($name, $sourceOptions);

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
	public static function get_config_settings($className, array $names, $sourceOptions = null) {
		$values = array();
		foreach ($names as $key => $name) {
			if (is_int($key)) {
				$values[] = static::get_config_setting($className, $name, null, $sourceOptions);
			} else {
				$values[] = static::get_config_setting($className, $key, $name, $sourceOptions);
			}
		}
		return $values;
	}
}