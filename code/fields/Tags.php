<?php
namespace Modular\Fields;

/**
 * Adds a multiple free text Tags relationship TagField to Tag model to extended model.
 *
 * @package Modular\Fields
 */

use Modular\Models\Tag;

class Tags extends Field {
	const RelationshipName = 'Tags';
	const RelatedClassName = 'Modular\Models\Tag';

	private static $multiple_tags = true;
	private static $can_create_tags = true;

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];
		return array_merge_recursive(
			$parent,
			[
				'many_many' => [
					static::RelationshipName => static::RelatedClassName,
				],
			]
		);
	}

	public function cmsFields() {
		return [
			(new \TagField(
				static::RelationshipName,
				'',
				$this->availableTags()
			))->setIsMultiple(
				(bool)$this->config()->get('multiple_tags')
			)->setCanCreate(
				(bool)$this->config()->get('can_create_tags')
			),
		];
	}

	protected function availableTags() {
		return Tag::get()->sort('Title');
	}
}