<?php
namespace Modular\GridList\Providers\Filters;

use Modular\Application;
use Modular\Fields\Field;
use Modular\GridList\Interfaces\FiltersProvider;
use Modular\Models\GridListFilter;

/**
 * Add the current pages config.filter_all as a read-only in the CMS and to filters on page.
 *
 * @package Modular\GridList\Providers\Filters
 */
class AllFilter extends Field implements FiltersProvider {
	private static $filter_all = [];

	/**
	 * Show the configured filter as a read-only field in the CMS.
	 * @return array
	 */
	public function cmsFields() {
		if ($filter = $this->provideGridListFilters()) {
			if (isset($filter['Title'])) {
				return [
					'AllFilter' => new \ReadonlyField('AllFilter', 'All Filter', $filter['Title'])
				];
			}
		}
	}

	/**
	 * Add the configured filter to the list of filters.
	 * @return array
	 */
	public function provideGridListFilters() {
		if ($page = Application::get_current_page()) {
			if ($filter = $page->config()->get('filter_all')) {
				// set ModelTag so GridList.Filter method can find it... yech
				$filter['ModelTag'] = $filter['Filter'];
				return [
					new GridListFilter($filter)
			    ];
			}
		}
	}
}