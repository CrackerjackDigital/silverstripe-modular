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
