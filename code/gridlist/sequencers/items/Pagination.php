<?php
namespace Modular\GridList\Sequencers\Items;

use Modular\Blocks\Block;
use Modular\GridList\GridList;
use Modular\GridList\Interfaces\ItemsConstraints;
use Modular\GridList\Interfaces\ItemsSequencer;
use Modular\Search\ModelExtension;

/**
 * PageLength extension limits the number of items to the PageLength parameter, this is an overall limit which can be applied
 * first when retrieving items from the database, and then again for example for pagination
 *
 * @package Modular\GridList\Constraints\Items
 */
class Pagination extends ModelExtension implements ItemsSequencer {
	/**
	 * @param \DataList|\ArrayList $items
	 * @param                      $filters
	 * @param array                $parameters
	 */
	public function sequenceGridListItems(&$items, $filters, $parameters = []) {
		$limit = isset($parameters['PageLength']) ? $parameters['PageLength'] : null;

		// filter to current requested length
		if ($limit) {
			$start = GridList::service()->Filters()->start() ?: 0;
			$out = new \ArrayList();

			foreach ($filters as $filter) {
				$index = 0;
				$added = 0;
				foreach ($items as $item) {
					++$index;
					if ($index > $start) {
						if ($item instanceof Block || $item->GridListFilters()->find('ID', $filter->ID)) {
							$out->push($item);
							$added++;
							if ($added >= $limit) {
								break;
							}
						}
					}
				}
			}
			$items = $out;
		}
	}
}
