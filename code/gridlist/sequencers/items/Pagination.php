<?php
namespace Modular\GridList\Sequencers\Items;

use Modular\Application;
use Modular\Blocks\Block;
use Modular\GridList\Constraints;
use Modular\GridList\GridList;
use Modular\GridList\Interfaces\ItemsConstraints;
use Modular\GridList\Interfaces\ItemsSequencer;
use Modular\Models\GridListFilter;
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
	public function sequenceGridListItems(&$items, $filters, &$parameters = []) {
		$limit = isset($parameters['PageLength']) ? $parameters['PageLength'] : null;

		// filter items for each filter to current page length
		if ($limit) {
			$start = GridList::service()->Filters()->start() ?: 0;

			$out = new \ArrayList();

			$currentFilter = GridList::service()->constraint(Constraints::FilterVar);

			if ($currentFilter && $currentFilter != 'all') {
				if ($filter = GridListFilter::get()->filter(['ModelTag' => $currentFilter])->first()) {
					$out->merge($items->limit($limit, $start));
				}
			} else {
				foreach ($filters as $filter) {
					$filtered = new \ArrayList();

					foreach ($items as $item) {
						if ($item instanceof Block) {
							// only push blocks first page
							if ($start == 0) {
								$filtered->push($item);
							}
						} else if ($currentFilter == 'all') {
							$filtered->push($item);

						} else if ($item->GridListFilters()->find('ID', $filter->ID)) {
							$filtered->push($item);
						}
					}
					// merge limited filtered items back in
					$out->merge($filtered->limit($limit, $start));
				}
			}
			$items = $out;
		}

	}
}
