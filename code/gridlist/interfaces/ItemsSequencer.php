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
	 * @param null|string          $mode e.g. 'grid' or 'list'
	 */
	public function sequenceGridListItems(&$items, $mode = null);
}