<?php
namespace Modular\GridList\Constraints\Filter;

use Modular\GridList\Interfaces\FilterConstraints;
use Modular\Model;
use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;

/**
 * Adds count of items for each filter to the filters for pagination, badging etc
 *
 * @package Modular\GridList\Constraints\Filter
 */
class AddItemCounts extends ModelExtension implements FilterConstraints {

	public function constrainGridListFilters(&$filters) {
		$items = $this()->gridListItems();

		/** @var HasGridListFilters|Model $item */
		foreach ($filters as $filter) {
			$filter->ItemCount = 0;
			foreach ($items as $item) {
				if ($item->hasExtension(HasGridListFilters::class_name())) {
					if ($item->GridListFilters()->find('ID', $filter->ID)) {
						$filter->ItemCount++;
					}
				}
			}
		}
	}
}