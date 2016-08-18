<?php
namespace Modular\Relationships;

use Modular\Fields\GridField;
use Modular\Model;

class HasMany extends GridField {

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension);

		return array_merge_recursive(
			$parent,
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