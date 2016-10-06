<?php
namespace Modular\GridList;

use Modular\config;
use Modular\ContentControllerExtension;
use Modular\Fields\ModelTag;
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

	private static $gridlist_page_length = self::DefaultPageLength;

	public function GridList() {
		static $gridlist;
		if (!$gridlist) {
			$extraData = [];
			foreach ($this()->extend('provideGridListTemplateData', $extraData) as $extendedData) {
				$extraData = array_merge(
					$extraData,
					$extendedData
				);
			}
			$firstItem = $this->service()->firstItem();
			$pageLength = isset($extraData['PageLength'])
				? $extraData['PageLength']
				: $this->config()->get('gridlist_page_length');

			$items = $this->items()->sort('EventDate desc');
			$totalCount = $items->count();

			$paginated = $this->paginator($items, $firstItem, $pageLength);

			$paginatedLast = $firstItem + $pageLength;

			$loadMore = ($totalCount > $paginatedLast) ? 1 : 0;

			$data = array_merge(
				[
					'Items'         => $paginated,
					'TotalItems'    => $totalCount,
					'Filters'       => $this->filters(),
					'Mode'          => $this->service()->mode(),
					'Sort'          => $this->service()->sort(),
					'DefaultFilter' => $this->service()->defaultFilter(),
					'LoadMore'      => $loadMore
				],
				$extraData
			);
			$gridlist = new \ArrayData($data);
		}
		return $gridlist;
	}

	/**
	 * @return \ArrayList
	 */
	protected function items() {
		static $items;
		if (!$items) {
			$items = new \ArrayList();

			$currentFilterID = $this->service()->currentFilterID();

			// first we get any items related to the GridList itself , e.g. curated blocks added by HasBlocks
			// this will return an array of SS_Lists
			$lists = $this()->extend('provideGridListItems');
			/** @var \ManyManyList $list */
			foreach ($lists as $itemList) {
				// filter to current filter if set
				if ($currentFilterID) {
					$itemList = $itemList->filter([
						HasGridListFilters::relationship_name('ID') => $currentFilterID,
					]);
				}
				$items->merge($itemList);
			}

			$items->removeDuplicates();

			$this()->extend('constrainGridListItems', $items);

			$this()->extend('sequenceGridListItems', $items);
		}
		return $items;
	}

	/**
	 * Returns the filters which should show in-page gathered via provideGridListFilters. These are composed of those specifically set on the GridList first
	 * and then those for the current page which may have an alternate strategy to provide them, such as most popular filters from child pages.
	 *
	 * @return \ArrayList
	 */
	protected function filters() {
		static $filters;
		if (!$filters) {
			$filters = new \ArrayList();

			// first get filters which have been added specifically to the GridList, e.g. via a HasGridListFilters extendiong on the extended class
			// this will return an array of SS_Lists
			$lists = $this()->extend('provideGridListFilters');
			foreach ($lists as $list) {
				$filters->merge($list);
			}
			$filters->removeDuplicates();

			$items = $this->items();

			$this()->extend('constrainGridListFilters', $items, $filters);
		}
		return $filters;
	}

	/**
	 * Given a list of items return a paginated version.
	 *
	 * @param \SS_List $items
	 * @param int      $firstItem
	 * @param int      $pageLength
	 * @return \PaginatedList
	 */
	protected function paginator(\SS_List $items, $firstItem, $pageLength) {
		$params = \Controller::curr()->getRequest();

		/** @var \PaginatedList $paginated */
		$paginated = \Injector::inst()->create(
			static::PaginatorServiceName,
			$items,
			$params
		);
		$paginated->setPageStart($firstItem);
		$paginated->setPageLength($pageLength);
		return $paginated;
	}

	/**
	 * @return \GridListService
	 */
	protected function service() {
		return singleton('GridListService');
	}

}