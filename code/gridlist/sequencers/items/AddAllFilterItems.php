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
 * Adds an excerpt of items with a filter of 'all'
 *
 * @package Modular\GridList\Sequencers\Items
 */
class AddAllFilterItems extends ModelExtension implements ItemsSequencer {

	public function sequenceGridListItems(&$items, $filters, &$parameters = []) {
		if (isset($parameters['AllFilter']) && isset($parameters['PageLength'])) {
			$allTag = $parameters['AllFilter']->ModelTag;
			$limited = $items->limit($parameters['PageLength']);

			foreach ($limited as $item) {
				if ($item->hasMethod('addCustomFilterTag')) {
					$item->addCustomFilterTag($allTag);
				}
			}
			$items->merge($limited);
		}
	}
}