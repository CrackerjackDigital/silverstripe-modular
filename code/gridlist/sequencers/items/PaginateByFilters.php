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
	 * @param \SS_LIst $items
	 * @param          $filters
	 * @param array    $parameters
	 */
	public function sequenceGridListItems(&$items, $filters, &$parameters = []) {
		$out = new \ArrayList();

		$start = $parameters[ Constraints::StartIndexGetVar ];
		$limit = $parameters[ Constraints::PageLengthGetVar ];
		if (!is_null($limit)) {
			$added = 0;

			if ($allFilter = Application::get_current_page()->FilterAll()) {
				// first add 'all filter' items
				if ($allTag = $allFilter->Filter) {
					$index = 0;
					$added = 0;
					foreach ($items as $item) {
						$index++;

						if ($index < $start) {
							continue;
						}
						if ($allTag == 'all' || $item->GridListFilters()->find('ModelTag', $allTag)) {
							// we don't add all, we're just getting the count
//							$out->push($item);
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
					// initial number of items loaded in page
					$filter->LoadCount = $added;
				}
			}
			$out->removeDuplicates();
			$items = $out;
		}
	}
}