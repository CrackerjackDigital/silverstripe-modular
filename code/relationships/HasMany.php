<?php
namespace Modular\Relationships;

use Modular\Fields\Field;
use Modular\Model;

class HasMany extends Field {
	const RelationshipName = '';
	const RelatedClassName = '';

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		return array_merge_recursive(
			$parent,
			[
				'has_many' => [
					$this->relationshipName() => $this->relatedClassName()
				]
			]
		);
	}

	/**
	 * When a page with blocks is published we also need to publish blocks. Blocks should also publish their 'sub' blocks.
	 */
	public function onAfterPublish() {
		/** @var Model|\Versioned $block */
		foreach ($this()->{$this->relationshipName()}() as $block) {
			if ($block->hasExtension('Versioned')) {
				$block->publish('Stage', 'Live', false);
			}
		}
	}

	protected function relationshipName() {
		return static::RelationshipName;
	}

	protected function relatedClassName() {
		return static::RelatedClassName;
	}

}