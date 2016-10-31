<?php
namespace Modular\GridList\Constraints\Items;

use Modular\GridList\GridList;
use Modular\GridList\Interfaces\ItemsConstraints;
use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;

/**
 * Constrain items to only be those for the current filter as set e.g. on url filter= query parameter
 *
 * @package Modular\GridList\Constraints
 */
class CurrentFilter extends ModelExtension implements ItemsConstraints {

	public function constrainGridListItems(&$items) {
		$service = GridList::service();

		$currentFilterID = $service->Filters()->currentFilterID();

		// filter to current filter if set
		if ($currentFilterID) {
			$relationship = HasGridListFilters::relationship_name('ID');
			
			$items = $items->filter([
				$relationship => $currentFilterID,
			]);
		}
	}
}