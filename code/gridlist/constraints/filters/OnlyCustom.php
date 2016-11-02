<?php
namespace Modular\GridList\Constraints\Filter;

use Modular\Fields\Title;
use Modular\GridList\Interfaces\FilterConstraints;
use Modular\ModelExtension;
use Modular\Models\GridListFilter;

/**
 * Limits filters to only those defined on the page via config.gridlist_custom_filters (only if defined though otherwise no change)
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
		if ($customFilters = $page->config()->get('gridlist_custom_filters') ?: []) {
			$filters = new \ArrayList();
			foreach ($customFilters as $tag => $title) {
				if (!$filter = GridListFilter::get()->filter(['ModelTag' => $tag])->first()) {
					$filter = new GridListFilter([
						Title::SingleFieldName       => $title,
						GridListFilter::TagFieldName => $tag
					]);
				}
				$filters->push($filter);
			}
		}
	}

}