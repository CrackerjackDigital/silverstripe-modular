<?php
namespace Modular\GridList\Controllers;

use Modular\Application;
use Modular\Relationships\HasBlocks;

/**
 * Standalone controller which services requests to e.g. '/gridlist/blocks?path=...&filter=...&start=...&limit=...'
 *
 * @package Modular\GridList\Controllers
 */
class Standalone extends \ContentController {
	private static $url_handlers = [
		'blocks' => 'blocks',
	];
	private static $allowed_actions = [
		'blocks' => true
	];

	public function blocks(\SS_HTTPRequest $request) {
		$page = null;

		/** @var \Page|HasBlocks $page */
		if ($pageID = $request->param('PageID')) {
			$page = \Page::get()->byID($pageID);
		} else {
			if ($path = Application::path_for_request($request)) {
				$page = Application::page_for_path($path);
			}
		}
		if ($page) {
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