<?php
namespace Modular\Relationships;

/**
 * Add Blocks to a GridList
 *
 * @method \DataList Blocks
 */
class HasGridList extends HasBlocks {
	const RelationshipName    = 'GridListBlocks';

	private static $cms_tab_name = 'Root.GridBlocks';
}
