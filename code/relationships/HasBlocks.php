<?php
namespace Modular\Relationships;

/**
 * Add a gridfield to which blocks can be added and managed.
 *
 * @method \DataList Blocks
 */
class HasBlocks extends HasManyMany {
	const RelationshipName    = 'Blocks';
	const RelatedClassName    = 'Modular\Blocks\Block';
	const GridFieldConfigName = 'Modular\GridField\HasBlocksGridFieldConfig';

	private static $cms_tab_name = 'Root.ContentBlocks';
}