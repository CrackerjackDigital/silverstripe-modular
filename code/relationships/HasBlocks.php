<?php
namespace Modular\Relationships;

use Modular\Blocks\Block;
use Modular\Fields\Field;

/**
 * Add a gridfield to which blocks can be added and managed.
 *
 * @method \DataList Blocks
 */
class HasBlocks extends Field {
	const RelationshipName = 'Blocks';
	const BlockClassName = 'Modular\Blocks\Block';
	const GridFieldConfigName = 'Modular\GridField\HasBlocksGridFieldConfig';

	private static $cms_tab_name = 'Root.ContentBlocks';

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];
		return array_merge_recursive(
			$parent,
			[
				'many_many' => [
					static::RelationshipName => static::BlockClassName
				],
			    'many_many_extraFields' => [
				    self::RelationshipName => [
					    self::GridFieldOrderableRowsFieldName => 'Int',
				    ],
			    ]
		    ]
		);
	}

	public function cmsFields() {
		return $this()->isInDB()
		? [$this->gridField(static::RelationshipName)]
		: [$this->saveMasterHint()];
	}

	/**
	 * When a page with blocks is published we also need to publish blocks. Blocks should also publish their 'sub' blocks.
	 */
	public function onAfterPublish() {
		/** @var Block|\Versioned $block */
		foreach ($this()->Blocks() as $block) {
			if ($block->hasExtension('Versioned')) {
				$block->publish('Stage', 'Live', false);
			}
		}
	}
}