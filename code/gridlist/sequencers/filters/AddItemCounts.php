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
		/** @var \ArrayList $items */
		$items = $this()->gridListItems();

		$defaultFilter = Application::get_current_page()->DefaultFilter();

		$parameters['AllItemCount'] = $items->count();

		$defaultCount = 0;

		/** @var HasGridListFilters|Model $item */
		foreach ($filters as $filter) {
			$filterItemCount = 0;
			foreach ($items as $item) {

				if ($defaultFilter) {
					if ($item->GridListFilters()->find('ModelTag', $defaultFilter->ModelTag)) {
						$defaultCount++;
					}
				}

				if ($item->hasExtension(HasGridListFilters::class_name())) {
					if ($item->GridListFilters()->find('ID', $filter->ID)) {
						$filterItemCount++;
					}
				}
			}
			$filter->ItemCount = $filterItemCount;
		}
		$parameters['DefaultItemCount'] = $defaultCount;
	}
}