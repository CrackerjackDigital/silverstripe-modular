<?php
namespace Modular\GridList;

use Modular\Object;

abstract class Constraints extends Object {
	const ModeGetVar       = 'mode';
	const SortGetVar       = 'sort';
	const StartIndexGetVar = 'start';
	const PageLengthGetVar = 'limit';
	const SessionKeyPrefix = '';
	// don't save to session at all
	const SessionNone = 0;
	// save for all pages
	const SessionSaveGlobal = 1;
	// save into session including url
	const SessionSaveURLParams = 2;
	// save into session including query string get vars
	const SessionSaveGetVars = 4;
	// save both url and query string
	const SessionSaveAll = 7;

	/** @var \NullHTTPRequest|\SS_HTTPRequest */
	protected $request;

	private static $params = [
		self::ModeGetVar,
		self::SortGetVar,
		self::StartIndexGetVar,
		self::PageLengthGetVar,
	];

	public function __construct() {
		parent::__construct();
		$this->request = \Controller::curr()->getRequest();
	}

	public function constraint($name, $sessionPersistance = self::SessionSaveAll) {
		return $this->getVar($name, $sessionPersistance) ?: $this->urlParam($name, $sessionPersistance);
	}

	public function mode() {
		return $this->getVar(static::ModeGetVar, self::SessionSaveAll);
	}

	public function sort() {
		return $this->getVar(static::SortGetVar, self::SessionSaveAll);
	}

	/**
	 * Return the pagination start from getVar or session
	 *
	 * @return int|null
	 */
	public function start() {
		return $this->getVar(self::StartIndexGetVar, self::SessionSaveAll);
	}

	/**
	 * Return the pagination limit from getVar or session
	 *
	 * @return int|null
	 */
	public function limit() {
		return $this->getVar(self::PageLengthGetVar, self::SessionSaveAll);
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
		return $this->request();
	}

	/**
	 * Returns map of parameters (getVars and urlParams) this Constraints derived class handles. Merges in
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
		return strtoupper(static::SessionKeyPrefix . ":$key:") . md5(strtolower($url));
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
		return $this->persist($name, $cached, $sessionPersistance);
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
		return $this->persist($name, $cached, $sessionPersistance);
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

	protected function persist($name, $cache, $sessionPersistance) {
		$key = null;
		$value = null;

		if (array_key_exists($name, $cache)) {

			$value = $cache[ $name ];

		} elseif ($sessionPersistance) {

			if (self::SessionSaveAll === ($sessionPersistance & self::SessionSaveAll)) {

				$key = $this->key($name, $this->url(), true);

			} elseif (self::SessionSaveURLParams === ($sessionPersistance & self::SessionSaveURLParams)) {

				$key = $this->key($name, $this->url(), false);

			} elseif (self::SessionSaveGetVars === ($sessionPersistance & self::SessionSaveGetVars)) {

				$key = $this->key($name, '', true);

			}
			$value = \Session::get($key);
		}
		if ($key) {
			\Session::set($key, $value);
		}
		return $value;
	}
}