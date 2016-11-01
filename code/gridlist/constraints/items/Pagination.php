<?php
namespace Modular\GridList\Constraints\Items;

use Modular\GridList\GridList;
use Modular\GridList\Interfaces\ItemsConstraints;
use Modular\Search\ModelExtension;

/**
 * PageLength extension limits the number of items to the PageLength parameter, this is an overall limit which can be applied
 * first when retrieving items from the database, and then again for example for pagination
 *
 * @package Modular\GridList\Constraints\Items
 */
class Pagination extends ModelExtension implements ItemsConstraints {
	/**
	 * @param \DataList|\ArrayList $items
	 * @return void
	 */
	public function constrainGridListItems(&$items, $parameters = []) {
		$limit = isset($parameters['PageLength']) ? $parameters['PageLength'] : null;

		// filter to current requested length
		if ($limit) {
			$start = GridList::service()->Filters()->start();
//			$items = $items->limit($limit, $start);
		}
	}

}
