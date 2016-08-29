<?php
namespace Modular\GridList;

use Modular\ContentControllerExtension;
use Modular\owned;

/**
 * Add extensions to models which provide items, filters and other control to a GridList.
 *
 * @package Modular\Extensions
 */
class GridList extends ContentControllerExtension {
	use owned;

	public function GridList() {
		return new \ArrayData([
			'Items'   => $this->GridListItems(),
			'Filters' => $this->GridListFilters(),
			'Mode'    => $this->GridListMode(),
			'Sort'    => $this->GridListSort(),
		]);
	}

	public function GridListItems() {
		$out = new \ArrayList();

		// first we get any items related to the GridList itself , e.g. curated blocks added by HasBlocks
		// this will return an array of SS_Lists
		$lists = $this()->extend('provideGridListItems');
		foreach ($lists as $list) {
			$out->merge($list);
		}

		// then we get items from the current page via relationships
		// such as HasRelatedPages, HasTags etc
		$page = \Director::get_current_page();
		$lists = $page->invokeWithExtensions('provideGridListItems');
		foreach ($lists as $list) {
			$out->merge($list);
		}
		return $out;

	}

	public function GridListFilters() {
		$filters = new \ArrayList();
		$this()->extend('provideGridListFilters', $filters);
		return $filters;
	}

	public function GridListSort() {
//		return singleton('GridListFilterService')->sort();
	}

	public function GridListMode() {
//		return singleton('GridListFilterService')->mode();
	}
}