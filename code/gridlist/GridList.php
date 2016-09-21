<?php
namespace Modular\GridList;

use Modular\config;
use Modular\ContentControllerExtension;
use Modular\Model;
use Modular\Models\GridListFilter;
use Modular\owned;
use Modular\Relationships\HasGridListFilters;

/**
 * Add extensions to models which provide items, filters and other control to a GridList.
 *
 * @package Modular\Extensions
 */
class GridList extends ContentControllerExtension {
	use owned;
	use config;

	const PaginatorServiceName = 'GridListPaginator';
	const DefaultPageLength    = 12;

	public function GridList() {
		$gridlist = new \ArrayData([
			'Items'     => $this->paginator($this->items()),
			'Filters'   => $this->filters(),
			'Mode'      => $this->Mode(),
			'Sort'      => $this->Sort(),
			'NextStart' => $this->NextStart(),
		    'MoreAvailable' => $this->moreAvailable(),
		    'DefaultFilter' => $this->defaultFilter()
		]);
		return $gridlist;
	}

	/**
	 * @return \ArrayList
	 */
	protected function items() {
		static $out;
		if (!$out) {
			$out = new \ArrayList();

			// first we get any items related to the GridList itself , e.g. curated blocks added by HasBlocks
			// this will return an array of SS_Lists
			$lists = $this()->extend('provideGridListItems');
			/** @var \ManyManyList $list */
			foreach ($lists as $items) {
				$out->merge($items);
			}

			$out->removeDuplicates();

			$this()->extend('sequenceGridListItems', $out);
		}
		return $out;
	}

	/**
	 * Returns the filters which should show in-page gathered via provideGridListFilters. These are composed of those specifically set on the GridList first
	 * and then those for the current page which may have an alternate strategy to provide them, such as most popular filters from child pages.
	 *
	 * @return \ArrayList
	 */
	protected function filters() {
		static $out;
		if (!$out) {
			$out = new \ArrayList();

			// first get filters which have been added specifically to the GridList, e.g. via a HasGridListFilters extendiong on the extended class
			// this will return an array of SS_Lists
			$lists = $this()->extend('provideGridListFilters');
			foreach ($lists as $list) {
				$out->merge($list);
			}
			$out->removeDuplicates();

			$items = $this->items();

			$this()->extend('constrainGridListFilters', $out, $items);
		}
		return $out;
	}

	protected function defaultFilter() {
		return \Director::get_current_page()->DefaultFilter();
	}

	/**
	 * Given a list of items return a paginated version.
	 *
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

	protected function moreAvailable() {
		return $this->NextStart() < $this->items()->Count();
	}

	protected function pageLength() {
		return $this()->config()->get('gridlist_page_length')
			?: ($this->config()->get('gridlist_page_length')
				?: static::DefaultPageLength);
	}

	public function Start() {
		return \Controller::curr()->getRequest()->getVar('start');
	}

	public function NextStart() {
		return (int) $this->Start() + (int) $this->pageLength();
	}

	/**
	 * Return current sort criteria which should be applied to the GridList items
	 *
	 * @return mixed
	 */
	public function Sort() {
		return singleton('GridListFilterService')->sort();
	}

	/**
	 * Return the current mode the GridList should show in.
	 *
	 * @return mixed
	 */
	public function Mode() {
		return singleton('GridListFilterService')->mode();
	}
}