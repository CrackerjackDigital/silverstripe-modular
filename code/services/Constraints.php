<?php

namespace Modular\GridList;

use Modular\Object;
use SS_HTTPRequest;
use Session;

class Constraints extends Object {

	const ReferrerHeader   = 'Referer';
	const KeyPrefix        = '';
	const BackToResultsKey = '';

	const ClearGetVar      = 'clr';
	const SortGetVar       = 'sort';
	const ModelsGetVar     = 'model';
	const StartIndexGetVar = 'start';
	const PageLengthGetVar = 'limit';

	const ModelsParam = 'Model';
	const ModeParam = 'Mode';
	const IDParam   = 'ID';

	/**
	 * Map and cache these request veriables for use by the service, parameters
	 * and getVars not listed here but passed will be ignored. Headers do
	 * not need to be listed here.
	 *
	 * @var array
	 */
	private static $params = [
		self::ModelsParam,
		self::IDParam,
		self::ModeParam,
		self::SortGetVar,
		self::StartIndexGetVar,
		self::PageLengthGetVar,
		self::ClearGetVar,
	];

	/** @var \SS_HTTPRequest */
	protected $request;

	/**
	 * Decode the request into fields that the GridList service can use. If the self.ClearGetVar
	 * parameter is passed in the query string then existing filters will be cleared from session and
	 * only new 'incoming' filters will subsequently apply.
	 *
	 * @param \SS_HTTPRequest $request
	 */
	public function __construct(SS_HTTPRequest $request = null) {
		parent::__construct();
		$this->request = $request;
		if ($this->request->getVar(static::ClearGetVar)) {
			$this->clearSession();
		}
	}

	/**
	 * Set the session variable passed as $param into the session if it is a valid one.
	 *
	 * @param $param
	 */
	public function setFromRequest($param) {
		if ($value = $this->getVar($param)) {
			Session::set($this->key($param, $this->referrer()), $value);
		}
	}

	/**
	 * Returns a link as requested /$Category/$Type verbatim, no Aspect!
	 * from incoming request, does no validation to make sure they exist.
	 *
	 * @return String link such as 'built/records'
	 */
	public function verbatimLink() {
		return $this->request()->getURL();
	}

	/**
	 * @param array $params
	 * @return string suitable for use in url query string, keys are lower cased
	 */
	public function buildQueryString($params = []) {
		return http_build_query(
			array_change_key_case(
				array_merge(
					[
						self::SortGetVar  => current($this->sorts()),
						self::ModelsParam => current($this->models()),
					],
					$params
				),
				CASE_LOWER
			)
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
	public function key($key, $url = null, $includeGetVars = false) {
		$url = is_null($url)
			? $this->verbatimLink()
			: $url;

		if ($includeGetVars) {
			$url .= '?' . http_build_query($this->getVars());
		}
		return strtoupper(static::KeyPrefix . ":$key:") . md5(strtolower($url));
	}

	/**
	 * Clear all session variables that start with the gridlist session key prefix (self.KeyPrefix).
	 */
	public function clearSession() {
		$len = strlen(static::KeyPrefix);
		foreach (Session::get_all() as $name => $value) {
			if (substr($name, 0, $len) == static::KeyPrefix) {
				Session::clear($name);
			}
		}
		Session::save();
	}

	/**
	 * Return the request being used to provide decoded parameters/variables.
	 *
	 * @return \SS_HTTPRequest
	 */
	public function request() {
		return $this->request;
	}

	/**
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}

	/**
	 * Returns previously stashed backurl (see saveBackURL);
	 *
	 * @return string
	 */
	public function backURL() {
		return Session::get(static::BackToSearchKey);
	}

	/**
	 * At and of processing (e.g. onAfterInit) call this to stash current url in backurl. Since
	 * only pages with a gridlist should call this, then it should always be available to go
	 * back to the last valid search/gridlist page.
	 */
	public function saveBackURL($link) {
		Session::set(static::BackToSearchKey, $link);
	}

	/**
	 * Return the url excluding scheme, host and query string so = request->getURL() for referrer not he
	 * actual request itself.
	 *
	 * @return string
	 */
	public function referrer() {
		$path = parse_url($this->request()->getHeader(static::ReferrerHeader), PHP_URL_PATH);
		$query = parse_url($this->request()->getHeader(static::ReferrerHeader), PHP_URL_QUERY);
		$fragment = parse_url($this->request()->getHeader(static::ReferrerHeader), PHP_URL_FRAGMENT);

		return $path
		. ($query ? "?$query" : '')
		. ($fragment ? "#$fragment" : '');
	}

	/**
	 * Return a list of ids for the passed parameter from the request or from the session.
	 *
	 * @param string $param name of getVar
	 * @param bool   $useSession
	 * @return array
	 */
	public function filterIDList($param, $useSession = true) {
		$stringValue = $this->getVar($param);
		if (is_null($stringValue)) {
			$stringValue = Session::get($this->key($param));
		} elseif ($useSession) {
			Session::set($this->key($param), $stringValue);
		}
		return array_filter(explode(static::ParamListSeperator, $stringValue));
	}

	/**
	 * Returns a link to this 'page' with provided, session or default filters. NB this will
	 * include the ID if a model is being displayed.
	 *
	 * @param array $filters as key => value map
	 * @return string
	 */
	public function currentLink($filters = []) {
		$link = $this->verbatimLink();
		return $link . '?' . $this->buildQueryString($filters);
	}

	/**
	 * /**
	 * Returns a link to this 'page' with provided, session or default filters, excluding any
	 * current record ID.
	 *
	 * @param array $filters as key => value map
	 * @return string
	 */
	public function filterLink($filters = []) {
		$link = $this->verbatimLink();
		if ($this->articleID()) {
			$link = dirname($link) . '/';
			if ($this->urlParam(static::ModelsParam)) {
				$link = dirname($link) . '/';
			}
		}
		return $link . '?' . $this->buildQueryString($filters);
	}

	/**
	 * Return an array of TaxonomyTerm id's for use in filtering. Tries:
	 * - the category getVar
	 * - children of the aspect getVar
	 * - the Category urlParam
	 * - children of the Aspect urlParam
	 *
	 * @return array
	 */
	public function categoryIDs() {
		$ids = [];

		// children of the aspect getVar if no category
		if ($aspects = $this->getVar(self::AspectsParam)) {
			$ids = TaxonomyTerm::get()->filter(
				'ParentID',
				explode(self::ParamListSeperator, $aspects)
			)->column('ID');
		} elseif ($categoryID = $this->taxonomyIDFromURLParam(self::CategoriesParam)) {
			$ids = [$categoryID];
		} elseif ($aspectID = $this->taxonomyIDFromURLParam(self::AspectsParam)) {
			$ids = TaxonomyTerm::get()->filter('ParentID', $aspectID)->column('ID');
		}
		return $ids;
	}

	/**
	 * Category ids explicitly passed on the query string.
	 *
	 * @return array
	 */
	public function secondaryCategoryIDs() {
		return $this->filterIDList(self::FilterCategoryGetVar);
	}

	/**
	 * Returns list of request 'mode' flags, e.g. 'grid', 'list' etc
	 *
	 * @return mixed|null
	 */
	public function modes() {
		return $this->filterIDList(self::ModeParam);
	}

	/**
	 * Return sort orders from the sort getVar or the session, this will be e.g. ['a-z', 'l-e']
	 *
	 * @return array
	 */
	public function sorts() {
		return $this->filterIDList(self::SortGetVar);
	}

	/**
	 * Return the current model type (record, resource) from the URL parameter. Using
	 * config.model_map the first matching one is found and returned, so e.g. 'rec' will
	 * map through to a return of 'record' which is more usefull in the template for
	 * e.g. display state etc.
	 *
	 * @return string e.g. 'record', 'resource'
	 */
	public function currentModel() {
		if (!$model = $this->getVar(self::ModelsParam)) {
			if (!$model = $this->urlParam(self::ModelsParam)) {
				$model = Session::get($this->key(self::ModelsParam));
			}
		}
		Session::set($this->key(self::ModelsParam), $model);
		return current(array_filter(explode(self::ParamListSeperator, $model)));
	}

	/**
	 * Return the type for the url (e.g. 'record', 'resource' from the passed model/class name)
	 *
	 * @param $modelOrClassName
	 * @return mixed
	 */
	public function mapModelToURLType($modelOrClassName) {
		if ($modelOrClassName instanceof ArticleModel) {
			$modelOrClassName = $modelOrClassName->ClassName;
		}
		return array_search(
			$modelOrClassName,
			$this->config()->get('model_map')
		);
	}

	/**
	 * Return the model ClassName e.g. 'RecordArticle' from a passed variable, e.g. 'record' or 'res'
	 * or empty string if not found
	 * not
	 *
	 * @param $urlValue
	 * @return string
	 */
	public function mapFromURLToModelClass($urlValue) {
		$map = $this->config()->get('model_map');
		return isset($map[ $urlValue ])
			? $map[ $urlValue ]
			: '';
	}

	/**
	 * Returns the requested numeric Article ID. Checks to see if an article exists
	 * either by numeric ID or URLSegment before returning the ID, and always returns a numeric ID. Does
	 * not apply any other filtering/checks to see that the Article found matches other request variables.
	 *
	 * @return int|string
	 */
	public function articleID() {
		static $id = null, $tried = false;

		if (is_null($id) && !$tried) {
			$tried = true;
			$idOrUrlSegment = $this->getVar(self::IDParam)
				?: $this->urlParam(self::IDParam);

			if ($idOrUrlSegment) {
				// parameter could be a numeric ID or a URLSegment, always return the ID
				if (is_numeric($idOrUrlSegment)) {
					if (1 == ArticleModel::get()->filter('ID', $idOrUrlSegment)->count()) {
						$id = $idOrUrlSegment;
					}
				} else {
					if ($article = ArticleModel::get()->filter('URLSegment', $idOrUrlSegment)->setQueriedColumns(['ID', 'URLSegment'])->first()) {
						$id = $article->ID;
					}
				}
			}
		}
		return $id;
	}

	/**
	 * Return requested article model classes from the types=<csv> get param or the Type url param.
	 *
	 * @return array
	 */
	public function models() {
		/** @var array $requested */
		if (!$requested = $this->getVar(self::ModelsParam)) {
			if (!$requested = $this->urlParam(self::ModelsParam)) {
				$requested = Session::get($this->key(self::ModelsParam));
			}
		}
		Session::set($this->key(self::ModelsParam), $requested);
		return array_filter(explode(self::ParamListSeperator, $requested));
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
	 * Return the pagination start from getVar or session
	 *
	 * @return int|null
	 */
	public function start() {
		if (is_null($start = $this->getVar(self::StartIndexGetVar))) {
			$start = Session::get($this->key(self::StartIndexGetVar));
		} else {
			Session::set($this->key(self::StartIndexGetVar), $start);
		}
		return $start;

	}

	/**
	 * Return the pagination limit from getVar or session
	 *
	 * @return int|null
	 */

	public function limit() {
		return $this->getVar(self::PageLengthGetVar)
			?: Session::get($this->key(self::PageLengthGetVar));
	}

	/**
	 * Return value of particular url parameter, filters out one's we're not interested in and caches those we are.
	 *
	 * @param $name
	 * @return mixed
	 */
	protected function urlParam($name) {
		static $cached = [];
		if (!array_key_exists($name, $cached)) {
			if (array_key_exists($name, $this->urlParams())) {

				$cached[ $name ] = $this->urlParams()[ $name ];

			}
		}
		return isset($cached[ $name ]) ? $cached[ $name ] : null;
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
				array_flip($this->config()->params)
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
	protected function getVar($name) {
		static $cached = [];
		$name = strtolower($name);
		if (!array_key_exists($name, $cached)) {
			$getVars = $this->getVars();
			if (array_key_exists($name, $getVars)) {
				$cached[ $name ] = $getVars[ $name ];
			}
		}
		return isset($cached[ $name ]) ? urldecode($cached[ $name ]) : null;
	}

	/**
	 * Return only the get vars we are interested in, keys are lowercased
	 *
	 * @return array
	 */
	public function getVars() {
		$cached = array_intersect_key(
			array_change_key_case(
				$this->request()->getVars(),
				CASE_LOWER
			),
			array_change_key_case(
				array_flip($this->config()->get('params')),
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
	public function postVars() {
		static $cached;

		if (!$cached) {
			$cached = array_intersect_key(
				array_change_key_case(
					$this->request()->postVars(),
					CASE_LOWER
				),
				array_change_key_case(
					array_flip($this->config()->get('params')),
					CASE_LOWER
				)
			);
		}
		return $cached;
	}

}