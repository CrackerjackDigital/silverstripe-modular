<?php
namespace Modular\GridList\Constraints\Items;

use Modular\GridList\GridList;
use Modular\GridList\Interfaces\ItemsConstraints;
use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;

/**
 * Filter down to only the current filter as provided on query string, if no current filter do nothing.
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