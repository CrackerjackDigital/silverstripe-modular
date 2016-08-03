<?php
namespace Modular;

use Director;
use Requirements;

abstract class Module extends Object {
	use config;

	// handled file types which for simplicity are also the file extensions
	const FileTypeCSS                = 'css';
	const FileTypeJavascript         = 'js';
	const FileTypeJavascriptTemplate = 'jst';

	const RequirementsTemplateDataExtensionMethod = 'modularRequirementsTemplateData';

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
			# 'components/select2/select2.js' => false,         // disable default library loading
			# '/themes/default/js/debug.js' => ['dev'],           // only load in dev mode, array means test mode
			# '/themes/default/js/debug.js' => ['dev', 'test']           // load in dev and test modes
			# '/app/js/mosaic.jst' => 'Application.MosaicJST' // call Application.MosaicJST to populate this javascriptTemplate

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
		self::FileTypeCSS                => true,
		self::FileTypeJavascript         => true,
		self::FileTypeJavascriptTemplate => true,
	];
	/**
	 * Files to combine by map [type => yes/no]
	 *
	 * @var array
	 */
	private static $combine = [
		self::FileTypeCSS                => false,
		self::FileTypeJavascript         => true,
		self::FileTypeJavascriptTemplate => true
		// javascript templates end with 'JST'.

		// TODO: implement ordering and filtering, exclusions
		# 'ordered' => [ 'jquery.min.js' => 1, 'jquery-extension.min.js' => 2 ]
		# 'excluded' => [ 'trouble-if-not-loaded-first.js' => 'before', 'load-last-separately.js' => 'after' ]
	];

	// JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_OBJECT_AS_ARRAY
	private static $json_encode_options = 288;

	private static $json_decode_options = 0;

	private static $decode_depth = 512;

	/**
	 * Adds javascript files to requirements based on them ending in '.js'
	 * using config.install_dir as base path.
	 *
	 * @param        $controller
	 * @param string $when - look at before or after components.
	 */
	public static function requirements($controller, $when) {
		$forClass = get_called_class();

		// have to save and pass as later calls will be this class not the real caller.
		$moduleName = $forClass::module_name();

		if ($when == self::Block) {

			static::block(static::get_config_setting('requirements', self::Block, $forClass));

		} else {
			$requirements = static::get_config_setting('requirements', $when, $forClass);

			$required = static::add_requirements($controller, $requirements, $moduleName);

			if (static::config()->get('combine')) {
				static::combine($required, $moduleName);
			}
		}
	}

	/**
	 * Iterate config.$configVariable map and return map excluding false values
	 * and with 'true' values as []
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
	 *
	 * @param array $block map of paths to block.
	 * @return array of blocked scripts as ['js' => 'path'] type entries
	 */
	protected static function block(array $block) {
		if ($block) {
			$blocked = $scriptTypes = static::include_script_types();

			$required = array_merge(
				$scriptTypes,
				[
					self::FileTypeCSS                => Requirements::backend()
						->get_css(),
					self::FileTypeJavascript         => Requirements::backend()
						->get_javascript(),
					self::FileTypeJavascriptTemplate => Requirements::backend()
						->get_custom_scripts(),
				]
			);

			foreach ($scriptTypes as $fileType => $_) {
				foreach ($block as $path) {

					$path = static::requirement_path($path);

					foreach ($required[ $fileType ] as $require) {

						if (fnmatch($path, $require)) {

							$blocked[ $fileType ][] = $require;

							Requirements::block(
								$required
							);
						}
					}
				}
			}
			return $blocked;
		}
		return [];
	}

	/**
	 * Iterate through configured requirements and require if:
	 *
	 * - The type (jst, etc from FileTypeABC constants) is enabled
	 * - The settings for the file match e.g. the current runtime environment
	 * (dev, test etc)
	 * - The file exists (this may be loosened once backend requirements can
	 * handle non-existant files)
	 *
	 * @param       $controller
	 * @param array $requirements
	 * @param       $moduleName
	 * @return array
	 */
	protected static function add_requirements($controller, array $requirements, $moduleName) {
		if ($requirements) {

			// files to get combined get added here under key of file type, e.g. 'js', or 'css'
			$required = self::include_script_types();

			$envType = Director::get_environment_type();

			foreach ($requirements as $key => $path) {
				if (is_numeric($key)) {
					$info = true;
				} else {
					// map has file path as key, information as value, if info is false then don't include
					$info = $path;
					$path = $key;
				}
				if (!$info) {
					// info is falsish, skip this requirement.
					continue;
				}
				if (is_array($info)) {
					// info is an array of environments in which to load this requirement,
					// test if any matches current environment
					if (!in_array($envType, $info)) {
						continue;
					}
				}

				$path = static::requirement_path($path);

				if (!is_file(Controller::join_links(Director::baseFolder(), $path))) {
					user_error("No such requirement file: '$path'");
				}
				foreach (static::include_script_types() as $extension => $_) {
					if (substr($path, -strlen($extension)) == $extension) {

						// MAGIC METHOD CALL through to self::js, self::css etc
						static::$extension($controller, $path, $info);

						$required[ $extension ][ basename($path) ] = $path;
					}
				}

			}
			return $required;
		}
		return [];
	}

	/**
	 * SilverStripe require CSS
	 *
	 * @param $controller
	 * @param $path
	 */
	protected static function css($controller, $path) {
		Requirements::css($path);
	}

	/**
	 * SilverStripe require javascript
	 *
	 * @param $controller
	 * @param $path
	 */
	protected static function js($controller, $path) {
		Requirements::javascript($path);
	}

	/**
	 * SilverStripe require javascript template, using self.template_data to
	 * gather variables to pass in (which in turn calls extensions on the
	 * current controller to get relevant info).
	 *
	 * @param      $controller
	 * @param      $path
	 * @param null $info
	 */
	protected static function jst($controller, $path, $info = null) {
		Requirements::javascriptTemplate(
			$path,
			self::requirements_template_data(
				$controller,
				self::FileTypeJavascriptTemplate,
				$info
			)
		);
	}

	/**
	 * @param array $requirements map of [ 'js' => [javascripts], 'css' => [css
	 *                            files]]
	 * @param       $moduleName
	 * @return array
	 */
	protected static function combine(array $requirements, $moduleName) {
		if ($requirements) {
			$scriptTypes = static::include_script_types('combine');

			foreach ($scriptTypes as $fileType => $_) {
				// check we should combine this file type
				if ($combine = static::get_config_setting('combine', $fileType)) {

					if (is_array($combine[ $fileType ])) {
						// if config is an array then we are filtering,
						// at the moment exclusion only by file name not path
						$requirements[ $fileType ] = array_diff_key(
							$requirements[ $fileType ],
							array_map(
								'basename',
								$combine[ $fileType ]
							)
						);
					}
					if (!empty($requirements[ $fileType ])) {
						Requirements::combine_files(
							"{$moduleName}.$fileType",
							$requirements[ $fileType ]
						);
					}
				}
			}
			return $requirements;
		}
		return [];
	}

	/**
	 * Return path in suitable format for Requirements either from the module
	 * install dir or from web root depending on path staring with '/' or not.
	 *
	 * @param string      $path
	 * @param string|null $baseDir to use building path or null for the current
	 *                             module install dir.
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
		return static::config()->get('module_name')
			?: rtrim(static::module_path(), '/');
	}

	/**
	 * This should be overriden by/copy-pasted to implementation to provide a
	 * default module path to the module, where the module installs relative to
	 * site root e.g. '/swipestreak-gallery'. Sadly can't seem to declare a
	 * static method abstract in php without getting an E_STRICT.
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
			ltrim(static::config()->get('module_path')
				?: Director::makeRelative(realpath(__DIR__ . '/../')), '/'),
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

	/**
	 * Return an array of merged results from an
	 * extend.modularRequirementsTemplateData call on the current controller.
	 *
	 * @param        $controller
	 * @param string $fileType e.g. ModularModule::JavascriptTemplateFile constant
	 * @param string $info
	 * @return array
	 */
	protected static function requirements_template_data($controller, $fileType, $info = '') {
		$controller = $controller ?: Controller::curr();

		return array_reduce(
			$controller->extend(
				self::RequirementsTemplateDataExtensionMethod,
				$controller,
				$fileType,
				$info
			),
			function ($carry, $extensionResult) {
				return array_merge(
					$carry ?: [],
					$extensionResult
				);
			},
			[]
		);
	}

}
