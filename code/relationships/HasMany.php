<?php
namespace Modular\Relationships;

use Modular\Model;

class HasMany extends RelatedModels {
	const GridFieldConfigName = 'Modular\GridField\HasManyGridFieldConfig';
	
	public function extraStatics($class = null, $extension = null) {
		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [],
			[
				'has_many' => [
					static::RelationshipName => static::RelatedClassName
				]
			]
		);
	}

	/**
	 * When a page with blocks is published we also need to publish blocks. Blocks should also publish their 'sub' blocks.
	 */
	public function onAfterPublish() {
		/** @var Model|\Versioned $block */
		foreach ($this()->{static::RelationshipName}() as $block) {
			if ($block->hasExtension('Versioned')) {
				$block->publish('Stage', 'Live', false);
			}
		}
	}

}