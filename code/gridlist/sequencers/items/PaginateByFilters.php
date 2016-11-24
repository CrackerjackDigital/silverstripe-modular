<?php
namespace Modular\GridList\Sequencers\Items;

use Modular\Application;
use Modular\GridList\Constraints;
use Modular\GridList\Interfaces\ItemsSequencer;
use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;

/**
 * Constrain count of items by filter to the page length
 *
 * @package Modular\GridList\Constraints\Items
 */
class PaginateByFilters extends ModelExtension implements ItemsSequencer {

	/**
	 * Expects parameters 'start' and 'limit' to be set, limits items by filter to page length
	 *
	 *
	 * @param \ArrayList|\DataList $items
	 * @param \DataList|\ArrayList $filters
	 * @param array                $parameters
	 */
	public function sequenceGridListItems(&$items, $filters, &$parameters = []) {
		$out = new \ArrayList();

		$start = $parameters[ Constraints::StartIndexGetVar ];
		$limit = $parameters[ Constraints::PageLengthGetVar ];
		if (!is_null($limit)) {
			$added = 0;

			$modelTags = $filters->column('ModelTag');

			if ($allFilter = Application::get_current_page()->FilterAll()) {
				// first add first limit 'all filter' items which don't exist in the other filters
				if ($allTag = $allFilter->Filter) {
					$index = 0;
					$added = 0;
					foreach ($items as $item) {
						$index++;

						if ($index < $start) {
							continue;
						}
						if ($allTag == 'all' || !$item->GridListFilters()->find('ModelTag', $modelTags)) {
							$out->push($item);
							$added++;
						}
						if ($added >= $limit) {
							break;
						}
					}
				}
			}
			// initial number of 'all filter' items loaded in page
			$parameters['AllLoadCount'] = $added;

			foreach ($filters as $filter) {
				if ($tag = $filter->ModelTag) {
					$index = 0;
					$added = 0;

					foreach ($items as $item) {
						$index++;

						if ($index < $start) {
							continue;
						}
						if ($item->hasExtension(HasGridListFilters::class_name())) {
							if ($item->GridListFilters()->find('ModelTag', $tag)) {
								$out->push($item);
								$added++;
							}
						}
						if ($added >= $limit) {
							break;
						}
					}
					// initial number of items loaded in page (may be less than page length)
					$filter->LoadCount = $added;
				}
			}
			// now add the first page length items which don't match any added above by a filter
			// back in for the 'all' filter
//			$out->merge(
//				$items->exclude('ID', $out->column('ID'))->limit($limit, $start)
//			);

			$items = $out;
		}
	}
}