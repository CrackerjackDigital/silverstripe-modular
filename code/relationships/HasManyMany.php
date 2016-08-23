<?php
namespace Modular\Relationships;

use Modular\Fields\Field;
use Modular\Model;

class HasManyMany extends Field {
	const RelationshipName = '';
	const RelatedClassName = '';

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		$extra = [];

		if ($this->config()->get('sortable')) {
			$extra = [
				'many_many_extraFields' => [
					$this->relationshipName() => [
						static::GridFieldOrderableRowsFieldName => 'Int',
					],
				],
			];
		}

		return array_merge_recursive(
			$parent,
			$extra,
			[
				'many_many' => [
					$this->relationshipName() => $this->relatedClassName(),
				],
			]
		);
	}

	protected function relationshipName() {
		return static::RelationshipName;
	}

	protected function relatedClassName() {
		return static::RelatedClassName;
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
}