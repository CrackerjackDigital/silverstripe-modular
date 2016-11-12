<?php
namespace Modular\GridList\Interfaces;
/**
 * Interface ItemsSequencer called by GridList after retrieving all items and filters, used to order or otherwise decorate
 * filters.
 *
 * @package Modular\GridList\Interfaces
 */
interface FiltersSequencer {
	/**
	 * @param                      $filters
	 * @param \ArrayList|\DataList $items
	 * @param array                $parameters (eg extra data from provideGridListTemplateData extension calls) can be mutated
	 * @return
	 */
	public function sequenceGridListFilters(&$filters, $items, &$parameters = []);
}