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
	 * Try to get page from Director and if in CMS then get it from CMS page, fallback to
	 * Controller url via page_for_path.
	 *
	 * @return \DataObject|\Page|\SiteTree
	 */
	public static function get_current_page() {
		$page = null;
		if (\Director::is_ajax()) {
			if ($path = self::path_for_request(\Controller::curr()->getRequest())) {
				$page = self::page_for_path($path);
			}
		} else {
			if ($page = \Director::get_current_page()) {
				if ($page instanceof \CMSMain) {
					$page = $page->currentPage();
				}
			}
			if (!$page && $controller = Controller::curr()) {
				if ($controller = Controller::curr()) {
					if ($request = $controller->getRequest()) {
						$page = Application::page_for_path($request->getURL());
					}
				}
			}
		}
		return $page;
	}

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
	 * Return a path from the request using a getVar or HTTP_REFERER or the request URL.
	 * @param \SS_HTTPRequest $request
	 * @param string          $getVar
	 * @return mixed|string
	 */
	public static function path_for_request($request = null, $getVar = 'path') {
		$request = $request ?: Controller::curr()->getRequest();

		if (!$path = $request->getVar($getVar)) {
			if (isset($_SERVER['HTTP_REFERER'])) {
				$path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
			} else {
				$path = $request->getURL();
			}
		}
		return $path;
	}

	/**
	 * Walk the site-tree to find a page given a nested path.
	 * @param $path
	 * @return \DataObject|\Page
	 */
	public static function page_for_path($path) {
		$path = trim($path, '/');

		if ($path == '') {
			return \HomePage::get()->first();
		}
		/** @var \Page $page */
		$page = null;

		$parts = explode('/', $path);
		$children = \Page::get()->filter('ParentID', 0);

		while ($segment = array_shift($parts)) {
			if (!$page = $children->find('URLSegment', $segment)) {
				break;
			}
			$children = $page->Children();
		}
		return $page;
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
	 * by default configuration the assets folder is always a safe path.
	 *
	 * @param string $path (no filename)
	 * @param bool   $fail throw an exception if test fails
	 * @param bool   $createIfNotExists create the directory if it doesn't exist (and is in assets folder only).
	 * @return bool|string path or false if not safe
	 * @throws \Modular\Exceptions\Application
	 */
	public static function make_safe_path($path, $fail = false, $createIfNotExists = true) {
		if ($fail && !$path) {
			throw new Exception("Empty path passed");
		}
		// output this path in errors before realpath etc
		$originalPath = $path;

		$basePath = rtrim(BASE_PATH, PATH_SEPARATOR);
		$assetsPath = rtrim(ASSETS_PATH, PATH_SEPARATOR);

		if (false !== strpos($path, '.')) {
			// any dots treat as relative to base folder, so could go up to '../logs' inside of parent of web root
			$path = "$basePath/$path";

		} elseif (substr($path, 0, 1) == '/') {
			// absolute from server root (not web root), but up one so e.g. '../logs' will work
			// TODO: this is not nice, seems arbitrary, fix
			$rpath = dirname(realpath($path));

			if (substr($rpath, 0, strlen(dirname($basePath))) != dirname($basePath)) {
				// we are absolute from assets folder
				$path = "$assetsPath/$path";
			}
		} else {
			// relative to assets folder
			$path = "$assetsPath/$path";
		}

		$safePaths = static::config()->get('safe_paths') ?: [];

		// rebuild path with parent 'realnamed' so we can at least be one path segment out ok (realpath fails if a dir doesn't exist)
		if ($parentPath = realpath(dirname($path))) {
			// parent exists so use that with the last bit of the
			$path = rtrim($parentPath, PATH_SEPARATOR) . '/' . basename($path);
		} else {
			// choose the first safe path
			$path = realpath($basePath . "/" . current($safePaths));
		}

		$found = false;

		// loop through each candidate path and append to the web root or use if absolute path to test against the passed path
		// paths are normalised to exclude trailing '/'
		foreach ($safePaths as $candidate) {
			$candidate = rtrim($candidate, PATH_SEPARATOR);

			if (realpath($candidate) == $candidate) {
				// if it's a real path then try that
				$test = rtrim($candidate, PATH_SEPARATOR);
			} else {
				// else treat as relative to base folder try that
				$test = rtrim(realpath($basePath . "/$candidate"), PATH_SEPARATOR);
			}
			// e.g. "/var/sites/website/logs"
			if (substr($path, 0, strlen($test)) == $test) {
				// it matches one of the registered config.safe_paths so break;
				$found = true;
				break;
			}
		}
		$path = rtrim(realpath($path), PATH_SEPARATOR);

		// create if requested and in assets folder
		if (!is_dir($path) && $createIfNotExists && (substr($path, 0, strlen(ASSETS_PATH)) == ASSETS_PATH)) {
			\Filesystem::makeFolder($path);
		}
		if ($fail && !($found && is_dir($path))) {
			throw new Exception("Not a safe path or path doesn't exist: '$originalPath");
		}
		return $found ? $path: false;
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
