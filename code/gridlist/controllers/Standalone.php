<?php
namespace Modular\GridList\Controllers;

use Modular\Relationships\HasBlocks;

class Standalone extends \ContentController {
	private static $url_handlers = [
		'blocks' => 'blocks'
	];
	private static $allowed_actions = [
		'blocks' => 'true'
	];
	public function blocks(\SS_HTTPRequest $request) {
		/** @var \Page|HasBlocks $page */
		if ($referrer = $_SERVER['HTTP_REFERER']) {
			$path = parse_url($referrer, PHP_URL_PATH);

			if ($page = $this->findPageForPath($path)) {
				if ($page->hasExtension(\Modular\Relationships\HasBlocks::class_name())) {
					\Director::set_current_page($page);

					/** @var \GridListBlock $gridList */
					if ($gridList = $page->Blocks()->find('ClassName', 'GridListBlock')) {
						return $gridList->renderWith('GridList/GridList_Items');
					}
				}
			}
		}
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