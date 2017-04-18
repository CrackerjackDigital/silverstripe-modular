<?php

namespace Modular;

use Modular\Controllers\Model as ModelController;
use Modular\Exceptions\Application as Exception;
use Modular\Extensions\Model\SiteConfig;
use Modular\Traits\reflection;
use Modular\Traits\requirements;
use SSViewer;

class Application extends Module {
	use reflection;
	// convenience constants for referencing string constants
	const SystemAdmin = 'SystemAdmin';
	const Admin       = 'Admin';

	// the name of the service expected by Injector e.g. in factory method
	const ServiceName  = 'Application';
	const ThemeMobile  = 'mobile';
	const ThemeDesktop = 'desktop';
	const ThemeDefault = 'default';
	// base dir for loading requirements from, if not set then theme folder will be used (e.g. themes/default/)
	private static $requirements_path;
	// map theme names to domains, these need to be in reverse specificity as config will append to the map so
	// most specific must be last so are checked first.
	private static $theme_domains = [
		self::ThemeDefault => [ '*' ],
		#	self::ThemeMobile => [ 'm.*' ],
	];
	private static $safe_paths = [];

	// use this
	private static $default_theme = self::ThemeDefault;

	// who to send errors, logs etc, SiteConfig will be tried with precedence
	private static $system_admin_email = '';

	// who to send administrative alerts, requests etc to, SiteConfig will be tried with precedence
	private static $admin_email = '';

	// field name of of the system admin email field e.g. on SiteConfig, change via config if some other field holds this info
	private static $system_admin_field_name = \Modular\Extensions\Model\SiteConfig::SystemAdminFieldName;

	// field name of of the admin email field e.g. on SiteConfig, change via config if some other field holds this info
	private static $admin_field_name = \Modular\Extensions\Model\SiteConfig::AdminFieldName;

	// set in ctor, used to track application startup/shutdown logging
	private $runID;
	// set in ctor, url requested for this application run
	private $url;

	public function __construct() {
		$this->runID = microtime();
		$this->url   = isset( $_REQUEST['url'] ) ? $_REQUEST['url'] : '[unknown url]';

		$this->debugger()->info( "START: $this->runID ($this->url)", get_called_class() );

		parent::__construct();
		static::start();
		static::register_modules();
		static::register_members_and_emails();
		static::register_model_controllers();
		static::register_paths();
	}

	public function __destruct() {
		static::end();
	}

	/** Log a 'Start' message
	 *
	 * @param string $message for log
	 */
	protected static function start( $message = 'START' ) {
		static::debug_trace( $message );
	}

	protected static function end( $message = 'END' ) {
		static::debug_trace( $message );
	}

	protected static function register_model_controllers() {
		$config      = \Config::inst();
		$controllers = ModelController::subclasses();
		/** @var string|Model $className */
		foreach ( $controllers as $className ) {
			$route = $className::route();
			static::debug_trace( "rule $route -> $className" );
			$config->update( 'Director', 'rules', [ "$route" => $className ] );
		}
	}

	protected static function register_paths() {
	}

	protected static function register_modules() {
	}

	public static function email( $for ) {
		return static::cache( "email-$for" );
	}

	public static function member( $for ) {
		return static::cache( "member-$for" );
	}

	protected static function register_members_and_emails() {
		static::cache(
			'member-' . static::system_admin_field_name(),
			static::cache(
				'email-' . self::SystemAdmin,
				static::find_system_admin()
			)
		);
		static::cache(
			'member-' . static::admin_field_name(),
			static::cache(
				'email-' . self::Admin,
				static::admin_email()
			)
		);
	}

	/**
	 * Try to find admin email address via extension call to provideEmail, otherwise try from
	 * this apps config.admin_email, Email.admin_email or Member.default_admin's Email. If all else fails
	 * returns the default_admin according to \Member.
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public static function find_system_admin() {
		// hardcoded from config or use admin as default
		$email = static::config()->get( 'system_admin_email' ) ?: static::admin_email();

		// try site config
		if ( $siteConfig = \SiteConfig::current_site_config() ) {
			$for = self::SystemAdmin;

			if ( $siteConfig->hasField( 'SystemAdminEmail' ) ) {

				$email = $siteConfig->SystemAdminEmail;
				static::debug_trace( "Found system admin email via site config: '$email'" );

			} elseif ( $options = $siteConfig->extend( 'provideEmail', $for ) ?: [] ) {

				$email = reset( $options );
				static::debug_trace( "Found system admin email via extension call: '$email'" );

			} else {
				static::debug_trace(
					"Site config should really have a '" . static::system_admin_field_name() . "' field, using '$email' from config instead"
				);
			}
		}

		return \Member::get()->filter( [ 'Email' => $email ] )->first() ?: \Member::default_admin();
	}

	/**
	 * Find a system admin and return the Email address.
	 *
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public static function system_admin_email() {
		/** @var \Member $sysAdmin */
		$sysAdmin = static::find_system_admin();

		return $sysAdmin ? $sysAdmin->Email : \Member::default_admin()->Email;
	}

	/**
	 * Try to find admin email address via extension call to provideEmail, otherwise try from
	 * this apps config.admin_email, Email.admin_email or Member.default_admin's Email.
	 *
	 * @return string
	 */
	public static function admin_email() {
		// default to configured options if not set in siteconfig
		$email = static::config()->get( 'admin_email' )
			?: \Email::config()->get( 'admin_email' )
				?: \Member::default_admin()->Email;

		if ( $siteConfig = \SiteConfig::current_site_config() ) {
			$for = static::admin_field_name();
			if ( $siteConfig->hasField( static::admin_field_name() ) && $siteConfig->{static::admin_field_name()} ) {
				$email = $siteConfig->{static::admin_field_name()};
				static::debug_trace( "Found system admin email via site config: '$email'" );
			} elseif ( $options = $siteConfig->extend( 'provideEmail', $for ) ?: [] ) {
				// use the first one returned
				$email = reset( $options );
				static::debug_trace( "Found system admin email via extension call: '$email'" );
			} else {
				static::debug_warn(
					"Site config should really have an 'AdminEmail' field, using '$email' from config instead"
				);
			}
		}

		return $email;
	}

	/**
	 * Returns the name used throughout the system (e.g. on SiteConfig) where the SystemAdminEmail is stored
	 *
	 * @return string
	 */
	public static function system_admin_field_name() {
		return static::config()->get( 'system_admin_field_name' );
	}

	/**
	 * Returns the name used throughout the system (e.g. on SiteConfig) where the AdminEmail is stored
	 *
	 * @return string
	 */
	public static function admin_field_name() {
		return static::config()->get( 'admin_field_name' );
	}

	/**
	 * Try to get page from Director and if in CMS then get it from CMS page, fallback to
	 * Controller url via page_for_path.
	 *
	 * @return \DataObject|\Page|\SiteTree
	 */
	public static function get_current_page() {
		$page = null;
		if ( \Director::is_ajax() ) {
			if ( $path = self::ajax_path_for_request( \Controller::curr()->getRequest() ) ) {
				$page = self::page_for_path( $path );
			}
		} else {
			if ( $page = \Director::get_current_page() ) {
				if ( $page instanceof \CMSMain ) {
					$page = $page->currentPage();
				}
			}
			if ( ! $page && $controller = Controller::curr() ) {
				if ( $controller = Controller::curr() ) {
					if ( $request = $controller->getRequest() ) {
						$page = Application::page_for_path( $request->getURL() );
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
		return static::config()->get( 'requirements_path' ) ?: SSViewer::get_theme_folder();
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
	 *
	 * @return mixed|string
	 */
	public static function ajax_path_for_request(
		$request = null, $requestVars = [
		'CMSMainCurrentPageID',
		'path',
		'url',
	]
	) {
		$request = $request ?: Controller::curr()->getRequest();
		foreach ( $requestVars as $varName ) {
			if ( $path = $request->requestVar( $varName ) ) {
				if ( is_numeric( $path ) ) {
					/** @var \SiteTree $page */
					if ( $page = \SiteTree::get()->byID( $path ) ) {
						$path = $page->Link();
					} else {
						continue;
					}
				}

				return $path;
			}
		}
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$path = parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_PATH );
		} else {
			$path = $request->getURL();
		}

		return $path;
	}

	/**
	 * Walk the site-tree to find a page given a nested path.
	 *
	 * @param $path
	 *
	 * @return \DataObject|\Page
	 */
	public static function page_for_path( $path ) {
		$path = trim( $path, '/' );
		if ( $path == '' ) {
			return \Page::get()->first();
		}
		/** @var \Page $page */
		$page     = null;
		$parts    = explode( '/', $path );
		$children = \Page::get()->filter( 'ParentID', 0 );
		while ( $segment = array_shift( $parts ) ) {
			if ( ! $page = $children->find( 'URLSegment', $segment ) ) {
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
		foreach ( array_reverse( static::get_config_setting( 'theme_domains' ), true ) as $theme => $domains ) {
			foreach ( $domains as $pattern ) {
				if ( fnmatch( $pattern, $hostName ) ) {
					return $theme;
				}
			}
		}

		return static::get_config_setting( 'default_theme' );
	}

	/**
	 * Return the server host name from file to url mappings. For cli mode you'll need to make sure a
	 * FILE_TO_URL_MAPPING is setup in environment file for the server.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function hostname() {
		if ( ! $hostname = parse_url( \Director::protocolAndHost(), PHP_URL_HOST ) ) {
			throw new Exception( "Can't determine hostname" );
		}

		return $hostname;
	}
}
