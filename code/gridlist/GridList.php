<?php
namespace Modular\GridList;

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

	public function GridList() {
		$items = $this->items();

		// get the count before constraints have been applied, e.g for 'showing count of y' type message
		$totalCount = $items->count();

		// call the extended model to apply edditional constraints on the items
		$constraints = $this->constrain($items);

		return new \ArrayData([
			'Items'       => $items,
			'Filters'     => $this->filters(),
			'Constraints' => $constraints,
			'TotalCount'  => $totalCount,
			'Mode'        => $this->mode(),
			'Sort'        => $this->sort(),
		]);
	}

	/**
	 * @param \SS_List $list
	 * @return \ArrayList of constraints as returned from constraint application extension calls.
	 */
	public function constrain(\SS_List &$list) {
		return new \ArrayList(
			$this()->extend(\GridListFilterConstraints::ApplyMethodName, $list)
		);
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

		return $out;
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
	 *
	 * @return mixed
	 */
	public function sort() {
		return singleton('GridListFilterService')->sort();
	}

	/**
	 * Return the current mode the GridList should show in.
	 *
	 * @return mixed
	 */
	public function mode() {
		return singleton('GridListFilterService')->mode();
	}
}