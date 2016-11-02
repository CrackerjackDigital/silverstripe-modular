<?php
namespace Modular\GridList\Constraints\Filters;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\FilterConstraints;
use Modular\Model;
use Modular\Relationships\HasGridListFilters;

/**
 * Removes filters which don't exist in any items, should be added to GridList host (e.g. GridListBlock).
 *
 * This will be called by GridList.filters after all filter providers have added their filters.
 *
 * @package Modular\GridList\Constraints\Filters
 */
class HideUnmatchedFilters extends Field implements FilterConstraints {
	const SingleFieldName   = 'HideUnmatchedFilters';
	const SingleFieldSchema = 'Boolean';

	private static $defaults = [
		self::SingleFieldName => false,
	];

	/**
	 * If HideUnmatchedFilters is on then remove all filters which are not found in the items by their 'AssociatedFilters' relationship.
	 *
	 * @param \DataList $filters list of GridListFilter models
	 * @param array     $parameters
	 * @return \ArrayList
	 */
	public function constrainGridListFilters(&$filters, &$parameters = []) {
		$out = new \ArrayList();
		if ($this()->{self::SingleFieldName}) {
			$ids = $filters->column('ID');

			if (count($ids)) {
				$items = $this()->GridListItems();

				// this is where we keep track of GridListFilters which have been found on items where ID is the key
				$foundFilters = array_combine(
					$ids,
					array_fill(0, count($ids), false)
				);

				foreach ($foundFilters as $filterID => &$found) {
					/** @var \Page|Model $item */
					foreach ($items as $item) {
						if ($item->hasExtension(HasGridListFilters::class_name())) {
							if ($itemFilters = $item->{HasGridListFilters::relationship_name()}()->column('ID')) {
								if (in_array($filterID, $itemFilters)) {
									$found = true;
									break;
								}
							}
						}
					}
				}
				foreach ($filters as $filter) {
					if (isset($foundFilters[ $filter->ID ])) {
						$out->push($filter);
					}
				}
				$filters = $out;
			}

		}
	}
}

