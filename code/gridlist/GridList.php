<?php
namespace Modular\GridList;

use Modular\Application;
use Modular\config;
use Modular\ContentControllerExtension;
use Modular\Controller;
use Modular\Fields\ModelTag;
use Modular\GridList\Fields\Mode;
use Modular\GridList\Providers\Filters\CurrentFilter;
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
	public function GridList() {
		static $gridlist;

		if (!$gridlist) {

			$providers = $this->providers();

			$mode = $this->mode();

			// this will give us 'raw' items
			$items = $this->items($mode);

			// this will give us 'raw' filters
			$filters = $this->filters($mode);

			$templateData = $this->templateData($items, $mode);

			// now do any constraints to filter out unwanted items
			foreach ($providers as $provider) {
				$provider->extend('constrainGridListItems', $items, $filters, $templateData);
			}
			// get raw item count before we do any more manipulation
			$rawItemCount = $items->count();

			// now do any grouping, direct manipulation of items such as fixed ordering, pagination etc
			foreach ($providers as $provider) {
				$provider->extend('sequenceGridListItems', $items, $filters, $templateData);
			}

			// re-arrange, decorate etc filters
			foreach ($providers as $provider) {
				$provider->extend('sequenceGridListFilters', $filters, $items, $templateData);
			}

			// hook final output for e.g. redirections if only one item
			foreach ($providers as $provider) {
				$provider->extend('handleGridListItems', $items, $filters, $templateData);
			}

			// merge in extra data from provideGridListTemplateData extension call above this takes precedence
			$data = array_merge(
				[
					'Items'      => $items,
					'TotalItems' => $rawItemCount,
					'Filters'    => $filters,
				],
				$templateData
			);
			$gridlist = new \ArrayData($data);
		}
		return $gridlist;
	}

	/**
	 * Use for partial caching, extensions will provide additional information for cache hash generation.
	 *
	 * @return mixed
	 */
	public function CacheHash() {
		$data = implode(':',
			array_filter(
				array_merge(
					$this()->extend('provideGridListCacheHashData'),
					[
						Application::get_current_page()->LastEdited,
					]
				)
			)
		);
		return md5(Controller::curr()->getRequest()->getURL(true) . ':' . $data);
	}

	/**
	 * Request data via extensions provideGridListTemplateData method which we can pass into the template and also for subsequent processing.
	 *
	 * @param $items
	 * @param $mode
	 * @return array
	 */
	protected function templateData($items, $mode) {
		$templateData = [
			'Sort'                         => $this->service()->sort(),
			'DefaultFilter'                => $this->service()->defaultFilter(),
			'AllFilter'                    => $this->service()->allFilter(),
			Mode::TemplateDataKey          => $mode,
			CurrentFilter::TemplateDataKey => '',
			Constraints::StartIndexGetVar  => $this->service()->start() ?: 0,
			Constraints::PageLengthGetVar  => $this->service()->limit()          // may be overwritten by e.g PageLength extension
		];
		// now get any extra data
		foreach ($this->providers() as $provider) {
			// get extra data such as for pagination PageLength, GridList Mode etc
			foreach ($provider->extend('provideGridListTemplateData', $templateData, $items) as $extendedData) {
				$templateData = array_merge(
					$templateData,
					$extendedData
				);
			}
		}
		return $templateData;
	}

	/**
	 * @return \ArrayList
	 */
	protected function items($mode) {
		static $items;
		if (!$items) {
			$providers = $this->providers();

			$items = new \ArrayList();

			foreach ($providers as $provider) {
				// first we get any items related to the GridList itself , e.g. curated blocks added by HasBlocks
				// this will return an array of SS_Lists
				$lists = $provider->extend('provideGridListItems');
				/** @var \ManyManyList $list */
				foreach ($lists as $itemList) {
					$items->merge($itemList);
				}
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
	protected function filters($mode) {
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

				$items = $this->items($mode);

				$provider->extend('constrainGridListFilters', $items, $filters);
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
