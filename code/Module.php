<?php
use Modular\ModularObject as Object;

abstract class ModularModule extends Object {
	use \Modular\config;

    const BeforeInit = 'before';
    const AfterInit = 'after';
	const Block = 'block';
    const All = 'all';           // you don't need to provide an 'all' index into array though

	/** @var string path to module relative to site root, override in derived class or set in config */
    private static $module_path;

	/** @var string base path to where requirements should load from if different to module path */
	private static $requirements_path;


	// what we load and when to load, from requirements.yml.
	private static $requirements = [
		// load those onBeforeInit
		self::BeforeInit => [
			// either a numerically indexed array or map with file name => enabled status e.g:
		    // if starts with '/' then loaded from webroot, otherwise relative to the current module dir
			# 'components/select2/select2.js',
		    # '/themes/default/js/example.js' => false,
		    # 'componants/select2/select2.js' => false
		],
		// load these onAfterInit
		self::AfterInit => [
		],
		// these get blocked on first call to add_requirements
	    self::Block => [
		    # /framework/thirdparty/jquery/jquery.js
		    # /framework/thirdparty/jquery/jquery.min.js
	    ]
	];

	/**
	 * Adds javascript files to requirements based on them ending in '.js' using config.install_dir as base path.
	 * @param string $when - look at before or after components.
	 */
	public static function add_requirements($when) {
		static $block;

		$forClass = get_called_class();

		if (!$block) {
			if ($block = static::get_config_setting('requirements', static::Block, $forClass)) {
				foreach($block as $path) {
					$path = static::requirement_path($path);
					Requirements::block(
						$path
					);
				}
			}
		}

		$requirements = static::get_config_setting('requirements', $when, $forClass) ?: [];
		foreach ($requirements as $key => $path) {
			if (!is_numeric($key)) {
				// map with file as key, enabled state as value
				if (!$path) {
					continue;
				}
				$path = $key;
			}
			$path = static::requirement_path($path);

			if (!is_file(Controller::join_links(Director::baseFolder(), $path))) {
				user_error("No such requirement file: '$path'");
			}

			if (substr($path, -3, 3) === '.js') {
				Requirements::javascript($path);
			}
			if (substr($path, -4, 4) === '.css') {
				Requirements::css($path);
			}
		}

	}

	/**
	 * Return path in suitable format for Requirements either from the module install dir or from web root depending
	 * on path staring with '/' or not.
	 *
	 * @param string      $path
	 * @param string|null $baseDir to use building path or null for the current module install dir.
	 * @return string
	 */
	public static function requirement_path($path, $baseDir = null) {
		$baseDir = is_null($baseDir) ? static::requirements_path() : $baseDir;

		if (substr($path, 0, 1) !== '/') {
			$path = Controller::join_links(
				$baseDir,
				$path
			);
		}
		if (substr($path, 0, 1) == '/') {
			$path = substr($path, 1);
		}
		return $path;
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
	    // TODO fix so we can find directory of called class not this class's directory
        return Controller::join_links(
            ltrim(static::config()->get('module_path') ?: Director::makeRelative(realpath(__DIR__ . '/../')), '/'),
            $append
        );
    }

	/**
	 * A module's requirements load from the same base path as the module.
	 * @return string
	 */
	public static function requirements_path() {
		return static::module_path();
	}


}
