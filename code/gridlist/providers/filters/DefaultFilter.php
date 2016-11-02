<?php
namespace Modular\GridList\Providers\Filters;

use Modular\Fields\Title;
use Modular\GridList\Interfaces\FilterConstraints;
use Modular\GridList\Interfaces\FiltersProvider;
use Modular\GridList\Interfaces\TempleDataProvider;
use Modular\ModelExtension;
use Modular\Models\GridListFilter;

/**
 * Allows filters to be explicitly set on a page-by-page basis by setting config.gridlist_custom_filters on a page model.
 *
 * @package Modular\GridList\Providers\Filters
 */
class DefaultFilter extends ModelExtension implements FiltersProvider, TempleDataProvider {

	public function provideGridListTemplateData($data = []) {
		return [
			'DefaultFilter' => $this->provideGridListFilters()
		];
	}
	/**
	 * Return the current pages default filter
	 *
	 * @return array
	 */
	public function provideGridListFilters() {
		$page = \Director::get_current_page();
		if ($page instanceof \CMSMain) {
			$page = $page->currentPage();
		}
		return $page->DefaultFilter();
	}

}