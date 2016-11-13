<?php
namespace Modular\Relationships;

use Modular\GridList\Interfaces\ItemsProvider;
use Modular\GridList\Interfaces\ItemsSequencer;

/**
 * Add blocks manually to a grid list items at the start.
 *
 * @package Modular\GridList
 */
class HasGridListBlocks extends HasBlocks implements ItemsSequencer, ItemsProvider {
	const RelationshipName    = 'GridListBlocks';
	const GridFieldConfigName = 'Modular\GridField\HasGridListBlocksGridFieldConfig';

	/**
	 * Returns the Blocks GridListBlocks
	 * @return mixed
	 */
	public function provideGridListItems($parameters = []) {
		return $this()->{static::RelationshipName}()->Sort(HasBlocks::SortFieldName);
	}
	/**
	 * Inserts manually added blocks at front of list.
	 *
	 * @param \ArrayList|\DataList $items
	 * @param                      $filters
	 * @param array                $parameters
	 */
	public function sequenceGridListItems(&$items, $filters, &$parameters = []) {
		$out = new \ArrayList();

		$blocks = $this->related()->Sort('Sort desc');
		foreach ($blocks as $block) {
			$out->push($block);
		}
		foreach ($items as $item) {
			$out->push($item);
		}
		$items = $out;
	}
}