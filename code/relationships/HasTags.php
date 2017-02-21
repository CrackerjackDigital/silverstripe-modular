<?php
namespace Modular\Relationships;

/**
 * Adds a multiple free text Tags relationship TagField to Tag model to extended model.
 *
 * @package Modular\Fields
 */

use Modular\Models\Tag;

class HasTags extends HasManyMany {
	const RelationshipName = 'Tags';
	const RelatedClassName = 'Modular\Models\Tag';

	private static $multiple_tags = true;

	private static $can_create_tags = true;

	private static $allow_sorting = false;

	public function cmsFields($mode = null) {
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