<?php
namespace Modular\Traits;

use \Requirements as Requirement;
use \Modular\Module;

trait requirements {
	public function requirements($beforeOrAfterInit) {
		static::require_all($beforeOrAfterInit);
	}

	/**
	 * Includes requirements from Injector configured Application. If a requirement starts with '/' then
	 * it is included relative to site root, otherwise it is included relative to module root. Requirements
	 * can be defined as 'before' and 'after' in which case they will be included as per $beforeOrAfterInit
	 * parameter otherwise all requirements will be included when called.
	 *
	 * Will also walk to parent class first to get it's requirements so they are included in the correct order, if it
	 * is a
	 *
	 * e.g.
	 *
	 * private static $requirements = array(
	 *  '/framework/thirdpary/jquery/jquery.min.js': true,  // will come relative to site root
	 *  'js/modulescript.js': true                          // will load from module_path/js/
	 *  'css/modulecss.js': false                           // will not load as value is set to 'false' (but would load from module_path/css/)
	 * )
	 *
	 * or
	 *
	 * private static $requirements = array(
	 *  'block' => array( ... ),                //  requirements in here will be blocked
	 *  'before' => array( ... )',              //  these will be loaded onBeforeInit of extended controller
	 *  'after' => array( ... )'                //  these will be loaded onAfterInit of extended controller
	 * )
	 *
	 * @param string $beforeOrAfterInit whether to include before or after requirements, or both
	 * @param string $modulePath        path to load scripts, css etc from if not the theme
	 * @return $this
	 */
	public static function require_all($beforeOrAfterInit = Module::BothInit, $modulePath = '') {
		$ancestry = array_slice(\ClassInfo::ancestry(get_called_class()), 0, -1);
		foreach ($ancestry as $className) {
			if (is_a($className, 'Modular\Module', true)) {
				static::add_requirement($beforeOrAfterInit, $className);
			}
		}
		// finish off ourselves with possibly custom module path
		static::add_requirement($beforeOrAfterInit, self::class, $modulePath);
	}

	/**
	 * @param string $beforeOrAfterInit
	 * @param string $className
	 * @param string $modulePath
	 * @param string $includeDefault if single level requirements, not 'before' and 'after' then use this initialisation step to include them
	 *
	 * @return $this
	 */
	protected static function add_requirement($beforeOrAfterInit, $className, $modulePath = '', $includeDefault = Module::AfterInit) {
		// we want the config for the actual class instance we are in as we will be doing Config::UNINHERITED
		// to read from that level first.

		if ($requirements = array_filter(\Config::inst()->get($className, 'requirements', \Config::UNINHERITED) ?: [])) {
			$config = \Config::inst()->forClass($className);

			$modulePath = $modulePath
				?: $config->get('module_path', \Config::UNINHERITED)
					?: \SSViewer::get_theme_folder();

			if (is_array(current($requirements))) {
				// two level requirements, probably with 'before' => [] and 'after' => []
				if ( isset( $requirements[ $beforeOrAfterInit ] ) ) {
					// exclude any requirements which have a value of 'false' in config
					$requirements = array_filter( $requirements[ $beforeOrAfterInit ] );
				} else {
					// no 'before' or 'after' requirements
					$requirements = [];
				}
			} else {
				// single level requirements, we will do according to default
				if ($includeDefault == $beforeOrAfterInit) {
					$requirements = array_filter( $requirements );
				} else {
					$requirements = [];
				}
			}
			if ($requirements) {
				if ( ! is_int( key( $requirements ) ) ) {
					// filenames are the keys, swap so the values
					$requirements = array_keys( $requirements );
				}

				$basePathLen = strlen(BASE_PATH);

				foreach ( $requirements as $path ) {
					if ( stream_is_local( $path ) ) {
						if ( substr( $path, 0, 1 ) != '/' ) {
							// prepend module path as not 'absolute' path (relative to web root)
							$path = \Controller::join_links( $modulePath, $path );
						}
						foreach (glob(BASE_PATH . '/' . $path) as $file) {
							static::require_file( substr($file, $basePathLen));
						}
					} else {
						static::require_url( $path );
					}
				}
			}
		}
	}

	/**
	 * Iterate through 'block' key in config.requirements and block each script.
	 *
	 * @param array $block map of paths to block.
	 *
	 * @return array of blocked scripts as ['js' => 'path'] type entries
	 */
	public static function block( array $block ) {
		if ( $block ) {
			$blocked = $scriptTypes = static::include_script_types();

			$required = array_merge(
				$scriptTypes,
				[
					self::FileTypeCSS                => \Requirements::backend()->get_css(),
					self::FileTypeJavascript         => \Requirements::backend()->get_javascript(),
					self::FileTypeJavascriptTemplate => \Requirements::backend()->get_custom_scripts(),
				]
			);

			foreach ( $scriptTypes as $fileType => $_ ) {
				foreach ( $block as $path ) {

					$path = static::requirement_path( $path );

					foreach ( $required[ $fileType ] as $require ) {

						if ( fnmatch( $path, $require ) ) {

							$blocked[ $fileType ][] = $require;

							\Requirements::block($required);
						}
					}
				}
			}

			return $blocked;
		}

		return [];
	}

	/**
	 * Add file to SS requirements depending on extension (.js or other atm).
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public static function require_file( $path) {
		static $required = [];

		if ($path = ltrim($path, '/')) {

			if ( ! isset( $required[ $path ] ) ) {
				$required[ $path ] = true;

				if ( substr( $path, - 3 ) == '.js' ) {
					Requirement::javascript( $path );
				} else {
					Requirement::css( $path );
				}
			}
		}

		return $path;
	}

	public static function require_url($url) {
		$extension = strtolower(pathinfo(parse_url( $url, PHP_URL_PATH), PATHINFO_EXTENSION));
		if ($extension == 'css') {
			\Requirements::insertHeadTags( '<link rel="stylesheet" href="' . $url . '"/>');
		} elseif ($extension == 'js') {
			\Requirements::insertHeadTags( '<script type="text/javascript" src="' . $url . '"></script>' );
/*
			\Requirements::customScript(<<<JS
				(function() {
					var script = document.createElement('script');
					script.type = 'text/javascript';
					script.src = '$url';
					document.getElementsByTagName('body')[0].appendChild(script);
				})();
JS;
*/
		}
	}
}
