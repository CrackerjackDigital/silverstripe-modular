<?php

abstract class ModularModule extends Object {
    const RequireBeforeInit = 'before';
    const RequireAfterInit = 'after';
    const RequireAll = 'all';           // you don't need to provide an 'all' index into array though

    private static $post_verify_url = '/account';

    private static $module_path;

    /**
     * Includes requirements from static.config.requirements. If a requirement starts with '/' then
     * it is included relative to site root, otherwise it is included relative to module root. Requirements
     * can be defined as 'before' and 'after' in which case they will be included as per $beforeOrAfterInit
     * parameter otherwise all requirements will be included when called.
     *
     * e.g.
     *
     * private static $requirements = array(
     *  '/framework/thirdpary/jquery/jquery.min.js', // will come relative to site root
     *  'js/modulescript.js' // will load from module_path/js/
     *  'css/modulecss.js'
     * )
     *
     * or
     *
     * private static $requirements = array(
     *  'before' => array( ... )',
     *  'after' => array( ... )'
     * )
     *
     * @param $beforeOrAfterInit wether to include before or after requirements
     */
    public static function requirements($beforeOrAfterInit = self::RequireAll) {
        $basePath = static::module_path();
        $requirements = static::config()->get('requirements');

        if (isset($requirements[$beforeOrAfterInit])) {
            $requirements = $requirements[$beforeOrAfterInit];
        }

        foreach ($requirements as $requirement) {
            if (substr($requirement, -3) == '.js') {
                if (substr($requirement, 0, 1) == '/') {
                    Requirements::javascript(substr($requirement, 1));
                } else {
                    Requirements::javascript(Controller::join_links($basePath, $requirement));
                }
            } else {
                if (substr($requirement, 0, 1) == '/') {
                    Requirements::css(substr($requirement, 1));
                } else {
                    Requirements::css(Controller::join_links($basePath, $requirement));
                }
            }
        }
    }

    /**
     * This should be overriden by/copy-pasted to implementation to provide a default module path to the module,
     * where the module installs relative to site root e.g. '/swipestreak-gallery'. Sadly
     * can't seem to declare a static method abstract in php without getting an E_STRICT.
     *
     * @param string $append - add this to end of found path
     * @return string
     */
    public static function module_path($append = '') {
        if (get_called_class() == 'ModularModule') {
            user_error('This method should be overridden in implementation');
        }
        return Controller::join_links(
            ltrim(static::config()->get('module_path') ?: Director::makeRelative(realpath(__DIR__ . '/../')), '/'),
            $append
        );
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
        if ($value = SiteConfig::current_site_config()->{"$source$name"}) {
            return _t($value, $value, $data);
        }
        return self::get_localised_config_string($source, $name, $default, $data, $configOptions);

    }
    /**
     * Return a string from localised language files or config or default in order of checking existence.
     *
     * @param       $source     - classname localised too or config classname
     * @param       $name       - e.g. fieldname on object or message name in lang
     * @param       $default    - default to use if not found in lang or config
     * @param array $data       - data for tokens in resulting string
     * @param null  $configOptions    - options for config, e.g. Config.UNINHERITED
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

    /**
     * @param      $className
     * @param      $name
     * @param null $key
     * @param null $options
     * @return array|null|scalar
     */
    public static function get_config_setting($className, $name, $key = null, $options = null) {
        // if no class then presume a module configuration variable
        if (is_null($className)) {
            $className = get_called_class();
        }
        $value = Config::inst()->get($className, $name, $options);
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
     * @param null $options
     * @return array
     */
    public static function get_config_settings($className, array $names, $options = null) {
        $values = array();
        foreach ($names as $key => $name) {
            if (is_int($key)) {
                $values[] = static::get_config_setting($className, $name, null, $options);
            } else {
                $values[] = static::get_config_setting($className, $key, $name, $options);
            }
        }
        return $values;
    }


}