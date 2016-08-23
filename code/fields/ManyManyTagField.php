<?php
namespace Modular\Fields;
use Modular\Relationships\HasManyMany;

/**
 * Adds a tag field representation of a HasManyMany relationship
 *
 * @package Modular\Fields
 */

class HasManyManyTagField extends HasManyMany {
	private static $multiple_tags = true;
	private static $can_create_tags = true;

	public function cmsFields() {
		return [
			(new \TagField(
				static::RelationshipName,
				'',
				$this->availableTags()
			))->setIsMultiple(
				(bool) $this->config()->get('multiple_tags')
			)->setCanCreate(
				(bool) $this->config()->get('can_create_tags')
			),
		];
	}

	protected function availableTags() {
		$tagClassName = static::RelatedClassName;
		return $tagClassName::get()->sort('Title');
	}
}