<?php
namespace Modular\GridList;

use Modular\Application;
use Modular\Exceptions\Exception;
use Modular\Object;
use Modular\Fields\ModelTag;
use Modular\Models\GridListFilter;

/**
 * Filters limit what models are displayed on page depending on user selection, they can further restrict models after Constraints are applied.
 *
 * @package Modular\GridList
 */
class Constraints extends Object {
	const ModeGetVar       = 'mode';
	const SortGetVar       = 'sort';
	const StartIndexGetVar = 'start';
	const PageLengthGetVar = 'limit';
	const FilterVar        = 'filter';

	// differentiate the constraint persistance sessions vars somehow if required.
	const SessionKeyPrefix = '';
	// session key is built with segments, mainly for debugging, seperate them as a string with this
	const SessionPathSeparator = ':';

	// the following constants govern how the current filters etc are saved across
	// multiple accesses to search.
	// don't save any incoming values to session at all
	const PersistNever = 0;
	// save for all pages
	const PersistEverywhere = 1;
	// save into session including url
	const PersistForURLParams = 2;
	// save into session including query string get vars
	const PersistForGetVars = 4;
	// save both url and query string
	const PersistExact = 7;

	// just don't persist by default
	const DefaultPersistance = self::PersistNever;

	/** @var array of parameters this filter deals with, add more in derived classes to handle them */
	private static $params = [
		self::ModeGetVar,
		self::SortGetVar,
		self::StartIndexGetVar,
		self::PageLengthGetVar,
		self::FilterVar
	];

	/** @var \NullHTTPRequest|\SS_HTTPRequest */
	protected $request;

	private static $default_mode = 'grid';

	private static $default_sort = 'a-z';

	public function __construct() {
		parent::__construct();
		$this->request = \Controller::curr()->getRequest();
	}

	public function constraint($name, $sessionPersistence = null) {
		$sessionPersistence = is_null($sessionPersistence) ? static::DefaultPersistance : $sessionPersistence;
		return $this->getVarOrParam($name, $sessionPersistence);
	}

	/**
	 * Default filter is just a field = value
	 *
	 * @param $className
	 * @param $term
	 * @param $field
	 * @return array
	 */
	public function filter($className, $term, $field) {
		return [
			"$className.$field" => $term,
		];
	}

	/**
	 * Return the ID of the current filter.
	 *
	 * @return int|null
	 */
	public function currentFilterID() {
		if ($filterTag = $this->constraint(static::FilterVar)) {
			if ($filter = GridListFilter::get()->filter(ModelTag::SingleFieldName, $filterTag)->first()) {
				return $filter->ID;
			}
		}
	}

	public function defaultFilter() {
		if ($page = Application::get_current_page()) {
			if ($page->hasMethod('DefaultFilter')) {
				return $page->DefaultFilter();
			}
		}
	}

	public function getVarOrParam($name, $sessionPersistance = self::PersistExact) {
		return $this->getVar($name, $sessionPersistance) ?: $this->urlParam($name, $sessionPersistance);
	}

	public function mode() {
		return current($this->modes());
	}

	/**
	 * Return array of mode strings in preference order from query string or configuration.
	 *
	 * @return mixed
	 */
	public function modes() {
		$options = [
			$this->getVar(static::ModeGetVar, self::PersistExact),
			\Director::get_current_page()->config()->get('gridlist_default_mode'),
			$this->config()->get('default_mode'),
		];
		return array_filter($options);
	}

	public function sort() {
		return $this->getVar(static::SortGetVar, self::PersistExact) ?: $this->config()->get('default_sort');
	}

	/**
	 * Return the pagination start from getVar or session
	 *
	 * @return int|null
	 */
	public function start() {
		return $this->getVar(self::StartIndexGetVar, self::PersistExact);
	}

	/**
	 * Return the pagination limit from getVar or session
	 *
	 * @return int|null
	 */
	public function limit() {
		return $this->getVar(self::PageLengthGetVar, self::PersistExact);
	}

	/**
	 * @return array of start, limit from getVar or from Session for current url
	 */
	public function pagination() {
		return [
			'start' => $this->start(),
			'limit' => $this->limit(),
		];
	}

	/**
	 * @param array $params
	 * @return string suitable for use in url query string, keys are lower cased
	 */
	protected function buildQueryString($params = []) {
		return http_build_query(
			array_change_key_case(
				$this->params($params),
				CASE_LOWER
			)
		);
	}

	/**
	 * @return \SS_HTTPRequest
	 */
	protected function request() {
		return $this->request;
	}

	/**
	 * Returns map of parameters (getVars and urlParams) this Filters derived class handles. Merges in
	 * with preference passed params if provided.
	 */
	protected function params($params = []) {
		return array_merge(
			$this->config()->get('params') ?: [],
			$params
		);
	}

	/**
	 * Provide a unique key for this request, e.g. for caching, UI state storage etc. By default this is without taking getVars into account.
	 *
	 * @param string      $key type of thing being cached, e.g. 'sort' or 'model'
	 * @param string|null $url of request to store key for, if null current url is used, otherwise whatever is passed (could be '' for all requests)
	 * @param bool        $includeGetVars
	 * @return string
	 */
	protected function persistKey($key, $url = null, $includeGetVars = false) {
		$url = is_null($url)
			? $this->url()
			: $url;

		if ($includeGetVars) {
			$url .= '?' . http_build_query($this->getVars());
		}
		return static::persist_key_prefix() . $key . static::SessionPathSeparator . md5(strtolower($url));
	}

	public static function persist_key_prefix() {
		return strtoupper(static::SessionKeyPrefix ?: basename(get_called_class())) . static::SessionPathSeparator;
	}

	/**
	 * Clear all session variables that start with the gridlist session key prefix (self.KeyPrefix).
	 */
	protected function clearSession() {
		$len = strlen(self::SessionKeyPrefix);
		foreach (\Session::get_all() as $name => $value) {
			if (substr($name, 0, $len) == self::SessionKeyPrefix) {
				\Session::clear($name);
			}
		}
		\Session::save();
	}

	protected function url($includeGetVars = false) {
		return $this->request()->getURL($includeGetVars);
	}

	/**
	 * Return value of particular url parameter, filters out one's we're not interested in and caches those we are.
	 *
	 * @param $name
	 * @return mixed
	 */
	protected function urlParam($name, $sessionPersistance) {
		static $cached = [];
		if (!array_key_exists($name, $cached)) {
			if (array_key_exists($name, $this->urlParams())) {
				$cached[ $name ] = $this->urlParams()[ $name ];
			}
		}
		return $this->persisted($name, $cached, $sessionPersistance);
	}

	/**
	 * Return only the url parameters we are interested in
	 *
	 * @return array
	 */
	protected function urlParams() {
		$cached = array_filter(
			array_intersect_key(
				$this->request()->allParams(),
				array_flip($this->params())
			)
		);
		return $cached;
	}

	/**
	 * Return a get var if it is one we are interested in, lookup is lowercased, return value is urldecoded.
	 * Values are cached for both lower and uppercase (so get and urlParam) options.
	 *
	 * @param $name
	 * @return null|string
	 */
	protected function getVar($name, $sessionPersistance) {
		static $cached = [];
		$name = strtolower($name);

		if (!array_key_exists($name, $cached)) {
			$getVars = $this->getVars();
			if (array_key_exists($name, $getVars)) {
				$cached[ $name ] = $getVars[ $name ];
			}
		}
		return $this->persisted($name, $cached, $sessionPersistance);
	}

	/**
	 * Return only the get vars we are interested in, keys are lowercased, values are trimmed
	 *
	 * @return array
	 */
	protected function getVars() {
		$cached = array_map(
			'trim',
			array_intersect_key(
				array_change_key_case(
					$this->request()->getVars(),
					CASE_LOWER
				),
				array_change_key_case(
					array_flip($this->params()),
					CASE_LOWER
				)
			)
		);
		return $cached;
	}

	/**
	 * Return a get var if it is one we are interested in, lookup is lowercased, return value is urldecoded.
	 * Values are cached for both lower and uppercase (so get and urlParam) options.
	 *
	 * @param $name
	 * @return null|string
	 */
	protected function postVar($name) {
		static $cached = [];
		$name = strtolower($name);
		if (!array_key_exists($name, $cached)) {
			$postVars = $this->postVars();
			if (array_key_exists($name, $postVars)) {
				$cached[ $name ] = $postVars[ $name ];
			}
		}
		return isset($cached[ $name ]) ? urldecode($cached[ $name ]) : null;
	}

	/**
	 * Return only the get vars we are interested in, keys are lowercased
	 *
	 * @return array
	 */
	protected function postVars() {
		static $cached;

		if (!$cached) {
			$cached = array_map(
				'trim',
				array_intersect_key(
					array_change_key_case(
						$this->request()->postVars(),
						CASE_LOWER
					),
					array_change_key_case(
						array_flip($this->params()),
						CASE_LOWER
					)
				)
			);
		}
		return $cached;
	}

	/**
	 * Lookup name in the provided cache key-value and save it to the session if required depending on seesionPersistance parameter.
	 *
	 * @param $name
	 * @param $cache
	 * @param $sessionPersistance
	 * @return array|mixed|null|\Session
	 * @throws Exception
	 */
	protected function persisted($name, $cache, $sessionPersistance) {
		$key = null;
		$value = null;

		if (array_key_exists($name, $cache)) {

			$value = $cache[ $name ];

			if ($sessionPersistance) {

				if (self::PersistExact === ($sessionPersistance & self::PersistExact)) {
					// key is for urlParams and getVars (so page and query string)
					$key = $this->persistKey($name, $this->url(), true);

				} elseif (self::PersistForURLParams === ($sessionPersistance & self::PersistForURLParams)) {
					// key is for urlParams only, not getVars (so page path only)
					$key = $this->persistKey($name, $this->url(), false);

				} elseif (self::PersistForGetVars === ($sessionPersistance & self::PersistForGetVars)) {
					// key is for getVars only, not urlParams (so query string only)
					$key = $this->persistKey($name, '', true);

				} else {
					throw new Exception("Unknown persistance mode: $sessionPersistance");
				}
				\Session::set($key, $value);
			}
		} else {
			// TODO if not in the cache then should we do anything?
		}
		return $value;
	}
}