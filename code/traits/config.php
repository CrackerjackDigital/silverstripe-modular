<?php
namespace Modular;

use Modular\Exceptions\Config as Exception;

trait config {
	
	abstract public function __invoke();
	
	public static function config($className = null) {
		return \Config::inst()->forClass($className ?: get_called_class());
	}
	
	/**
	 * Try the owner first then the exhibiting object.
	 * @param      $name
	 * @param null $key
	 * @return array|null|string
	 */
	public function ownerOrThisConfig($name, $key = null) {
		return $this()->get_config_setting($name, $key) ?: $this->get_config_setting($name, $key);
	}
	
	public function thisOrOwnerConfig($name, $key = null) {
		return $this->get_config_setting($name, $key) ?: $this()->get_config_setting($name, $key);
	}
	
	/**
	 * Try the merged owner and this object config, owner takes precedent over this.
	 * @param string $name config variable name, e.g 'allowed_actions'
	 * @param null   $key  optional key into config variable if found and variable is an array
	 * @return array|null|string
	 */
	public function ownerOrThisMergedConfig($name, $key = null) {
		$merged = [];
		if ($thisConfig = $this->get_config_setting($name)) {
			$merged[ $name ] = $thisConfig;
		}
		// this will override thisConfig
		if ($ownerConfig = $this()->get_config_setting($name)) {
			$merged[ $name ] = $ownerConfig;
		}
		
		return $this->v_or_kv($this->v_or_n($merged, $name), $key);
	}
	
	/**
	 * Try this config or owner object config, this takes precedent over owner.
	 * @param string $name config variable name, e.g 'allowed_actions'
	 * @param null   $key  optional key into config variable if found and variable is an array
	 * @return array|null|string
	 */
	public function thisOrOwnerMergedConfig($name, $key = null) {
		$merged = [];
		// this will override thisConfig
		if ($ownerConfig = $this()->get_config_setting($name)) {
			$merged[ $name ] = $ownerConfig;
		}
		if ($thisConfig = $this->get_config_setting($name)) {
			$merged[ $name ] = $thisConfig;
		}
		
		return $this->v_or_kv($this->v_or_n($merged, $name), $key);
	}
	
	/**
	 * If the value is an array and key exists return the value for the key or null.
	 * @param mixed      $value
	 * @param string|int $key
	 * @return mixed|null
	 */
	public static function v_or_n($value, $key = null) {
		$out = null;
		if (!is_null($key) && is_array($value)) {
			if (array_key_exists($key, $value)) {
				$out = $value[ $key ];
			}
		}
		
		return $out;
	}
	
	/**
	 * If the value is an array and key exists return the value for the key or return the value as provided.
	 * @param mixed      $value
	 * @param string|int $key
	 * @return mixed
	 */
	public function v_or_kv($value, $key = null) {
		if (!is_null($key) && is_array($value)) {
			if (array_key_exists($key, $value)) {
				$value = $value[ $key ];
			}
		}
		
		return $value;
	}
	
	/**
	 * Given an array of variable name => value do a config.update for config on the called
	 * class or supplied class name.
	 *
	 * @param array $options
	 * @param null  $className optional class to configure if not provided get_called_class is used
	 */
	public static function configure(array $options, $className = null) {
		foreach ($options as $variable => $value) {
			\Config::inst()->update($className ?: get_called_class(), $variable, $value);
		}
	}
	
	/**
	 * Require a non-null setting.
	 *
	 * @param      $name
	 * @param null $key
	 * @param null $className
	 * @param null $sourceOptions
	 * @throws \Modular\Exceptions\Exception
	 * @return mixed
	 */
	public static function require_config_setting($name, $key = null, $className = null, $sourceOptions = null) {
		$value = static::get_config_setting($name, $key, $className, $sourceOptions);
		if (is_null($value)) {
			throw new Exception("config variable '$name' not set");
		}
		if (!is_null($key)) {
			if (!is_array($value) || !array_key_exists($key, $value)) {
				throw new Exception("config variable '{$name}[{$key}]' not set or '$name' is not an array");
			}
			$value = $value[ $key ];
		}
		
		return $value;
	}
	
	/**
	 * @param      $name
	 * @param null $key           if value is an array and key is supplied return this key or null
	 * @param null $className     class name to get config of or null for get_called_class()
	 * @param null $sourceOptions SilverStripe config.get options e.g. Config::UNINHERITED
	 * @return array|null|string
	 */
	public static function get_config_setting($name, $key = null, $className = null, $sourceOptions = null) {
		$className = $className ?: get_called_class();
		
		$value = static::config($className)->get($name, $sourceOptions);
		
		return static::v_or_n($value, $key);
	}
	
	/**
	 * Return multiple config settings for class as an array in provided order with null as value where not found.
	 *
	 * @param       $className
	 * @param array $names either names as values or names as key and key into value as value
	 * @param null  $sourceOptions
	 * @return array
	 */
	public static function get_config_settings(array $names, $className, $sourceOptions = null) {
		$values = [];
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