<?php

namespace Modular\GridList;

use Modular\owned;

/**
 * Add extensions to models which provide items, filters and other control to a GridList.
 *
 * @package Modular\Extensions
 */
class HasGridList extends \Extension {
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
		// first we get any related items, e.g. from GridListBlocks
		// this will return an array of SS_Lists
		$items = $this()->extend('provideGridListItems');


		// then we get related items from the current page via relationships
		// such as HasRelatedPages, HasTags etc
		$page = \Director::get_current_page();
		$items = $page->extend('provideGridListItems');

		return $items;
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