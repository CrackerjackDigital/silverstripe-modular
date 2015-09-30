<?php
abstract class ModularModule extends Object {
	use \Modular\config;

    const RequireBeforeInit = 'before';
    const RequireAfterInit = 'after';
    const RequireAll = 'all';           // you don't need to provide an 'all' index into array though

    private static $module_path;

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


}
