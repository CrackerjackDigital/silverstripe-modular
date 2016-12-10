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

	private static $sortable = false;

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

	/**
	 * Publish related tags when the owner is published.
	 */
	public function onAfterPublish() {
		if ($tags = $this->related()) {
			/** @var Tag|\Versioned $tag */
			foreach ($tags as $tag) {
				if ($tag->hasExtension('Versioned')) {
					$tag->publish('Stage', 'Live');
					// now ask the block to publish it's own blocks.
					$tag->extend('onAfterPublish');
				}
			}
		}
	}

	protected function availableTags() {
		return Tag::get()->sort('Title');
	}
}