<?php
namespace Modular\GridList\Constraints\Filter;

use Modular\Fields\Title;
use Modular\GridList\Interfaces\FilterConstraints;
use Modular\ModelExtension;
use Modular\Models\GridListFilter;

/**
 * Limits filters to only thosed defined on the page
 *
 * @package Modular\GridList\Constraints\Filter
 */
class OnlyCustom extends ModelExtension implements FilterConstraints {

	/**
	 * Make sure only the custom filters are provided. This needs to go after other filter providers.
	 *
	 * @param $filters
	 */
	public function constrainGridListFilters(&$filters, &$parameters = []) {
		$page = \Director::get_current_page();
		if ($page instanceof \CMSMain) {
			$page = $page->currentPage();
		}
		$filters = new \ArrayList();
		$customFilters = $page->config()->get('gridlist_custom_filters') ?: [];
		foreach ($customFilters as $filter => $title) {
			if (!$filter = GridListFilter::get()->filter(['ModelTag' => $filter])->first()) {
				$filter = new GridListFilter([
					Title::SingleFieldName       => $title,
					GridListFilter::TagFieldName => $filter
				]);
			}
			$filters->push($filter);
		}
	}

}