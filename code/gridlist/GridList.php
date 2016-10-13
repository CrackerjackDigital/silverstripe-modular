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

	const ModeGrid = 'grid';
	const ModeList = 'list';

	const PaginatorServiceName = 'GridListPaginator';
	const DefaultPageLength    = 12;

	private static $default_page_length = self::DefaultPageLength;

	private static $default_mode = self::ModeGrid;

	/**
	 * Return data for templates, accessible via e.g. GridList.Items and GridList.Mode
	 * @param string $overrideMode one of the self.ModeABC constants, will force gridlist to be in this mode always
	 * @return \ArrayData
	 */
	public function GridList($overrideMode = '') {
		static $gridlist;
		if (!$gridlist) {
			$extraData = [];
			// get extra data such as for pagination PageLength etc
			foreach ($this()->extend('provideGridListTemplateData', $extraData) as $extendedData) {
				$extraData = array_merge(
					$extraData,
					$extendedData
				);
			}
			$firstItem = $this->service()->firstItem();
			$pageLength = isset($extraData['PageLength'])
				? $extraData['PageLength']
				: $this->config()->get('default_page_length');

			$mode = $this->mode($overrideMode);

			$items = $this->items($mode);

			$totalCount = $items->count();

			$this()->extend('groupGridListItems', $items, $mode);

			$paginated = $this->paginator($items, $firstItem, $pageLength);

			$paginatedLast = $firstItem + $pageLength;

			// this will be sent back as a header X-Load-More
			$loadMore = ($totalCount > $paginatedLast) ? 1 : 0;

			$data = array_merge(
				[
					'Items'         => $paginated,
					'TotalItems'    => $totalCount,
					'Filters'       => $this->filters($mode),
					'Mode'          => $mode,
					'Sort'          => $this->service()->sort(),
					'DefaultFilter' => $this->service()->defaultFilter(),
					'LoadMore'      => $loadMore,
				],
				$extraData
			);
			$gridlist = new \ArrayData($data);
		}
		return $gridlist;
	}

	/**
	 * Returns first mode from:
	 *  -   template parameter
	 *  -   url query string via service
	 *  -   extended models config.gridlist_mode (a GridListBlock not a page)
	 *  -   this config.default_mode
	 *
	 * @param string $fromTemplate will override other choices if provided
	 * @return string mode chosen, e.g. 'grid' or 'list'
	 */
	protected function mode($fromTemplate = '') {
		$options = [
			$fromTemplate,
			$this->service()->mode(),
			$this()->config()->get('gridlist_mode'),
			$this->config()->get('default_mode'),
		];
		return trim(current(array_filter($options)));
	}

	/**
	 * Return instance of service that this gridlist is using
	 *
	 * @return \GridListService
	 */
	public static function service() {
		return \Injector::inst()->get('GridListService');
	}

	/**
	 * @return \ArrayList
	 */
	protected function items($mode) {
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

			$this()->extend('sequenceGridListItems', $items, $mode);
		}
		return $items;
	}

	/**
	 * Returns the filters which should show in-page gathered via provideGridListFilters. These are composed of those specifically set on the GridList first
	 * and then those for the current page which may have an alternate strategy to provide them, such as most popular filters from child pages.
	 *
	 * @return \ArrayList
	 */
	protected function filters($mode) {
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

			$items = $this->items($mode);

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

}