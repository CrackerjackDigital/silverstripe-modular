<?php
namespace Modular\GridList\Controllers;

use Modular\Application;
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
		return Application::ajax_path_for_request($request);
	}

	protected function findPageForPath($path) {
		return Application::page_for_path($path);
	}
}