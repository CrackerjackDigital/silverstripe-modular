<?php
namespace Modular\Traits;

use Modular\Exceptions\Config as Exception;

trait config {

	/**
	 * @return mixed
	 */
	abstract public function __invoke();

	public static function config($className = null) {
		return \Config::inst()->forClass($className ?: get_called_class());
	}

	/**
	 * Try the owner first then the exhibiting object, only one or the other will be returned with no merging.
	 *
	 * @param      $name
	 * @param null $key
	 * @return string|array|null
	 */
	public function ownerOverThisConfig($name, $key = null) {
		return $this()->get_config_setting($name, $key) ?: $this->config_subsetting($name, $key);
	}

	/**
	 * Try the exhibiting object then the owner object, only one or the other will be returned with no merging.
	 *
	 * @param      $name
	 * @param null $key
	 * @return string|array|null
	 */
	public function thisOverOwnerConfig($name, $key = null) {
		return $this->config_subsetting($name, $key) ?: $this()->get_config_setting($name, $key);
	}

	/**
	 * Merge the owner's config over the exhibiting objects config so owner's config takes precedence.
	 *
	 * @param string $name config variable name, e.g 'allowed_actions'
	 * @param string $key  optional key into config variable if found and variable is an array
	 * @return array|null|string
	 */
	public function ownerOverThisMergedConfig($name, $key = null) {
		$merged = [];
		if ($thisConfig = $this->config_subsetting($name)) {
			$merged[ $name ] = $thisConfig;
		}
		// this will override thisConfig
		if ($ownerConfig = $this()->get_config_setting($name)) {
			$merged[ $name ] = $ownerConfig;
		}

		return $this->v_or_kv($this->v_or_n($merged, $name), $key);
	}

	/**
	 * Merge the exhibiting objects config over the owner's config so the exhibiting objects config takes precedence.
	 *
	 * @param string $name config variable name, e.g 'allowed_actions'
	 * @param null   $key  optional key into config variable if found and variable is an array
	 * @return array|null|string
	 */
	public function thisOverOwnerMergedConfig($name, $key = null) {
		$merged = [];
		// this will override thisConfig
		if ($ownerConfig = $this()->get_config_setting($name)) {
			$merged[ $name ] = $ownerConfig;
		}
		if ($thisConfig = $this->config_subsetting($name)) {
			$merged[ $name ] = $thisConfig;
		}

		return $this->v_or_kv($this->v_or_n($merged, $name), $key);
	}

	/**
	 * If the value is an array and key exists return the value for the key or null.
	 *
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
	 *
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
		$value = static::config_subsetting($name, $key, $className, $sourceOptions);
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
	public static function config_subsetting($name, $key = null, $className = null, $sourceOptions = null) {
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
	public static function config_subsettings(array $names, $className, $sourceOptions = null) {
		$values = [];
		foreach ($names as $key => $name) {
			if (is_int($key)) {
				$values[] = static::config_subsetting($name, null, $className, $sourceOptions);
			} else {
				$values[] = static::config_subsetting($key, $name, $className, $sourceOptions);
			}
		}

		return $values;
	}

	/**
	 * Do an fnmatch with keys of config var $name to $match and return the first found match.
	 *
	 * e.g. if config.map = [ 'Varchar*' => 'String' ]
	 *      then match_config_setting('map', 'Varchar(255)') will return 'String'
	 *
	 * Will try a direct 'get' first before using matching
	 *
	 * @param string $name      the name of the config var to check, should be a map of [ pattern => value ]
	 * @param string $match     with this test value against keys in the config using fnmatch($key, $match)
	 * @param null   $className of configuration to get (by default get_called_class will be used).
	 * @param null   $sourceOptions
	 *
	 * @return mixed
	 * @throws \Exception
	 * @throws \Modular\Exceptions\Debug
	 */
	public static function match_config_setting($name, $match, $className = null, $sourceOptions = null) {
		$className = $className ?: get_called_class();

		// try a direct get first
		if (!$type = static::config_subsetting($name, $match, $className, $sourceOptions)) {

			// get the map as is
			$map = static::config($className)->get($name, $sourceOptions);

			if (!($map && is_array($map))) {
				static::debugger()->fail(new Exception("No such config array '$name' set on class '$className'"));
			}
			// now loop through map treating as pattern => type
			foreach ($map as $pattern => $type) {
				if (fnmatch($pattern, $match)) {
					// drop out of loop with this value
					break;
				}
				// reset to null for last loop iteration being not found
				$type = null;
			}
		}
		return $type;
	}

}