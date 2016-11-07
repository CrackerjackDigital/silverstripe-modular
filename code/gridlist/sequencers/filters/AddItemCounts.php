<?php
namespace Modular\GridList\Sequencers\Filters;

use Modular\Application;
use Modular\GridList\Interfaces\FiltersSequencer;
use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;
use Modular\Relationships\HasTags;

/**
 * Iterates through filters and adds an item count of items for that filter, for use by pagination, badges etc.
 *
 * @package Modular\GridList\Providers\Filters
 */
class AddItemCounts extends ModelExtension implements FiltersSequencer {
	const AllItemCountKey = 'AllItemCount';

	public function sequenceGridListFilters(&$filters, $items, &$parameters = []) {

		$allItemCount = $items->count();

		if ($allFilter = Application::get_current_page()->FilterAll()) {
			$allTag = $allFilter->Filter;
		} else {
			$allTag = '';
		}

		foreach ($filters as $filter) {
			$itemCount = 0;
			$tag = $filter->ModelTag;

			/** @var \DataObject|HasGridListFilters $item */
			foreach ($items as $item) {
				if ($item->hasExtension(HasGridListFilters::class_name())) {
					if ($item->GridListFilters()->find('ModelTag', $tag)) {
						$itemCount++;
					}
					if ($allTag && $item->GridListFilters()->find('ModelTag', $allTag)) {
						$allItemCount++;
					}
				}
			}
			$filter->ItemCount = $itemCount;
		}
		$parameters[self::AllItemCountKey] = $allItemCount;
	}
}