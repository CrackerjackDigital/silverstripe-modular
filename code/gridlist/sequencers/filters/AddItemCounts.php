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

		if ($allFilter = Application::get_current_page()->FilterAll()) {
			$allTag = $allFilter->Filter;
			$allItemCount = 0;
		} else {
			$allTag = '';
			$allItemCount = $items->count();
		}

		foreach ($filters as $filter) {
			$itemCount = 0;
			$tag = $filter->ModelTag;

			/** @var \DataObject|HasGridListFilters $item */
			foreach ($items as $item) {
				if ($item->hasExtension(HasGridListFilters::class_name())) {
					if ($item->GridListFilters()->find('ModelTag', $tag)) {
						$itemCount++;
					} else if (($allTag == $tag) || ($allTag && $item->GridListFilters()->find('ModelTag', $allTag))) {
						$allItemCount++;
					}
				}
			}
			// don't recount 'all filter' after first pass
			$allTag = false;

			$filter->ItemCount = max($itemCount - 1, 0);
		}
		$parameters[self::AllItemCountKey] = max($allItemCount - 1, 0);
	}
}