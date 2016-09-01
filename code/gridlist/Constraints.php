<?php
namespace Modular\GridList;

use Modular\Object;
use Modular\Exception;

/**
 * Constraints limit what models are displayed on page depending on user selection or by context specific rules.
 *
 * @package Modular\GridList
 */
class Constraints extends Object {
	const ModeGetVar       = 'mode';
	const SortGetVar       = 'sort';
	const StartIndexGetVar = 'start';
	const PageLengthGetVar = 'limit';
	// this could be hardwired instead of using generated value
	const SessionKeyPrefix = '';
	// session key is broken into tokens, mainly for debugging
	const SessionPathSeparator = ':';
	// don't save any incoming values to session at all
	const PersistNone = 0;
	// save for all pages
	const PersistGlobal = 1;
	// save into session including url
	const PersistPath = 2;
	// save into session including query string get vars
	const PersistQuery = 4;
	// save both url and query string
	const PersistPathAndQuery = 7;

	// seperate multiple values passed as a single query string parameter with this
	const MultiValueVarSeparator = '|';

	const PersistDefault = self::PersistPath;

	/** @var \NullHTTPRequest|\SS_HTTPRequest */
	protected $request;

	private static $params = [
		self::ModeGetVar,
		self::SortGetVar,
		self::StartIndexGetVar,
		self::PageLengthGetVar,
	];

	private static $default_mode = 'grid';

	private static $default_sort = 'a-z';

	public function __construct() {
		parent::__construct();
		$this->request = \Controller::curr()->getRequest();
	}

	public function constraint($name, $sessionPersistance = self::PersistDefault) {
		return $this->getVar($name, $sessionPersistance) ?: $this->urlParam($name, $sessionPersistance);
	}

	public function mode() {
		return $this->getVar(static::ModeGetVar, self::PersistPathAndQuery) ?: $this->config()->get('default_mode');
	}

	public function sort() {
		return $this->getVar(static::SortGetVar, self::PersistPathAndQuery) ?: $this->config()->get('default_sort');
	}

	/**
	 * Return the pagination start from getVar or session
	 *
	 * @return int|null
	 */
	public function start() {
		return $this->getVar(self::StartIndexGetVar, self::PersistPathAndQuery);
	}

	/**
	 * Return the pagination limit from getVar or session
	 *
	 * @return int|null
	 */
	public function limit() {
		return $this->getVar(self::PageLengthGetVar, self::PersistPathAndQuery);
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
	protected function key($key, $url = null, $includeGetVars = false) {
		$url = is_null($url)
			? $this->url()
			: $url;

		if ($includeGetVars) {
			$url .= '?' . http_build_query($this->getVars());
		}
		return join(static::SessionPathSeparator, [
			static::session_key_prefix(),
			$key,
			md5(strtolower($url)
			);
	}

	public function session_key_prefix() {
		return strtoupper(static::SessionKeyPrefix ?: basename(get_called_class()));
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
	 * @param                $name
	 * @param                $sessionPersistance
	 * @param string|boolean $multiValue attempt to split the returned value using this, set to false to leave whole
	 * @return null|string
	 * @throws \Modular\Exceptions\Exception
	 */
	protected function getVar($name, $sessionPersistance, $multiValue = self::MultiValueVarSeparator) {
		static $cached = [];
		$name = strtolower($name);

		if (!array_key_exists($name, $cached)) {
			$getVars = $this->getVars();
			if (array_key_exists($name, $getVars)) {
				$cached[ $name ] = $getVars[ $name ];
			}
		}
		$value = $this->persisted($name, $cached, $sessionPersistance);
		return ($multiValue === false)
			? explode($multiValue, $value)
			: $value;
	}

	/**
	 * Return only the get vars we are interested in, keys are lowercased
	 *
	 * @return array
	 */
	protected function getVars() {
		$cached = array_intersect_key(
			array_change_key_case(
				$this->request()->getVars(),
				CASE_LOWER
			),
			array_change_key_case(
				array_flip($this->params()),
				CASE_LOWER
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
			$cached = array_intersect_key(
				array_change_key_case(
					$this->request()->postVars(),
					CASE_LOWER
				),
				array_change_key_case(
					array_flip($this->params()),
					CASE_LOWER
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

				if (self::PersistPathAndQuery === ($sessionPersistance & self::PersistPathAndQuery)) {
					// key is for urlParams and getVars (so page and query string)
					$key = $this->key($name, $this->url(), true);

				} elseif (self::PersistPath === ($sessionPersistance & self::PersistPath)) {
					// key is for urlParams only, not getVars (so page path only)
					$key = $this->key($name, $this->url(), false);

				} elseif (self::PersistQuery === ($sessionPersistance & self::PersistQuery)) {
					// key is for getVars only, not urlParams (so query string only)
					$key = $this->key($name, '', true);

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