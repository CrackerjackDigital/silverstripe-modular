<?php
namespace Modular\GridList\Controllers;

use Modular\GridList\GridList;
use Modular\Relationships\HasBlocks;

class Standalone extends \ContentController {
	private static $url_handlers = [
		'blocks' => 'blocks',
	];
	private static $allowed_actions = [
		'blocks' => true
	];

	public function blocks(\SS_HTTPRequest $request) {
		/** @var \Page|HasBlocks $page */
		if ($path = $this->pathForRequest($request)) {
			if ($page = $this->findPageForPath($path)) {
				if ($page->hasExtension(\Modular\Relationships\HasBlocks::class_name())) {
					\Director::set_current_page($page);

					/** @var \GridListBlock $gridList */
					if ($gridListBlock = $page->Blocks()->find('ClassName', 'GridListBlock')) {
						$gridList = $gridListBlock->GridList();
						// set the load more header sued by client to show/hide laod more button
						$this->getResponse()->addHeader('X-Load-More', $gridList->LoadMore);
						return $gridListBlock->renderWith("GridListItems");
					}
				}
			}
		}
	}

	/**
	 * Return the path from:
	 *  -   query string 'path' parameter,
	 *  -   HTTP_REFERER if set
	 *  -   the request url (mainly for testing).
	 *
	 * @param \SS_HTTPRequest $request
	 * @return string
	 */
	protected function pathForRequest(\SS_HTTPRequest $request) {
		if (!$path = $request->getVar('path')) {
			if (isset($_SERVER['HTTP_REFERER'])) {
				$path = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
			} else {
				$path = $request->getURL();
			}
		}
		return $path;
	}

	protected function findPageForPath($path) {
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
}