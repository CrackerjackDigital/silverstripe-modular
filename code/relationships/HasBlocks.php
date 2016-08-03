<?php
namespace Modular\Relationships;

use Modular\Fields;

/**
 * Add a gridfield to which blocks can be added and managed.
 *
 * @method \DataList Blocks
 */
class HasBlocks extends Fields {
	const RelationshipName = 'Blocks';

	private static $many_many = [
		self::RelationshipName => 'BlockModel',
	];
	private static $many_many_extraFields = [
		self::RelationshipName => [
			self::GridFieldOrderableRowsFieldName => 'Int',
		],
	];

	private static $cms_tab_name = 'Root.ContentBlocks';

	public function cmsFields() {
		return $this()->isInDB()
		? [$this->gridField(self::RelationshipName)]
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