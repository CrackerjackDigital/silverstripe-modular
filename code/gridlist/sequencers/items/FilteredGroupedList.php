<?php
namespace Modular\GridList\Sequencers\Items;

use Modular\Application;
use Modular\Fields\ModelTag;
use Modular\GridList\Constraints;
use Modular\GridList\Fields\Mode;
use Modular\GridList\GridList;
use Modular\GridList\Interfaces\ItemsSequencer;
use Modular\GridList\Providers\Filters\CurrentFilter;
use Modular\GridList\Sequencers\GroupByField;
use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;

/**
 * Constrain count of items by filter to the page length when in Grid Mode
 *
 * // TODO move the non-filter aspects into parent class e.g. 'PaginateGroupedList'
 *
 * @package Modular\GridList\Constraints\Items
 */
class FilteredGroupedList extends ModelExtension implements ItemsSequencer {
	/**
	 * Expects $parameters 'start' and 'limit' to be set, limits number of groups in a grouped list to page length. Adds Filters from all the items within
	 * a group to the group as 'Filters' property. Adds the count of groups for a filter to the Filter.
	 *
	 * @param \ArrayList|\DataList $groups expected to be already a grouped list
	 * @param \DataList|\ArrayList $filters
	 * @param array                $parameters
	 */
	public function sequenceGridListItems(&$groups, $filters, &$parameters = []) {
		// get from GridListMode extension
		$mode = $parameters[ Mode::TemplateDataKey ];

		if ($mode == GridList::ModeList) {
			// add filters to groups for all their child items
			foreach ($groups as &$group) {
				$groupFilters = [];

				// add filters for all items in a group to the group as 'Filters'
				foreach ($group->Children as $item) {
					if ($item->hasExtension(HasGridListFilters::class_name())) {
						$groupFilters += $item->GridListFilters()->column('ModelTag');
					}
				}
				$group->Filters = $groupFilters;
			}
			// add counts to filters of groups with that filter
			foreach ($filters as $filter) {
				$groupCount = 0;
				$filterTag = $filter->ModelTag;
				foreach ($groups as $group) {
					$groupFilters = $group->Filters ?: [];

					if (in_array($filterTag, $groupFilters)) {
						$groupCount++;
					}
				}
				$filter->ItemCount = $groupCount;
			}

			// if we are filtering by a filter then we need to prune the results down by filter
 			$filter = @$parameters[CurrentFilter::TemplateDataKey];
/*
			foreach ($groups as &$group) {
				$groupFilters = [];
				$items = $group->Children;

				foreach ($items as $item) {
					if ($item->hasExtension(HasGridListFilters::class_name())) {
						$itemFilters = $item->GridListFilters()->column('ModelTag');

						if ($filter && !in_array($filter, $itemFilters)) {
							// prune item from group as doesn't match filter
							$group->Children->remove($item);
						} else {
							$groupFilters += $itemFilters;
						}
					}
				}
				if ($filter && $group->Children->count() == 0) {
					// prune whole group as no items matching filter in it
					$groups->remove($group);
				} else {
					$group->Filters = $groupFilters;
				}
			}
*/
			$start = @$parameters[ Constraints::StartIndexGetVar ];
			$limit = @$parameters[ Constraints::PageLengthGetVar ];

			$loaded = [];
			// now calculate how many will actually be loaded in page (less than or equal to page length).
			foreach ($filters as $filter) {
				$scan = 0;
				$loadCount = 0;
				$filterTag = $filter->ModelTag;

				foreach ($groups as $group) {
					$groupFilters = $group->Filters;

					if ($scan++ >= $start) {
						if (in_array($filterTag, $groupFilters)) {
							if ($loadCount++ >= $limit) {
								break;
							}
						}
					}
				}
				$filter->LoadCount = $loadCount;
			}
		}
	}
}