<?php
namespace Modular\GridList\Sequencers\Filter;

use Modular\Application;
use Modular\GridList\Interfaces\FilterConstraints;
use Modular\Model;
use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;

/**
 * Adds count of items for each filter to the filters for pagination, badging etc, also adds DefaultItemCount and AllItemCount to $parameters
 *
 * @package Modular\GridList\Constraints\Filter
 */
class AddItemCounts extends ModelExtension implements FilterConstraints {

	public function constrainGridListFilters(&$filters, &$parameters = []) {
		$items = $this()->gridListItems();

		$defaultFilter = Application::get_current_page()->DefaultFilter();

		$allCount = 0;
		$defaultCount = 0;

		/** @var HasGridListFilters|Model $item */
		foreach ($items as $item) {

			$allCount++;

			if ($defaultFilter) {
				if ($item->GridListFilters()->find('ModelTag', $defaultFilter->ModelTag)) {
					$defaultCount++;
				}
			}
			foreach ($filters as $filter) {
				$filter->ItemCount = 0;

				if ($item->hasExtension(HasGridListFilters::class_name())) {
					if ($item->GridListFilters()->find('ID', $filter->ID)) {
						$filter->ItemCount++;
					}
				}
			}
		}
		$parameters['DefaultItemCount'] = $defaultCount;
		$parameters['AllItemCount'] = $allCount;
	}
}