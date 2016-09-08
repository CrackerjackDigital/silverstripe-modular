<?php
namespace Modular\GridList;

use Modular\config;
use Modular\ContentControllerExtension;
use Modular\Models\GridListFilter;
use Modular\owned;

/**
 * Add extensions to models which provide items, filters and other control to a GridList.
 *
 * @package Modular\Extensions
 */
class GridList extends ContentControllerExtension {
	use owned;
	use config;

	const PaginatorServiceName = 'GridListPaginator';
	const DefaultPageLength = 12;

	public function GridList() {
		return new \ArrayData([
			'Items'   => $this->items(),
			'Filters' => $this->filters(),
			'Mode'    => $this->mode(),
			'Sort'    => $this->sort(),
		]);
	}

	public function items() {
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
		$out->removeDuplicates();
		return $this->paginator($out);
	}

	/**
	 * @param \SS_List $items
	 * @return \PaginatedList
	 */
	protected function paginator(\SS_List $items) {
		$params = \Controller::curr()->getRequest();

		/** @var \PaginatedList $paginated */
		$paginated = \Injector::inst()->create(
			static::PaginatorServiceName,
			$items,
			$params
		);
		$paginated->setPageLength($this->pageLength());
		return $paginated;
	}

	protected function pageLength() {
		return $this()->config()->get('gridlist_page_length')
			?: ($this->config()->get('gridlist_page_length')
				?: static::DefaultPageLength);
	}

	/**
	 * Returns the filters which should show in-page gathered via provideGridListFilters. These are composed of those specifically set on the GridList first
	 * and then those for the current page which may have an alternate strategy to provide them, such as most popular filters from child pages.
	 *
	 * @return \ArrayList
	 */
	public function filters() {
		$out = new \ArrayList();

		// first get filters which have been added specifically to the GridList, e.g. via a HasGridListFilters extendiong on the extended class
		// this will return an array of SS_Lists
		$lists = $this()->extend('provideGridListFilters');
		foreach ($lists as $list) {
			$out->merge($list);
		}
		// then we get items from the current page via relationships
		// such as HasRelatedPages, HasTags etc
		$page = \Director::get_current_page();
		$lists = $page->invokeWithExtensions('provideGridListFilters');
		foreach ($lists as $list) {
			$out->merge($list);
		}
		$out->removeDuplicates();
		return $out;
	}

	/**
	 * Return current sort criteria which should be applied to the GridList items
	 * @return mixed
	 */
	public function sort() {
		return singleton('GridListFilterService')->sort();
	}

	/**
	 * Return the current mode the GridList should show in.
	 * @return mixed
	 */
	public function mode() {
		return singleton('GridListFilterService')->mode();
	}
}