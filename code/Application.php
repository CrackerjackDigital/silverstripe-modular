<?php
namespace Modular;

use Modular\Exceptions\Application as Exception;
use SSViewer;
use Director;

class Application extends Module {
	use reflection;
	use requirements;

	// the name of the service expected by Injector e.g. in factory method
	const ServiceName = 'Application';

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

	private static $safe_paths = [];

	// use this
	private static $default_theme = self::ThemeDefault;

	// who to send errors, logs etc
	private static $system_admin_email = '';

	// who to send administrative alerts, requests etc to
	private static $admin_email = '';

	/**
	 * Return an instance of Application as registered with Injector or the called class.
	 *
	 * @return Application
	 */
	public static function factory() {
		$injector = \Injector::inst();

		if ($injector->hasService(static::ServiceName)) {
			$application = $injector::inst()->get(static::ServiceName, true, func_get_args());
		} else {
			$application = $injector::inst()->get(get_called_class(), true, func_get_args());
		}
		return $application;
	}

	/**
	 * Returns member configured via system_admin_email() or admin_email() if no system_admin_email
	 * @return \DataObject
	 */
	public static function system_admin() {
		return \Member::get()->filter('Email', static::system_admin_email())->first();
	}

	/**
	 * Returns an email address from current SiteConfig or the system default_admin's email address.
	 * Tries first result of provideSystemAdminEmail on current SiteConfig,
	 * as well as fields 'SystemAdminEmail' and 'AdminEmail' on SiteConfig.
	 *
	 * @return string
	 */
	public static function system_admin_email() {
		$email = static::config()->get('system_admin_email') ?: static::admin_email();

		// try site config
		if ($siteConfig = \SiteConfig::current_site_config()) {
			$options = $siteConfig->extend('provideSystemAdminEmail') ?: [];
			if ($options) {

				$email = reset($options);

			} else if ($siteConfig->hasField('SystemAdminEmail')) {

				$email = $siteConfig->SystemAdminEmail;

			} else if ($siteConfig->hasField('AdminEmail')) {

				$email = $siteConfig->AdminEmail;

			} else {
				static::debug_warn("Site config should really have an 'AdminEmail' field");
			}
		}
		return $email;
	}

	/**
	 * Return admin Member found via the admin_email.
	 * @return \Member
	 */
	public static function admin_member() {
		return \Member::get()->filter('Email', static::$admin_email)->first();
	}

	/**
	 * Try and find admin email address from this apps config, Email.admin_email or default_admins Email.
	 * This should always return something, however config.admin_email should really be set on Application.
	 *
	 * @return string
	 */
	public static function admin_email() {
		return static::config()->get('admin_email')
			?: \Email::config()->get('admin_email')
				?: \Member::default_admin()->Email;
	}

	/**
	 * Try to get page from Director and if in CMS then get it from CMS page, fallback to
	 * Controller url via page_for_path.
	 *
	 * @return \DataObject|\Page|\SiteTree
	 */
	public static function get_current_page() {
		$page = null;
		if (\Director::is_ajax()) {
			if ($path = self::ajax_path_for_request(\Controller::curr()->getRequest())) {
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
	 *
	 * @param \SS_HTTPRequest $request
	 * @param array           $requestVars check these get vars looking for a path
	 * @return mixed|string
	 */
	public static function ajax_path_for_request($request = null, $requestVars = ['CMSMainCurrentPageID', 'path', 'url']) {
		$request = $request ?: Controller::curr()->getRequest();

		foreach ($requestVars as $varName) {
			if ($path = $request->requestVar($varName)) {
				if (is_numeric($path)) {
					/** @var \SiteTree $page */
					if ($page = \SiteTree::get()->byID($path)) {
						$path = $page->Link();
					} else {
						continue;
					}
				}
				return $path;
			}
		}
		if (isset($_SERVER['HTTP_REFERER'])) {
			$path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
		} else {
			$path = $request->getURL();
		}
		return $path;
	}

	/**
	 * Walk the site-tree to find a page given a nested path.
	 *
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
