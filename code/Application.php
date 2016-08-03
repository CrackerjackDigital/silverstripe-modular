<?php
namespace Modular;

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
	 * Return the server host name. For cli mode you'll need to make sure a FILE_TO_URL_MAPPING is
	 * setup in environment file for the server.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function hostname() {
		global $_FILE_TO_URL_MAPPING;

		if (Director::is_cli()) {
			$root = Director::baseFolder();

			if (!isset($_FILE_TO_URL_MAPPING[ $root ])) {
				throw new Exception("Please setup a FILE_TO_URL_MAPPING for '$root'");
			}

			$hostname = parse_url($_FILE_TO_URL_MAPPING[ $root ], PHP_URL_HOST);
		} else {
			$hostname = $_SERVER['HTTP_HOST'];
		}
		if (!$hostname) {
			throw new Exception("Can't determine hostname");
		}
		return $hostname;
	}
}