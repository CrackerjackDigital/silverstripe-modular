<?php
namespace Modular;

use Modular\Exceptions\Application as Exception;
use SSViewer;
use Director;

class Application extends Module {
	const ThemeMobile  = 'mobile';
	const ThemeDesktop = 'desktop';
	const ThemeDefault = 'default';

	// base dir for loading requirements from, if not set then theme folder will be used (e.g. themes/default/)
	private static $requirements_path;

	// map theme names to domains, these need to be in reverse specificity as config will append to the map so
	// most specific must be last so are checked first.
	private static $theme_domains = [
		self::ThemeDefault => ['*'],
		#	self::ThemeMobile => [ 'm.*' ],
	];

	private static $safe_paths = [
		ASSETS_PATH,
	    '../logs'
	];

	// use this
	private static $default_theme = self::ThemeDefault;

	/**
	 * Override to provide current theme folder if requirements_path not set.
	 *
	 * @return string
	 */
	public static function requirements_path() {
		return static::config()->get('requirements_path') ?: SSViewer::get_theme_folder();
	}

	/**
	 * Return the device mode, e.g.  'mobile', 'desktop', 'default'. At the moment just uses domain_theme.
	 * TODO: allow this to be found out in alternative ways, e.g. UserAgent
	 */
	public static function device_mode() {
		return static::domain_theme();
	}

	/**
	 * Check the path is inside the base folder or relative to base folder when safe paths are appended and the real path is resolved.
	 *
	 * e.g. if  config.allow_paths = [ '../logs' ]
	 *      and web root is /var/sites/website/htdocs
	 *
	 *      then
	 *          is_safe_path('/var/sites/website/logs') will return true
	 *      but
	 *          is_safe_path('/var/sites/website/conf') will return false
	 *
	 * by default the assets folder is always a safe path.
	 *
	 * @param string $pathWithoutFileName
	 * @param bool   $fail throw an exception if test fails
	 * @return bool
	 * @throws \Modular\Exceptions\Application
	 */
	public static function is_safe_path($pathWithoutFileName, $fail = false) {
		if (!$pathWithoutFileName)  {
			if ($fail) {
				throw new Exception("No path passed");
			}
			return false;
		}
		$baseFolder = rtrim(\Director::baseFolder(), PATH_SEPARATOR);
		$pathWithoutFileName = rtrim(realpath($pathWithoutFileName), PATH_SEPARATOR);

		// loop through each candidate path and append to the web root or use if absolute path to test against the passed path
		// paths are normalised to exclude trailing '/'
		foreach (static::config()->get('safe_paths') as $candidate) {
			$candidate = rtrim($candidate, PATH_SEPARATOR);

			if (realpath($candidate) == $candidate) {
				// if it's a real path then try that
				$test = rtrim($candidate, PATH_SEPARATOR);
			} else {
				// else treat as relative to base folder try that
				$test = rtrim(realpath($baseFolder . "/$candidate"), PATH_SEPARATOR);
			}
			// e.g. "/var/sites/website/logs"
			if (substr($pathWithoutFileName, 0, strlen($test)) == $test) {
				return true;
			}
		}
		if ($fail) {
			throw new Exception("Not a safe path: '$pathWithoutFileName");
		}
		return false;
	}

	/**
	 * Given a path relative to the assets folder e.g. 'uploads/images'
	 * or relative to base folder e.g. '../logs'
	 * or absolute from server root folder e.g. '/var/sites/website/htdocs/assets/logs/'
	 *
	 * return an absolute path with no '/' at end which is safe according to config.safe_paths or null if can't do it
	 * from provided parameters
	 *
	 * @param      $path
	 * @param bool $hasFileName strip filename first before making path
	 * @return null|string
	 */
	public static function make_safe_path($path, $hasFileName = true) {
		$baseFolder = rtrim(\Director::baseFolder(), PATH_SEPARATOR);
		$assetsFolder = rtrim(ASSETS_PATH, PATH_SEPARATOR);

		$path = rtrim(($hasFileName ? dirname($path) : $path), PATH_SEPARATOR);

		if (false !== strpos($path, '.')) {
			// any dots treat as relative to base folder
			$path = $baseFolder . "/$path";
		} elseif (substr($path, 0, 1) == '/') {
			// absolute from server root (but up one, e.g. may be '../logs' equivalent
			// TODO: this is not nice, seems arbitrary, fix
			$rpath = dirname(realpath($path));

			if (substr($rpath, 0, strlen(dirname($baseFolder))) == dirname($baseFolder)) {
				// yes, leave it
			} else {
				// no we are absolute from assets folder
				$path = $assetsFolder . "/$path";
			}
		} else {
			// relative to assets folder
			$path = $assetsFolder . "/$path";
		}
		$path = realpath($path);
		if (static::is_safe_path($path)) {
			return $path;
		}
		return null;
	}

	/**
	 * Return the theme name matching on domain name via config.theme_domains
	 *
	 * @return string
	 */
	public static function domain_theme() {
		$hostName = static::hostname();

		foreach (array_reverse(static::get_config_setting('theme_domains'), true) as $theme => $domains) {
			foreach ($domains as $pattern) {
				if (fnmatch($pattern, $hostName)) {
					return $theme;
				}
			}
		}
		return static::get_config_setting('default_theme');
	}

	/**
	 * Return the server host name from file to url mappings. For cli mode you'll need to make sure a FILE_TO_URL_MAPPING is
	 * setup in environment file for the server.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function hostname() {
		if (!$hostname = parse_url(\Director::protocolAndHost(), PHP_URL_HOST)) {
			throw new Exception("Can't determine hostname");
		}
		return $hostname;
	}
}