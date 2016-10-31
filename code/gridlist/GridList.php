<?php
namespace Modular\GridList;

use Modular\Application;
use Modular\config;
use Modular\ContentControllerExtension;
use Modular\Controller;
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

	private static $gridlist_service = 'GridListService';

	private static $default_page_length = self::DefaultPageLength;

	private static $default_mode = self::ModeGrid;

	/**
	 * Return data for templates, accessible via e.g. GridList.Items and GridList.Mode
	 *
	 * @param string $overrideMode one of the self.ModeABC constants, will force gridlist to be in this mode always
	 * @return \ArrayData
	 */
	public function GridList($mode = null) {
		static $gridlist = [];

		if (!isset($gridlist[$mode])) {
			$items = $this->GridListItems($mode);

			$itemCount = $items->count();

			$extraData = $this->extraData($mode);

			// merge in extra data from provideGridListTemplateData extension call above this takes precedence
			$data = array_merge(
				[
					'Items'         => $items,
					'TotalItems'    => $itemCount,
					'Filters'       => $this->filters(),
					'Sort'          => $this->service()->sort(),
					'DefaultFilter' => $this->service()->Filters()->defaultFilter()
				],
				$extraData
			);
			$gridlist[$mode] = new \ArrayData($data);
		}
		return $gridlist[$mode];
	}

	protected function extraData($mode = null) {
		$mode = $mode ?: $this->mode();

		$providers = $this->providers();

		$extraData = [
			'Mode' => $mode
		];

		// now get any extra data
		foreach ($providers as $provider) {
			// get extra data such as for pagination PageLength, GridList Mode etc
			foreach ($provider->extend('provideGridListTemplateData', $extraData) as $extendedData) {
				$extraData = array_merge(
					$extraData,
					$extendedData
				);
			}
		}
		return $extraData;
	}

	/**
	 * Return all items unpaginated though may be limited as to how many items in each filter are returned.
	 *
	 * @return \ArrayList
	 */
	public function gridListItems($mode = null) {
		static $items;
		if (!$items) {
			$extraData = $this->extraData($mode);

			$items = new \ArrayList();
			$provided = [];

			$providers = $this->providers();

			// get all the lists from all the providers e.g. related pages, associated filters etc
			foreach ($providers as $provider) {
				$providerItems = $provider->extend('provideGridListItems', $extraData);
				$provided = array_merge(
					$provided,
					$providerItems
				);
			}
			// apply constraints to each list of items and merge into the 'master' list
			// this is where e.g. limits would be applied to total number of items for each partial list returned
			foreach ($provided as $providerItems) {
				if ($numProvided = $providerItems->count()) {
					$items->merge($providerItems);
				}
			}
			// now we need to go through and limit items by filters to blocks of 12 by filterID
			// from page start to limit
			$start = GridList::service()->Filters()->start();
			$limit = $extraData['PageLength'];

			$filterIDs = $this->filters()->column('ID');

			$items->removeDuplicates();

			// now do any grouping, direct manipulation of items such as fixed ordering
			foreach ($providers as $provider) {
				$provider->extend('sequenceGridListItems', $items, $extraData);
			}
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
			$providers = $this->providers();

			$filters = new \ArrayList();

			foreach ($providers as $provider) {
				// first get filters which have been added specifically to the GridList, e.g. via a HasGridListFilters extendiong on the extended class
				// this will return an array of SS_Lists
				$lists = $provider->extend('provideGridListFilters');

				foreach ($lists as $list) {
					$filters->merge($list);
				}
				$filters->removeDuplicates();

				$provider->extend('constrainGridListFilters', $filters);
			}
		}
		return $filters;
	}

	/**
	 * Returns an array of providers of items, filters etc to show in on-page grids. Starts with
	 * the current extended model, but may add the current page if it has config.gridlist_provider set
	 *
	 * @return \DataObject|\SiteTree
	 */
	protected function providers() {
		$providers = [
			$this(),
		];

		$page = null;

		$page = Application::get_current_page();
		if ($page) {
			if ($page->config()->get('gridlist_provider')) {
				$providers[] = $page;
			}
		}
		return $providers;
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
	 * Returns first mode from:
	 *  -   template parameter
	 *  -   url query string via service
	 *  -   extended models config.gridlist_mode (a GridListBlock not a page)
	 *  -   this config.default_mode
	 *
	 * @return string mode chosen, e.g. 'grid' or 'list'
	 */
	public function Mode() {
		return $this->service()->mode();
	}

	/**
	 * Return instance of service that this gridlist is using
	 *
	 * @return Service
	 */
	public static function service() {
		/** @var \Page $page */
		$service = '';

		if ($page = Application::get_current_page()) {
			$service = $page->config()->get('gridlist_service');
		}
		$service = $service ?: static::config()->get('gridlist_service');

		return \Injector::inst()->get($service);
	}

}