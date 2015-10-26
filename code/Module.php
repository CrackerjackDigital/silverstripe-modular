<?php
use Modular\ModularObject as Object;

abstract class ModularModule extends Object
{
	const CSSExtension        = 'css';
	const JSExtension         = 'js';
	const JSTemplateExtension = 'jst';

	use \Modular\config;

	const BeforeInit = 'before';
	const AfterInit  = 'after';
	const Block      = 'block';
	const BothInit   = 'all';           // you don't need to provide an 'all' index into array though

	/** @var  string overrride in derived or set in config or dirname(module_path()) is used */
	private static $module_name;

	/** @var string path to module relative to site root, override in derived class or set in config */
	private static $module_path;

	// what we load and when to load, configure in e.g. a requirements.yml for the module or application.
	private static $requirements = [
		// load those onBeforeInit
		self::BeforeInit => [
			// Examples: either a numerically indexed array or map with file name => enabled status e.g:
			// if starts with '/' then loaded from webroot, otherwise relative to the current module dir
			# 'components/select2/select2.js',
			# '/themes/default/js/example.js' => false,
			# 'components/select2/select2.js' => false
		],
		// load these onAfterInit
		self::AfterInit  => [
			// same syntax as BeforeInit
		],
		// these get blocked on first call to add_requirements
		self::Block      => [
			// Examples:
			# /framework/thirdparty/jquery/jquery.js
			# /framework/thirdparty/jquery/jquery.min.js
		],
	];

	/** @var string base path to where requirements should load from if different to module path */
	private static $requirements_path;

	private static $script_types = [
		self::CSSExtension        => true,
		self::JSExtension         => true,
		self::JSTemplateExtension => true,
	];
	/**
	 * Files to combine by map [type => yes/no]
	 *
	 * @var array
	 */
	private static $combine = [
		self::CSSExtension        => false,
		self::JSExtension         => true,
		self::JSTemplateExtension => true       // javascript templates end with 'JST'.

		// TODO: implement ordering and filtering, exclusions
		# 'ordered' => [ 'jquery.min.js' => 1, 'jquery-extension.min.js' => 2 ]
		# 'excluded' => [ 'trouble-if-not-loaded-first.js' => 'before', 'load-last-separately.js' => 'after' ]
	];

	/**
	 * Adds javascript files to requirements based on them ending in '.js' using config.install_dir as base path.
	 *
	 * @param string $when - look at before or after components.
	 */
	public static function requirements($when) {
		$forClass = get_called_class();

		// have to save and pass as later calls will be this class not the real caller.
		$moduleName = $forClass::module_name();

		if ($when == self::Block) {

			static::block(static::get_config_setting('requirements', self::Block, $forClass));

		} else {
			$requirements = static::get_config_setting('requirements', $when, $forClass);

			$required = static::add_requirements($requirements, $moduleName);

			if (static::config()->get('combine')) {
				static::combine($required, $moduleName);
			}
		}
	}

	/**
	 * Iterate config.$configVariable map and return map excluding false values and with 'true' values as []
	 *
	 * @param string $configVariable
	 * @return array
	 */
	protected static function include_script_types($configVariable = 'script_types') {
		return array_filter(
			array_map(
				function ($item) {
					return $item ? [] : null;
				},
				static::config()->get($configVariable)
			),
			function ($item) {
				return is_array($item);
			}
		);
	}

	/**
	 * Iterate through 'block' key in config.requirements and block each script.
	 * @param array $block map of paths to block.
	 * @return array of blocked scripts as ['js' => 'path'] type entries
	 */
	protected static function block(array $block) {
		if ($block) {
			$blocked = $scriptTypes = static::include_script_types();

			$required = array_merge(
				$scriptTypes,
				[
					self::CSSExtension        => Requirements::backend()->get_css(),
					self::JSExtension         => Requirements::backend()->get_javascript(),
					self::JSTemplateExtension => Requirements::backend()->get_custom_scripts(),
				]
			);

			foreach ($scriptTypes as $fileType => $_) {
				foreach ($block as $path) {

					$path = static::requirement_path($path);

					foreach ($required[$fileType] as $require) {

						if (fnmatch($path, $require)) {

							$blocked[$fileType][] = $require;

							Requirements::block(
								$required
							);
						}
					}
				}
			}
			return $blocked;
		}
	}

	protected static function add_requirements(array $requirements, $moduleName) {
		if ($requirements) {

			// files to get combined get added here under key of file type, e.g. 'js', or 'css'
			$required = self::include_script_types();

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
				foreach (static::include_script_types() as $extension => $_) {
					if (substr($path, -strlen($extension)) == $extension) {

						static::$extension($path);

						$required[$extension][basename($path)] = $path;
					}
				}

			}
			return $required;
		}
	}

	protected static function css($path) {
		Requirements::css($path);
	}

	protected static function js($path) {
		Requirements::javascript($path);
	}

	protected static function jst($path) {
		Requirements::javascriptTemplate(
			$path,
			static::template_data(self::JSTemplateExtension)
		);
	}

	/**
	 * @param array $requirements map of [ 'js' => [javascripts], 'css' => [css files]]
	 * @param       $moduleName
	 * @return array
	 */
	protected static function combine(array $requirements, $moduleName) {
		if ($requirements) {
			$scriptTypes = static::include_script_types('combine');

			foreach ($scriptTypes as $fileType => $_) {
				// check we should combine this file type
				if ($combine = static::get_config_setting('combine', $fileType)) {

					if (is_array($combine[$fileType])) {
						// if config is an array then we are filtering, at the moment exclusion only by
						// file name not path
						$requirements[$fileType] = array_diff_key(
							$requirements[$fileType],
							array_map(
								'basename',
								$combine[$fileType]
							)
						);
					}
					if (!empty($requirements[$fileType])) {
						Requirements::combine_files(
							"{$moduleName}.$fileType",
							$requirements[$fileType]
						);
					}
				}
			}
			return $requirements;
		}
	}


	/**
	 * Return an array of merged results from an extend.provideJavascriptTemplateData call
	 * on the current controller.
	 *
	 * @param string $path to template script passed to extension call.
	 * @return array
	 */
	protected static function template_data($fileType) {
		return array_reduce(
			Controller::curr()->extend('provideTemplateData', $fileType),
			function ($carry, $extensionResult) {
				return array_merge(
					$carry,
					$extensionResult
				);
			},
			[]
		);
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
	 * @return string
	 */
	public static function module_name() {
		return static::config()->get('module_name') ?: rtrim(static::module_path(), '/');
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
	 *
	 * @return string
	 */
	public static function requirements_path() {
		return static::module_path();
	}

}
