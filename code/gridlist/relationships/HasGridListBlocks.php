<?php
namespace Modular\Relationships;

use Modular\Relationships\HasBlocks;

/**
 * Add manually curated blocks to a grid list.
 *
 * @package Modular\GridList
 */
class HasGridListBlocks extends HasBlocks {
	const RelationshipName = 'GridListBlocks';

	/**
	 * Provides Blocks for the GridList via GridListBlocks relationship
	 *
	 * @return \SS_List
	 */
	public function provideGridListItems() {
		return $this->related();
	}

}