<?php
namespace Modular\GridList\Interfaces;
/**
 * Interface ItemsSequencer called by GridList when building items to re-arrange items or to inject items at a specific place in the list.
 *
 * @package Modular\GridList\Interfaces
 */
interface ItemsSequencer {
	/**
	 * @param \ArrayList|\DataList $items
	 * @param                      $filters
	 * @param array                $parameters
	 * @return
	 */
	public function sequenceGridListItems(&$items, $filters, &$parameters = []);
}