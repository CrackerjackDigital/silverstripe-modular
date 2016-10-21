<?php
namespace Modular\Extensions\Controller;

use Controller;
use Modular\ContentControllerExtension;
use Modular\GridList\Interfaces\Service\Service;
use Injector;

/**
 * Provides links to a GridList either for filtering or as an action such as 'clear'. This is removed from
 * GridList as it may go on pages which link to the gridlist but do not themselves have a GridList. The GridList
 * maintains state so cannot be added to all pages which need these links.
 *
 * GridListLinks
 */
class GridListLinks extends ContentControllerExtension {

	/**
	 * Provide a link back to the template for the current 'page' augmented
	 * with passed in filters in a key, value, key, value format, e.g.
	 * $GridListFilterLink('sort', 'a-z', 'models', 'rec,resource') in template
	 *
	 * If any parameters are not passed then the current values could be provided from
	 * Session or from default values.
	 *
	 * @return string
	 */
	public function GridListFilterLink() {
		$args = func_get_args();

		$params = [];

		// fold key, value argument pairs into key => value map overriding the defaults
		while ($name = array_shift($args)) {
			$params[ $name ] = array_shift($args);
		}
		/** @var Service $service */
		$service = Injector::inst()->get('GridListService');
		return $service->filterLink($params);
	}

	/**
	 * Like GridListFilterLink but returns a link which forces the grid list into list mode and
	 * hides the controls, so looks like search.
	 */
	public function GridListSearchLink() {
		$args = func_get_args();

		$params = [];

		// fold key, value argument pairs into key => value map overriding the defaults
		while ($name = array_shift($args)) {
			$params[ $name ] = array_shift($args);
		}
		// set to search mode
		$params['mode'] = 'search';

		/** @var Service $service */
		$service = Injector::inst()->get('GridListService');
		return $service->filterLink($params);

	}

	/**
	 * Return the link for a valid gridlist action, e.g. clear, setmode etc
	 *
	 * @param $action
	 * @return string
	 */
	public function GridListActionLink($action) {
		if (in_array($action, $this()->config()->get('allowed_actions'))) {
			return Controller::join_links(
				'gridlist',
				$action
			);
		}
	}
}