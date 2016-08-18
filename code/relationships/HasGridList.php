<?php
namespace Modular\Relationships;

/**
 * Add a gridfield to which blocks can be added and managed.
 *
 * @method \DataList Blocks
 */
class HasGridList extends ManyMany {
	const RelationshipName    = 'GridListBlocks';
	const RelatedClassName    = 'Modular\Blocks\Block';
	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

	private static $cms_tab_name = 'Root.GridBlocks';
}